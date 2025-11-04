<?php
require_once __DIR__ . '/../config/database.php';

class ClienteModel
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
        $sql = "SELECT id_cliente, id_RolUsuario, cliente, email, direccion, telefono FROM cliente";
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
        $stmt = $conn->prepare("SELECT id_cliente, id_RolUsuario, cliente, email, direccion, telefono  FROM usuario WHERE id_cliente = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = $res->fetch_assoc();
        $stmt->close();

        return $data
            ? ['ok' => true, 'data' => $data]
            : ['ok' => false, 'error' => 'Usuario no encontrado'];
    }

    public static function crear($data)
    {
        $conn = self::getConnection();
        if (!isset($data['nombre'], $data['email'], $data['rol'])) {
            return ['ok' => false, 'error' => 'Faltan campos requeridos'];
        }

        $stmt = $conn->prepare("INSERT INTO cliente (cliente, email, id_RolUsuario) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $data['cliente'], $data['email'], $data['id_RolUsuario']);
        $ok = $stmt->execute();
        $id = $conn->insert_id;
        $stmt->close();

        return $ok ? ['ok' => true, 'id' => $id] : ['ok' => false, 'error' => $conn->error];
    }

    public static function actualizar($id, $data)
    {
        $conn = self::getConnection();
        $stmt = $conn->prepare("UPDATE cliente SET cliente=?, email=?, id_RolUsuario=? WHERE id_cliente=?");
        $stmt->bind_param("ssii", $data['cliente'], $data['email'], $data['id_RolUsuario'], $id);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok ? ['ok' => true] : ['ok' => false, 'error' => $conn->error];
    }

    public static function eliminar($id)
    {
        $conn = self::getConnection();
        $stmt = $conn->prepare("DELETE FROM cliente WHERE id_cliente=?");
        $stmt->bind_param("i", $id);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok ? ['ok' => true] : ['ok' => false, 'error' => $conn->error];
    }
}
?>
