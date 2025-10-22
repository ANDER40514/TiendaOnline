<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once 'db.php';

// GET /api/inventario.php?id=123  -> { id_juego:123, cantidad: 5 }
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare('SELECT id_juego, cantidad FROM inventario WHERE id_juego = ? LIMIT 1');
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Error interno: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        echo json_encode($row, JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Inventario no encontrado']);
    }
    $stmt->close();
    $conn->close();
    exit;
}

// listar todo
$result = $conn->query('SELECT id_juego, cantidad FROM inventario');
if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => $conn->error]);
    $conn->close();
    exit;
}
$rows = [];
while ($r = $result->fetch_assoc()) $rows[] = $r;
echo json_encode($rows, JSON_UNESCAPED_UNICODE);
$conn->close();
exit;

?>