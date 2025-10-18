<?php
$pageTitle = 'Administración de Juegos';
$pageJSON = [
    'navbar' => 'data/navbar.json',
    'footer' => 'data/footer.json'
];
$extraCSS = ['modules/admin-juegos/admin-juegos.css'];
$extraJS  = ['modules/admin-juegos/admin-juegos.js'];

include __DIR__ . '/../../includes/header.php';
?>

<main class="admin">
    
    <form class="form" id="game-form">
        <h1 class="form__title">Administración de Juegos</h1>

        <input type="hidden" name="id" id="game-id">
        
        <label for="game-titulo" class="form__label" >Título:</label>
        <input type="text" name="titulo" id="game-titulo" class="form__input form__input--titulo" placeholder="Titulo del juego..." required>
        
        <label for="game-descripcion" class="form__label" >Descripción:</label>
        <textarea name="descripcion" id="game-descripcion" class="form__textarea form__textarea--descripcion" placeholder="Descripcion del juego..." required></textarea>
        
        <label for="game-precio" class="form__label" >Precio:</label>
        <input type="number" name="precio" id="game-precio" min="1"  step="0.01" class="form__input form__input--precio" placeholder="Precio del juego..." required>
        
        <label for="game-consola" class="form__label" >Consola:</label>
        <input type="text" name="consola" id="game-consola" class="form__input form__input--consola" placeholder="Consola donde se puede jugar.." required>
        
        <label for="game-imagen" class="form__label" >Imagen:</label>
        <input type="text" name="imagen" id="game-imagen" class="form__input form__input--img" placeholder="URL de la ubicacion de la imagen...">
        
        <div class="form__preview">
            <img id="preview-img" src="../../assets/img/no-photo.jpg" alt="Preview del juego" style="max-width: 150px; border-radius: 8px; margin-top: 10px;">
        </div>

        <button class="form__btn form__btn--save" type="submit" id="btn-save">Guardar</button>
        <button class="form__btn form__btn--cancel" type="button" id="btn-cancel">Cancelar</button>
    </form>

    <!-- Tabla de juegos -->
    <table class="table" id="games-table">
        <thead class="table__head">
            <tr class="table__row">
                <th class="table__header" >ID</th>
                <th class="table__header" >Título</th>
                <th class="table__header" >Descripción</th>
                <th class="table__header" >Precio</th>
                <th class="table__header" >Consola</th>
                <th class="table__header" >Imagen</th>
                <th class="table__header" >Acciones</th>
            </tr>
        </thead>

        <tbody> </tbody>
    
    </table>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
<script src="<?= BASE_URL ?>modules/admin-juegos/admin-juegos.js"></script>


<?php include __DIR__ . '/../../includes/footer.php'; ?>
