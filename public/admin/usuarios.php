<?php
// ============================================================
// public/admin/usuarios.php – Router del panel de administración
// ============================================================
require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../app/controllers/UsuarioController.php';

// Despacho de acciones
$action = $_GET['action'] ?? 'index';

match ($action) {
    'store'   => usuario_store(),
    'update'  => usuario_update(),
    'destroy' => usuario_destroy(),
    default   => usuario_index(),
};
