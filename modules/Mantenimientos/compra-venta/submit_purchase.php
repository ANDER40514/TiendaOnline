<?php
header('Content-Type: application/json; charset=UTF-8');

session_set_cookie_params(0);
session_start();

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'JSON inválido']);
    exit;
}

// Conectar a BD
require_once __DIR__ . '/../../../api/db.php';

// Validaciones básicas: siempre se requieren items; cliente puede venir desde sesión o desde payload
if (empty($data['items']) || !is_array($data['items'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan items en la orden']);
    exit;
}

// Require logged-in user. The purchase workflow uses server-side session customer data.
if (empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado. Debe iniciar sesión para realizar compras.']);
    exit;
}

// If user is logged in, prefer server-side session data for cliente
$useSession = !empty($_SESSION['user']);
if (!$useSession) {
    if (empty($data['cliente']['nombre']) || empty($data['cliente']['email'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Faltan datos del cliente (nombre/email)']);
        exit;
    }
}

$conn->begin_transaction();
$messages = [];
try {
    // Use session user's id_cliente for FK in venta and for stored procedure
    $sess = $_SESSION['user'];
    $id_cliente = isset($sess['id_cliente']) ? intval($sess['id_cliente']) : 0;
    $cliente_nombre = isset($sess['cliente']) ? $conn->real_escape_string($sess['cliente']) : '';
    $cliente_email = isset($sess['email']) ? $conn->real_escape_string($sess['email']) : '';
    $cliente_direccion = isset($sess['direccion']) ? $conn->real_escape_string($sess['direccion']) : '';
    $cliente_telefono = isset($sess['telefono']) ? $conn->real_escape_string($sess['telefono']) : '';

    foreach ($data['items'] as $it) {
        $id = intval($it['id']);
        $cantidad = intval($it['cantidad']);
        $monto = floatval($it['precio']) * $cantidad;

        // Formatear monto con punto decimal para MySQL
        $monto_sql = number_format($monto, 2, '.', '');

    // Ejecutar el procedure con parámetros esperados: id_juego, cantidad, monto, id_cliente
    // Pasamos el id numérico del cliente (FK) desde la sesión para evitar problemas de integridad referencial
    $sql = "CALL sp_compra($id, $cantidad, $monto_sql, $id_cliente)";

        $msg = '';
        if (!$conn->multi_query($sql)) {
            throw new Exception('Error ejecutando procedimiento: ' . $conn->error);
        }

        // Recolectar valores retornados por los SELECT dentro del procedure (si los hay)
        $collected = [];
        do {
            if ($res = $conn->store_result()) {
                while ($row = $res->fetch_assoc()) {
                    foreach ($row as $val) {
                        $collected[] = $val;
                    }
                }
                $res->free();
            }
        } while ($conn->more_results() && $conn->next_result());

        // Elegir el primer valor no vacío como mensaje, o el último si todos son vacíos
        foreach ($collected as $c) {
            if (strlen(trim((string)$c)) > 0) {
                $msg = $c;
                break;
            }
        }
        if ($msg === '' && !empty($collected)) {
            $msg = end($collected);
        }

        // Store a sanitized message only (do not expose raw SQL or DB rows via API)
        $messages[] = ['id' => $id, 'message' => $msg, 'monto' => $monto];

        // Si el mensaje no indica compra exitosa, abortar
        if (strpos($msg, 'Compra realizada correctamente') === false) {
            throw new Exception('Error en item ' . $id . ': ' . $msg);
        }
    }

    // Si todo bien, commit
    $conn->commit();

    // Guardar copia de la orden en archivo (backup) usando los datos finales del cliente
    $purchasesFile = __DIR__ . '/../../data/purchases.json';
    if (!is_dir(dirname($purchasesFile))) mkdir(dirname($purchasesFile), 0755, true);
    $backupCliente = ['nombre' => $cliente_nombre, 'email' => $cliente_email, 'direccion' => $cliente_direccion, 'telefono' => $cliente_telefono];
    $backup = ['cliente' => $backupCliente, 'items' => $data['items'], 'total' => isset($data['total']) ? $data['total'] : array_sum(array_map(function ($i) {
        return $i['precio'] * $i['cantidad'];
    }, $data['items'])), 'fecha' => date('c'), 'db_messages' => $messages];
    // append safe
    $fp = fopen($purchasesFile, 'c+');
    if ($fp) {
        if (flock($fp, LOCK_EX)) {
            $contents = stream_get_contents($fp);
            $all = $contents ? (json_decode($contents, true) ?: []) : [];
            $all[] = $backup;
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, json_encode($all, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            fflush($fp);
            flock($fp, LOCK_UN);
        }
        fclose($fp);
    }

    echo json_encode(['ok' => true, 'messages' => $messages]);
    exit;
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage(), 'messages' => $messages]);
    exit;
}
