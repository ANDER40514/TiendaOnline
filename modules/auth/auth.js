(() => {
    const API_CLIENTES = 'http://localhost/TiendaOnline/api/index.php?endpoint=clientes';
    const roleSelect = document.querySelector('.auth__role-select');

    function qs(form) {
        const fd = new FormData(form);
        const o = {};
        for (const [k, v] of fd.entries()) o[k] = v;
        return o;
    }

    async function postAction(payload) {
        const res = await fetch(API_CLIENTES, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        return res;
    }

    async function fetchRoles() {
        try {
            const res = await fetch('http://localhost/TiendaOnline/api/index.php?endpoint=roles');
            if (!res.ok) throw new Error('No roles');
            const json = await res.json();
            const list = Array.isArray(json.data) ? json.data : [];
            if (roleSelect) {
                roleSelect.innerHTML = list.map(r => `<option value="${r.id_usuario}">${r.nombre} (${r.rol})</option>`).join('');
            }
        } catch (e) {
            if (roleSelect) roleSelect.innerHTML = '<option value="">(No disponible)</option>';
        }
    }

    async function loginUser(data) {
        try {
            const res = await fetch(API_CLIENTES);
            if (!res.ok) throw new Error('Error fetching clients');
            const json = await res.json();
            const clients = Array.isArray(json.data) ? json.data : [];

            // Buscar usuario por nombre
            const user = clients.find(u => u.cliente === data.usuario);
            if (!user) throw new Error('Usuario no encontrado');

            // Validar password (hash o plain)
            const valid = user.password.startsWith('$2y$') // hash bcrypt
                ? await bcryptCompare(data.password, user.password)
                : data.password === user.password;

            if (!valid) throw new Error('Contraseña incorrecta');

            // Guardar sesión en localStorage (puedes adaptar)
            localStorage.setItem('tienda_user', JSON.stringify(user));

            if (window.Swal) {
                Swal.fire({ icon: 'success', title: 'Bienvenido', text: `Usuario: ${user.cliente}` })
                    .then(() => window.location.href = BASE_URL + 'index.php');
            } else {
                window.location.href = BASE_URL + 'index.php';
            }
        } catch (err) {
            if (window.Swal) Swal.fire({ icon: 'error', title: 'Error', text: err.message });
            else alert(err.message);
        }
    }

    async function registerUser(data) {
        try {
            const payload = {
                action: 'register',
                usuario: data.usuario,
                password: data.password,
                email: data.email,
                direccion: data.direccion,
                telefono: data.telefono,
                id_RolUsuario: data.id_RolUsuario
            };

            const res = await postAction(payload);
            const json = await res.json();

            if (res.ok) {
                if (window.Swal) {
                    Swal.fire({ icon: 'success', title: 'Cuenta creada', text: 'Bienvenido ' + json.user.cliente })
                        .then(() => window.location.href = BASE_URL + 'index.php');
                } else {
                    window.location.href = BASE_URL + 'index.php';
                }
            } else {
                throw new Error(json.error || 'No se pudo crear la cuenta');
            }
        } catch (err) {
            if (window.Swal) Swal.fire({ icon: 'error', title: 'Error', text: err.message });
            else alert(err.message);
        }
    }

    // bcrypt compare simulada para front-end (solo para demo, ideal usar back-end)
    async function bcryptCompare(password, hash) {
        // Aquí se recomienda que la validación se haga en backend
        // En front-end solo una demo usando bcryptjs si está cargado
        if (typeof bcrypt !== 'undefined' && bcrypt.compare) {
            return await bcrypt.compare(password, hash);
        }
        return false; // si no hay bcrypt, no validar hash
    }

    document.addEventListener('DOMContentLoaded', () => {
        fetchRoles();

        const loginForm = document.getElementById('login-form');
        const registerForm = document.getElementById('register-form');
        const toggleBtn = document.getElementById('toggle-register');
        const registerPanel = document.getElementById('register-panel');

        if (toggleBtn && registerPanel) {
            toggleBtn.addEventListener('click', () => {
                const hidden = registerPanel.classList.toggle('hidden');
                toggleBtn.innerText = hidden ? 'Mostrar formulario de registro' : 'Ocultar formulario de registro';
            });
        }

        if (loginForm) {
            loginForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const data = qs(loginForm);
                loginUser(data);
            });
        }

        if (registerForm) {
            registerForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const data = qs(registerForm);
                registerUser(data);
            });
        }
    });
})();
