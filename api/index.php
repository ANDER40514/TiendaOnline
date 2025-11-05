<?php
// ====================================
// CORS + CONFIGURACIÓN GLOBAL
// ====================================
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$isLocal = preg_match('/127\.0\.0\.1|localhost/', $origin);

if ($isLocal) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
} else {
    header("Access-Control-Allow-Origin: *");
}
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ====================================
// SESIÓN GLOBAL
// ====================================
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);

session_name('PHPSESSID');
session_save_path(__DIR__ . '/sessions');
if (!file_exists(session_save_path())) {
    mkdir(session_save_path(), 0777, true);
}

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// ====================================
// DEPENDENCIAS
// ====================================
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/controllers/ClienteController.php';
require_once __DIR__ . '/controllers/RolController.php';
require_once __DIR__ . '/controllers/JuegoController.php';
require_once __DIR__ . '/models/Consola.php';

$db = new Database();
$conn = $db->connect();

$endpoint = $_GET['endpoint'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// ====================================
// RUTEO PRINCIPAL
// ====================================
switch ($endpoint) {

    // =======================
    // CLIENTES (Login / CRUD)
    // =======================
    case 'clientes':
        require_once __DIR__ . '/models/Cliente.php';
        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        if ($method === 'GET') {
            if (isset($_GET['id'])) {
                $res = ClienteModel::obtenerPorId((int)$_GET['id']);
            } else {
                $res = ClienteModel::obtenerTodos();
            }
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            break;
        }

        if ($method === 'POST') {
            $action = $body['action'] ?? ($body['tipo'] ?? null);

            // === LOGIN ===
            if ($action === 'login') {
                $usuario = $body['usuario'] ?? $body['email'] ?? '';
                $password = $body['password'] ?? '';

                $auth = ClienteModel::autenticar($usuario, $password);

                if ($auth['ok']) {
                    $_SESSION['user'] = $auth['user'];
                    error_log("✅ Sesión creada: " . print_r($_SESSION, true));

                    echo json_encode([
                        'ok' => true,
                        'user' => $auth['user']
                    ]);
                } else {
                    http_response_code(401);
                    echo json_encode(['ok' => false, 'error' => $auth['error']]);
                }
                break;
            }

            // === REGISTRO ===
            if ($action === 'register') {
                $res = ClienteModel::crear($body);
                if ($res['ok']) {
                    echo json_encode(['ok' => true, 'id' => $res['id']]);
                } else {
                    http_response_code(400);
                    echo json_encode(['ok' => false, 'error' => $res['error']]);
                }
                break;
            }
        }

        if ($method === 'PUT') {
            $id = (int)($_GET['id'] ?? 0);
            $res = ClienteModel::actualizar($id, $body);
            echo json_encode($res);
            break;
        }

        if ($method === 'DELETE') {
            $id = (int)($_GET['id'] ?? 0);
            $res = ClienteModel::eliminar($id);
            echo json_encode($res);
            break;
        }

        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        break;

    // =======================
    // ROLES
    // =======================
    case 'roles':
        $controller = new RolController();
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        $controller->handleRequest($id);
        break;

    // =======================
    // JUEGOS
    // =======================
    case 'juegos':
        require_once __DIR__ . '/models/Juego.php';
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        if ($method === 'GET') {
            $res = isset($_GET['id'])
                ? JuegoModel::obtenerPorId((int)$_GET['id'])
                : JuegoModel::obtenerTodos();
        } elseif ($method === 'POST') {
            $res = JuegoModel::insertar($data);
        } elseif ($method === 'PUT') {
            $id = (int)($_GET['id'] ?? 0);
            $res = JuegoModel::actualizar($id, $data);
        } elseif ($method === 'DELETE') {
            $id = (int)($_GET['id'] ?? 0);
            $res = JuegoModel::eliminar($id);
        } else {
            http_response_code(405);
            $res = ['error' => 'Método no permitido'];
        }

        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        break;

    // =======================
    // CONSOLAS
    // =======================
    case 'consolas':
        $consola = new Consola($conn);
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        if ($method === 'GET') {
            echo json_encode($consola->getAll());
        } elseif ($method === 'POST') {
            echo json_encode($consola->create($data));
        } elseif ($method === 'PUT') {
            $id = (int)($_GET['id'] ?? 0);
            echo json_encode($consola->update($id, $data));
        } elseif ($method === 'DELETE') {
            $id = (int)($_GET['id'] ?? 0);
            echo json_encode($consola->delete($id));
        }
        break;

    // =======================
    // INVENTARIO
    // =======================
    case 'inventario':
        require_once __DIR__ . '/controllers/InventarioController.php';
        $controller = new InventarioController($conn);
        $controller->handleRequest($method, $_GET['id'] ?? null);
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint no encontrado']);
        break;
}
?>