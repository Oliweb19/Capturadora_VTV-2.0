<?php
// ============================================================
// Controller: Auth
// Gestiona el login y logout del sistema
// ============================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/UsuarioModel.php';

/**
 * Procesa el formulario de login (POST).
 * Redirige al destino correcto o vuelve al login con error.
 */
function auth_login(): void {
    session_start();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/');
        exit;
    }

    $nombre = trim($_POST['usuario'] ?? '');
    $clave  = $_POST['password'] ?? '';

    if ($nombre === '' || $clave === '') {
        $_SESSION['error_login'] = 'Completa todos los campos.';
        header('Location: ' . BASE_URL . '/');
        exit;
    }

    $usuario = usuario_validar_login($nombre);

    if ($usuario === false) {
        $_SESSION['error_login'] = 'Usuario inválido.';
        header('Location: ' . BASE_URL . '/');
        exit;
    }

    if ($usuario['estatus'] == 0) {
        $_SESSION['error_login'] = 'Usuario inactivo. Contacte al Administrador.';
        header('Location: ' . BASE_URL . '/');
        exit;
    }

    if (!password_verify($clave, $usuario['clave'])) {
        $_SESSION['error_login'] = 'Contraseña inválida.';
        header('Location: ' . BASE_URL . '/');
        exit;
    }

    // Login exitoso
    session_regenerate_id(true);
    $_SESSION['usuario']   = $nombre;
    $_SESSION['privilegio'] = $usuario['privilegio'];

    header('Location: ' . BASE_URL . '/public/user/material.php');
    exit;
}

/**
 * Cierra la sesión y redirige al login.
 */
function auth_logout(): void {
    session_start();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
    header('Location: ' . BASE_URL . '/');
    exit;
}

/**
 * Verifica que haya sesión activa; si no, redirige al login.
 */
function auth_requerir_sesion(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['usuario'])) {
        header('Location: ' . BASE_URL . '/');
        exit;
    }
}

/**
 * Verifica que el usuario sea Administrador.
 */
function auth_requerir_admin(): void {
    auth_requerir_sesion();
    if ($_SESSION['privilegio'] !== 'A') {
        header('Location: ' . BASE_URL . '/public/user/material.php');
        exit;
    }
}
