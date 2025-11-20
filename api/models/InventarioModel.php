<?php
class InventarioModel
{

    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getById($id)
    {
        try {
            $stmt = $this->conn->prepare('SELECT id_juego, cantidad FROM inventario WHERE id_juego = ? LIMIT 1');
            if (!$stmt) return null;
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $res = $stmt->get_result();
            $data = $res->fetch_assoc();
            $stmt->close();
            return $data ?: null;
        } catch (\Throwable $e) {
            error_log("InventarioModel::getById error: " . $e->getMessage());
            return null;
        }
    }

    public function getAll()
    {
        try {
            $result = $this->conn->query('SELECT id_juego, cantidad FROM inventario');
            if (!$result) return [];
            $rows = [];
            while ($r = $result->fetch_assoc()) $rows[] = $r;
            return $rows;
        } catch (\Throwable $e) {
            error_log("InventarioModel::getAll error: " . $e->getMessage());
            return [];
        }
    }

    public function create($data)
    {
        try {
            if (!isset($data['id_juego']) || !isset($data['cantidad'])) {
                return ['ok' => false, 'error' => 'Campos requeridos faltantes'];
            }

            $id_juego = (int)$data['id_juego'];
            $cantidad = (int)$data['cantidad'];

            $stmt = $this->conn->prepare('INSERT INTO inventario (id_juego, cantidad) VALUES (?, ?)');
            if (!$stmt) return ['ok' => false, 'error' => $this->conn->error];
            $stmt->bind_param('ii', $id_juego, $cantidad);

            if ($stmt->execute()) {
                $stmt->close();
                return ['ok' => true, 'id_juego' => $id_juego];
            }

            $err = $stmt->error;
            $stmt->close();
            return ['ok' => false, 'error' => $err];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function update($id, $data)
    {
        try {
            if (!isset($data['cantidad'])) {
                return ['ok' => false, 'error' => 'Campo cantidad requerido'];
            }

            $cantidad = (int)$data['cantidad'];

            $stmt = $this->conn->prepare('UPDATE inventario SET cantidad = ? WHERE id_juego = ?');
            if (!$stmt) return ['ok' => false, 'error' => $this->conn->error];
            $stmt->bind_param('ii', $cantidad, $id);

            if ($stmt->execute()) {
                $affected = $stmt->affected_rows;
                $stmt->close();
                return ['ok' => true, 'affected' => $affected];
            }

            $err = $stmt->error;
            $stmt->close();
            return ['ok' => false, 'error' => $err];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function delete($id)
    {
        try {
            $stmt = $this->conn->prepare('DELETE FROM inventario WHERE id_juego = ?');
            if (!$stmt) return ['ok' => false, 'error' => $this->conn->error];
            $stmt->bind_param('i', $id);

            if ($stmt->execute()) {
                $stmt->close();
                return ['ok' => true];
            }

            $err = $stmt->error;
            $stmt->close();
            return ['ok' => false, 'error' => $err];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
