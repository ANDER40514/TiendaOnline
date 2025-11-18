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
        $sql = "SELECT id_cliente, id_RolUsuario, cliente, email, direccion, telefono, password FROM cliente";
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
        $stmt = $conn->prepare("SELECT id_cliente, id_RolUsuario, cliente, email, direccion, telefono FROM cliente WHERE id_cliente = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = $res->fetch_assoc();
        $stmt->close();

        return $data
            ? ['ok' => true, 'data' => $data]
            : ['ok' => false, 'error' => 'Cliente no encontrado'];
    }

    // insertar cliente con todos los campos
    public static function crear($data)
    {
        $conn = self::getConnection();

        if (
            empty($data['usuario']) ||
            empty($data['email']) ||
            empty($data['direccion']) ||
            empty($data['telefono']) ||
            empty($data['password']) ||
            empty($data['id_RolUsuario'])
        ) {
            return ['ok' => false, 'error' => 'Faltan campos requeridos'];
        }

        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO cliente (cliente, email, direccion, telefono, password, id_RolUsuario)
                                VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "sssssi",
            $data['usuario'],
            $data['email'],
            $data['direccion'],
            $data['telefono'],
            $hashedPassword,
            $data['id_RolUsuario']
        );

        $ok = $stmt->execute();
        $id = $conn->insert_id;
        $stmt->close();

        return $ok
            ? ['ok' => true, 'id' => $id]
            : ['ok' => false, 'error' => $conn->error];
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

    public static function autenticar($usuario, $password)
    {
        $conn = self::getConnection();
        $stmt = $conn->prepare("SELECT id_cliente, cliente, password, id_RolUsuario FROM cliente WHERE cliente = ? LIMIT 1");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0) {
            return ['ok' => false, 'error' => 'Usuario no encontrado'];
        }

        $user = $res->fetch_assoc();
        $stmt->close();

        if (password_verify($password, $user['password'])) {
            unset($user['password']);

            // ✅ Aquí agregamos la sesión
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $_SESSION['user'] = [
                'id_cliente' => $user['id_cliente'],
                'usuario' => $user['cliente'],
                'rol' => $user['id_RolUsuario']
            ];

            return ['ok' => true, 'data' => $_SESSION['user']];
        } else {
            return ['ok' => false, 'error' => 'Contraseña incorrecta'];
        }
    }
}
