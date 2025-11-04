<?php
require_once __DIR__ . '/../config/database.php';

class JuegoModel {
    private static $conn;

    private static function getConnection() {
        if (!self::$conn) {
            $db = new Database();
            self::$conn = $db->connect();
        }
        return self::$conn;
    }

    public static function obtenerTodos() {
        $conn = self::getConnection();
        $sql = "SELECT id_juego, titulo, descripcion, precio, imagen FROM juego";
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

    public static function obtenerPorId($id) {
        $conn = self::getConnection();
        $stmt = $conn->prepare("SELECT id_juego, titulo, descripcion, precio, imagen FROM juego WHERE id_juego = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = $res->fetch_assoc();
        $stmt->close();

        return $data ? ['ok' => true, 'data' => $data] : ['ok' => false, 'error' => 'Juego no encontrado'];
    }

    public static function insertar($data) {
        $conn = self::getConnection();
        $stmt = $conn->prepare("INSERT INTO juego (titulo, descripcion, precio, imagen) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssds", $data['titulo'], $data['descripcion'], $data['precio'], $data['imagen']);
        $ok = $stmt->execute();
        $id = $conn->insert_id;
        $stmt->close();

        return $ok ? ['ok' => true, 'id' => $id] : ['ok' => false, 'error' => $conn->error];
    }

    public static function actualizar($id, $data) {
        $conn = self::getConnection();
        $stmt = $conn->prepare("UPDATE juego SET titulo=?, descripcion=?, precio=?, imagen=? WHERE id_juego=?");
        $stmt->bind_param("ssdsi", $data['titulo'], $data['descripcion'], $data['precio'], $data['imagen'], $id);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok ? ['ok' => true] : ['ok' => false, 'error' => $conn->error];
    }

    public static function eliminar($id) {
        $conn = self::getConnection();
        $stmt = $conn->prepare("DELETE FROM juego WHERE id_juego=?");
        $stmt->bind_param("i", $id);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok ? ['ok' => true] : ['ok' => false, 'error' => $conn->error];
    }
}
?>
