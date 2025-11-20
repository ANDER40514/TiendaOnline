<?php
require_once __DIR__ . '/../models/InventarioModel.php';

class InventarioController
{
    private $model;

    public function __construct($db)
    {
        // $db debe ser la conexión mysqli (tu $conn)
        $this->model = new InventarioModel($db);
        header('Content-Type: application/json; charset=UTF-8');
    }

    public function handleRequest($method, $id = null)
    {
        try {
            switch ($method) {
                case 'GET':
                    $this->get($id);
                    break;
                case 'POST':
                    $this->post();
                    break;
                case 'PUT':
                    $this->put($id);
                    break;
                case 'DELETE':
                    $this->delete($id);
                    break;
                default:
                    http_response_code(405);
                    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
            }
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno: ' . $e->getMessage()]);
        }
    }

    private function get($id)
    {
        if ($id) {
            $data = $this->model->getById($id);
            if ($data) {
                echo json_encode(['ok' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(404);
                echo json_encode(['ok' => false, 'error' => 'Inventario no encontrado']);
            }
        } else {
            $res = $this->model->getAll();
            echo json_encode(['ok' => true, 'data' => $res], JSON_UNESCAPED_UNICODE);
        }
    }

    private function post()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'JSON inválido']);
            return;
        }

        $result = $this->model->create($data);
        if (isset($result['ok']) && $result['ok']) {
            http_response_code(201);
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => $result['error'] ?? 'Error al crear inventario']);
        }
    }

    private function put($id)
    {
        if (!$id) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Falta el parámetro ID']);
            return;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'JSON inválido']);
            return;
        }
        $result = $this->model->update($id, $data);
        if (isset($result['ok']) && $result['ok']) {
            echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => $result['error'] ?? 'Error al actualizar inventario']);
        }
    }

    private function delete($id)
    {
        if (!$id) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Falta el parámetro ID']);
            return;
        }
        $result = $this->model->delete($id);
        if (isset($result['ok']) && $result['ok']) {
            echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => $result['error'] ?? 'Error al eliminar inventario']);
        }
    }
}
