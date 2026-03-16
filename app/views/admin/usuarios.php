<?php
/**
 * Vista: Usuarios (Admin)
 * Variables disponibles:
 *   $data         array  filas de usuarios
 *   $total        int    total de registros
 *   $total_pages  int
 *   $page         int    página actual
 *   $search       string término de búsqueda
 *   $limit        int
 *   $flash        array|null  ['tipo' => 'ok'|'error', 'msg' => string]
 */
$head_data = [
    'title' => 'Capturadora – Administración de Usuarios',
    'css'   => [
        'components/sidebar_admin.css',
        'components/table.css',
        'components/modal.css',
        'components/form.css',
        'components/pagination.css',
    ],
];
require __DIR__ . '/../layouts/head.php';
require __DIR__ . '/../layouts/navbar_admin.php';
?>

<div class="admin-layout">
    <!-- El sidebar ya fue incluido arriba -->

    <main class="admin-main">
        <div class="admin-header">
            <h1>Gestión de Usuarios</h1>
        </div>

        <?php if (!empty($flash)): ?>
            <div class="flash-message flash-<?= $flash['tipo'] ?>">
                <?= htmlspecialchars($flash['msg']) ?>
            </div>
        <?php endif; ?>

        <!-- Controles: búsqueda + botón agregar -->
        <div class="table-controls">
            <form action="<?= BASE_URL ?>/public/admin/usuarios.php" method="GET" class="search-form-admin">
                <div class="search-input-wrapper">
                    <span class="material-symbols-outlined">search</span>
                    <input type="text" name="search" placeholder="Buscar usuario..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <button type="submit" class="btn-primary">Buscar</button>
                <?php if ($search !== ''): ?>
                    <a href="<?= BASE_URL ?>/public/admin/usuarios.php" class="btn-secondary">Limpiar</a>
                <?php endif; ?>
            </form>
            <button id="openModalBtn" class="btn-primary">
                <span class="material-symbols-outlined">add</span> Agregar Usuario
            </button>
        </div>

        <!-- Tabla de usuarios -->
        <div class="table-wrapper">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data)): ?>
                        <tr>
                            <td colspan="3" class="table-empty">
                                <?= $search !== '' ? 'No se encontraron usuarios con ese nombre.' : 'No hay usuarios registrados.' ?>
                            </td>
                        </tr>
                    <?php else: foreach ($data as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['usuario']) ?></td>
                            <td>
                                <span class="badge badge-<?= $u['privilegio'] === 'A' ? 'admin' : 'user' ?>">
                                    <?= $u['privilegio'] === 'A' ? 'Administrador' : 'Usuario' ?>
                                </span>
                            </td>
                            <td class="table-actions">
                                <a href="#" class="btn-icon btn-edit"
                                   data-id="<?= $u['id'] ?>"
                                   data-usuario="<?= htmlspecialchars($u['usuario']) ?>"
                                   title="Editar">
                                    <span class="material-symbols-outlined">edit</span>
                                </a>
                                <a href="#" class="btn-icon btn-delete"
                                   data-id="<?= $u['id'] ?>"
                                   data-usuario="<?= htmlspecialchars($u['usuario']) ?>"
                                   title="Desactivar">
                                    <span class="material-symbols-outlined">delete</span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php
            $search_q = $search !== '' ? '&search=' . urlencode($search) : '';
            for ($i = 1; $i <= $total_pages; $i++):
            ?>
                <a href="?page=<?= $i . $search_q ?>"
                   class="page-link <?= $i === $page ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </main>
</div>

<!-- ========== MODAL: Agregar Usuario ========== -->
<div id="modal-add" class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-add-title">
    <div class="modal-content">
        <button class="modal-close" data-modal="modal-add" aria-label="Cerrar">&times;</button>
        <h2 id="modal-add-title">Agregar Nuevo Usuario</h2>
        <form action="<?= BASE_URL ?>/public/admin/usuarios.php?action=store" method="POST" class="form-vertical">
            <div class="form-group">
                <label for="add-usuario">Usuario</label>
                <input type="text" id="add-usuario" name="usuario" required>
            </div>
            <div class="form-group">
                <label for="add-clave1">Contraseña</label>
                <div class="password-wrapper">
                    <input type="password" id="add-clave1" name="clave" required>
                    <span class="material-symbols-outlined toggle-password" data-target="add-clave1">visibility</span>
                </div>
            </div>
            <div class="form-group">
                <label for="add-clave2">Confirmar Contraseña</label>
                <div class="password-wrapper">
                    <input type="password" id="add-clave2" name="clave2" required>
                    <span class="material-symbols-outlined toggle-password" data-target="add-clave2">visibility</span>
                </div>
            </div>
            <div class="form-group">
                <label for="add-rol">Rol</label>
                <select id="add-rol" name="rol" required>
                    <option value="A">Administrador</option>
                    <option value="U" selected>Usuario</option>
                </select>
            </div>
            <button type="submit" class="btn-primary btn-full">Agregar Usuario</button>
        </form>
    </div>
</div>

<!-- ========== MODAL: Editar Usuario ========== -->
<div id="modal-edit" class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-edit-title">
    <div class="modal-content">
        <button class="modal-close" data-modal="modal-edit" aria-label="Cerrar">&times;</button>
        <h2 id="modal-edit-title">Editar Usuario</h2>
        <form action="<?= BASE_URL ?>/public/admin/usuarios.php?action=update" method="POST" class="form-vertical">
            <input type="hidden" id="edit-id" name="id_usuario">
            <div class="form-group">
                <label for="edit-usuario">Nombre de Usuario</label>
                <input type="text" id="edit-usuario" name="usuario" required>
            </div>
            <div class="form-group">
                <label for="edit-clave">Nueva Contraseña</label>
                <input type="password" id="edit-clave" name="clave" autocomplete="new-password">
                <span class="form-hint">Dejar en blanco para no cambiar la contraseña.</span>
            </div>
            <button type="submit" class="btn-primary btn-full">Actualizar Usuario</button>
        </form>
    </div>
</div>

<!-- ========== MODAL: Confirmar Eliminación ========== -->
<div id="modal-delete" class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-delete-title">
    <div class="modal-content">
        <button class="modal-close" data-modal="modal-delete" aria-label="Cerrar">&times;</button>
        <h2 id="modal-delete-title">Confirmar Desactivación</h2>
        <p>¿Estás seguro de que deseas desactivar al usuario <strong id="delete-user-name"></strong>?</p>
        <p class="form-hint">Esta acción desactivará la cuenta. No se puede deshacer fácilmente.</p>
        <div class="modal-buttons">
            <button type="button" class="btn-secondary modal-close" data-modal="modal-delete">Cancelar</button>
            <a href="#" id="btn-confirm-delete" class="btn-danger">Desactivar</a>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/public/js/admin/usuarios.js"></script>
</body>
</html>
