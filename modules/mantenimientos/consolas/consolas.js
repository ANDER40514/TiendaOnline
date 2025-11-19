(() => {
    const API = BASE_URL + "api/index.php?endpoint=consolas";

    // =========================
    // Elementos DOM
    // =========================
    const tbody = document.querySelector(".admin-consola__tbody");
    const addBtn = document.querySelector(".admin-consola__add-btn");
    const panel = document.querySelector(".admin-consola__panel");
    const fieldCode = document.querySelector(".admin-consola__field--code");
    const fieldName = document.querySelector(".admin-consola__field--name");
    const fieldColor = document.querySelector(".admin-consola__field--color");
    const saveBtn = document.querySelector(".admin-consola__save");
    const cancelBtn = document.querySelector(".admin-consola__cancel");

    let editingId = null;

    // =========================
    // Helpers
    // =========================
    function openPanel() {
        panel?.classList.remove("hidden");
        panel?.setAttribute("aria-hidden", "false");
    }

    function closePanel() {
        panel?.classList.add("hidden");
        panel?.setAttribute("aria-hidden", "true");
    }

    function escapeHtml(str) {
        return String(str || "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;");
    }

    function textColor(hex) {
        if (!hex) return "#000";
        const h = hex.replace("#", "");
        const r = parseInt(h.substr(0, 2), 16);
        const g = parseInt(h.substr(2, 2), 16);
        const b = parseInt(h.substr(4, 2), 16);
        const yiq = (r * 299 + g * 587 + b * 114) / 1000;
        return yiq >= 128 ? "#000" : "#fff";
    }

    async function fetchList() {
        try {
            const res = await fetch(API);
            if (!res.ok) throw new Error(`Error ${res.status}`);
            const data = await res.json();
            renderList(data || []);
        } catch (err) {
            console.error(err);
            Swal.fire({
                icon: "error",
                title: "Error",
                text: err.message || "No se pudo cargar la lista de consolas",
            });
        }
    }

    function renderList(items) {
        if (!Array.isArray(items) || !items.length) {
            tbody.innerHTML = `<tr><td colspan="5">No hay consolas disponibles.</td></tr>`;
            return;
        }

        tbody.innerHTML = items
            .map(
                (i) => `
            <tr class="table__row">
                <td class="table__data table__data--id">${i.id_consola}</td>
                <td class="table__data table__data--code">${escapeHtml(
                    i.code
                )}</td>
                <td class="table__data table__data--name">${escapeHtml(
                    i.nombre
                )}</td>
                <td class="table__data table__data--color">
                    <span style="
                        display:inline-block;
                        padding:6px 10px;
                        border-radius:999px;
                        background:${escapeHtml(i.consola_color)};
                        color:${textColor(i.consola_color)};
                    ">
                        ${escapeHtml(i.consola_color)}
                    </span>
                </td>
                <td class="table__data table__data--actions">
                    <button class="table__btn admin-consola__edit" data-id="${i.id_consola
                    }">Editar</button>
                    <button class="table__btn admin-consola__delete" data-id="${i.id_consola
                    }">Eliminar</button>
                </td>
            </tr>`
            )
            .join("");
    }

    // =========================
    // Eventos panel
    // =========================
    addBtn?.addEventListener("click", () => {
        editingId = null;
        fieldCode.value = "";
        fieldName.value = "";
        fieldColor.value = "#cccccc";
        openPanel();
    });

    cancelBtn?.addEventListener("click", () => {
        closePanel();
    });

    saveBtn?.addEventListener("click", async () => {
        const code = fieldCode.value.trim();
        const nombre = fieldName.value.trim();
        const color = fieldColor.value || "#cccccc";

        if (!code || !nombre) {
            Swal.fire({
                icon: "warning",
                title: "Faltan campos",
                text: "Completa código y nombre",
            });
            return;
        }

        const payload = { code, nombre, consola_color: color };
        saveBtn.disabled = true;

        try {
            const method = editingId ? "PUT" : "POST";
            let url = API;
            if (editingId) {
                url += "&id=" + encodeURIComponent(editingId);
            }

            const res = await fetch(url, {
                method,
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload),
            });

            const json = await res.json();
            if (!res.ok)
                throw new Error(json.error || json.message || "Error en la API");

            Swal.fire({
                toast: true,
                position: "top-end",
                icon: "success",
                title: editingId ? "Consola actualizada" : "Consola creada",
                showConfirmButton: false,
                timer: 1500,
            });

            closePanel();
            fetchList();
        } catch (err) {
            console.error(err);
            Swal.fire({
                icon: "error",
                title: "Error",
                text: err.message || "No se pudo guardar la consola",
            });
        } finally {
            saveBtn.disabled = false;
        }
    });

    // =============================
    // Editar / Eliminar desde tabla
    // ===========================
    tbody?.addEventListener("click", async (e) => {
        const editBtn = e.target.closest(".admin-consola__edit");
        const delBtn = e.target.closest(".admin-consola__delete");

        // ----- EDITAR -----
        if (editBtn) {
            const id = editBtn.dataset.id;
            editingId = id;

            try {
                const res = await fetch(`${API}&id=${encodeURIComponent(id)}`);
                if (!res.ok) throw new Error("Error " + res.status);

                const data = await res.json();

                // Asegurarnos que venga un array con al menos un elemento
                if (!Array.isArray(data) || data.length === 0) {
                    throw new Error("Consola no encontrada");
                }

                const consola = data[0];

                fieldCode.value = consola.code || "";
                fieldName.value = consola.nombre || "";
                fieldColor.value = consola.consola_color || "#cccccc";

                openPanel();
            } catch (err) {
                console.error(err);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: err.message || "No se pudo cargar la consola",
                });
            }
        }

        // ----- ELIMINAR -----
        if (delBtn) {
            const id = delBtn.dataset.id;

            const choice = await Swal.fire({
                title: "Eliminar consola?",
                text: "Esta acción no se puede deshacer",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar",
            });
            if (!choice.isConfirmed) return;

            try {
                const res = await fetch(`${API}&id=${encodeURIComponent(id)}`, {
                    method: "DELETE",
                });
                const json = await res.json();
                if (!res.ok) throw new Error(json.error || "Error al eliminar");

                Swal.fire({
                    toast: true,
                    position: "top-end",
                    icon: "success",
                    title: "Eliminado",
                    showConfirmButton: false,
                    timer: 1200,
                });

                fetchList(); // refresca la lista
            } catch (err) {
                console.error(err);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: err.message || "No se pudo eliminar",
                });
            }
        }
    });

    // =========================
    // Inicialización
    // =========================
    fetchList();
})();
