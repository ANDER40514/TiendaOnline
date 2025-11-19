<?php
$pageTitle = 'Consolas | Tienda Online';
$extraCSS = ['modules/mantenimientos/consolas/consolas.css'];
$extraJS = [
    'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js',
    'modules/mantenimientos/consolas/consolas.js'
];
include __DIR__ . '/../../../includes/header.php';
?>

<main class="admin-consola">
    <h1 class="admin-consola__title">Administrar Consolas</h1>

    <section class="admin-consola__list">
        <button class="admin-consola__add-btn">Agregar Consola</button>
        <table class="admin-consola__table">
            <thead class="admin-consola__thead">
                <tr>
                    <th>ID</th>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Color</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody class="admin-consola__tbody">
                <!-- cargado por JS -->
            </tbody>
        </table>
    </section>

    <!-- panel de edición -->
    <aside class="admin-consola__panel hidden" role="region" aria-hidden="true">
        <div class="admin-consola__panel-inner">
            <header class="admin-consola__panel-header">
                <h2 class="admin-consola__panel-title">Consola</h2>
            </header>
            <div class="admin-consola__panel-body">
                <label class="admin-consola__label"> Código <input type="text" class="admin-consola__field admin-consola__field--code" placeholder="Ingresa el codigo del color..." /></label>
                <label class="admin-consola__label"> Nombre <input type="text" class="admin-consola__field admin-consola__field--name" placeholder="Ingresa el nombre del color..." /></label>
                <label class="admin-consola__label"> Color <input type="color" class="admin-consola__field admin-consola__field--color" value="#cccccc" /></label>
            </div>
            <footer class="admin-consola__panel-actions">
                <button class="admin-consola__save">Guardar</button>
                <button class="admin-consola__cancel">Cancelar</button>
            </footer>
        </div>
    </aside>
</main>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>