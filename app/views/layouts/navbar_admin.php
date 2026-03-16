<?php
/**
 * Layout: navbar_admin.php
 * Sidebar lateral para el panel de administración.
 */
?>
<aside class="sidebar-admin" id="sidebar-admin">
    <div class="sidebar-head">
        <img src="<?= BASE_URL ?>/img/logo.png" alt="Capturadora Admin">
        <span>Capturadora</span>
    </div>
    <nav class="sidebar-nav">
        <a href="<?= BASE_URL ?>/public/admin/usuarios.php" class="sidebar-link">
            <span class="material-symbols-outlined">manage_accounts</span>Usuarios
        </a>
        <a href="<?= BASE_URL ?>/public/user/material.php" class="sidebar-link">
            <span class="material-symbols-outlined">play_circle</span>Panel Usuario
        </a>
    </nav>
    <a href="<?= BASE_URL ?>/public/api/cerrar.php" class="sidebar-logout">Salir</a>
</aside>
