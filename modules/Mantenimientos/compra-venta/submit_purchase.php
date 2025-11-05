<?php
// =======================================
// Compra - submit_purchase.php
// =======================================

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed_origins = ['http://127.0.0.1', 'http://localhost'];

if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header("Access-Control-Allow-Origin: http://127.0.0.1");
}
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// =======================================
// Configuración de sesión (unificada con login)
// =======================================
session_name('PHPSESSID');
session_save_path(__DIR__ . '/../../../sessions'); // usa la misma carpeta que el login
session_start();

// =======================================
// Verificación de sesión
// =======================================
if (empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado. Debe iniciar sesión.']);
    exit;
}

// =======================================
// Capturar JSON del body
// =======================================
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'JSON inválido o vacío.']);
    exit;
}

// =======================================
// Conexión a la BD
// =======================================
$rootPath = realpath(__DIR__ . '/../../../api/config/database.php');
if (!$rootPath || !file_exists($rootPath)) {
    http_response_code(500);
    echo json_encode(['error' => 'No se encontró la configuración de base de datos.']);
    exit;
}
require_once $rootPath;

$db = new Database();
$conn = $db->connect();
if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al conectar a la base de datos.']);
    exit;
}

// =======================================
// Datos del usuario autenticado
// =======================================
$user = $_SESSION['user'];
$id_cliente = intval($user['id_cliente'] ?? 0);

if ($id_cliente <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Usuario sin ID de cliente válido.']);
    exit;
}

// =======================================
// Validar orden
// =======================================
$items = $data['items'] ?? [];
$total = $data['total'] ?? 0;

if (empty($items)) {
    http_response_code(400);
    echo json_encode(['error' => 'No hay productos en la orden.']);
    exit;
}

// =======================================
// Procesar compra
// =======================================
$messages = [];

try {
    $conn->begin_transaction();

    foreach ($items as $it) {
        $id = intval($it['id'] ?? 0);
        $cantidad = intval($it['cantidad'] ?? 0);
        $precio = floatval($it['precio'] ?? 0);
        $monto = $precio * $cantidad;

        if ($id <= 0 || $cantidad <= 0) {
            throw new Exception("Datos inválidos para el producto.");
        }

        // Validar stock
        $resStock = $conn->query("SELECT cantidad FROM inventario WHERE id_juego = $id");
        $stock = $resStock ? intval(($resStock->fetch_assoc())['cantidad'] ?? 0) : 0;
        if ($stock < $cantidad) {
            throw new Exception("Stock insuficiente para el juego ID $id.");
        }

        // Llamar procedure
        $sql = "CALL sp_compra($id, $cantidad, $monto, $id_cliente)";
        if (!$conn->multi_query($sql)) {
            throw new Exception("Error ejecutando procedimiento: " . $conn->error);
        }

        // Extraer respuesta del procedure
        $msg = '';
        do {
            if ($res = $conn->store_result()) {
                while ($row = $res->fetch_assoc()) {
                    foreach ($row as $val) {
                        $msg = (string)$val;
                    }
                }
                $res->free();
            }
        } while ($conn->more_results() && $conn->next_result());

        $messages[] = [
            'id' => $id,
            'message' => $msg ?: 'Compra realizada correctamente',
            'monto' => $monto
        ];
    }

    $conn->commit();

    // =======================================
    // Guardar copia JSON local
    // =======================================
    $dataDir = __DIR__ . '/../../data';
    if (!is_dir($dataDir)) mkdir($dataDir, 0777, true);

    $file = $dataDir . '/purchases.json';
    $existing = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
    $existing[] = [
        'cliente' => $user,
        'items' => $items,
        'total' => $total,
        'fecha' => date('Y-m-d H:i:s'),
        'mensajes' => $messages
    ];
    file_put_contents($file, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    echo json_encode(['ok' => true, 'messages' => $messages]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
