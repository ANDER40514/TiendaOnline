document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("game-form");
    const tableBody = document.querySelector("#games-table tbody");
    const tableHead = document.querySelector("#games-table thead");
    const btnCancel = document.getElementById("btn-cancel");
    const inputImg = document.getElementById("game-imagen");
    const previewImg = document.getElementById("preview-img");

    if (!form || !tableBody || !btnCancel) {
        console.error("Error: faltan elementos requeridos.");
        return;
    }

    let juegosGlobal = [];
    let filtros = {
        id: "",
        titulo: "",
        descripcion: "",
        precio: "",
        consola: "",
    };
    let debounceTimer;

    async function cargarJuegos() {
        try {
            const res = await fetch(BASE_URL + "api/juegos.php");
            if (!res.ok) throw new Error(`Error ${res.status}`);
            const data = await res.json();
            juegosGlobal = data;
            renderTabla(data);
        } catch (err) {
            console.error("Error al consumir la API:", err);
            tableBody.innerHTML = `<tr><td colspan="7">Error cargando los juegos.</td></tr>`;
            Swal.fire("Error", "No se pudieron cargar los juegos", "error");
        }
    }

    function renderTabla(juegos) {
        if (!Array.isArray(juegos)) {
            tableBody.innerHTML = `<tr><td colspan="7">No hay juegos disponibles.</td></tr>`;
            return;
        }

        tableBody.innerHTML = juegos
            .map((j) => {
                const imgSrc =
                    j.imagen && j.imagen.trim()
                        ? `../../../assets/${j.imagen}`
                        : "../../../assets/img/no-photo.jpg";

                return `
                <tr class="table__row">
                    <td class="table__data table__data--id">${j.id_juego}</td>
                    <td class="table__data table__data--titulo">${j.titulo}</td>
                    <td class="table__data table__data--descripcion">${j.descripcion
                    }</td>
                    <td class="table__data table__data--precio">${parseFloat(
                        j.precio || 0
                    ).toFixed(2)}</td>
                    <td class="table__data table__data--consola">${j.consola
                    }</td>
                    <td class="table__data table__data--imagen">
                        <img src="${imgSrc}" alt="${j.titulo
                    }" class="table__img">
                    </td>
                    <td class="table__data table__data--acciones">
                        <button class="table__btn table__btn--edit" data-id="${j.id_juego
                    }">Editar</button>
                        <button class="table__btn table__btn--delete" data-id="${j.id_juego
                    }">Eliminar</button>
                    </td>
                </tr>
            `;
            })
            .join("");

        document
            .querySelectorAll(".table__btn--edit")
            .forEach((btn) =>
                btn.addEventListener("click", () => editarJuego(btn.dataset.id))
            );
        document
            .querySelectorAll(".table__btn--delete")
            .forEach((btn) =>
                btn.addEventListener("click", () => eliminarJuego(btn.dataset.id))
            );
    }

    // Editar
    async function editarJuego(id) {
        try {
            const res = await fetch(`${BASE_URL}api/juegos.php?id=${id}`);
            if (!res.ok) throw new Error(`Error ${res.status}`);
            const juego = await res.json();

            document.getElementById("game-id").value = juego.id_juego || "";
            document.getElementById("game-titulo").value = juego.titulo || "";
            document.getElementById("game-descripcion").value =
                juego.descripcion || "";
            document.getElementById("game-precio").value = juego.precio || "";
            document.getElementById("game-consola").value = juego.consola || "";
            document.getElementById("game-imagen").value = juego.imagen || "";
            previewImg.src = juego.imagen
                ? `../../../assets/${juego.imagen}`
                : "../../../assets/img/no-photo.jpg";

            Swal.fire(
                "Editando",
                "Formulario rellenado con los datos del juego",
                "info"
            );
        } catch (err) {
            console.error(err);
            Swal.fire("Error", "No se pudo cargar el juego para editar", "error");
        }
    }

    // Eliminar
    async function eliminarJuego(id) {
        Swal.fire({
            title: "¿Deseas eliminar este juego?",
            text: "Esta acción no se puede deshacer.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#dc3545",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
        }).then(async (result) => {
            if (!result.isConfirmed) return;
            try {
                const res = await fetch(`${BASE_URL}api/juegos.php?id=${id}`, {
                    method: "DELETE",
                });
                if (!res.ok) throw new Error(`Error ${res.status}`);
                await res.json();
                Swal.fire("Eliminado", "El juego ha sido eliminado", "success");
                cargarJuegos();
            } catch (err) {
                console.error(err);
                Swal.fire("Error", "No se pudo eliminar el juego", "error");
            }
        });
    }

    // Guardar
    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const id = document.getElementById("game-id").value;
        const data = {
            titulo: document.getElementById("game-titulo").value,
            descripcion: document.getElementById("game-descripcion").value,
            precio: parseFloat(document.getElementById("game-precio").value),
            consola: document.getElementById("game-consola").value,
            imagen: document.getElementById("game-imagen").value,
        };
        if (id) data.id = id;
        const method = id ? "PUT" : "POST";

        Swal.fire({
            title: "Guardando...",
            text: "Espere un momento",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });

        try {
            const res = await fetch(BASE_URL + "api/juegos.php", {
                method,
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data),
            });
            if (!res.ok) throw new Error(`Error ${res.status}`);
            await res.json();

            form.reset();
            previewImg.src = "../../../assets/img/no-photo.jpg";
            document.getElementById("game-id").value = "";
            Swal.fire("Guardado", "El juego se ha guardado correctamente", "success");
            cargarJuegos();
        } catch (err) {
            console.error(err);
            Swal.fire("Error", "No se pudo guardar el juego", "error");
        }
    });

    // Cancelar
    btnCancel.addEventListener("click", () => {
        form.reset();
        previewImg.src = "../../../assets/img/no-photo.jpg";
        document.getElementById("game-id").value = "";
        Swal.fire("Cancelado", "Se ha limpiado el formulario", "info");
    });

    // Preview dinámica
    inputImg.addEventListener("input", () => {
        const url = inputImg.value.trim();
        previewImg.src = url ? `../../../${url}` : "../../../assets/img/no-photo.jpg";
    });

    // Fila filtros
    function crearFilaFiltros() {
        if (tableHead.querySelector(".table__filters")) return;

        const filterRow = document.createElement("tr");
        filterRow.classList.add("table__filters");

        const columnas = ["id_juego", "titulo", "descripcion", "precio", "consola"];
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
                }, 1300);
            });
            th.appendChild(input);
            filterRow.appendChild(th);
        });

        // Imagen
        filterRow.appendChild(document.createElement("th"));

        // Acciones con botón limpiar
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
                (!filtros.id || j.id_juego.toString().includes(filtros.id)) &&
                (!filtros.titulo || j.titulo.toLowerCase().includes(filtros.titulo)) &&
                (!filtros.descripcion ||
                    j.descripcion.toLowerCase().includes(filtros.descripcion)) &&
                (!filtros.precio || j.precio.toString().includes(filtros.precio)) &&
                (!filtros.consola || j.consola.toLowerCase().includes(filtros.consola))
        );
        renderTabla(filtrados);
    }

    function limpiarFiltros() {
        filtros = { id: "", titulo: "", descripcion: "", precio: "", consola: "" };
        document
            .querySelectorAll(".table__filters input")
            .forEach((inp) => (inp.value = ""));
        renderTabla(juegosGlobal);
    }

    crearFilaFiltros();
    cargarJuegos();
});