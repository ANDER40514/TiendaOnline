document.addEventListener("DOMContentLoaded", async () => {
    // =========================
    // Elementos del DOM
    // =========================
    const form = document.getElementById("game-form");
    const tableBody = document.querySelector("#games-table tbody");
    const tableHead = document.querySelector("#games-table thead");
    const btnCancel = document.getElementById("btn-cancel");
    const selectConsola = document.getElementById("game-consola");
    const inputImg = document.getElementById("game-imagen");
    const previewImg = document.getElementById("preview-img");

    if (!form || !tableBody || !btnCancel || !selectConsola) {
        console.error("Faltan elementos requeridos.");
        return;
    }

    let juegosGlobal = [];
    let consolasGlobal = [];
    let filtros = {
        id_juego: "",
        titulo: "",
        descripcion: "",
        precio: "",
        nombre_consola: "",
    };
    let debounceTimer;

    const BASE_API = BASE_URL + "api/index.php?endpoint=";

    // =========================
    // Helper fetch
    // =========================
    const fetchJSON = async (url, options = {}) => {
        try {
            const res = await fetch(url, options);
            if (!res.ok) throw new Error(`Error ${res.status}`);
            return await res.json();
        } catch (err) {
            console.error("Error fetch:", err);
            return null;
        }
    };

    // =========================
    // Cargar consolas
    // =========================
    //TODO: Arreglar que se consuman correctamente el select de consolas
    async function cargarConsolas() {
        const res = await fetchJSON(BASE_API + "consolas");
        if (res && Array.isArray(res.data)) {
            consolasGlobal = res.data;
            console.log(consolasGlobal);
            selectConsola.innerHTML = `<option value="">Seleccione una consola...</option>` +
                consolasGlobal
                    .map(c => `<option value="${c.nombre}">${c.nombre}</option>`)
                    .join("");
        }
    }

    // =========================
    // Cargar juegos
    // =========================
    async function cargarJuegos() {
        const res = await fetchJSON(BASE_API + "juegos");
        if (res && Array.isArray(res.data)) {
            juegosGlobal = res.data;
            renderTabla(juegosGlobal);
        } else {
            tableBody.innerHTML = `<tr><td colspan="7">No hay juegos disponibles</td></tr>`;
        }
    }

    // =========================
    // Render tabla
    // =========================
    function renderTabla(juegos) {
        if (!Array.isArray(juegos) || !juegos.length) {
            tableBody.innerHTML = `<tr><td colspan="7">No hay juegos disponibles.</td></tr>`;
            return;
        }

        tableBody.innerHTML = juegos
            .map((j) => {
                const imgSrc =
                    j.imagen && j.imagen.trim()
                        ? `${BASE_URL}assets/${j.imagen}`
                        : `${BASE_URL}assets/img/no-photo.jpg`;

                return `
                <tr class="table__row">
                    <td class="table__data table__data--id">${j.id_juego}</td>
                    <td class="table__data table__data--titulo">${j.titulo}</td>
                    <td class="table__data table__data--descripcion">${j.descripcion}</td>
                    <td class="table__data table__data--precio">${parseFloat(j.precio || 0).toFixed(2)}</td>
                    <td class="table__data table__data--consola">${j.nombre_consola || ""}</td>
                    <td class="table__data table__data--imagen">
                        <img src="${imgSrc}" alt="${j.titulo}" class="table__img">
                    </td>
                    <td class="table__data table__data--acciones">
                        <button class="table__btn table__btn--edit" data-id="${j.id_juego}">Editar</button>
                        <button class="table__btn table__btn--delete" data-id="${j.id_juego}">Eliminar</button>
                    </td>
                </tr>`;
            })
            .join("");

        document.querySelectorAll(".table__btn--edit").forEach(btn =>
            btn.addEventListener("click", () => editarJuego(btn.dataset.id))
        );
        document.querySelectorAll(".table__btn--delete").forEach(btn =>
            btn.addEventListener("click", () => eliminarJuego(btn.dataset.id))
        );
    }

    // =========================
    // Editar juego
    // =========================
    async function editarJuego(id) {
        const res = await fetchJSON(`${BASE_API}juegos&id=${id}`);
        if (!res || !res.data) {
            Swal.fire("Error", "No se pudo obtener la información del juego", "error");
            return;
        }

        const juego = res.data; // Siempre objeto

        document.getElementById("game-id").value = juego.id_juego || "";
        document.getElementById("game-titulo").value = juego.titulo || "";
        document.getElementById("game-descripcion").value = juego.descripcion || "";
        document.getElementById("game-precio").value = juego.precio || "";
        selectConsola.value = juego.nombre_consola || "";
        document.getElementById("game-imagen").value = juego.imagen || "";
        previewImg.src = juego.imagen ? `${BASE_URL}assets/${juego.imagen}` : `${BASE_URL}assets/img/no-photo.jpg`;

        Swal.fire("Editando", "Formulario rellenado con los datos del juego", "info");
    }

    // =========================
    // Eliminar juego
    // =========================
    async function eliminarJuego(id) {
        const result = await Swal.fire({
            title: "¿Deseas eliminar este juego?",
            text: "Esta acción no se puede deshacer.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
        });
        if (!result.isConfirmed) return;

        try {
            const res = await fetch(`${BASE_API}juegos&id=${id}`, { method: "DELETE" });
            if (!res.ok) throw new Error(`Error ${res.status}`);
            await res.json();
            Swal.fire("Eliminado", "El juego ha sido eliminado", "success");
            cargarJuegos();
        } catch (err) {
            console.error(err);
            Swal.fire("Error", "No se pudo eliminar el juego", "error");
        }
    }

    // =========================
    // Guardar juego (POST/PUT)
    // =========================
    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const id = document.getElementById("game-id").value;
        const data = {
            titulo: document.getElementById("game-titulo").value,
            descripcion: document.getElementById("game-descripcion").value,
            precio: parseFloat(document.getElementById("game-precio").value),
            nombre_consola: selectConsola.value,
            imagen: document.getElementById("game-imagen").value,
        };

        let url = BASE_API + "juegos";
        let method = "POST";
        if (id) {
            url += `&id=${id}`;
            method = "PUT";
        }

        Swal.fire({
            title: "Guardando...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });

        try {
            const res = await fetch(url, {
                method,
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data),
            });
            if (!res.ok) throw new Error(`Error ${res.status}`);
            await res.json();

            form.reset();
            selectConsola.value = consolasGlobal[0]?.nombre_consola || "";
            previewImg.src = `${BASE_URL}assets/img/no-photo.jpg`;
            document.getElementById("game-id").value = "";
            Swal.fire("Guardado", "El juego se ha guardado correctamente", "success");
            cargarJuegos();
        } catch (err) {
            console.error(err);
            Swal.fire("Error", "No se pudo guardar el juego", "error");
        }
    });

    // =========================
    // Cancelar formulario
    // =========================
    btnCancel.addEventListener("click", () => {
        form.reset();
        selectConsola.value = consolasGlobal[0]?.nombre_consola || "";
        previewImg.src = `${BASE_URL}assets/img/no-photo.jpg`;
        document.getElementById("game-id").value = "";
        Swal.fire("Cancelado", "Se ha limpiado el formulario", "info");
    });

    // =========================
    // Preview dinámica
    // =========================
    inputImg.addEventListener("input", () => {
        const url = inputImg.value.trim();
        previewImg.src = url
            ? `${BASE_URL}assets/${url}`
            : `${BASE_URL}assets/img/no-photo.jpg`;
    });

    // =========================
    // Filtros tabla
    // =========================
    function crearFilaFiltros() {
        if (tableHead.querySelector(".table__filters")) return;

        const filterRow = document.createElement("tr");
        filterRow.classList.add("table__filters");
        const columnas = ["id_juego", "titulo", "descripcion", "precio", "nombre_consola"];
        columnas.forEach((col) => {
            const th = document.createElement("th");
            const input = document.createElement("input");
            input.type = "text";
            input.placeholder = `Filtrar ${col}...`;
            input.classList.add("table__filter-input", `table__filter-input--${col}`);
            input.addEventListener("input", (e) => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    filtros[col] = e.target.value.toLowerCase();
                    aplicarFiltros();
                }, 800);
            });
            th.appendChild(input);
            filterRow.appendChild(th);
        });

        filterRow.appendChild(document.createElement("th")); // imagen
        const thAcciones = document.createElement("th");
        const btnClear = document.createElement("button");
        btnClear.textContent = "Limpiar filtros";
        btnClear.classList.add("table__btn", "table__btn--clear-filters");
        btnClear.addEventListener("click", limpiarFiltros);
        thAcciones.appendChild(btnClear);
        filterRow.appendChild(thAcciones);

        tableHead.appendChild(filterRow);
    }

    function aplicarFiltros() {
        const filtrados = juegosGlobal.filter(
            (j) =>
                (!filtros.id_juego || j.id_juego.toString().includes(filtros.id_juego)) &&
                (!filtros.titulo || j.titulo.toLowerCase().includes(filtros.titulo)) &&
                (!filtros.descripcion || j.descripcion.toLowerCase().includes(filtros.descripcion)) &&
                (!filtros.precio || j.precio.toString().includes(filtros.precio)) &&
                (!filtros.nombre_consola || j.nombre_consola.toLowerCase().includes(filtros.nombre_consola))
        );
        renderTabla(filtrados);
    }

    function limpiarFiltros() {
        filtros = {
            id_juego: "",
            titulo: "",
            descripcion: "",
            precio: "",
            nombre_consola: "",
        };
        document.querySelectorAll(".table__filters input").forEach(inp => (inp.value = ""));
        renderTabla(juegosGlobal);
    }

    // =========================
    // Inicializar
    // =========================
    await cargarConsolas();
    crearFilaFiltros();
    await cargarJuegos();
});
