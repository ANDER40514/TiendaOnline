<?php
require_once __DIR__ . '/config.php';

// Aseguramos que BASE_URL siempre termine con "/"
$baseUrl = rtrim(BASE_URL, '/') . '/';
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle ?? 'Tienda Online') ?></title>

    <!-- CSS global -->
    <link rel="stylesheet" href="<?= $baseUrl ?>assets/css/style.css">

    <!-- CSS específicos -->
    <?php if (!empty($extraCSS)) : ?>
        <?php foreach ($extraCSS as $cssFile) : ?>
            <link rel="stylesheet" href="<?= $baseUrl . ltrim($cssFile, '/') ?>">
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Variables JS -->
    <script>
        // 🔧 Forzamos la barra final y mostramos en consola para depurar
        const BASE_URL = '<?= rtrim(BASE_URL, "/") ?>/';
        console.log("BASE_URL ->", BASE_URL);

        // Definimos rutas completas
        const PAGE_JSON = {
            navbar: BASE_URL + 'data/navbar.json',
            gallery: BASE_URL + 'data/gallery-item.json',
            footer: BASE_URL + 'data/footer.json'
        };

        console.log("PAGE_JSON ->", PAGE_JSON);
    </script>

    <!-- JS específicos -->
    <?php if (!empty($extraJS)) : ?>
        <?php foreach ($extraJS as $jsFile) : ?>
            <script src="<?= BASE_URL . ltrim($jsFile, '/') ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>


    <link rel="shortcut icon" href="<?= $baseUrl ?>assets/img/store_icon.ico" type="image/x-icon">
</head>

<body>
    <header>
        <nav class="navbar" id="navbar">
            <ul class="navbar__list" id="main-nav"></ul>
        </nav>
    </header>