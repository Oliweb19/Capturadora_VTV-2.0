<?php
// thumbs.php - sirve miniaturas desde el filesystem de forma segura.
// Parámetro: file=path/within/base.jpg (por ejemplo: 2025-10-28_13-38-40.jpg o 2025-10-28/xxx.jpg)

date_default_timezone_set('America/Caracas');

$base = realpath(__DIR__ . '/../Videos/capturadora/clips/');
if (!$base) {
    http_response_code(500);
    echo "Base thumbnails directory not found";
    exit;
}
$base = rtrim($base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

if (!isset($_GET['file']) || trim($_GET['file']) === '') {
    http_response_code(400);
    echo "Missing file";
    exit;
}

$file = $_GET['file'];
// Sanitizar: quitar NULs y arreglar separadores
$file = str_replace("\0", '', $file);
$file = str_replace('..', '', $file);
$file = ltrim($file, '/\\');
$path = $base . str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $file);

if (!file_exists($path) || !is_file($path)) {
    http_response_code(404);
    echo "Not found";
    exit;
}

$mime = mime_content_type($path) ?: 'application/octet-stream';
// Solo permitir imágenes por seguridad
if (strpos($mime, 'image/') !== 0) {
    http_response_code(403);
    echo "Forbidden";
    exit;
}

// Cabeceras de cache (ajusta según convenga)
header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($path));
header('Cache-Control: public, max-age=86400');
readfile($path);
exit;
