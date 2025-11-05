<?php
require_once __DIR__ . '/../../includes/config.php';

$pageTitle = 'Misión | Tienda Online';
$extraCSS = ['modules/Informaciones/contacto/contacto.css'];

$pageJSON = [
    'navbar' => BASE_URL . 'data/navbar.json',
    'gallery' => null,
    'footer' => BASE_URL . 'data/footer.json'
];

include __DIR__ . '/../../includes/header.php';
?>


<main>
    <section class="informacion">
        <div class="informacion__wrap">
            <section class="mision">
                <h1 class="mision__title">Misión</h1>
                <br>
                <p class="mision__lead">
                    Somos una tienda en línea especializada en la compra y venta de videojuegos. 
                    Nuestra misión es facilitar a jugadores y coleccionistas un mercado seguro, 
                    sencillo y justo donde encontrar títulos nuevos, clásicos y ediciones especiales, 
                    así como dar una segunda vida a los juegos que ya no usan.
                </p>

                <h2>Nuestros compromisos</h2>
                <br>
                <ul>
                    <li>Ofrecer una selección curada de juegos con descripciones claras, fotos reales y precios competitivos.</li>
                    <li>Garantizar procesos de compra y venta transparentes, con atención al cliente rápida y políticas claras de devolución y garantía.</li>
                    <li>Proteger los datos personales y financieros de nuestros usuarios mediante buenas prácticas de seguridad.</li>
                    <li>Impulsar la comunidad de jugadores promoviendo intercambio responsable y actividades que fomenten el coleccionismo y la preservación del patrimonio lúdico.</li>
                </ul>
            </section>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
