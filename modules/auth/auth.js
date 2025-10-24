(() => {
    const API = '/TiendaOnline/api/auth.php';

    function qs(form) {
        const fd = new FormData(form);
        const o = {};
        for (const [k, v] of fd.entries()) o[k] = v;
        return o;
    }

    async function postAction(payload) {
        const res = await fetch(API, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
        return res;
    }

    document.addEventListener('DOMContentLoaded', () => {
        const loginForm = document.getElementById('login-form');
        const registerForm = document.getElementById('register-form');
        const toggleBtn = document.getElementById('toggle-register');
        const registerPanel = document.getElementById('register-panel');
    const roleSelect = document.querySelector('.auth__role-select');

        (async () => {
            try {
                const res = await fetch('/TiendaOnline/api/roles.php');
                if (!res.ok) throw new Error('No roles');
                const list = await res.json();
                if (Array.isArray(list) && roleSelect) {
                    roleSelect.innerHTML = list.map(r => `<option value="${r.id_usuario}">${r.nombre} (${r.rol})</option>`).join('');
                }
            } catch (e) {
                if (roleSelect) roleSelect.innerHTML = '<option value="">(No disponible)</option>';
            }
        })();

        // collapse toggle
        if (toggleBtn && registerPanel) toggleBtn.addEventListener('click', () => {
            const hidden = registerPanel.classList.toggle('hidden');
            toggleBtn.innerText = hidden ? 'Mostrar formulario de registro' : 'Ocultar formulario de registro';
        });

        if (loginForm) loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = qs(loginForm);
            const res = await postAction({ action: 'login', usuario: data.usuario, password: data.password });
            const j = await res.json();
            if (res.ok) {
                if (window.Swal) Swal.fire({ icon: 'success', title: 'Bienvenido', text: j.user.cliente || '' }).then(() => window.location.href = BASE_URL + 'index.php' );
                else window.location.href = BASE_URL + 'index.php';
            } else {
                if (window.Swal) Swal.fire({ icon: 'error', title: 'Error', text: j.error || 'Credenciales inválidas' });
                else alert(j.error || 'Credenciales inválidas');
            }
        });

        if (registerForm) registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = qs(registerForm);
            const payload = { action: 'register', usuario: data.usuario, password: data.password, email: data.email, direccion: data.direccion, telefono: data.telefono, id_RolUsuario: data.id_RolUsuario };
            const res = await postAction(payload);
            const j = await res.json();
            if (res.ok) {
                if (window.Swal) Swal.fire({ icon: 'success', title: 'Cuenta creada', text: 'Bienvenido ' + j.user.cliente }).then(() => window.location.href = BASE_URL + 'index.php' );
                else window.location.href = BASE_URL + 'index.php';
            } else {
                if (window.Swal) Swal.fire({ icon: 'error', title: 'Error', text: j.error || 'No se pudo crear la cuenta' });
                else alert(j.error || 'No se pudo crear la cuenta');
            }
        });

    });

})();
