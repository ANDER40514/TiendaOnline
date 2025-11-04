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
        }
        return self::$conn;
    }

    public static function obtenerTodos()
    {
        $conn = self::getConnection();
        $sql = "SELECT id_usuario, nombre, rol FROM usuarioRoles";
        $res = $conn->query($sql);

        if (!$res) {
            return ['ok' => false, 'error' => $conn->error];
        }

        $data = [];
        while ($row = $res->fetch_assoc()) {
            $data[] = $row;
        }

        return ['ok' => true, 'data' => $data];
    }

    public static function obtenerPorId($id)
    {
        $conn = self::getConnection();
        $stmt = $conn->prepare("SELECT id_usuario, nombre, rol FROM usuarioRoles WHERE id_usuario = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = $res->fetch_assoc();
        $stmt->close();

        return $data
            ? ['ok' => true, 'data' => $data]
            : ['ok' => false, 'error' => 'Rol no encontrado'];
    }

    public static function crear($data)
    {
        $conn = self::getConnection();
        $stmt = $conn->prepare("INSERT INTO usuarioRoles (nombre, rol) VALUES (?, ?)");
        $stmt->bind_param("ss", $data['nombre'], $data['descripcion']);
        $ok = $stmt->execute();
        $id = $conn->insert_id;
        $stmt->close();

        return $ok ? ['ok' => true, 'id' => $id] : ['ok' => false, 'error' => $conn->error];
    }

    public static function actualizar($id, $data)
    {
        $conn = self::getConnection();
        $stmt = $conn->prepare("UPDATE usuarioRoles SET nombre = ?, rol = ? WHERE id_usuario = ?");
        $stmt->bind_param("ssi", $data['nombre'], $data['rol'], $id);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok ? ['ok' => true] : ['ok' => false, 'error' => $conn->error];
    }

    public static function eliminar($id)
    {
        $conn = self::getConnection();
        $stmt = $conn->prepare("DELETE FROM usuarioRoles WHERE id_usuario = ?");
        $stmt->bind_param("i", $id);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok ? ['ok' => true] : ['ok' => false, 'error' => $conn->error];
    }
}
