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
            if (!self::$conn) throw new Exception("Error al conectar a la base de datos");
        }
        return self::$conn;
    }

    public static function obtenerTodos()
    {
        try {
            $conn = self::getConnection();
            $sql = "SELECT id_cliente, id_RolUsuario, cliente AS usuario, email, direccion, telefono FROM cliente";
            $res = $conn->query($sql);

            if (!$res) return ['ok' => false, 'error' => $conn->error];

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
            $stmt = $conn->prepare("SELECT id_cliente, id_RolUsuario, cliente AS usuario, email, direccion, telefono FROM cliente WHERE id_cliente = ? LIMIT 1");
            if (!$stmt) return ['ok' => false, 'error' => $conn->error];

            $stmt->bind_param("i", $id);
            $stmt->execute();
            $res = $stmt->get_result();
            $data = $res->fetch_assoc();
            $stmt->close();

            return $data
                ? ['ok' => true, 'data' => $data]
                : ['ok' => false, 'error' => 'Cliente no encontrado'];
        } catch (\Exception $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public static function autenticar($usuario, $password)
    {
        try {
            $conn = self::getConnection();
            $stmt = $conn->prepare("SELECT id_cliente, cliente AS usuario, password, id_RolUsuario FROM cliente WHERE cliente = ? OR email = ? LIMIT 1");
            if (!$stmt) return ['ok' => false, 'error' => $conn->error];

            $stmt->bind_param("ss", $usuario, $usuario);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res->num_rows === 0) return ['ok' => false, 'error' => 'Usuario no encontrado'];

            $user = $res->fetch_assoc();
            $stmt->close();

            if (!password_verify($password, $user['password'])) {
                return ['ok' => false, 'error' => 'Contraseña incorrecta'];
            }

            unset($user['password']);

            session_save_path(__DIR__ . '/../../sessions');
            if (session_status() === PHP_SESSION_NONE) {
                session_name('PHPSESSID');
                session_save_path(realpath(__DIR__ . '/../../sessions'));
                session_start();


                $_SESSION['user'] = [
                    'id_cliente' => $user['id_cliente'],
                    'usuario' => $user['usuario'],
                    'rol' => $user['id_RolUsuario']
                ];
            }

            return ['ok' => true, 'user' => $_SESSION['user']];
        } catch (\Exception $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    // =========================
    // CREAR REGISTRO
    // =========================
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

        // Validar existencia previa
        $stmt = $conn->prepare("SELECT id_cliente FROM cliente WHERE cliente=? OR email=? LIMIT 1");
        $stmt->bind_param("ss", $data['usuario'], $data['email']);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) return ['ok' => false, 'error' => 'Usuario o email ya existe'];
        $stmt->close();

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

        if (!$ok) {
            return ['ok' => false, 'error' => $stmt->error ?: $conn->error];
        }

        return $ok
            ? ['ok' => true, 'id' => $id]
            : ['ok' => false, 'error' => $conn->error];
    }

    // =========================
    // ACTUALIZAR
    // =========================
    public static function actualizar($id, $data)
    {
        $conn = self::getConnection();
        $stmt = $conn->prepare("UPDATE cliente SET usuario=?, email=?, id_RolUsuario=? WHERE id_cliente=?");
        $stmt->bind_param("ssii", $data['usuario'], $data['email'], $data['id_RolUsuario'], $id);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok ? ['ok' => true] : ['ok' => false, 'error' => $conn->error];
    }

    // =========================
    // ELIMINAR
    // =========================
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