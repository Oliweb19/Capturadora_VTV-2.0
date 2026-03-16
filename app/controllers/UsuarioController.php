<?php
// ============================================================
// Controller: Usuario
// Gestiona las acciones CRUD del panel de administración
// ============================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/../controllers/AuthController.php';

/**
 * Muestra la lista de usuarios paginada.
 */
function usuario_index(): void {
    auth_requerir_admin();

    $limit  = 10;
    $page   = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;
    $search = trim($_GET['search'] ?? '');

    $result      = usuario_listar($limit, $offset, $search);
    $data        = $result['data'];
    $total       = $result['total'];
    $total_pages = (int) ceil($total / $limit);

    // Mensaje de flash
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    require __DIR__ . '/../views/admin/usuarios.php';
}

/**
 * Procesa el formulario de creación de usuario (POST).
 */
function usuario_store(): void {
    auth_requerir_admin();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/public/admin/usuarios.php');
        exit;
    }

    $nombre = trim($_POST['usuario'] ?? '');
    $clave  = $_POST['clave']  ?? '';
    $clave2 = $_POST['clave2'] ?? '';
    $rol    = $_POST['rol']    ?? 'U';

    if ($clave !== $clave2) {
        $_SESSION['flash'] = ['tipo' => 'error', 'msg' => 'Las contraseñas no coinciden.'];
        header('Location: ' . BASE_URL . '/public/admin/usuarios.php');
        exit;
    }

    $resultado = usuario_crear($nombre, $clave, $rol);

    if ($resultado === true) {
        $_SESSION['flash'] = ['tipo' => 'ok', 'msg' => 'Usuario creado exitosamente.'];
    } else {
        $_SESSION['flash'] = ['tipo' => 'error', 'msg' => $resultado];
    }

    header('Location: ' . BASE_URL . '/public/admin/usuarios.php');
    exit;
}

/**
 * Procesa la edición de un usuario (POST).
 */
function usuario_update(): void {
    auth_requerir_admin();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/public/admin/usuarios.php');
        exit;
    }

    $id     = (int)($_POST['id_usuario'] ?? 0);
    $nombre = trim($_POST['usuario'] ?? '');
    $clave  = $_POST['clave'] ?? '';

    $resultado = usuario_editar($id, $nombre, $clave);

    if ($resultado === true) {
        $_SESSION['flash'] = ['tipo' => 'ok', 'msg' => 'Usuario actualizado exitosamente.'];
    } else {
        $_SESSION['flash'] = ['tipo' => 'error', 'msg' => $resultado];
    }

    header('Location: ' . BASE_URL . '/public/admin/usuarios.php');
    exit;
}

/**
 * Desactiva un usuario (GET con ?id=).
 */
function usuario_destroy(): void {
    auth_requerir_admin();

    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        $_SESSION['flash'] = ['tipo' => 'error', 'msg' => 'ID de usuario inválido.'];
        header('Location: ' . BASE_URL . '/public/admin/usuarios.php');
        exit;
    }

    $id = (int)$_GET['id'];
    $resultado = usuario_desactivar($id);

    if ($resultado === true) {
        $_SESSION['flash'] = ['tipo' => 'ok', 'msg' => 'Usuario desactivado exitosamente.'];
    } else {
        $_SESSION['flash'] = ['tipo' => 'error', 'msg' => $resultado];
    }

    header('Location: ' . BASE_URL . '/public/admin/usuarios.php');
    exit;
}
