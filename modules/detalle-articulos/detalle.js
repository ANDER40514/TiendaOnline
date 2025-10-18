(() => {
    const getIdFromUrl = () => new URLSearchParams(window.location.search).get("id_juego");

    // Función para obtener la URL correcta de la imagen o un placeholder
    function getImageUrl(card) {
        const posible = card.imagen || card.img || card.image || "";

        if (!posible) return BASE_URL + "assets/img/no-photo.jpg";

        // Si ya es una URL absoluta (http(s)://) devuélvela tal cual
        if (/^(?:https?:)?\/\//i.test(posible)) return posible;

        // Normaliza rutas relativas: quita prefijos ../ o ./ al inicio
        let ruta = posible.replace(/^(\.\.\/)+/, "").replace(/^\.\//, "");

        // Si la ruta ya empieza por 'img/' o '/img', mantenla; si no, asume que es 'img/...'
        if (!/^\/?img\//i.test(ruta)) ruta = "assets/img/" + ruta;

        if (ruta.startsWith("/")) return window.location.origin + ruta;

        return BASE_URL + "assets/" + ruta;
    }

    const renderProducto = (producto) => {
        const detalle = document.getElementById("detalle__producto");

        if (!producto || !producto.titulo) {
            detalle.innerHTML = "<p>Producto no encontrado.</p>";
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
                <p class="detalle__tag">Consola: ${producto.consola}</p>
                <a class="detalle__btn" href="${BASE_URL}">Volver</a>
            </div>
        `;
    };

    document.addEventListener("DOMContentLoaded", async () => {
        const id_juego = getIdFromUrl();
        if (!id_juego) {
            document.getElementById("detalle__producto").innerHTML =
                "<p>ID del producto no especificado.</p>";
            return;
        }

        try {
            const res = await fetch(`${BASE_URL}api/juegos.php?id=${id_juego}`);
            if (!res.ok) throw new Error(`Error ${res.status}`);
            const producto = await res.json();
            renderProducto(producto);
        } catch (error) {
            console.error(error);
            document.getElementById("detalle__producto").innerHTML =
                "<p>Error cargando el producto.</p>";
        }
    });
})();