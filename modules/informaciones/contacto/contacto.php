<?php
$pageTitle = 'Contacto | Tienda Online';
$extraCSS = ['modules/informaciones/contacto/contacto.css'];
$extraJS = ['modules/informaciones/contacto/contacto.js',   
            'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js'
];

$pageJSON = [
    'navbar' => 'data/navbar.json',
    'gallery' => null,
    'footer' => 'data/footer.json'
];

include __DIR__ . '/../../../includes/header.php';
?>

<section class="contacto" id="contacto">
    <h2 class="contacto__title">Contáctanos</h2>
    <p class="contacto__text">
        Si tienes alguna pregunta o necesitas asistencia, no dudes en
        contactarnos a través del siguiente formulario:
    </p>

    <form id="form" class="form" id="contact-form" action="<?= BASE_URL ?>modules/Informaciones/contacto/submit_contact.php" method="post">
        <div class="form__data">
            <label class="form__label" for="name">Nombre</label>
            <input class="form__input" type="text" id="name" name="name" placeholder="Ingresa tu nombre" required />
            <p class="form__alert" id="nameAlert"></p>
        </div>

        <div class="form__data">
            <label class="form__label" for="email">Correo Electrónico</label>
            <input class="form__input" type="email" id="email" name="email" placeholder="Ingresa tu Correo Electronico" required />
            <p class="form__alert" id="emailAlert"></p>
        </div>

        <div class="form__data">
            <label class="form__label" for="message">Mensaje</label>
            <textarea class="form__textarea" id="message" name="message" rows="4" placeholder="Ingresa un mensaje"></textarea>
        </div>

        <button class="form__btn-form" id="btn-form" type="submit">Enviar</button>
    </form>
</section>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>