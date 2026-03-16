<?php
// ============================================================
// public/api/thumbs.php – Sirve miniaturas de video de forma segura
// Migrado desde User/thumbs.php – rutas actualizadas
// ============================================================
require_once __DIR__ . '/../../app/config/config.php';

date_default_timezone_set('America/Caracas');

$base = realpath(BASE_PATH . '/Videos/capturadora/clips/');
if (!$base) { http_response_code(500); echo 'Base dir not found'; exit; }
$base = rtrim($base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

if (!isset($_GET['file']) || trim($_GET['file']) === '') {
    http_response_code(400); echo 'Missing file'; exit;
}

$file = str_replace(["\0", '..'], '', $_GET['file']);
$file = ltrim($file, '/\\');
$path = $base . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $file);

if (!file_exists($path) || !is_file($path)) {
    http_response_code(404); echo 'Not found'; exit;
}

$mime = mime_content_type($path) ?: 'application/octet-stream';
if (strpos($mime, 'image/') !== 0) {
    http_response_code(403); echo 'Forbidden'; exit;
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($path));
header('Cache-Control: public, max-age=86400');
readfile($path);
exit;
