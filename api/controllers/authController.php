<?php
require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../helpers/response.php';

header('Content-Type: application/json');

// =======================
// SESIÓN Y CONFIG
// =======================
$sessionPath = realpath(__DIR__ . '/../../sessions'); 
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}
session_name('PHPSESSID');
session_save_path($sessionPath);

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '127.0.0.1', // o 'localhost'
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

// =======================
// CONTROL DE MÉTODO
// =======================
$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit;
}

// =======================
// ENTRADA JSON
// =======================
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? null;
if (!$action) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Acción no especificada']);
    exit;
}

// =======================
// ACCIONES
// =======================
switch ($action) {
    case 'register':
        $result = ClienteModel::crear($input);
        http_response_code($result['ok'] ? 200 : 400);
        echo json_encode($result);
        break;

    case 'login':
        if (empty($input['usuario']) || empty($input['password'])) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Usuario o contraseña faltante']);
            exit;
        }

        $result = ClienteModel::autenticar($input['usuario'], $input['password']);

        if ($result['ok'] && !empty($result['data'])) {
            // Guardar en la sesión
            $_SESSION['user'] = $result['data'];
            session_write_close();

            error_log("LOGIN -> Sesión creada: " . print_r($_SESSION, true));

            http_response_code(200);
            echo json_encode([
                'ok' => true,
                'message' => 'Inicio de sesión exitoso',
                'data' => $result['data']
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['ok' => false, 'error' => 'Credenciales inválidas']);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Acción no válida']);
        break;
}
