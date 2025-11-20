<?php
require_once __DIR__ . '/../models/RolModel.php';

class RolController
{
    public function handleRequest($id = null)
    {
        $method = $_SERVER['REQUEST_METHOD'];

        try {
            switch ($method) {
                case 'GET':
                    $id !== null ? $this->getById($id) : $this->getAll();
                    break;

                case 'POST':
                    $input = json_decode(file_get_contents('php://input'), true);
                    $this->create($input);
                    break;

                case 'PUT':
                    $input = json_decode(file_get_contents('php://input'), true);
                    $id !== null ? $this->update($id, $input)
                        : $this->errorResponse('Falta el ID del rol', 400);
                    break;

                case 'DELETE':
                    $id !== null ? $this->delete($id)
                        : $this->errorResponse('Falta el ID del rol', 400);
                    break;

                case 'OPTIONS':
                    http_response_code(200);
                    echo json_encode(['ok' => true]);
                    break;

                default:
                    $this->errorResponse('Método no permitido', 405);
                    break;
            }
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }

    private function getAll()
    {
        $res = RolModel::obtenerTodos();
        http_response_code($res['ok'] ? 200 : 400);
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

    private function errorResponse(string $msg, int $code = 500)
    {
        http_response_code($code);
        echo json_encode(['ok' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
