<?php
$pageTitle = "Compra de videojuegos";

$extraCSS = ['modules/mantenimientos/compra-venta/compra-venta.css'];
$extraJS  = ['modules/mantenimientos/compra-venta/compra-venta.js'];

include __DIR__ . '/../../../includes/header.php';
?>

<main class="compra">

    <!-- Seccion de compra de videojuegos -->
    <section class="compra__catalog">
        <h2 class="compra__title">Compra de Videojuegos</h2>
        <div class="compra__catalog-wrap">
            <table class="compra__catalog-table">
                <thead class="compra__catalog-head">
                    <tr class="compra__catalog-row compra__catalog-head-row">
                        <th class="compra__catalog-header">ID</th>
                        <th class="compra__catalog-header">Título</th>
                        <th class="compra__catalog-header">Consola</th>
                        <th class="compra__catalog-header">Precio</th>
                        <th class="compra__catalog-header">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- filas cargadas por JS -->
                </tbody>
            </table>
        </div>
    </section>

    <!-- seccion de Carrito -->
    <section class="compra__cart">
        <h2 class="compra__cart-title">Carrito</h2>
        <div class="compra__cart-empty">El carrito está vacío</div>
        <table class="compra__cart-table compra__table hidden">
            <thead>
                <tr class="compra__cart-row compra__cart-head-row">
                    <th class="compra__cart-header">Título</th>
                    <th class="compra__cart-header">Precio</th>
                    <th class="compra__cart-header">Cantidad</th>
                    <th class="compra__cart-header">Subtotal</th>
                    <th class="compra__cart-header">Acción</th>
                </tr>
            </thead>

            <tbody></tbody>

            <tfoot>
                <tr class="compra__cart-foo-row">
                    <td colspan="3" class="compra__total-label">Total</td>
                    <td class="compra__cart-total">0.00</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <input type="hidden" class="compra__order-data" />
        <button id="realizar-compra" class="compra__btn compra__btn--submit hidden">Realizar compra</button>

        <div class="compra__result"></div>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>

<?php
include __DIR__ . '/../../../includes/footer.php';
?>