<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /TiendaOnline/contacto/contacto.php');
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');
$errors = [];


if ($name === '') $errors[] = 'Nombre requerido';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email inválido';
if ($message === '') $errors[] = 'Mensaje requerido';

if ($errors) {
    $q = http_build_query(['errors' => $errors]);
    header('Location: /TiendaOnline/contact/contact.php?' . $q);
    exit;
}

$logLine = sprintf("[%s] %s <%s> : %s\n", date('c'), $name, $email, $message);
file_put_contents(__DIR__ . '/messages.log', $logLine, FILE_APPEND | LOCK_EX);
header('Location: /TiendaOnline/contact/contact.php?sent=1');
exit;
