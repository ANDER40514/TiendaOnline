(() => {
    // =========================
    // Detectar ruta base dinámica
    // =========================
    // Esto toma la primera parte de la ruta para proyectos en subcarpeta o raíz
    const BASE_PATH = window.location.pathname.split('/').slice(0,2).join('/'); // "/" + "TiendaOnline"
    const ORIGIN = window.location.origin;

    // =========================
    // URLs de la API
    // =========================
    const AUTH_URL  = `${ORIGIN}/api/index.php?endpoint=clientes`;
    const ROLES_URL = `${ORIGIN}/api/index.php?endpoint=roles`;
    const LOGIN_PAGE = `${ORIGIN}/modules/auth/login.php`;
    const HOME_PAGE  = `${ORIGIN}/index.php`;

    // =========================
    // Elementos del DOM
    // =========================
    const formLogin       = document.getElementById("login-form");
    const formRegister    = document.getElementById("register-form");
    const toggleRegisterBtn = document.getElementById("toggle-register");
    const registerPanel     = document.getElementById("register-panel");
    const navRoot           = document.getElementById("main-nav");
    const selectRoles       = document.getElementById("form__select");

    // =========================
    // Funciones auxiliares
    // =========================
    function saveSession(user, token = "") {
        sessionStorage.setItem("user", JSON.stringify(user));
        localStorage.setItem("tienda_user", JSON.stringify(user));
        if (token) localStorage.setItem("token", token);
    }

    function mostrarUsuarioNavbar() {
        if (!navRoot) return;

        // Limpiar previos
        navRoot.querySelectorAll(".navbar__user-wrapper").forEach(n => n.remove());

        const raw = sessionStorage.getItem("user") || localStorage.getItem("tienda_user");
        const user = raw ? JSON.parse(raw) : null;

        const li = document.createElement("li");
        li.classList.add("navbar__user-wrapper");

        if (user) {
            const nombre = user.usuario || user.cliente || user.email || "Usuario";
            li.innerHTML = `
                <a href="#" class="navbar__links">👤 ${nombre}</a>
                <ul class="navbar__submenu">
                    <li class="navbar__sub-items"><a href="${HOME_PAGE}">Mi perfil</a></li>
                    <li class="navbar__sub-items"><a href="#" id="logout-link">Cerrar sesión</a></li>
                </ul>
            `;
            navRoot.appendChild(li);

            const logout = li.querySelector("#logout-link");
            logout?.addEventListener("click", (e) => {
                e.preventDefault();
                Swal.fire({
                    title: "Cerrar sesión",
                    text: "¿Deseas cerrar la sesión?",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonText: "Sí, cerrar"
                }).then(r => {
                    if (r.isConfirmed) {
                        sessionStorage.removeItem("user");
                        localStorage.removeItem("tienda_user");
                        localStorage.removeItem("token");
                        fetch(`${BASE_PATH}/modules/auth/logout.php`)
                            .finally(() => location.href = LOGIN_PAGE);
                    }
                });
            });
        } else {
            li.innerHTML = `<a href="${LOGIN_PAGE}" class="navbar__links">Iniciar sesión</a>`;
            navRoot.appendChild(li);
        }
    }

    // =========================
    // Mostrar / ocultar registro
    // =========================
    function initToggleRegister() {
        if (!toggleRegisterBtn || !registerPanel) return;
        toggleRegisterBtn.addEventListener("click", () => {
            const hidden = registerPanel.classList.toggle("hidden");
            toggleRegisterBtn.textContent = hidden
                ? "Mostrar formulario de registro"
                : "Ocultar formulario de registro";
        });
    }

    // =========================
    // Cargar roles dinámicamente
    // =========================
    async function cargarRoles() {
        if (!selectRoles) return;
        try {
            const res = await fetch(ROLES_URL);
            const json = await res.json();

            selectRoles.innerHTML = "";

            if (json.ok && Array.isArray(json.data)) {
                selectRoles.innerHTML = `<option value="">Selecciona un rol...</option>`;
                json.data.forEach(rol => {
                    const opt = document.createElement("option");
                    opt.value = rol.id_usuario;
                    opt.textContent = rol.nombre;
                    selectRoles.appendChild(opt);
                });
            } else {
                selectRoles.innerHTML = `<option value="">No se pudieron cargar los roles</option>`;
            }
        } catch (error) {
            console.error("Error cargando roles:", error);
            selectRoles.innerHTML = `<option value="">Error al cargar roles</option>`;
        }
    }

    // =========================
    // LOGIN
    // =========================
    async function loginHandler(e) {
        e.preventDefault();
        const usuario = formLogin.querySelector('input[name="usuario"]').value.trim();
        const password = formLogin.querySelector('input[name="password"]').value.trim();

        if (!usuario || !password) {
            Swal.fire("Campos incompletos", "Completa usuario y contraseña", "warning");
            return;
        }

        try {
            const res = await fetch(AUTH_URL, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                credentials: "include",
                body: JSON.stringify({ action: "login", usuario, password })
            });

            const json = await res.json().catch(() => null);

            if (!res.ok || !json.ok) {
                const msg = (json && json.error) || "Credenciales inválidas";
                Swal.fire("Error", msg, "error");
                return;
            }

            saveSession(json.data || json.user);
            Swal.fire({
                icon: "success",
                title: "Bienvenido",
                text: `Hola ${json.data?.usuario || json.user?.usuario || "Usuario"}`,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location.href = HOME_PAGE;
            });
        } catch (err) {
            console.error("Login error:", err);
            Swal.fire("Error", "Error de red al iniciar sesión", "error");
        }
    }

    // =========================
    // REGISTRO
    // =========================
    async function registerHandler(e) {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(formRegister).entries());
        data.action = "register";

        if (!data.usuario || !data.password || !data.email || !data.id_RolUsuario) {
            Swal.fire("Campos incompletos", "Completa todos los campos requeridos", "warning");
            return;
        }

        try {
            const res = await fetch(AUTH_URL, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data)
            });

            const json = await res.json().catch(() => null);

            if (!res.ok || !json.ok) {
                Swal.fire("Error", json?.error || "No se pudo registrar", "error");
                return;
            }

            Swal.fire({
                icon: "success",
                title: "Cuenta creada",
                text: "Ahora inicia sesión",
                timer: 1500,
                showConfirmButton: false
            }).then(() => window.location.reload());
        } catch (err) {
            console.error("Register error:", err);
            Swal.fire("Error", "Error de red al registrar", "error");
        }
    }

    // =========================
    // Inicializar
    // =========================
    document.addEventListener("DOMContentLoaded", () => {
        if (formLogin) formLogin.addEventListener("submit", loginHandler);
        if (formRegister) formRegister.addEventListener("submit", registerHandler);
        initToggleRegister();
        cargarRoles();
        mostrarUsuarioNavbar();
    });
})();
