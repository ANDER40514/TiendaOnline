// =======================================
// Configuración API
// =======================================
const API_JUEGOS = "/TiendaOnline/api/index.php?endpoint=juegos";
const API_INVENTARIO = "/TiendaOnline/api/index.php?endpoint=inventario";
const API_CLIENTES = "/TiendaOnline/api/index.php?endpoint=clientes";
const SUBMIT_ORDER = "/TiendaOnline/modules/mantenimientos/compra-venta/submit_purchase.php";
const LOGIN_PAGE = "/TiendaOnline/modules/auth/login.php";

// =======================================
// Variables globales
// =======================================
let juegos = [];
let cart = {}; // id_juego => {id, titulo, precio, cantidad}

// =======================================
// Funciones auxiliares
// =======================================
function fmt(n) {
    return Number(n).toFixed(2);
}

function escapeHtml(s) {
    if (s === null || s === undefined) return "";
    return String(s)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");
}

function textColorByBg(hex) {
    if (!hex) return "#000";
    const h = String(hex).replace("#", "");
    if (h.length !== 6) return "#000";
    const r = parseInt(h.substr(0, 2), 16);
    const g = parseInt(h.substr(2, 2), 16);
    const b = parseInt(h.substr(4, 2), 16);
    const yiq = (r * 299 + g * 587 + b * 114) / 1000;
    return yiq >= 128 ? "#000" : "#fff";
}

// =======================================
// Sesión de usuario
// =======================================
function getUser() {
    const raw = sessionStorage.getItem("user");
    return raw ? JSON.parse(raw) : null;
}

function ensureSession() {
    const user = getUser();
    if (!user) {
        const next = encodeURIComponent(window.location.href);
        window.location.href = `${LOGIN_PAGE}?next=${next}`;
        return false;
    }
    return true;
}

// =======================================
// LocalStorage carrito
// =======================================
function saveCart() {
    localStorage.setItem("tienda_cart", JSON.stringify(cart));
}

function loadCart() {
    try {
        cart = JSON.parse(localStorage.getItem("tienda_cart")) || {};
    } catch {
        cart = {};
    }
}

// =======================================
// Fetch juegos
// =======================================
async function fetchJuegos() {
    try {
        const res = await fetch(API_JUEGOS);
        if (!res.ok) throw new Error("Error al cargar juegos");
        const json = await res.json();
        // Asegurarse que sea array
        juegos = Array.isArray(json.data) ? json.data : [];
        renderJuegos();
    } catch (e) {
        console.error(e);
        const tbody = document.querySelector(".compra__catalog-table tbody");
        if (tbody)
            tbody.innerHTML = `<tr><td colspan="5">No se pudieron cargar los juegos.</td></tr>`;
    }
}

