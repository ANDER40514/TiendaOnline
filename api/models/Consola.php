<?php
// models/Consola.php
require_once __DIR__ . '/../config/database.php';

class Consola
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    private function valid_hex($color)
    {
        return preg_match('/^#[0-9A-Fa-f]{6}$/', $color);
    }

    public function getAll()
    {
        $sql = 'SELECT id_consola, code, nombre, consola_color FROM consola';
        $result = $this->conn->query($sql);
        $rows = [];

        while ($r = $result->fetch_assoc()) {
            if (!$this->valid_hex($r['consola_color'])) {
                $r['consola_color'] = '#cccccc';
            }
            $rows[] = $r;
        }
        return $rows;
    }

    public function getById($id)
    {
        $stmt = $this->conn->prepare('SELECT id_consola, code, nombre, consola_color FROM consola WHERE id_consola = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();

        if ($row && !$this->valid_hex($row['consola_color'])) {
            $row['consola_color'] = '#cccccc';
        }

        return $row ?: null;
    }

    public function create($data)
    {
        if (empty($data['code']) || empty($data['nombre'])) {
            return ['error' => 'Campos requeridos faltantes'];
        }

        $code = $data['code'];
        $nombre = $data['nombre'];
        $color = isset($data['consola_color']) && $this->valid_hex($data['consola_color'])
            ? $data['consola_color']
            : '#cccccc';

        $stmt = $this->conn->prepare('INSERT INTO consola (code, nombre, consola_color) VALUES (?, ?, ?)');
        $stmt->bind_param('sss', $code, $nombre, $color);

        if ($stmt->execute()) {
            return ['ok' => true, 'id' => $this->conn->insert_id];
        }

        return ['error' => $this->conn->error];
    }

    public function update($id, $data)
    {
        $fields = [];
        $params = [];
        $types = '';

        if (isset($data['code'])) {
            $fields[] = 'code=?';
            $params[] = $data['code'];
            $types .= 's';
        }

        if (isset($data['nombre'])) {
            $fields[] = 'nombre=?';
            $params[] = $data['nombre'];
            $types .= 's';
        }

        if (isset($data['consola_color'])) {
            $color = $this->valid_hex($data['consola_color']) ? $data['consola_color'] : '#cccccc';
            $fields[] = 'consola_color=?';
            $params[] = $color;
            $types .= 's';
        }

        if (empty($fields)) {
            return ['mensaje' => 'Nada que actualizar'];
        }

        $sql = 'UPDATE consola SET ' . implode(', ', $fields) . ' WHERE id_consola=?';
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return ['error' => 'Error en la preparación: ' . $this->conn->error];
        }

        $params[] = $id;
        $types .= 'i';

        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                return ['ok' => true, 'updated' => $stmt->affected_rows];
            } else {
                return ['ok' => false, 'mensaje' => 'No se actualizó ningún registro'];
            }
        }

        return ['error' => $stmt->error];
    }


    public function delete($id)
    {
        $stmt = $this->conn->prepare('DELETE FROM consola WHERE id_consola = ?');
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            return ['ok' => true];
        }

        return ['error' => $this->conn->error];
    }
}
