<?php
require_once __DIR__ . '/../models/ClienteModel.php';

class ClienteController
{
    public function handleRequest($id = null)
    {
        $method = $_SERVER['REQUEST_METHOD'];

        // Obtenemos los datos enviados
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        switch ($method) {
            case 'GET':
                if ($id !== null) {
                    $this->getById($id);
                } else {
                    $this->getAll();
                }
                break;

            case 'POST':
                // Determinar si es login o registro
                $action = $input['action'] ?? null;

                if ($action === 'login') {
                    $this->login($input);
                } elseif ($action === 'register') {
                    $this->create($input);
                } else {
                    http_response_code(400);
                    echo json_encode(['ok' => false, 'error' => 'Acción POST no válida']);
                }
                break;

            case 'PUT':
                if ($id !== null) {
                    $this->update($id, $input);
                } else {
                    http_response_code(400);
                    echo json_encode(['ok' => false, 'error' => 'Falta el ID del usuario']);
                }
                break;

            case 'DELETE':
                if ($id !== null) {
                    $this->delete($id);
                } else {
                    http_response_code(400);
                    echo json_encode(['ok' => false, 'error' => 'Falta el ID del usuario']);
                }
                break;

            case 'OPTIONS':
                http_response_code(200);
                echo json_encode(['ok' => true]);
                break;

            default:
                http_response_code(405);
                echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
                break;
        }
    }

    // =========================
    // MÉTODOS CRUD
    // =========================
    private function getAll()
    {
        $res = ClienteModel::obtenerTodos();
        http_response_code($res['ok'] ? 200 : 404);
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    private function getById(int $id)
    {
        $res = ClienteModel::obtenerPorId($id);
        http_response_code($res['ok'] ? 200 : 404);
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    private function create(array $input)
    {
        $res = ClienteModel::crear($input);
        http_response_code($res['ok'] ? 201 : 400);
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    private function update(int $id, array $input)
    {
        $res = ClienteModel::actualizar($id, $input);
        http_response_code($res['ok'] ? 200 : 400);
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    private function delete(int $id)
    {
        $res = ClienteModel::eliminar($id);
        http_response_code($res['ok'] ? 200 : 400);
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    // =========================
    // LOGIN
    // =========================
private function login(array $input)
{
    $usuario = $input['usuario'] ?? '';
    $password = $input['password'] ?? '';

    if (!$usuario || !$password) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Usuario y contraseña requeridos']);
        return;
    }

    $res = ClienteModel::autenticar($usuario, $password);

    if ($res['ok']) {
        http_response_code(200);
        echo json_encode(['ok' => true, 'data' => $res['user']], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => $res['error']], JSON_UNESCAPED_UNICODE);
    }
}

}
