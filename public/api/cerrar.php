<?php
// ============================================================
// public/api/cerrar.php – Logout
// ============================================================
require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../app/controllers/AuthController.php';

auth_logout();
