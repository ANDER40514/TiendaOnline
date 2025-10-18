<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

require_once "db.php";

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
case 'GET':
    if (isset($_GET['id_juego'])) {
        $id = intval($_GET['id_juego']);
        $sql = "SELECT id_juego, titulo, descripcion, precio, consola, FechaInsercion, imagen 
                FROM juego 
                WHERE id_juego = $id";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            echo json_encode($result->fetch_assoc(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } else {
            echo json_encode(["mensaje" => "Juego no encontrado"]);
        }
        break;
    }

    // Si no viene ID, se listan todos
    $sql = "SELECT id_juego, titulo, descripcion, precio, consola, FechaInsercion, imagen FROM juego";
    $result = $conn->query($sql);

    if (!$result) {
        echo json_encode(["error_sql" => $conn->error]);
        break;
    }

    $juegos = [];
    while ($row = $result->fetch_assoc()) {
        $juegos[] = $row;
    }

    echo json_encode($juegos, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['titulo'], $data['descripcion'], $data['precio'], $data['consola'])) {
            echo json_encode(["error" => "Faltan campos obligatorios."]);
            break;
        }

        $titulo = $conn->real_escape_string($data['titulo']);
        $descripcion = $conn->real_escape_string($data['descripcion']);
        $precio = $conn->real_escape_string($data['precio']);
        $consola = $conn->real_escape_string($data['consola']);
        $imagen = isset($data['imagen']) ? $conn->real_escape_string($data['imagen']) : 'default.jpg';

        $sql = "INSERT INTO juego (titulo, descripcion, precio, consola, imagen)
                VALUES ('$titulo', '$descripcion', '$precio', '$consola', '$imagen')";

        if ($conn->query($sql)) {
            echo json_encode(["mensaje" => "Juego insertado correctamente.", "id" => $conn->insert_id]);
        } else {
            echo json_encode(["error" => "Error al insertar: " . $conn->error]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['id'])) {
            echo json_encode(["error" => "El campo 'id' es obligatorio para actualizar."]);
            break;
        }

        $id_juego = intval($data['id']);
        $updates = [];

        foreach (['titulo', 'descripcion', 'precio', 'consola', 'imagen'] as $campo) {
            if (isset($data[$campo])) {
                $valor = $conn->real_escape_string($data[$campo]);
                $updates[] = "$campo='$valor'";
            }
        }

        if (empty($updates)) {
            echo json_encode(["error" => "No se enviaron campos para actualizar."]);
            break;
        }

        $sql = "UPDATE juego SET " . implode(", ", $updates) . " WHERE id_juego=$id_juego";

        if ($conn->query($sql)) {
            echo json_encode(["mensaje" => "Juego actualizado correctamente."]);
        } else {
            echo json_encode(["error" => "Error al actualizar: " . $conn->error]);
        }
        break;

    case 'DELETE':
        if (!isset($_GET['id'])) {
            echo json_encode(["error" => "Debe especificar el parámetro 'id' en la URL."]);
            break;
        }

        $id = intval($_GET['id']);
        $sql = "DELETE FROM juego WHERE id_juego = $id";

        if ($conn->query($sql)) {
            if ($conn->affected_rows > 0) {
                echo json_encode(["mensaje" => "Juego eliminado correctamente."]);
            } else {
                echo json_encode(["mensaje" => "No se encontró ningún juego con ese ID."]);
            }
        } else {
            echo json_encode(["error" => "Error al eliminar: " . $conn->error]);
        }
        break;

    default:
        echo json_encode(["error" => "Método no permitido."]);
        break;
}

$conn->close();