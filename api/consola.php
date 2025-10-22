<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

function valid_hex($h) {
    return preg_match('/^#[0-9A-Fa-f]{6}$/', $h);
}

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $stmt = $conn->prepare('SELECT id_consola, code, nombre, consola_color FROM consola WHERE id_consola = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res->fetch_assoc();
            if ($row) {
                if (!valid_hex($row['consola_color'])) $row['consola_color'] = '#cccccc';
                echo json_encode($row, JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Consola no encontrada']);
            }
            $stmt->close();
            $conn->close();
            exit;
        }

        $result = $conn->query('SELECT id_consola, code, nombre, consola_color FROM consola');
        $rows = [];
        while ($r = $result->fetch_assoc()) {
            if (!valid_hex($r['consola_color'])) $r['consola_color'] = '#cccccc';
            $rows[] = $r;
        }
        echo json_encode($rows, JSON_UNESCAPED_UNICODE);
        $conn->close();
        exit;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) { http_response_code(400); echo json_encode(['error' => 'JSON inválido']); exit; }
        if (!isset($data['code'], $data['nombre'])) { http_response_code(400); echo json_encode(['error' => 'Faltan campos']); exit; }
        $code = $conn->real_escape_string($data['code']);
        $nombre = $conn->real_escape_string($data['nombre']);
        $color = isset($data['consola_color']) && valid_hex($data['consola_color']) ? $conn->real_escape_string($data['consola_color']) : '#cccccc';
        $stmt = $conn->prepare('INSERT INTO consola (code, nombre, consola_color) VALUES (?, ?, ?)');
        $stmt->bind_param('sss', $code, $nombre, $color);
        if ($stmt->execute()) {
            echo json_encode(['ok' => true, 'id' => $conn->insert_id]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => $conn->error]);
        }
        $stmt->close();
        $conn->close();
        exit;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['id'])) { http_response_code(400); echo json_encode(['error' => 'Faltan campos']); exit; }
        $id = intval($data['id']);
        $fields = [];
        $params = [];
        if (isset($data['code'])) { $fields[] = 'code=?'; $params[] = $conn->real_escape_string($data['code']); }
        if (isset($data['nombre'])) { $fields[] = 'nombre=?'; $params[] = $conn->real_escape_string($data['nombre']); }
        if (isset($data['consola_color'])) { $colorv = valid_hex($data['consola_color']) ? $data['consola_color'] : '#cccccc'; $fields[] = 'consola_color=?'; $params[] = $conn->real_escape_string($colorv); }
        if (empty($fields)) { echo json_encode(['mensaje' => 'Nada que actualizar']); exit; }
        $sql = 'UPDATE consola SET ' . implode(', ', $fields) . ' WHERE id_consola=?';
        $stmt = $conn->prepare($sql);
        // bind params dynamically
        $types = str_repeat('s', count($params)) . 'i';
        $values = $params;
        $values[] = $id;
        $stmt->bind_param($types, ...$values);
        if ($stmt->execute()) {
            echo json_encode(['ok' => true]);
        } else {
            http_response_code(500); echo json_encode(['error' => $conn->error]);
        }
        $stmt->close(); $conn->close(); exit;

    case 'DELETE':
        if (!isset($_GET['id'])) { http_response_code(400); echo json_encode(['error' => 'id requerido']); exit; }
        $id = intval($_GET['id']);
        $stmt = $conn->prepare('DELETE FROM consola WHERE id_consola=?');
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            echo json_encode(['ok' => true]);
        } else { http_response_code(500); echo json_encode(['error' => $conn->error]); }
        $stmt->close(); $conn->close(); exit;

    default:
        http_response_code(405); echo json_encode(['error' => 'Método no permitido']); exit;
}

?>
