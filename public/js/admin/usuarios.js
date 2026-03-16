/**
 * usuarios.js – Lógica de modales en el panel de administración de usuarios.
 * Maneja: abrir/cerrar modales de agregar, editar y eliminar.
 */
document.addEventListener('DOMContentLoaded', () => {

    // ─── Helpers ────────────────────────────────────────────────────────────

    /**
     * Abre un modal (agrega clase 'open').
     */
    function openModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.add('open');
    }

    /**
     * Cierra un modal.
     */
    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.remove('open');
    }

    /**
     * Toggle de visibilidad para campos password.
     * Busca todos los .toggle-password con data-target.
     */
    document.querySelectorAll('.toggle-password[data-target]').forEach(toggle => {
        toggle.addEventListener('click', function () {
            const input = document.getElementById(this.dataset.target);
            if (!input) return;
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            this.textContent = show ? 'visibility_off' : 'visibility';
        });
    });

    // ─── Cerrar modales con .modal-close ────────────────────────────────────
    document.querySelectorAll('.modal-close[data-modal]').forEach(btn => {
        btn.addEventListener('click', function () {
            closeModal(this.dataset.modal);
        });
    });

    // Cerrar al hacer clic fuera del contenido
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function (e) {
            if (e.target === this) closeModal(this.id);
        });
    });

    // ─── Modal: Agregar usuario ──────────────────────────────────────────────
    const openAddBtn = document.getElementById('openModalBtn');
    if (openAddBtn) {
        openAddBtn.addEventListener('click', () => openModal('modal-add'));
    }

    // ─── Modal: Editar usuario ───────────────────────────────────────────────
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const id      = this.dataset.id;
            const usuario = this.dataset.usuario;

            document.getElementById('edit-id').value       = id;
            document.getElementById('edit-usuario').value  = usuario;

            openModal('modal-edit');
        });
    });

    // ─── Modal: Eliminar usuario ─────────────────────────────────────────────
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const id      = this.dataset.id;
            const usuario = this.dataset.usuario;

            document.getElementById('delete-user-name').textContent = usuario;
            document.getElementById('btn-confirm-delete').href =
                window.location.pathname + '?action=destroy&id=' + id;

            openModal('modal-delete');
        });
    });

});
