(() => {
	// =========================
	// Variables base
	// =========================
	const PAGE_JSON = {
		navbar: BASE_URL + "data/navbar.json",
		footer: BASE_URL + "data/footer.json",
	};

	// =========================
	// Funciones helper
	// =========================
	const fetchJSON = (path) => {
		// Si la ruta ya es absoluta (http:// o https://), úsala directamente
		const url = /^(?:https?:)?\/\//i.test(path) ? path : BASE_URL + path;

		return fetch(url)
			.then((res) => {
				if (!res.ok) throw new Error(`Error ${res.status} en ${path}`);
				return res.json();
			})
			.catch((err) => console.error("Error cargando JSON:", err));
	};

	const fetchAPI = (endpoint) =>
		fetch(endpoint)
			.then((res) => {
				if (!res.ok) throw new Error(`Error ${res.status} en la API`);
				return res.json();
			})
			.catch((err) => console.error("Error al consumir la API:", err));

	const navList = document.getElementById("main-nav");
	const galleryContainer = document.querySelector(".gallery");
	const footer = document.getElementById("footer");

	// =========================
	// Resolver enlaces
	// =========================
	function resolveHref(href) {
		if (!href) return "#";
		if (/^(?:[a-z]+:)?\/\//i.test(href) || href.startsWith("/")) return href;
		if (href.startsWith("#")) return BASE_URL + "index.php" + href;
		return BASE_URL + href;
	}

	// =========================
	// Navbar
	// =========================
	function mostrarNavbar(data) {
		if (!navList) return;
		navList.innerHTML = data
			.map((item) => {
				const itemHref = resolveHref(item.href);
				const submenu = item["sub-module"]?.length
					? `<ul class="navbar__submenu">
						${item["sub-module"]
						.map(
							(sub) =>
								`<li class="${sub.class || "navbar__sub-items"
								}"><a href="${resolveHref(sub.href)}">${sub.text}</a></li>`
						)
						.join("")}
					</ul>`
					: "";
				return `<li class="${item.class || ""}">
						<a href="${itemHref}" class="navbar__links">${item.text}</a>
						${submenu}
					</li>`;
			})
			.join("");
	}

	// =========================
	// Resolver imágenes
	// =========================
	function getImageUrl(card) {
		const posible = card.imagen || card.img || card.image || "";
		if (!posible) return BASE_URL + "assets/img/no-photo.jpg";

		// Si es URL absoluta
		if (/^(?:https?:)?\/\//i.test(posible)) return posible;

		let ruta = posible.replace(/^(\.\.\/)+/, "").replace(/^\.\//, "");
		if (!/^\/?assets\/img\//i.test(ruta)) {
			ruta = "assets/img/" + ruta.replace(/^img\//, "");
		}

		if (ruta.startsWith("/")) return window.location.origin + ruta;
		return BASE_URL + ruta;
	}

	// =========================
	// Mostrar tarjetas (galería)
	// =========================
	function mostrarCards(data) {
		if (!galleryContainer) return;

		const items = Array.isArray(data)
			? data
			: Array.isArray(data.data)
				? data.data
				: [];

		if (!items.length) {
			galleryContainer.innerHTML = `<div class="gallery__empty">No hay productos disponibles.</div>`;
			return;
		}

		galleryContainer.innerHTML = items
			.map((card) => {
				const imgUrl = getImageUrl(card);
				const id =
					card.id_juego ??
					card.id ??
					card.idJuego ??
					card.idConsola ??
					card.ID ??
					"";
				const detalleUrl =
					BASE_URL +
					"modules/detalle-articulos/detalle.php?id_juego=" +
					encodeURIComponent(id);
				const title = card.titulo || card.title || card.nombre || "Sin título";
				const desc = card.descripcion || card.description || card.desc || "";
				const price = parseFloat(card.precio ?? card.price ?? 0) || 0;

				return `
				<div class="gallery__item">
					<div class="${card.class || "gallery__card"}">
						<img src="${imgUrl}" alt="${title}" class="gallery__img">
						<h2 class="gallery__title-card">${title}</h2>
						${(card.tag || card.tags || [])
						.map((tag) => `<span class="gallery__tag-card">${tag}</span>`)
						.join("") || ""
					}
						<p class="gallery__description-card">${desc}</p>
						<span class="gallery__price-card cossette-titre-bold">DOP $${price.toFixed(
						2
					)}</span>
						<a href="${detalleUrl}" class="gallery__btn-card">Ver más</a>
					</div>
				</div>`;
			})
			.join("");
	}

	// =========================
	// Footer
	// =========================
	function mostrarFooter(data) {
		if (!footer) return;
		footer.innerHTML = `
			<div class="footer__content">
				<div class="footer__title cossette-titre-bold">${data.brand.title}</div>
				${data.columns
				.map(
					(col) => `
					<div class="footer footer__header-links">
						<h4 class="footer__subtitle cossette-titre-bold">${col.title}</h4>
						<ul class="footer__list">
							${col.links
							.map(
								(link) =>
									`<li><a href="${resolveHref(
										link.href
									)}" class="footer__links">${link.text}</a></li>`
							)
							.join("")}
						</ul>
					</div>`
				)
				.join("")}
			</div>
			<div class="footer__text-copy">${data.copyright}</div>`;
	}

	// =========================
	// Inicialización
	// =========================
	if (PAGE_JSON) {
		const promises = [];

		// Navbar y Footer desde JSON
		if (PAGE_JSON.navbar)
			promises.push(fetchJSON(PAGE_JSON.navbar).then(mostrarNavbar));
		if (PAGE_JSON.footer)
			promises.push(fetchJSON(PAGE_JSON.footer).then(mostrarFooter));

		// Galería desde API
		promises.push(fetchAPI(BASE_URL + "api/juegos.php").then(mostrarCards));

		Promise.all(promises)
			.then(() => console.log("Contenido cargado correctamente"))
			.catch((err) => console.error("Error cargando contenido:", err));
	}
})();
