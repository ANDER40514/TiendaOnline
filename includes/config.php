<?php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

$rootFolder = '/TiendaOnline/'; 

// BASE_URL apunta SIEMPRE al root del sitio
define('BASE_URL', $protocol . '://' . $host . $rootFolder);

// API_URL apunta al subdirectorio de API
define('API_URL', BASE_URL . 'api/');
