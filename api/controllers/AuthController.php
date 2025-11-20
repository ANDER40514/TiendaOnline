<?php
require_once __DIR__ . '/../models/ClienteModel.php';
require_once __DIR__ . '/../models/RolModel.php';
require_once __DIR__ . '/../../sessions';

class AuthController
{
    public function handleRequest()
    {
        header('Content-Type: application/json; charset=UTF-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        session_name("PHPSESSID");

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => false,
            'httponly' => false,
            'samesite' => 'Lax'
        ]);

        session_save_path('/var/www/html/TiendaOnline/sessions');
        session_start();


        require __DIR__ . '/../';
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $action = $data['action'] ?? ($_GET['action'] ?? null);

        switch ($action) {
            case 'login':
                $this->login($data);
                break;

            case 'register':
                $this->register($data);
                break;

            case 'logout':
                $this->logout();
                break;

            default:
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Acción no válida']);
                break;
        }
    }

    private function login($data)
    {
        $usuario = $data['usuario'] ?? null;
        $password = $data['password'] ?? null;

        if (!$usuario || !$password) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Faltan campos']);
            return;
        }

        $auth = ClienteModel::autenticar($usuario, $password);

        if ($auth['ok']) {
            $_SESSION['user'] = $auth['user'];
            echo json_encode(['ok' => true, 'user' => $auth['user']]);
        } else {
            http_response_code(401);
            echo json_encode(['ok' => false, 'error' => $auth['error']]);
        }
    }

    private function register($data)
    {
        $usuario = $data['usuario'] ?? '';
        $password = $data['password'] ?? '';
        $email = $data['email'] ?? '';
        $rolId = $data['id_RolUsuario'] ?? null;

        if (!$usuario || !$password || !$email || !$rolId) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Faltan campos requeridos']);
            return;
        }

        $res = ClienteModel::crear([
            'usuario' => $usuario,
            'password' => $password,
            'email' => $email,
            'id_RolUsuario' => $rolId
        ]);

        if ($res['ok']) {
            http_response_code(201);
            echo json_encode(['ok' => true, 'id' => $res['id']]);
        } else {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => $res['error']]);
        }
    }

    private function logout()
    {
        session_start();
        session_unset();
        session_destroy();
        echo json_encode(['ok' => true, 'message' => 'Sesión cerrada']);
    }
}