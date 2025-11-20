<?php
// Detecta el protocolo automáticamente
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';

// Host (DNS, localhost o ngrok)
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// BASE_URL = solo la raíz del host
define('BASE_URL', $protocol . '://' . $host . '/');

// BASE_PATH = ruta absoluta del proyecto en el sistema de archivos
define('BASE_PATH', realpath(__DIR__ . '/../..'));

// API_URL = subdirectorio de API
define('API_URL', BASE_URL . 'api/');
