<?php
require_once __DIR__ . '/../models/ConsolaModel.php';

class ConsolasController
{
    private $model;

    public function __construct($db)
    {
        $this->model = new ConsolaModel($db);

        header('Content-Type: application/json; charset=UTF-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
    }

    public function handleRequest($id = null)
    {
        $method = $_SERVER['REQUEST_METHOD'];

        switch ($method) {
            case 'GET':
                $this->get($id);
                break;

            case 'POST':
                $this->post();
                break;

            case 'PUT':
                if ($id === null) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Falta el ID de la consola']);
                    return;
                }
                $this->put($id);
                break;

            case 'DELETE':
                if ($id === null) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Falta el ID de la consola']);
                    return;
                }
                $this->delete($id);
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

    private function get($id = null)
    {
        if ($id !== null) {
            $data = $this->model->getById($id);
            if ($data) {
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Consola no encontrada']);
            }
        } else {
            $data = $this->model->getAll();
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
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
        http_response_code(isset($result['ok']) ? 201 : 400);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    private function put($id)
    {
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
        $result = $this->model->delete($id);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }
}
