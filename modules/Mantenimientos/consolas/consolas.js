(() => {
    const API = BASE_URL + "api/consola.php";

    const tbody = document.querySelector(".admin-consola__tbody");
    const addBtn = document.querySelector(".admin-consola__add-btn");
    const panel = document.querySelector(".admin-consola__panel");
    const panelInner = document.querySelector(".admin-consola__panel-inner");
    const fieldCode = document.querySelector(".admin-consola__field--code");
    const fieldName = document.querySelector(".admin-consola__field--name");
    const fieldColor = document.querySelector(".admin-consola__field--color");
    const saveBtn = document.querySelector(".admin-consola__save");
    const cancelBtn = document.querySelector(".admin-consola__cancel");

    let editingId = null;

    function openPanel() {
        if (!panel) return;
        panel.classList.remove("hidden");
        panel.setAttribute("aria-hidden", "false");
    }
    function closePanel() {
        if (!panel) return;
        panel.classList.add("hidden");
        panel.setAttribute("aria-hidden", "true");
    }

    async function fetchList() {
        const res = await fetch(API);
        const data = await res.json();
        renderList(data || []);
    }

    function renderList(items) {
        tbody.innerHTML = items
            .map(
                (i) => `
            <tr>
                <td>${i.id_consola}</td>
                <td>${escapeHtml(i.code)}</td>
                <td>${escapeHtml(i.nombre)}</td>
                <td><span style="display:inline-block;padding:6px 10px;border-radius:999px;background:${escapeHtml(
                    i.consola_color
                )};color:${textColor(i.consola_color)}">${escapeHtml(
                    i.consola_color
                )}</span></td>
                <td>
                    <button class="admin-consola__edit" data-id="${i.id_consola
                    }">Editar</button>
                    <button class="admin-consola__delete" data-id="${i.id_consola
                    }">Eliminar</button>
                </td>
            </tr>
        `
            )
            .join("");
    }

    function escapeHtml(s) {
        return String(s || "")
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

    if (addBtn)
        addBtn.addEventListener("click", () => {
            editingId = null;
            if (fieldCode) fieldCode.value = "";
            if (fieldName) fieldName.value = "";
            if (fieldColor) fieldColor.value = "#cccccc";
            openPanel();
        });

    if (cancelBtn)
        cancelBtn.addEventListener("click", () => {
            closePanel();
        });

    if (saveBtn)
        saveBtn.addEventListener("click", async () => {
            const code = ((fieldCode && fieldCode.value) || "").trim();
            const nombre = ((fieldName && fieldName.value) || "").trim();
            const color = (fieldColor && fieldColor.value) || "#cccccc";
            if (!code || !nombre) {
                if (window.Swal)
                    Swal.fire({
                        icon: "warning",
                        title: "Faltan campos",
                        text: "Completa código y nombre",
                    });
                else alert("Completa código y nombre");
                return;
            }

            const payload = { code, nombre, consola_color: color };
            saveBtn.disabled = true;
            try {
                let res;
                if (editingId) {
                    payload.id = editingId;
                    res = await fetch(API, {
                        method: "PUT",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify(payload),
                    });
                } else {
                    res = await fetch(API, {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify(payload),
                    });
                }
                const j = await res.json();
                if (!res.ok) throw new Error(j.error || j.message || "Error en la API");
                // success
                if (window.Swal)
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
                if (window.Swal)
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: err.message || "Error al guardar",
                    });
                else alert("Error: " + (err.message || "Error al guardar"));
            } finally {
                saveBtn.disabled = false;
            }
        });

    // edit/delete
    if (tbody)
        tbody.addEventListener("click", async (e) => {
            const edit = e.target.closest && e.target.closest(".admin-consola__edit");
            const del =
                e.target.closest && e.target.closest(".admin-consola__delete");
            if (edit) {
                const id = edit.dataset.id;
                editingId = id;
                try {
                    const res = await fetch(API + "?id=" + encodeURIComponent(id));
                    if (!res.ok) throw new Error("Error " + res.status);
                    const data = await res.json();
                    if (fieldCode) fieldCode.value = data.code;
                    if (fieldName) fieldName.value = data.nombre;
                    if (fieldColor) fieldColor.value = data.consola_color || "#cccccc";
                    openPanel();
                } catch (err) {
                    alert("No se pudo cargar la consola: " + err.message);
                }
            }
            if (del) {
                const id = del.dataset.id;
                if (window.Swal) {
                    const choice = await Swal.fire({
                        title: "Eliminar consola?",
                        text: "Esta acción no se puede deshacer",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Sí, eliminar",
                        cancelButtonText: "Cancelar",
                    });
                    if (!choice.isConfirmed) return;
                } else {
                    if (!confirm("Eliminar consola?")) return;
                }
                try {
                    const res = await fetch(API + "?id=" + encodeURIComponent(id), {
                        method: "DELETE",
                    });
                    const j = await res.json();
                    if (!res.ok) throw new Error(j.error || "Error al eliminar");
                    if (window.Swal)
                        Swal.fire({
                            toast: true,
                            position: "top-end",
                            icon: "success",
                            title: "Eliminado",
                            showConfirmButton: false,
                            timer: 1200,
                        });
                    fetchList();
                } catch (err) {
                    console.error(err);
                    if (window.Swal)
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: err.message || "No se pudo eliminar",
                        });
                    else alert("No se pudo eliminar");
                }
            }
        });

    // init
    fetchList();
})();
