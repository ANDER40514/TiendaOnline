<?php
header('Content-Type: application/json; charset=UTF-8');

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'JSON inválido']);
    exit;
}

// Validaciones básicas
if (empty($data['cliente']['nombre']) || empty($data['cliente']['email']) || empty($data['items']) || !is_array($data['items'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan datos requeridos en la orden']);
    exit;
}

// Conectar a BD
require_once __DIR__ . '/../../api/db.php';

$conn->begin_transaction();
$messages = [];
try {
    // Prepara datos del cliente (escapados)
    $cliente_nombre = isset($data['cliente']['nombre']) ? $conn->real_escape_string($data['cliente']['nombre']) : '';
    $cliente_email = isset($data['cliente']['email']) ? $conn->real_escape_string($data['cliente']['email']) : '';
    $cliente_direccion = isset($data['cliente']['direccion']) ? $conn->real_escape_string($data['cliente']['direccion']) : '';
    $cliente_telefono = isset($data['cliente']['telefono']) ? $conn->real_escape_string($data['cliente']['telefono']) : '';

    foreach ($data['items'] as $it) {
        $id = intval($it['id']);
        $cantidad = intval($it['cantidad']);
        $monto = floatval($it['precio']) * $cantidad;

        // Formatear monto con punto decimal para MySQL
        $monto_sql = number_format($monto, 2, '.', '');

        // Ejecutar el procedure con parámetros de cliente usando multi_query
        $sql = "CALL sp_compra($id, $cantidad, $monto_sql, '$cliente_nombre', '$cliente_email', '$cliente_direccion', '$cliente_telefono')";

        $msg = '';
        if (!$conn->multi_query($sql)) {
            throw new Exception('Error ejecutando procedimiento: ' . $conn->error);
        }

        // Recolectar valores retornados por los SELECT dentro del procedure (si los hay)
        $collected = [];
        do {
            if ($res = $conn->store_result()) {
                while ($row = $res->fetch_assoc()) {
                    foreach ($row as $val) {
                        $collected[] = $val;
                    }
                }
                $res->free();
            }
        } while ($conn->more_results() && $conn->next_result());

        // Elegir el primer valor no vacío como mensaje, o el último si todos son vacíos
        foreach ($collected as $c) {
            if (strlen(trim((string)$c)) > 0) { $msg = $c; break; }
        }
        if ($msg === '' && !empty($collected)) {
            $msg = end($collected);
        }

        $messages[] = ['id' => $id, 'message' => $msg, 'monto' => $monto, 'collected' => $collected, 'sql' => $sql];

        // Si el mensaje no indica compra exitosa, abortar
        if (strpos($msg, 'Compra realizada correctamente') === false) {
            throw new Exception('Error en item ' . $id . ': ' . $msg . ' -- collected: ' . json_encode($collected) . ' -- sql: ' . $sql);
        }
    }

    // Si todo bien, commit
    $conn->commit();

    // Guardar copia de la orden en archivo (backup)
    $purchasesFile = __DIR__ . '/../../data/purchases.json';
    if (!is_dir(dirname($purchasesFile))) mkdir(dirname($purchasesFile), 0755, true);
    $backup = ['cliente' => $data['cliente'], 'items' => $data['items'], 'total' => isset($data['total']) ? $data['total'] : array_sum(array_map(function($i){return $i['precio']*$i['cantidad'];}, $data['items'])), 'fecha' => date('c'), 'db_messages' => $messages];
    // append safe
    $fp = fopen($purchasesFile, 'c+');
    if ($fp) {
        if (flock($fp, LOCK_EX)) {
            $contents = stream_get_contents($fp);
            $all = $contents ? (json_decode($contents, true) ?: []) : [];
            $all[] = $backup;
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, json_encode($all, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            fflush($fp);
            flock($fp, LOCK_UN);
        }
        fclose($fp);
    }

    echo json_encode(['ok' => true, 'messages' => $messages]);
    exit;

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage(), 'messages' => $messages]);
    exit;
}

?>