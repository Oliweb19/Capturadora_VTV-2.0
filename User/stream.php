<?php
// archivo encargado de servir video desde Videos/capturadora/clips/ con soporte de rangos
$baseDir = realpath(__DIR__ . '/../Videos/capturadora/clips/');
if (!$baseDir) {
    http_response_code(500);
    exit('Base directory for videos not found');
}
$baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

if (!isset($_GET['file']) || trim($_GET['file']) === '') {
    http_response_code(400);
    exit('Missing file');
}

$fileParam = $_GET['file'];
// Sanitizar la entrada: no permitir navegaciones arriba del directorio y caracteres sospechosos
$fileParam = str_replace("\0", '', $fileParam);
$fileParam = ltrim($fileParam, '/\\');
if (strpos($fileParam, '..') !== false) {
    http_response_code(400);
    exit('Invalid file');
}

// Permitir mp4 y webm
$ext = strtolower(pathinfo($fileParam, PATHINFO_EXTENSION));
if (!in_array($ext, ['mp4', 'webm'])) {
    http_response_code(400);
    exit('Unsupported file type');
}

$fullPath = realpath($baseDir . $fileParam);
if ($fullPath === false || strpos($fullPath, $baseDir) !== 0 || !is_file($fullPath)) {
    http_response_code(404);
    exit('Not found');
}

$size = filesize($fullPath);
$fp = fopen($fullPath, 'rb');
if (!$fp) {
    http_response_code(500);
    exit('Unable to open file');
}
// Inicializar valores por defecto para rangos
$start = 0;
$end = $size - 1;
$length = $size;
$httpStatus = 200;

// Si se solicita preview, intentamos servir un .webm de preview si existe
// o si no, devolvemos solo los primeros N bytes del fichero original (206 Partial Content)
$isPreview = isset($_GET['preview']) && ($_GET['preview'] === '1' || $_GET['preview'] === 'true');
if ($isPreview) {
    // nombre base sin extensión
    $nameNoExt = pathinfo($fileParam, PATHINFO_FILENAME);
    $captBase = dirname($baseDir); // .../Videos/capturadora/
    // candidate 1: miniaturas/<name>.webm
    $candidate1 = realpath($captBase . DIRECTORY_SEPARATOR . 'miniaturas' . DIRECTORY_SEPARATOR . $nameNoExt . '.webm');
    // candidate 2: miniaturas/<date>/<name>.webm (si fileParam tenía carpeta date/)
    $dirPart = pathinfo($fileParam, PATHINFO_DIRNAME);
    $candidate2 = false;
    if ($dirPart && $dirPart !== '.' && $dirPart !== '') {
        $candidate2 = realpath($captBase . DIRECTORY_SEPARATOR . 'miniaturas' . DIRECTORY_SEPARATOR . $dirPart . DIRECTORY_SEPARATOR . $nameNoExt . '.webm');
    }
    if ($candidate1 && is_file($candidate1)) {
        // servir el webm de preview completo
        fclose($fp);
        $fullPath = $candidate1;
        $ext = 'webm';
        $size = filesize($fullPath);
        $fp = fopen($fullPath, 'rb');
        if (!$fp) { http_response_code(500); exit('Unable to open preview'); }
    } elseif ($candidate2 && is_file($candidate2)) {
        fclose($fp);
        $fullPath = $candidate2;
        $ext = 'webm';
        $size = filesize($fullPath);
        $fp = fopen($fullPath, 'rb');
        if (!$fp) { http_response_code(500); exit('Unable to open preview'); }
    } else {
        // No hay webm de preview; limitamos la respuesta a los primeros PREVIEW_BYTES
        $PREVIEW_BYTES = 500 * 1024; // 500 KB
        // ajustamos end y length ahora; ignoraremos HTTP_RANGE del cliente para forzar el preview inicial
        $end = min($size - 1, $PREVIEW_BYTES - 1);
        $length = $end - 0 + 1;
        // forzamos respuesta parcial
        $httpStatus = 206;
        $ignoreRange = true;
        fseek($fp, 0);
    }
}
if (isset($_SERVER['HTTP_RANGE'])) {
    if (empty($ignoreRange) && preg_match('/bytes=(\d*)-(\d*)/', $_SERVER['HTTP_RANGE'], $matches)) {
        if ($matches[1] !== '') $start = intval($matches[1]);
        if ($matches[2] !== '') $end = intval($matches[2]);
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

switch ($ext) {
    case 'mp4': $mime = 'video/mp4'; break;
    case 'webm': $mime = 'video/webm'; break;
    default: $mime = 'application/octet-stream';
}

header_remove();
if ($httpStatus === 206) {
    header('HTTP/1.1 206 Partial Content');
} else {
    header('HTTP/1.1 200 OK');
}
header('Content-Type: ' . $mime);
header('Accept-Ranges: bytes');
header('Content-Length: ' . $length);
header('Content-Range: bytes ' . $start . '-' . $end . '/' . $size);
header('Content-Disposition: inline; filename="' . basename($fullPath) . '"');

$bufferSize = 8192;
while (!feof($fp) && $length > 0) {
    $read = fread($fp, min($bufferSize, $length));
    echo $read;
    flush();
    $length -= strlen($read);
}
fclose($fp);
exit;
?>