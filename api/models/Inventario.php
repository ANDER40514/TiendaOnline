<?php
require_once __DIR__ . '/../config/database.php';

class InventarioModel {

    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getById($id) {
        $stmt = $this->conn->prepare('SELECT id_juego, cantidad FROM inventario WHERE id_juego = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = $res->fetch_assoc();
        $stmt->close();
        return $data;
    }

    public function getAll() {
        $result = $this->conn->query('SELECT id_juego, cantidad FROM inventario');
        $rows = [];
        while ($r = $result->fetch_assoc()) $rows[] = $r;
        return $rows;
    }

    public function create($data) {
        if (!isset($data['id_juego']) || !isset($data['cantidad'])) {
            return ['error' => 'Campos requeridos faltantes'];
        }

        $id_juego = $data['id_juego'];
        $cantidad = $data['cantidad'];

        $stmt = $this->conn->prepare('INSERT INTO inventario (id_juego, cantidad) VALUES (?, ?)');
        $stmt->bind_param('ii', $id_juego, $cantidad);

        if ($stmt->execute()) {
            return ['ok' => true, 'id_juego' => $id_juego];
        }

        return ['error' => $stmt->error];
    }

    public function update($id, $data) {
        if (!isset($data['cantidad'])) {
            return ['error' => 'Campo cantidad requerido'];
        }

        $cantidad = $data['cantidad'];

        $stmt = $this->conn->prepare('UPDATE inventario SET cantidad = ? WHERE id_juego = ?');
        $stmt->bind_param('ii', $cantidad, $id);

        if ($stmt->execute()) {
            return ['ok' => true];
        }

        return ['error' => $stmt->error];
    }

    public function delete($id) {
        $stmt = $this->conn->prepare('DELETE FROM inventario WHERE id_juego = ?');
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            return ['ok' => true];
        }

        return ['error' => $stmt->error];
    }
}
?>
