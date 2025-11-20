<?php
require_once __DIR__ . '/../config/database.php';

class RolModel
{
    private static $conn;

    private static function getConnection()
    {
        if (!self::$conn) {
            $db = new Database();
            self::$conn = $db->connect();
            if (!self::$conn) {
                throw new Exception("Error al conectar a la base de datos");
            }
        }
        return self::$conn;
    }

    public static function obtenerTodos()
    {
        try {
            $conn = self::getConnection();
            $sql = "SELECT id_usuario, nombre, rol FROM usuarioroles";
            $res = $conn->query($sql);

            if (!$res) {
                return ['ok' => false, 'error' => $conn->error];
            }

            $data = [];
            while ($row = $res->fetch_assoc()) {
                $data[] = $row;
            }

            return ['ok' => true, 'data' => $data];
        } catch (\Exception $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public static function obtenerPorId($id)
    {
        try {
            $conn = self::getConnection();
            $stmt = $conn->prepare("SELECT id_usuario, nombre, rol FROM usuarioroles WHERE id_usuario = ? LIMIT 1");
            if (!$stmt) return ['ok' => false, 'error' => $conn->error];

            $stmt->bind_param("i", $id);
            $stmt->execute();
            $res = $stmt->get_result();
            $data = $res->fetch_assoc();
            $stmt->close();

            return $data
                ? ['ok' => true, 'data' => $data]
                : ['ok' => false, 'error' => 'Rol no encontrado'];
        } catch (\Exception $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public static function crear($data)
    {
        try {
            $conn = self::getConnection();
            if (empty($data['nombre']) || empty($data['rol'])) {
                return ['ok' => false, 'error' => 'Campos requeridos faltantes'];
            }

            $stmt = $conn->prepare("INSERT INTO usuarioroles (nombre, rol) VALUES (?, ?)");
            if (!$stmt) return ['ok' => false, 'error' => $conn->error];

            $stmt->bind_param("ss", $data['nombre'], $data['rol']);
            $ok = $stmt->execute();
            $id = $conn->insert_id;
            $stmt->close();

            return $ok ? ['ok' => true, 'id' => $id] : ['ok' => false, 'error' => $conn->error];
        } catch (\Exception $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public static function actualizar($id, $data)
    {
        try {
            $conn = self::getConnection();
            $stmt = $conn->prepare("UPDATE usuarioroles SET nombre = ?, rol = ? WHERE id_usuario = ?");
            if (!$stmt) return ['ok' => false, 'error' => $conn->error];

            $stmt->bind_param("ssi", $data['nombre'], $data['rol'], $id);
            $ok = $stmt->execute();
            $stmt->close();

            return $ok ? ['ok' => true] : ['ok' => false, 'error' => $conn->error];
        } catch (\Exception $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public static function eliminar($id)
    {
        try {
            $conn = self::getConnection();
            $stmt = $conn->prepare("DELETE FROM usuarioroles WHERE id_usuario = ?");
            if (!$stmt) return ['ok' => false, 'error' => $conn->error];

            $stmt->bind_param("i", $id);
            $ok = $stmt->execute();
            $stmt->close();

            return $ok ? ['ok' => true] : ['ok' => false, 'error' => $conn->error];
        } catch (\Exception $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
?>