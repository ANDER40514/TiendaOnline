<?php
header('Content-Type: application/json; charset=UTF-8');

session_set_cookie_params(0);
session_start();

// ✅ Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// ✅ Leer datos enviados
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'JSON inválido']);
    exit;
}

// ✅ Conectar a BD
require_once __DIR__ . '/../../../api/db.php';

// ✅ Validaciones básicas
if (empty($data['items']) || !is_array($data['items'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan items en la orden']);
    exit;
}

// ✅ Requiere sesión activa
if (empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado. Debe iniciar sesión para realizar compras.']);
    exit;
}

// ✅ Datos del cliente desde sesión
$sess = $_SESSION['user'];
$id_cliente = intval($sess['id_cliente'] ?? 0);
$cliente_nombre = $conn->real_escape_string($sess['cliente'] ?? '');
$cliente_email = $conn->real_escape_string($sess['email'] ?? '');
$cliente_direccion = $conn->real_escape_string($sess['direccion'] ?? '');
$cliente_telefono = $conn->real_escape_string($sess['telefono'] ?? '');

// ✅ Iniciar transacción
$conn->begin_transaction();
$messages = [];

try {
    foreach ($data['items'] as $it) {
        $id = intval($it['id'] ?? $it['id_juego'] ?? 0);
        $cantidad = intval($it['cantidad'] ?? 0);
        $precio = floatval($it['precio'] ?? 0);
        $monto = $precio * $cantidad;
        $monto_sql = number_format($monto, 2, '.', '');

        if ($id <= 0 || $cantidad <= 0 || $precio <= 0) {
            throw new Exception("Datos inválidos para el item.");
        }

        // ✅ Comprobar stock antes del procedure
        $resStock = $conn->query("SELECT cantidad FROM inventario WHERE id_juego = $id");
        $rowStock = $resStock->fetch_assoc();
        if (!$rowStock || intval($rowStock['cantidad']) < $cantidad) {
            throw new Exception("Stock insuficiente para el juego ID $id.");
        }

        // ✅ Ejecutar procedure
        $sql = "CALL sp_compra($id, $cantidad, $monto_sql, $id_cliente)";
        if (!$conn->multi_query($sql)) {
            throw new Exception('Error ejecutando procedimiento: ' . $conn->error);
        }

        // ✅ Limpiar resultados del procedure
        $collected = [];
        do {
            if ($res = $conn->store_result()) {
                while ($row = $res->fetch_assoc()) {
                    foreach ($row as $val) $collected[] = $val;
                }
                $res->free();
            }
        } while ($conn->more_results() && $conn->next_result());

        // ✅ Determinar mensaje
        $msg = '';
        foreach ($collected as $c) {
            if (strlen(trim((string)$c)) > 0) {
                $msg = $c;
                break;
            }
        }
        if ($msg === '' && !empty($collected)) {
            $msg = end($collected);
        }

        $messages[] = ['id' => $id, 'message' => $msg, 'monto' => $monto];

        if (strpos($msg, 'Compra realizada correctamente') === false) {
            throw new Exception("Error en item $id: $msg");
        }
    }

    // ✅ Commit si todo OK
    $conn->commit();

    // ✅ Guardar copia de la orden en archivo JSON (backup)
    $purchasesFile = __DIR__ . '/../../data/purchases.json';
    $backupCliente = [
        'nombre' => $cliente_nombre,
        'email' => $cliente_email,
        'direccion' => $cliente_direccion,
        'telefono' => $cliente_telefono
    ];
    $backup = [
        'cliente' => $backupCliente,
        'items' => $data['items'],
        'total' => $data['total'] ?? array_sum(array_map(fn($i) => $i['precio'] * $i['cantidad'], $data['items'])),
        'fecha' => date('c'),
        'db_messages' => $messages
    ];

    $all = [];
    if (file_exists($purchasesFile)) {
        $all = json_decode(file_get_contents($purchasesFile), true) ?: [];
    }
    $all[] = $backup;
    file_put_contents($purchasesFile, json_encode($all, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);

    echo json_encode(['ok' => true, 'messages' => $messages]);
    exit;
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage(), 'messages' => $messages]);
    exit;
}