// =======================================
// Render catálogo
// =======================================
function renderJuegos() {
    const tbody = document.querySelector(".compra__catalog-table tbody");
    if (!tbody) return;
    tbody.innerHTML = "";
    juegos.forEach((j) => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td>${j.id_juego}</td>
            <td>${escapeHtml(j.titulo)}</td>
            <td>
                <span class="compra__consola-tag" style="
                    background:${escapeHtml(j.consola_color || "#cccccc")};
                    color:${textColorByBg(j.consola_color || "#cccccc")};
                    padding:4px 8px;
                    border-radius:6px;
                    display:inline-block;
                ">${escapeHtml(j.nombre_consola)}</span>
            </td>
            <td>${fmt(j.precio)}</td>
            <td><button data-id="${j.id_juego}" class="compra__catalog-btn compra__catalog-btn--add">Agregar</button></td>
        `;
        tbody.appendChild(tr);
    });

    document.querySelectorAll(".compra__catalog-btn--add").forEach((btn) => {
        btn.addEventListener("click", async (e) => {
            const id = e.target.dataset.id;
            if (!ensureSession()) return; // Redirige login si no hay sesión
            await addToCart(id);
        });
    });
}

// =======================================
// Check stock
// =======================================
async function checkStock(id, needed) {
    try {
        const res = await fetch(`${API_INVENTARIO}&id=${encodeURIComponent(id)}`);
        if (!res.ok) return false;
        const data = await res.json();
        const available = parseInt(data.cantidad || 0);
        return available >= needed;
    } catch (e) {
        console.error("Error consultando inventario", e);
        return false;
    }
}

// =======================================
// Carrito
// =======================================
async function addToCart(id) {
    const j = juegos.find((x) => String(x.id_juego) === String(id));
    if (!j) return;

    const currentQty = cart[id] ? cart[id].cantidad : 0;
    const desired = currentQty + 1;

    if (!(await checkStock(id, desired))) {
        Swal.fire({
            icon: "error",
            title: "Stock insuficiente",
            text: `No hay suficiente stock para ${j.titulo}.`,
        });
        return;
    }

    cart[id] = cart[id] || {
        id: j.id_juego,
        titulo: j.titulo,
        precio: parseFloat(j.precio),
        cantidad: 0,
    };
    cart[id].cantidad += 1;

    saveCart();
    renderCart();

    Swal.fire({
        toast: true,
        position: "top-end",
        icon: "success",
        title: `${j.titulo} agregado`,
        showConfirmButton: false,
        timer: 1400,
    });
}

function removeFromCart(id) {
    delete cart[id];
    saveCart();
    renderCart();
}

async function updateQuantity(id, qty) {
    qty = parseInt(qty) || 0;
    if (qty <= 0) {
        removeFromCart(id);
        return;
    }

    if (!(await checkStock(id, qty))) {
        Swal.fire({
            icon: "error",
            title: "Stock insuficiente",
            text: "No hay suficiente stock para ajustar la cantidad.",
        });
        renderCart();
        return;
    }

    if (cart[id]) {
        cart[id].cantidad = qty;
        saveCart();
        renderCart();
    }
}

// =======================================
// Render carrito
// =======================================
function renderCart() {
    const tbody = document.querySelector(".compra__cart-table tbody");
    const table = document.querySelector(".compra__cart-table");
    const empty = document.querySelector(".compra__cart-empty");
    const checkoutBtn = document.getElementById("realizar-compra");

    const items = Object.values(cart);
    if (!items.length) {
        table?.classList.add("hidden");
        empty?.classList.remove("hidden");
        checkoutBtn?.classList.add("hidden");
        document.querySelector(".compra__result").innerText = "";
        return;
    }

    empty?.classList.add("hidden");
    table?.classList.remove("hidden");
    checkoutBtn?.classList.remove("hidden");

    tbody.innerHTML = "";
    let total = 0;
    items.forEach((it) => {
        const subtotal = it.precio * it.cantidad;
        total += subtotal;
        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td>${escapeHtml(it.titulo)}</td>
            <td>${fmt(it.precio)}</td>
            <td><input type="number" min="1" max="100" value="${it.cantidad}" data-id="${it.id}" class="compra__cart-qty" /></td>
            <td>${fmt(subtotal)}</td>
            <td><button data-id="${it.id}" class="compra__cart-remove">Quitar</button></td>
        `;
        tbody.appendChild(tr);
    });

    tbody.querySelectorAll(".compra__cart-qty").forEach((inp) => {
        inp.addEventListener("change", (e) =>
            updateQuantity(e.target.dataset.id, e.target.value)
        );
    });

    document.querySelector(".compra__cart-total").innerText = fmt(total);
    document.querySelector(".compra__order-data").value = JSON.stringify({ items, total });
}

// =======================================
// Checkout
// =======================================
async function checkout() {
    const orderDataEl = document.querySelector(".compra__order-data");
    const raw = orderDataEl?.value || "{}";
    let order;
    try {
        order = JSON.parse(raw);
    } catch {
        order = null;
    }

    if (!order || !Array.isArray(order.items) || order.items.length === 0) {
        Swal.fire({ icon: "info", title: "Carrito vacío", text: "Agrega productos antes de comprar." });
        return;
    }

    for (const it of order.items) {
        if (!(await checkStock(it.id, it.cantidad))) {
            Swal.fire({ icon: "error", title: "Stock insuficiente", text: `No hay suficiente stock para ${it.titulo}.` });
            return;
        }
    }

    try {
        const res = await fetch(SUBMIT_ORDER, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(order),
        });
        const json = await res.json();
        if (res.ok) {
            Swal.fire({ icon: "success", title: "Compra realizada", text: json.messages ? "Compra procesada" : "Gracias" });
            cart = {};
            saveCart();
            renderCart();
        } else {
            Swal.fire({ icon: "error", title: "Error", text: json.error || "No se pudo crear la orden" });
        }
    } catch (err) {
        console.error(err);
        Swal.fire({ icon: "error", title: "Error", text: "Error de red al enviar la orden." });
    }
}

// =======================================
// Inicialización
// =======================================
document.addEventListener("DOMContentLoaded", () => {
    loadCart();
    fetchJuegos();
    renderCart();

    document.getElementById("realizar-compra")?.addEventListener("click", checkout);

    // Quitar del carrito
    document.querySelector(".compra__cart-table tbody")?.addEventListener("click", (e) => {
        const btn = e.target.closest(".compra__cart-remove");
        if (!btn) return;
        const id = btn.dataset.id;
        Swal.fire({
            title: "Quitar producto",
            text: "¿Deseas quitar este producto del carrito?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, quitar",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                removeFromCart(id);
                Swal.fire({ toast: true, position: "top-end", icon: "success", title: "Producto quitado", showConfirmButton: false, timer: 1200 });
            }
        });
    });
});
