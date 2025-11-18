(() => {
	// =========================
	// Configuración BASE
	// =========================
	const BASE_URL = window.location.origin + "/TiendaOnline/";
	const API_URL = BASE_URL + "api/";

	// =========================
	// Archivos base (JSON locales)
	// =========================
	const PAGE_JSON = {
		navbar: BASE_URL + "data/navbar.json",
		footer: BASE_URL + "data/footer.json",
	};

	// =========================
	// Elementos del DOM
	// =========================
	const navList = document.getElementById("main-nav");
	const galleryContainer = document.querySelector(".gallery");
	const footer = document.getElementById("footer");

	// =========================
	// Funciones Helper
	// =========================
	const fetchJSON = async (path) => {
		try {
			const res = await fetch(path);
			if (!res.ok) throw new Error(`Error ${res.status} al cargar ${path}`);
			return await res.json();
		} catch (err) {
			console.error("Error cargando JSON:", err);
			return null;
		}
	};

	const fetchAPI = async (endpoint) => {
		try {
			const res = await fetch(endpoint);
			if (!res.ok) throw new Error(`Error ${res.status} al consumir la API`);
			const json = await res.json();

			if (json && Array.isArray(json.data)) return json.data;
			if (json && json.id_juego !== undefined) return [json];
			return [];
		} catch (err) {
			console.error("Error al consumir la API:", err);
			return [];
		}
	};

	const resolveHref = (href) => {
		if (!href) return "#";
		if (/^(?:[a-z]+:)?\/\//i.test(href) || href.startsWith("/")) return href;
		if (href.startsWith("#")) return BASE_URL + "index.php" + href;
		return BASE_URL + href;
	};

	const getUser = () => {
		const raw = sessionStorage.getItem("user") || localStorage.getItem("tienda_user");
		return raw ? JSON.parse(raw) : null;
	};

	// =========================
	// Navbar
	// =========================
	function mostrarNavbar(data) {
		if (!navList) return;
		const items = (data || []).filter(item => item && item.text);

		navList.innerHTML = items
			.map(item => {
				const itemHref = resolveHref(item.href);
				const submenu = item["sub-module"]?.length
					? `<ul class="navbar__submenu">
                        ${item["sub-module"]
						.map(sub => `<li class="${sub.class || "navbar__sub-items"}">
                                <a href="${resolveHref(sub.href)}">${sub.text}</a>
                            </li>`).join("")}
                    </ul>`
					: "";
				return `<li class="${item.class || ""}">
                            <a href="${itemHref}" class="navbar__links">${item.text}</a>
                            ${submenu}
                        </li>`;
			}).join("");

		// Mostrar login o usuario
		const user = getUser();
		const liExtra = document.createElement("li");
		liExtra.classList.add("navbar__extra");

		if (user) {
			liExtra.innerHTML = `
				<a href="#" class="navbar__links navbar__links--user">👤 ${user.cliente || user.usuario || "Usuario"}</a>
				<a href="#" id="logout-link" class="navbar__links navbar__links--logout">Cerrar sesión</a>
			`;
			navList.appendChild(liExtra);

			// Logout
			const logoutBtn = document.getElementById("logout-link");
			logoutBtn?.addEventListener("click", (e) => {
				e.preventDefault();
				sessionStorage.removeItem("user");
				localStorage.removeItem("tienda_user");
				location.reload();
			});
		} else {
			liExtra.innerHTML = `
				<a href="${BASE_URL}modules/auth/login.php" class="navbar__links navbar__links--login">Iniciar sesión</a>
			`;
			navList.appendChild(liExtra);
		}
	}

	// =========================
	// Galería (juegos)
	// =========================
	function mostrarCards(data) {
		if (!galleryContainer) return;
		const items = Array.isArray(data) ? data : [];

		if (!items.length) {
			galleryContainer.innerHTML = `<div class="gallery__empty">No hay productos disponibles.</div>`;
			return;
		}

		galleryContainer.innerHTML = items
			.map(card => {
				const img = card.imagen || card.image || "/img/no-photo.jpg";
				const title = card.titulo || card.nombre || "Sin título";
				const desc = card.descripcion || "";
				const price = parseFloat(card.precio || 0);
				const id = card.id_juego;
				const detalleUrl = BASE_URL + "modules/detalle-articulos/detalle.php?id_juego=" + encodeURIComponent(id);

				return `
                    <div class="gallery__item">
                        <div class="gallery__card">
                            <img src="${BASE_URL + "/assets/" + img}" alt="${title}" class="gallery__img">
                            <h2 class="gallery__title-card">${title}</h2>
                            <p class="gallery__description-card">${desc}</p>
                            <span class="gallery__price-card">DOP $${price.toFixed(2)}</span>
                            <a href="${detalleUrl}" class="gallery__btn-card">Ver más</a>
                        </div>
                    </div>`;
			}).join("");
	}

	// =========================
	// Footer
	// =========================
	function mostrarFooter(data) {
		if (!footer || !data) return;
		footer.innerHTML = `
            <div class="footer__content">
                <div class="footer__title">${data.brand?.title || ""}</div>
                ${(data.columns || []).map(col => `
                    <div class="footer__section">
						<h4 class="footer__subtitle cossette-titre-bold">${col.title}</h4>
						<ul class="footer__list">
                            ${col.links.map(link => `<li><a class="footer__links" href="${resolveHref(link.href)}">${link.text}</a></li>`).join("")}
                        </ul>
                    </div>`).join("")}
            </div>
            <div class="footer__text-copy">${data.copyright || ""}</div>`;
	}

	// =========================
	// Inicialización automática
	// =========================
	window.addEventListener("DOMContentLoaded", async () => {
		try {
			const [navbarData, footerData, juegosData] = await Promise.all([
				fetchJSON(PAGE_JSON.navbar),
				fetchJSON(PAGE_JSON.footer),
				fetchAPI(API_URL + "index.php?endpoint=juegos")
			]);

			mostrarNavbar(navbarData);
			mostrarFooter(footerData);
			mostrarCards(juegosData);
		} catch (err) {
			console.error("Error en la inicialización:", err);
		}
	});
})();
