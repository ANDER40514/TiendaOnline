<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/controllers/ClienteController.php';
require_once __DIR__ . '/controllers/RolController.php';
require_once __DIR__ . '/controllers/JuegoController.php';

// Crear conexión
$db = new Database();
$conn = $db->connect(); // <-- Esto devuelve la conexión válida

// =============================================
// Capturar el endpoint (por ejemplo ?endpoint=usuarios)
// =============================================
$endpoint = $_GET['endpoint'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// Manejar preflight (OPTIONS) para CORS
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// =============================================
// Enrutar según el endpoint
// =============================================
switch ($endpoint) {
    // ==== Clientes ====
    case 'clientes':
        require_once __DIR__ . '/controllers/ClienteController.php';
        $controller = new ClienteController();
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        $controller->handleRequest($id);
        break;

    // ==== ROLES ====
    case 'roles':
        require_once __DIR__ . '/controllers/RolController.php';
        $controller = new RolController();
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        $controller->handleRequest($id);
        break;

    // ==== JUEGOS ====
    case 'juegos':
        require_once __DIR__ . '/controllers/JuegoController.php';
        $controller = new JuegoController();
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        $controller->handleRequest($id);
        break;

    // ==== CONSOLAS ====
    case 'consolas':
        require_once __DIR__ . '/controllers/ConsolaController.php';
        require_once __DIR__ . '/config/database.php';
        $controller = new ConsolasController($conn);
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        $controller->handleRequest($id);
        break;

    // ==== INVENTARIO ====
    case 'inventario':
        require_once __DIR__ . '/controllers/InventarioController.php';
        require_once __DIR__ . '/config/database.php';
        $controller = new InventarioController($conn);
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        $controller->handleRequest($_SERVER['REQUEST_METHOD'], $id);
        break;

    // ==== ENDPOINT NO ENCONTRADO ====
    default:
        http_response_code(404);
        echo json_encode([
            'error' => 'Endpoint no encontrado',
            'endpoint' => $endpoint
        ]);
        break;
}