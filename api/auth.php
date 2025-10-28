<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

session_set_cookie_params(0);
session_start();
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

function json($v, $code = 200)
{
    http_response_code($code);
    echo json_encode($v, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($method === 'OPTIONS') json(['ok' => true]);

if ($method === 'GET') {
    if (!empty($_SESSION['user'])) {
        $u = $_SESSION['user'];
        json(['ok' => true, 'user' => [
            'id_cliente' => $u['id_cliente'],
            'cliente' => $u['cliente'],
            'email' => $u['email'],
            'direccion' => $u['direccion'],
            'telefono' => $u['telefono']
        ]]);
    }
    json(['ok' => false, 'user' => null], 204);
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];

if ($method === 'POST') {
    $action = $input['action'] ?? ($input['register'] ? 'register' : 'login');

    if ($action === 'login') {
        $usuario = $input['usuario'] ?? '';
        $password = $input['password'] ?? '';
        if (!$usuario || !$password) json(['error' => 'Crendeciales Incompletas'], 400);

        $stmt = $conn->prepare('SELECT id_cliente, id_RolUsuario, cliente, password, email, direccion, telefono FROM cliente WHERE cliente = ? LIMIT 1');
        $stmt->bind_param('s', $usuario);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        if (!$row) json(['error' => 'Usuario no encontrado'], 401);
        if (!password_verify($password, $row['password'])) json(['error' => 'Contraseña inválida'], 401);

        unset($row['password']);
        $_SESSION['user'] = $row;
        json(['ok' => true, 'user' => $row]);
    }

    if ($action === 'register') {
        $usuario = trim($input['usuario'] ?? '');
        $password = $input['password'] ?? '';
        $email = $input['email'] ?? null;
        $direccion = $input['direccion'] ?? null;
        $telefono = $input['telefono'] ?? null;
        $requestedRole = isset($input['id_RolUsuario']) ? intval($input['id_RolUsuario']) : null;
        if (!$usuario || !$password) json(['error' => 'Usuario y contraseña requeridos'], 400);

        $stmt = $conn->prepare('SELECT id_cliente FROM cliente WHERE cliente = ?');
        $stmt->bind_param('s', $usuario);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($r) json(['error' => 'El usuario ya existe'], 409);

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $idRol = 2; // default role
        if ($requestedRole) {
            $s2 = $conn->prepare('SELECT id_usuario FROM usuarioRoles WHERE id_usuario = ?');
            $s2->bind_param('i', $requestedRole);
            $s2->execute();
            $res2 = $s2->get_result()->fetch_assoc();
            $s2->close();
            if ($res2) $idRol = $requestedRole;
        }

        $stmt = $conn->prepare('INSERT INTO cliente (id_RolUsuario, cliente, password, email, direccion, telefono) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('isssss', $idRol, $usuario, $hash, $email, $direccion, $telefono);
        if ($stmt->execute()) {
            $id = $conn->insert_id;
            $stmt->close();
            $user = ['id_cliente' => $id, 'id_RolUsuario' => $idRol, 'cliente' => $usuario, 'email' => $email, 'direccion' => $direccion, 'telefono' => $telefono];
            $_SESSION['user'] = $user;
            json(['ok' => true, 'user' => $user], 201);
        } else {
            json(['error' => $conn->error], 500);
        }
    }

    json(['error' => 'Acción desconocida'], 400);
}

json(['error' => 'Método no permitido'], 405);
?>