<?php
// ============================================================
// index.php – Punto de entrada principal (Login)
// ============================================================
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/controllers/AuthController.php';

// Si ya tiene sesión activa, redirigir al panel de usuario
if (session_status() === PHP_SESSION_NONE) session_start();
if (!empty($_SESSION['usuario'])) {
    header('Location: ' . BASE_URL . '/public/user/material.php');
    exit;
}

// Mostrar error de login si existe en sesión
$error = $_SESSION['error_login'] ?? null;
unset($_SESSION['error_login']);

require __DIR__ . '/app/views/auth/login.php';