<?php
require_once __DIR__ . '/config.php';

// Asegura que la sesión esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(0);
    session_start();
}

// Variables adicionales
$pageTitle = $pageTitle ?? 'Tienda Online';
$extraCSS = $extraCSS ?? [];
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle) ?></title>

    <!-- CSS global -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">

    <!-- CSS adicionales -->
    <?php foreach ($extraCSS as $cssFile): ?>
        <link rel="stylesheet" href="<?= BASE_URL . ltrim($cssFile, '/') ?>">
    <?php endforeach; ?>

    <!-- Variables JS -->
    <script>
        const BASE_URL = '<?= BASE_URL ?>';

        // Usuario actual (desde sesión PHP)
        const CURRENT_USER = <?= json_encode($_SESSION['user'] ?? null, JSON_UNESCAPED_UNICODE) ?>;

        const PAGE_JSON = {
            navbar: BASE_URL + 'data/navbar.json',
            gallery: BASE_URL + 'data/gallery-item.json',
            footer: BASE_URL + 'data/footer.json'
        };
    </script>

    <link rel="shortcut icon" href="<?= BASE_URL ?>assets/img/store_icon.ico" type="image/x-icon">
</head>
<body>
<header>
    <nav class="navbar" id="navbar">
        <ul class="navbar__list" id="main-nav"></ul>
    </nav>
</header>
