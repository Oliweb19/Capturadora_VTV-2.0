<?php
// ============================================================
// public/api/stream.php – Sirve videos con soporte de rangos HTTP
// Migrado desde User/stream.php – rutas actualizadas para nueva estructura
// ============================================================
require_once __DIR__ . '/../../app/config/config.php';
$baseDir = rtrim(VIDEO_BASE_DIR, '/\\') . DIRECTORY_SEPARATOR;

if (!isset($_GET['file']) || trim($_GET['file']) === '') {
    http_response_code(400);
    exit('Missing file');
}

$fileParam = $_GET['file'];
$fileParam = str_replace("\0", '', $fileParam);
$fileParam = ltrim($fileParam, '/\\');
if (strpos($fileParam, '..') !== false) {
    http_response_code(400);
    exit('Invalid file');
}

$ext = strtolower(pathinfo($fileParam, PATHINFO_EXTENSION));
if (!in_array($ext, ['mp4', 'webm'])) {
    http_response_code(400);
    exit('Unsupported file type');
}

// Evitar realpath estricto en la validación porque fallaría para unidades mapeadas (ej. O:/)
$fullPath = $baseDir . $fileParam;
if (!is_file($fullPath)) {
    http_response_code(404);
    exit('Not found: ' . $fullPath);
}

$size = filesize($fullPath);
$fp   = fopen($fullPath, 'rb');
if (!$fp) { http_response_code(500); exit('Unable to open file'); }

$start      = 0;
$end        = $size - 1;
$length     = $size;
$httpStatus = 200;
$ignoreRange = false;

// Preview parcial
$isPreview = isset($_GET['preview']) && in_array($_GET['preview'], ['1', 'true']);
if ($isPreview) {
    $nameNoExt  = pathinfo($fileParam, PATHINFO_FILENAME);
    $captBase   = dirname($baseDir);
    $candidate1 = realpath($captBase . DIRECTORY_SEPARATOR . 'miniaturas' . DIRECTORY_SEPARATOR . $nameNoExt . '.webm');
    $dirPart    = pathinfo($fileParam, PATHINFO_DIRNAME);
    $candidate2 = false;
    if ($dirPart && $dirPart !== '.' && $dirPart !== '') {
        $candidate2 = realpath($captBase . DIRECTORY_SEPARATOR . 'miniaturas' . DIRECTORY_SEPARATOR . $dirPart . DIRECTORY_SEPARATOR . $nameNoExt . '.webm');
    }
    if ($candidate1 && is_file($candidate1)) {
        fclose($fp);
        $fullPath = $candidate1; $ext = 'webm'; $size = filesize($fullPath);
        $fp = fopen($fullPath, 'rb');
        if (!$fp) { http_response_code(500); exit('Unable to open preview'); }
    } elseif ($candidate2 && is_file($candidate2)) {
        fclose($fp);
        $fullPath = $candidate2; $ext = 'webm'; $size = filesize($fullPath);
        $fp = fopen($fullPath, 'rb');
        if (!$fp) { http_response_code(500); exit('Unable to open preview'); }
    } else {
        $PREVIEW_BYTES = 500 * 1024;
        $end = min($size - 1, $PREVIEW_BYTES - 1);
        $length = $end - 0 + 1;
        $httpStatus = 206;
        $ignoreRange = true;
        fseek($fp, 0);
    }
}

if (isset($_SERVER['HTTP_RANGE']) && !$ignoreRange) {
    if (preg_match('/bytes=(\d*)-(\d*)/', $_SERVER['HTTP_RANGE'], $m)) {
        if ($m[1] !== '') $start = intval($m[1]);
        if ($m[2] !== '') $end   = intval($m[2]);
        if ($start > $end || $start >= $size) {
            header("HTTP/1.1 416 Requested Range Not Satisfiable");
            header("Content-Range: bytes */$size");
            exit;
        }
        $length = $end - $start + 1;
        fseek($fp, $start);
        $httpStatus = 206;
    }
}

$mime = match($ext) { 'mp4' => 'video/mp4', 'webm' => 'video/webm', default => 'application/octet-stream' };

header_remove();
header($httpStatus === 206 ? 'HTTP/1.1 206 Partial Content' : 'HTTP/1.1 200 OK');
header('Content-Type: ' . $mime);
header('Accept-Ranges: bytes');
header('Content-Length: ' . $length);
header('Content-Range: bytes ' . $start . '-' . $end . '/' . $size);
header('Content-Disposition: inline; filename="' . basename($fullPath) . '"');

$buf = 8192;
while (!feof($fp) && $length > 0) {
    $read = fread($fp, min($buf, $length));
    echo $read;
    flush();
    $length -= strlen($read);
}
fclose($fp);
exit;
