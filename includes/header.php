<?php
require_once __DIR__ . '/config.php';

// BASE_URL siempre termine con "/"
$baseUrl = rtrim(BASE_URL, '/') . '/';

if (session_status() === PHP_SESSION_NONE) {

    session_set_cookie_params(0);
    session_start();
}
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
        const BASE_URL = '<?= rtrim(BASE_URL, "/") ?>/';
        console.log("BASE_URL ->", BASE_URL);

        // Usuario actual (desde sesión PHP)
        const CURRENT_USER = <?= json_encode($_SESSION['user'] ?? null, JSON_UNESCAPED_UNICODE) ?>;
        console.log('CURRENT_USER ->', CURRENT_USER);

        const PAGE_JSON = {
            navbar: BASE_URL + 'data/navbar.json',
            gallery: BASE_URL + 'data/gallery-item.json',
            footer: BASE_URL + 'data/footer.json'
        };

        console.log("PAGE_JSON ->", PAGE_JSON);
    </script>

    <link rel="shortcut icon" href="<?= $baseUrl ?>assets/img/store_icon.ico" type="image/x-icon">
</head>

<body>
    <header>
        <nav class="navbar" id="navbar">
            <ul class="navbar__list" id="main-nav"></ul>
        </nav>
    </header>