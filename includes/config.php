<?php
// Determina el protocolo (http o https)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

$rootFolder = '/TiendaOnline/';

define('BASE_URL', $protocol . '://' . $host . $rootFolder);
