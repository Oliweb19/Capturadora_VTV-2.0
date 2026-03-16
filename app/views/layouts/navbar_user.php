<?php
/**
 * Layout: navbar_user.php
 * Barra de navegación superior para el panel de usuario.
 * Requiere $_SESSION['privilegio'] disponible.
 */
?>
<nav class="navbar-user" id="navbar-user">
    <div class="navbar-brand">
        <img src="<?= BASE_URL ?>/img/vtv.png" alt="Capturadora VTV">
    </div>
    <div class="navbar-links">
        <a href="<?= BASE_URL ?>/public/user/material.php" class="nav-link">Inicio</a>
        <?php if (($_SESSION['privilegio'] ?? '') === 'A'): ?>
            <a href="<?= BASE_URL ?>/public/admin/usuarios.php" class="nav-link">Administrar</a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/public/api/cerrar.php" class="nav-link nav-logout" title="Cerrar sesión">
            <span class="material-symbols-outlined">logout</span>
        </a>
    </div>
</nav>
