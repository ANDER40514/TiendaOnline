(() => {
    const form = document.getElementById("game-form");
    const tableBody = document.querySelector("#games-table tbody");
    const btnCancel = document.getElementById("btn-cancel");

    // Cargar todos los juegos
    async function cargarJuegos() {
        try {
            const res = await fetch(BASE_URL + "api/juegos.php");
            if (!res.ok) throw new Error(`Error ${res.status}`);
            const data = await res.json();
            renderTabla(data);
        } catch (err) {
            console.error("Error al consumir la API:", err);
            tableBody.innerHTML = `<tr><td colspan="7">Error cargando los juegos.</td></tr>`;
            Swal.fire('Error', 'No se pudieron cargar los juegos', 'error');
        }
    }

    // Renderizar la tabla
    function renderTabla(juegos) {
        tableBody.innerHTML = juegos.map(j => `
            <tr class="table__row">
                <td class="table__data">${j.id_juego}</td>
                <td class="table__data">${j.titulo}</td>
                <td class="table__data">${j.descripcion}</td>
                <td class="table__data table__data--precio">${parseFloat(j.precio).toFixed(2)}</td>
                <td class="table__data">${j.consola}</td>
                <td class="table__data">${j.imagen || 'No existe imagen asignada'}</td>
                <td class="table__data">
                    <button class="table__btn table__btn--edit" data-id="${j.id_juego}">Editar</button>
                    <button class="table__btn table__btn--delete" data-id="${j.id_juego}">Cancelar</button>
                </td>
            </tr>
        `).join("");

        // Agregar eventos de editar/eliminar
        document.querySelectorAll(".table__btn--edit").forEach(btn => btn.addEventListener("click", () => editarJuego(btn.dataset.id)));
        document.querySelectorAll(".table__btn--delete").forEach(btn => btn.addEventListener("click", () => eliminarJuego(btn.dataset.id)));
    }

    // Editar juego
    async function editarJuego(id) {
        try {
            const res = await fetch(`${BASE_URL}api/juegos.php?id=${id}`);
            if (!res.ok) throw new Error(`Error ${res.status}`);
            const juego = await res.json();

            document.getElementById("game-id").value = juego.id_juego;
            document.getElementById("game-titulo").value = juego.titulo;
            document.getElementById("game-descripcion").value = juego.descripcion;
            document.getElementById("game-precio").value = juego.precio;
            document.getElementById("game-consola").value = juego.consola;
            document.getElementById("game-imagen").value = juego.imagen || "";

            Swal.fire('Editando', 'Formulario rellenado con los datos del juego', 'info');
        } catch (err) {
            console.error("Error al cargar el juego:", err);
            Swal.fire('Error', 'No se pudo cargar el juego para editar', 'error');
        }
    }

    // Eliminar juego
    async function eliminarJuego(id) {
        Swal.fire({
            title: '¿Deseas eliminar este juego?',
            text: "Esta acción no se puede deshacer.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(async (result) => {
            if (!result.isConfirmed) return;

            try {
                const res = await fetch(`${BASE_URL}api/juegos.php?id=${id}`, { method: "DELETE" });
                if (!res.ok) throw new Error(`Error ${res.status}`);
                await res.json();
                Swal.fire('Eliminado', 'El juego ha sido eliminado', 'success');
                cargarJuegos();
            } catch (err) {
                console.error("Error al eliminar el juego:", err);
                Swal.fire('Error', 'No se pudo eliminar el juego', 'error');
            }
        });
    }

    // Guardar juego (POST / PUT)
    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const id = document.getElementById("game-id").value;
        const data = {
            titulo: document.getElementById("game-titulo").value,
            descripcion: document.getElementById("game-descripcion").value,
            precio: parseFloat(document.getElementById("game-precio").value),
            consola: document.getElementById("game-consola").value,
            imagen: document.getElementById("game-imagen").value
        };
        if (id) data.id = id;

        const method = id ? "PUT" : "POST";

        Swal.fire({
            title: 'Guardando...',
            text: 'Espere un momento',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            const res = await fetch(BASE_URL + "api/juegos.php", {
                method,
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data)
            });
            if (!res.ok) throw new Error(`Error ${res.status}`);
            await res.json();

            form.reset();
            document.getElementById("game-id").value = "";

            Swal.fire('Guardado', 'El juego se ha guardado correctamente', 'success');
            cargarJuegos();
        } catch (err) {
            console.error("Error al guardar el juego:", err);
            Swal.fire('Error', 'No se pudo guardar el juego', 'error');
        }
    });

    // Cancelar formulario
    btnCancel.addEventListener("click", () => {
        form.reset();
        document.getElementById("game-id").value = "";
        Swal.fire('Cancelado', 'Se ha limpiado el formulario', 'info');
    });

    // Inicializar
    document.addEventListener("DOMContentLoaded", cargarJuegos);
})();