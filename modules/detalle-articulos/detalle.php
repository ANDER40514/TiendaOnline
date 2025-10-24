<?php
require_once __DIR__ . '/../../includes/config.php';

$pageTitle = 'Detalle del Producto | Tienda Online';
$pageJSON = [
    'navbar' => 'data/navbar.json',
    'gallery' => null,
    'footer' => 'data/footer.json'
];

$extraCSS = ['modules/detalle-articulos/detalle.css'];

$extraJS = ['modules/detalle-articulos/detalle.js'];

include __DIR__ . '/../../includes/header.php';
?>

<main class="detalle" id="detalle__producto">
    <div id="detalle_placeholder">Cargando...</div>
</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>