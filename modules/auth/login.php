<?php
$pageTitle = 'Iniciar sesión | Tienda Online';
$extraCSS = ['modules/auth/auth.css'];
$extraJS = [
    'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js',
    'modules/auth/auth.js'
];
include __DIR__ . '/../../includes/header.php';
?>

<main class="auth">
    <section class="auth__box">
        <h1 class="auth__title">Iniciar sesión</h1>
        <form id="login-form" class="auth__form">
            <label>Usuario
                <input type="text" name="usuario" placeholder="Usuario...." />
            </label>
            <label>Contraseña
                <input type="password" name="password" placeholder="Contraseña..."  />
            </label>
            <button type="submit" class="auth__btn">Entrar</button>
        </form>

        <hr />

        <h2 class="auth__title">Registrarse</h2>
        <button id="toggle-register" type="button" class="auth__btn auth__btn--secondary">Mostrar formulario de registro</button>
        <div id="register-panel" class="auth__register-panel hidden">
            <form id="register-form" class="auth__form auth__form--register">
                <label>Usuario
                    <input type="text" name="usuario"
                    placeholder="Usuario..."  />
                </label>
                <label>Contraseña
                    <input type="password" name="password"  placeholder="contraseña..."  />
                </label>
                <label>Email
                    <input type="email" name="email"
                    placeholder="Correo..." />
                </label>
                <label>Dirección
                    <input type="text" name="direccion"
                    placeholder="Direccion..." />
                </label>
                <label>Teléfono
                    <input type="text" name="telefono"
                    placeholder="Telefono..." />
                </label>
                <label>Rol
                    <select name="id_RolUsuario" class="auth__role-select">
                        <option value="">Cargando roles...</option>
                    </select>
                </label>
                <button type="submit" class="auth__btn auth__btn--secondary">Crear cuenta</button>
            </form>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
