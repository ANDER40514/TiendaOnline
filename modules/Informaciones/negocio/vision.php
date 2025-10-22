<?php
$pageTitle = 'Visión | Tienda Online';
$extraCSS = ['modules/Informaciones/contacto/contacto.css'];

$pageJSON = [
    'navbar' => 'data/navbar.json',
    'gallery' => null,
    'footer' => 'data/footer.json'
];

include __DIR__ . '/../../../includes/header.php';
?>

<main>
    <section class="informacion">
        <div class="informacion__wrap">
            <section class="vision">
                <h1 class="vision__title">Visión</h1>
                <br>
                <p class="vision__lead">Ser la plataforma de referencia en la región para la compra y venta de videojuegos, reconocida por la confianza de nuestros usuarios, la variedad de nuestro catálogo y la calidad del servicio. Aspiramos a conectar generaciones de jugadores ofreciendo tanto lo último en novedades como títulos que forman parte de la historia del entretenimiento interactivo.</p>

                <h2>Hacia dónde vamos</h2>
                <br>
                <ul>
                    <li>Expandir nuestra presencia ofreciendo integración con marketplaces y tiendas físicas asociadas.</li>
                    <li>Fomentar una comunidad activa y sostenible en torno al coleccionismo y el intercambio responsable.</li>
                    <li>Innovar en servicios (valoraciones, autenticación de ediciones limitadas, logística optimizada) para crear valor real a compradores y vendedores.</li>
                </ul>
            </section>
        </div>
    </section>
</main>


<?php include __DIR__ . '/../../../includes/footer.php'; ?>