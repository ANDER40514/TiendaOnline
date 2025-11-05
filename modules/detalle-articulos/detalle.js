(() => {
    const getIdFromUrl = () =>
        new URLSearchParams(window.location.search).get("id_juego");

    const getImageUrl = (card) => {
        const posible = card.imagen || card.img || card.image || "";
        if (!posible) return BASE_URL + "assets/img/no-photo.jpg";
        if (/^(?:https?:)?\/\//i.test(posible)) return posible;
        let ruta = posible.replace(/^(\.\.\/)+/, "").replace(/^\.\//, "");
        if (!/^\/?img\//i.test(ruta)) ruta = "assets/img/" + ruta;
        if (ruta.startsWith("/")) return window.location.origin + ruta;
        return BASE_URL + "assets/" + ruta;
    };

    const renderProducto = (producto) => {
        const detalle = document.getElementById("detalle__producto");

        if (!producto || !producto.titulo) {
            detalle.innerHTML = "<p style='color:black; font-weight:bold;'>Producto no encontrado.</p>";
            return;
        }

        const imgUrl = getImageUrl(producto);

        detalle.innerHTML = `
            <div class="detalle__container-img">
                <img class="detalle__img" src="${imgUrl}" alt="${producto.titulo}">
            </div>
            <div class="detalle__info">
                <h1 class="detalle__title">${producto.titulo}</h1>
                <p class="detalle__description">${producto.descripcion}</p>
                <div class="detalle__price">DOP $${parseFloat(producto.precio).toFixed(2)}</div>
                <p class="detalle__tag">Consola: 
                    <span class="detalle__tag-badge" 
                          style="background:${producto.consola_color || "#cccccc"};
                                 color:${textColorByBg(producto.consola_color || "#cccccc")};
                                 padding:6px 10px; border-radius:999px;">
                        ${producto.nombre_consola || "N/A"}
                    </span>
                </p>

                <div class="detalle__purchase">
                    <label class="detalle__purchase-field">Cantidad
                        <input type="number" min="1" value="1" class="detalle__purchase-qty" />
                    </label>
                    <div class="detalle__purchase-actions">
                        <button class="detalle__btn detalle__btn--add">Agregar al carrito</button>
                        <button class="detalle__btn detalle__btn--goto" 
                                data-href="${BASE_URL}modules/Mantenimientos/compra-venta/compra-venta.php">
                            Ir al carrito
                        </button>
                    </div>
                    <div class="detalle__purchase-msg" aria-live="polite"></div>
                </div>

                <a class="detalle__btn detalle__btn--back" href="${BASE_URL}">Volver</a>
            </div>
        `;
    };

    const textColorByBg = (hex) => {
        if (!hex) return "#000";
        const h = String(hex).replace("#", "");
        if (h.length !== 6) return "#000";
        const r = parseInt(h.substr(0, 2), 16);
        const g = parseInt(h.substr(2, 2), 16);
        const b = parseInt(h.substr(4, 2), 16);
        const yiq = (r * 299 + g * 587 + b * 114) / 1000;
        return yiq >= 128 ? "#000" : "#fff";
    };

    document.addEventListener("DOMContentLoaded", async () => {
        const id_juego = getIdFromUrl();
        const detalleEl = document.getElementById("detalle__producto");
        if (!id_juego) {
            detalleEl.innerHTML = "<p style='color:black; font-weight:bold;'>ID del producto no especificado.</p>";
            return;
        }

        try {
            const res = await fetch(`${BASE_URL}api/index.php?endpoint=juegos&id=${id_juego}`);
            if (!res.ok) throw new Error(`Error ${res.status}`);
            const json = await res.json();

            console.log("JSON recibido:", json);

            // Detecta si data es objeto o array
            const producto = Array.isArray(json.data)
                ? json.data[0]
                : (json.data && typeof json.data === 'object')
                    ? json.data
                    : null;

            console.log("Producto extraído:", producto);

            if (!producto) {
                detalleEl.innerHTML = "<p style='color:black; font-weight:bold;'>Producto no encontrado.</p>";
                return;
            }

            renderProducto(producto);

            const addBtn = document.querySelector(".detalle__btn--add");
            const gotoBtn = document.querySelector(".detalle__btn--goto");
            const qtyEl = document.querySelector(".detalle__purchase-qty");
            const msgEl = document.querySelector(".detalle__purchase-msg");

            const loadCart = () => {
                try { return JSON.parse(localStorage.getItem("tienda_cart")) || {}; } 
                catch (e) { return {}; }
            };
            const saveCart = (cart) => {
                try { localStorage.setItem("tienda_cart", JSON.stringify(cart)); } 
                catch (e) { }
            };

            if (addBtn)
                addBtn.addEventListener("click", async () => {
                    const qty = Math.max(1, parseInt(qtyEl.value) || 1);
                    const id = String(producto.id_juego || id_juego);

                    // Consulta inventario
                    try {
                        const resInv = await fetch(`${BASE_URL}api/inventario.php?id=${encodeURIComponent(id)}`);
                        if (!resInv.ok) throw new Error("No inventory");
                        const inv = await resInv.json();
                        const available = parseInt(inv.cantidad || 0);
                        if (available < qty) {
                            if (typeof Swal !== "undefined") {
                                Swal.fire({
                                    icon: "error",
                                    title: "Stock insuficiente",
                                    text: `Solo disponemos de ${available} unidades.`,
                                });
                            } else if (msgEl) {
                                msgEl.textContent = `Stock insuficiente. Disponibles: ${available}`;
                            }
                            return;
                        }
                    } catch (e) {
                        console.warn("No se pudo consultar inventario", e);
                    }

                    const cart = loadCart();
                    if (!cart[id]) {
                        cart[id] = {
                            id: id,
                            titulo: producto.titulo,
                            precio: parseFloat(producto.precio) || 0,
                            cantidad: qty,
                        };
                    } else {
                        cart[id].cantidad = (cart[id].cantidad || 0) + qty;
                    }
                    saveCart(cart);

                    if (typeof Swal !== "undefined") {
                        Swal.fire({
                            toast: true,
                            position: "top-end",
                            icon: "success",
                            title: `${qty} × ${producto.titulo} agregado al carrito`,
                            showConfirmButton: false,
                            timer: 1500,
                        });
                    } else if (msgEl) {
                        msgEl.textContent = `Agregado ${qty} × ${producto.titulo} al carrito`;
                        setTimeout(() => { if (msgEl) msgEl.textContent = ""; }, 3000);
                    }
                });

            if (gotoBtn)
                gotoBtn.addEventListener("click", () => {
                    window.location.href = `${BASE_URL}modules/Mantenimientos/compra-venta/compra-venta.php`;
                });

        } catch (error) {
            console.error(error);
            detalleEl.innerHTML = "<p style='color:black; font-weight:bold;'>Error cargando el producto.</p>";
        }
    });
})();
