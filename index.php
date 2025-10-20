<?php $pageTitle = 'Anderson Castillo | Tienda Online';

$pageJSON = [
    'navbar' => 'data/navbar.json',
    'gallery' => 'data/gallery-item.json',
    'footer' => 'data/footer.json'
];

include __DIR__ . '/includes/header.php'; ?>

<!-- Section Inicio (Hero) -->
<div class="inicio" id="inicio">
    <h2 class="inicio__title ">Bienvenido a mi Tienda Online</h2>
    <p class="inicio__text">Presentacion de tarea de Programacion Teoria III
</div>

<!-- Section Principal - Catalogo -->
<div class="catalogo" id="body">
    <h2 class="catalogo__title">Catálogo de Juegos</h2>
    <h4 class="catalogo__tags">PS4 - PS5 - WII-U - Xbox Series S - Steam Deck - Nintendo Switch </h4>

    <span class="catalogo__header">Lista de Articulos</span>
    <div class="gallery"></div>


    <?php include __DIR__ . '/includes/footer.php'; ?>