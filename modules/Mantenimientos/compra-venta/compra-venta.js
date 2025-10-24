const API_JUEGOS = "/TiendaOnline/api/juegos.php";
const API_INVENTARIO = "/TiendaOnline/api/inventario.php";
const SUBMIT_ORDER = "/TiendaOnline/modules/mantenimientos/compra-venta/submit_purchase.php";

let juegos = [];
let cart = {}; // id_juego => {id, titulo, precio, cantidad}

function fmt(n) {
    return Number(n).toFixed(2);
}

async function fetchJuegos() {
    try {
        const res = await fetch(API_JUEGOS);
        if (!res.ok) throw new Error("Error al cargar juegos");
        juegos = await res.json();
        renderJuegos();
    } catch (e) {
        console.error(e);
        const tbody = document.querySelector('.compra__catalog-table tbody');
        if (tbody) tbody.innerHTML = `<tr><td colspan="5">No se pudieron cargar los juegos.</td></tr>`;
    }
}

function renderJuegos() {
    const tbody = document.querySelector('.compra__catalog-table tbody');
    if (!tbody) return;
    tbody.innerHTML = '';
    juegos.forEach(j => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
			<td>${j.id_juego}</td>
			<td>${escapeHtml(j.titulo)}</td>
			<td><span class="compra__consola-tag" style="background:${escapeHtml(j.consola_color || '#cccccc')}; color:${textColorByBg(j.consola_color || '#cccccc')}; padding:4px 8px; border-radius:6px; display:inline-block">${escapeHtml(j.consola)}</span></td>
			<td>${fmt(j.precio)}</td>
			<td><button data-id="${j.id_juego}" class="compra__catalog-btn compra__catalog-btn--add">Agregar</button></td>
		`;
        tbody.appendChild(tr);
    });
    document.querySelectorAll('.compra__catalog-btn--add').forEach(btn => btn.addEventListener('click', e => {
        const id = e.target.dataset.id;
        addToCart(id);
    }));
}

function textColorByBg(hex) {
    if (!hex) return '#000';
    const h = String(hex).replace('#', '');
    if (h.length !== 6) return '#000';
    const r = parseInt(h.substr(0, 2), 16);
    const g = parseInt(h.substr(2, 2), 16);
    const b = parseInt(h.substr(4, 2), 16);
    const yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;
    return yiq >= 128 ? '#000' : '#fff';
}

async function checkStock(id, needed) {
    try {
        const res = await fetch(`${API_INVENTARIO}?id=${encodeURIComponent(id)}`);
        if (!res.ok) {
            // If inventory not found treat as no stock
            return false;
        }
        const data = await res.json();
        const available = parseInt(data.cantidad || 0);
        return available >= needed;
    } catch (e) {
        console.error('Error consultando inventario', e);
        return false;
    }
}

async function addToCart(id) {
    const j = juegos.find(x => String(x.id_juego) === String(id));
    if (!j) return;
    const currentQty = cart[id] ? cart[id].cantidad : 0;
    const desired = currentQty + 1;
    const ok = await checkStock(id, desired);
    if (!ok) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'error', title: 'Stock insuficiente', text: `No hay suficiente stock para ${j.titulo}.` });
        } else {
            alert('Stock insuficiente');
        }
        return;
    }
    if (!cart[id]) {
        cart[id] = { id: j.id_juego, titulo: j.titulo, precio: parseFloat(j.precio), cantidad: 1 };
    } else {
        cart[id].cantidad += 1;
    }
    saveCart();
    renderCart();
    // toast
    if (typeof Swal !== 'undefined') {
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: `${j.titulo} agregado`, showConfirmButton: false, timer: 1400 });
    }
}

function removeFromCart(id) {
    delete cart[id];
    saveCart();
    renderCart();
}

function updateQuantity(id, qty) {
    qty = parseInt(qty) || 0;
    if (qty <= 0) {
        removeFromCart(id);
        return;
    }
    if (cart[id]) {
        cart[id].cantidad = qty;
        saveCart();
        renderCart();
    }
}

function renderCart() {
    const tbody = document.querySelector('.compra__cart-table tbody');
    const table = document.querySelector('.compra__cart-table');
    const empty = document.querySelector('.compra__cart-empty');
    const checkoutBtn = document.getElementById('realizar-compra');

    const items = Object.values(cart);
    if (items.length === 0) {
        if (table) table.classList.add('hidden');
        if (empty) empty.classList.remove('hidden');
        if (checkoutBtn) checkoutBtn.classList.add('hidden');
        const resEl = document.querySelector('.compra__result'); if (resEl) resEl.innerText = '';
        return;
    }

    if (empty) empty.classList.add('hidden');
    if (table) table.classList.remove('hidden');
    if (checkoutBtn) checkoutBtn.classList.remove('hidden');

    tbody.innerHTML = '';
    let total = 0;
    items.forEach(it => {
        const tr = document.createElement('tr');
        const subtotal = it.precio * it.cantidad;
        total += subtotal;
        tr.innerHTML = `
			<td>${escapeHtml(it.titulo)}</td>
			<td>${fmt(it.precio)}</td>
			<td><input type="number" min="1" max="100" value="${it.cantidad}" data-id="${it.id}" class="compra__cart-qty" /></td>
			<td>${fmt(subtotal)}</td>
			<td><button data-id="${it.id}" class="compra__cart-remove">Quitar</button></td>
		`;
        tbody.appendChild(tr);
    });

    // remove buttons will be handled by delegated listener below to allow confirmation
    // document.querySelectorAll('.compra__cart-remove').forEach(b => b.addEventListener('click', e => removeFromCart(e.target.dataset.id)));
    document.querySelectorAll('.compra__cart-qty').forEach(inp => inp.addEventListener('change', async e => await updateQuantity(e.target.dataset.id, e.target.value)));

    const totalEl = document.querySelector('.compra__cart-total'); if (totalEl) totalEl.innerText = fmt(total);
    const orderDataEl = document.querySelector('.compra__order-data'); if (orderDataEl) orderDataEl.value = JSON.stringify({ items, total });
}

function saveCart() {
    try { localStorage.setItem('tienda_cart', JSON.stringify(cart)); } catch (e) { console.warn('No se pudo guardar carrito', e); }
}

function loadCart() {
    try {
        const raw = localStorage.getItem('tienda_cart');
        if (raw) cart = JSON.parse(raw);
    } catch (e) { cart = {}; }
}

function escapeHtml(s) {
    if (s === null || s === undefined) return '';
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

document.addEventListener('DOMContentLoaded', () => {
    loadCart();
    fetchJuegos();
    renderCart();

    // Purchase button flow: require login (session) and then submit order using server-side session data
    const checkoutBtn = document.getElementById('realizar-compra');
    const orderDataEl = document.querySelector('.compra__order-data');
    if (!checkoutBtn || !orderDataEl) return;

    // Show/hide button based on cart state inside renderCart

    checkoutBtn.addEventListener('click', async (e) => {
        e.preventDefault();

        // Check if user is logged in (server will respond with user data)
        try {
            const me = await fetch('/TiendaOnline/api/auth.php');
            if (!me.ok) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'No autenticado', text: 'Debes iniciar sesión para realizar una compra.' });
                } else alert('Debes iniciar sesión para realizar una compra.');
                return;
            }
            const meJson = await me.json();
            if (!meJson || !meJson.ok || !meJson.user) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'No autenticado', text: 'Debes iniciar sesión para realizar una compra.' });
                } else alert('Debes iniciar sesión para realizar una compra.');
                return;
            }
        } catch (err) {
            console.warn('Error checking session', err);
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'warning', title: 'No autenticado', text: 'Debes iniciar sesión para realizar una compra.' });
            } else alert('Debes iniciar sesión para realizar una compra.');
            return;
        }

        // Build order from current cart (orderDataEl populated in renderCart)
        const raw = orderDataEl.value || '{}';
        let order;
        try { order = JSON.parse(raw); } catch (e) { order = null; }
        if (!order || !Array.isArray(order.items) || order.items.length === 0) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'info', title: 'Carrito vacío', text: 'Agrega productos al carrito antes de comprar.' });
            else alert('Carrito vacío');
            return;
        }

        // Validate stock before sending
        for (const it of order.items) {
            const okStock = await checkStock(it.id, it.cantidad);
            if (!okStock) {
                if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'Stock insuficiente', text: `No hay suficiente stock para ${it.titulo}. Ajusta la cantidad.` });
                else alert('Stock insuficiente para ' + it.titulo);
                return;
            }
        }

        try {
            const res = await fetch(SUBMIT_ORDER, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(order) });
            const json = await res.json();
            if (res.ok) {
                if (typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: 'Compra realizada', text: json.messages ? 'Compra procesada' : 'Gracias' });
                cart = {}; saveCart(); renderCart();
            } else {
                const err = json.error || 'No se pudo crear la orden';
                if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'Error', text: err });
            }
        } catch (err) {
            console.error('Error enviando orden', err);
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'Error', text: 'Error de red al enviar la orden.' });
        }
    });
});

// Delegated listener for remove buttons (works even after re-render)
document.addEventListener('click', function (e) {
    const btn = e.target.closest && e.target.closest('.compra__cart-remove');
    if (!btn) return;
    e.preventDefault();
    const id = btn.dataset.id;
    if (typeof Swal !== 'undefined') {
        Swal.fire({ title: 'Quitar producto', text: '¿Deseas quitar este producto del carrito?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, quitar', cancelButtonText: 'Cancelar' }).then(result => {
            if (result.isConfirmed) {
                removeFromCart(id);
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Producto quitado', showConfirmButton: false, timer: 1200 });
            }
        });
    } else {
        if (confirm('¿Quitar este producto del carrito?')) removeFromCart(id);
    }
});
