<?php
    $host = "localhost";
    $port = 3307;
    $user = "root";
    $password = "";
    $database = "mi_tienda";

    $conn = new mysqli($host, $user, $password, $database, $port);

    if ($conn->connect_error) {
        die(json_encode(["error" => "Error de conexión: " . $conn->connect_error]));
    }

    $conn->set_charset("utf8");
?>
