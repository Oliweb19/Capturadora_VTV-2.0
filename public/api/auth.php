<?php
// ============================================================
// public/api/auth.php – Endpoint de login (POST) y logout
// ============================================================
require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../app/controllers/AuthController.php';

// Ejecutar login
auth_login();
