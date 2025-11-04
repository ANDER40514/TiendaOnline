<?php
require_once __DIR__ . '/../models/inventario.php';

class InventarioController
{
    private $model;

    public function __construct($db)
    {
        $this->model = new InventarioModel($db);
    }

    public function handleRequest($method, $id = null)
    {
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
                echo json_encode(['error' => 'Método no permitido']);
                break;
        }
    }

    private function get($id)
    {
        if ($id) {
            $data = $this->model->getById($id);
            if ($data) {
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Inventario no encontrado']);
            }
        } else {
            echo json_encode($this->model->getAll(), JSON_UNESCAPED_UNICODE);
        }
    }

    private function post()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            http_response_code(400);
            echo json_encode(['error' => 'JSON inválido']);
            return;
        }

        $result = $this->model->create($data);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    private function put($id)
    {
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Falta el parámetro ID']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            http_response_code(400);
            echo json_encode(['error' => 'JSON inválido']);
            return;
        }

        $result = $this->model->update($id, $data);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    private function delete($id)
    {
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Falta el parámetro ID']);
            return;
        }

        $result = $this->model->delete($id);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }
}
?>