<?php
require_once __DIR__ . '/../models/Rol.php';

class RolController
{
    public function handleRequest($id = null)
    {
        $method = $_SERVER['REQUEST_METHOD'];

        switch ($method) {
            case 'GET':
                if ($id !== null) {
                    $this->getById($id);
                } else {
                    $this->getAll();
                }
                break;

            case 'POST':
                $input = json_decode(file_get_contents('php://input'), true);
                $this->create($input);
                break;

            case 'PUT':
                $input = json_decode(file_get_contents('php://input'), true);
                if ($id !== null) {
                    $this->update($id, $input);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Falta el ID del rol']);
                }
                break;

            case 'DELETE':
                if ($id !== null) {
                    $this->delete($id);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Falta el ID del rol']);
                }
                break;

            case 'OPTIONS':
                http_response_code(200);
                echo json_encode(['ok' => true]);
                break;

            default:
                http_response_code(405);
                echo json_encode(['error' => 'Método no permitido']);
                break;
        }
    }

    private function getAll()
    {
        $res = RolModel::obtenerTodos();
        http_response_code($res['ok'] ? 200 : 404);
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    private function getById(int $id)
    {
        $res = RolModel::obtenerPorId($id);
        http_response_code($res['ok'] ? 200 : 404);
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    private function create(array $input)
    {
        $res = RolModel::crear($input);
        http_response_code($res['ok'] ? 201 : 400);
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    private function update(int $id, array $input)
    {
        $res = RolModel::actualizar($id, $input);
        http_response_code($res['ok'] ? 200 : 400);
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    private function delete(int $id)
    {
        $res = RolModel::eliminar($id);
        http_response_code($res['ok'] ? 200 : 400);
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }
}
?>
