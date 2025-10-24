<?php
header('Content-Type: application/json; charset=UTF-8');
require_once 'db.php';

$res = $conn->query('SELECT id_usuario, nombre, rol FROM usuarioRoles ORDER BY id_usuario');
$rows = [];
while ($r = $res->fetch_assoc()) $rows[] = $r;
echo json_encode($rows, JSON_UNESCAPED_UNICODE);
$conn->close();

?>
