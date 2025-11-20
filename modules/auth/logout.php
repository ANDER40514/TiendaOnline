<?php
// Inicia la sesión si no está activa
if (session_status() === PHP_SESSION_NONE) session_start();

// Limpiar todas las variables de sesión
$_SESSION = [];

// Borrar la cookie de sesión si existe
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Destruir la sesión
session_destroy();

// También eliminar almacenamiento local a través de un script JS
echo '<script>
    localStorage.removeItem("tienda_user");
    localStorage.removeItem("token");
    sessionStorage.removeItem("user");
    window.location.href = "/TiendaOnline/modules/auth/login.php";
</script>';
exit;
