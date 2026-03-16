<?php
// ============================================================
// public/api/download_clip.php – Genera y guarda un clip con FFmpeg
// Migrado desde User/download_clip.php – rutas actualizadas
// ============================================================
require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../app/controllers/AuthController.php';

auth_requerir_sesion();

date_default_timezone_set('America/Caracas');
header('Content-Type: application/json');

function send_error(string $msg): never {
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}

$start_raw  = $_POST['start_time'] ?? '00:00';
$end_raw    = $_POST['end_time']   ?? '00:00';
$video_param = $_POST['video_path'] ?? '';

if (empty($video_param)) send_error('No se especificó el archivo de video.');

[$sm, $ss] = explode(':', $start_raw);
[$em, $es] = explode(':', $end_raw);
$start_s  = (int)$sm * 60 + (int)$ss;
$end_s    = (int)$em * 60 + (int)$es;
$duration = $end_s - $start_s;

if ($duration <= 0)   send_error('La duración del clip debe ser positiva.');
if ($duration > 600)  send_error('El clip no puede durar más de 10 minutos.');

$safe_param   = preg_replace('/[^a-zA-Z0-9_\-\.\/]/', '', $video_param);
$base_dir     = rtrim(VIDEO_BASE_DIR, '/') . '/';
$video_path   = $base_dir . $safe_param;

if (!file_exists($video_path)) send_error('Archivo de video no encontrado.');

$out_dir = rtrim(BASE_PATH . '/Videos/capturadora/clips_cortados', '/') . '/';
if (!is_dir($out_dir)) mkdir($out_dir, 0777, true);
if (!is_writable($out_dir)) send_error('Directorio de salida sin permisos de escritura.');

$basename    = basename($video_path, '.mp4');
$out_name    = $basename . '_clip_' . str_replace(':', '-', $start_raw) . '.mp4';
$out_path_fs = $out_dir . $out_name;
$out_path_web = BASE_URL . '/Videos/capturadora/clips_cortados/' . $out_name;

$ffmpeg_start = sprintf('%02d:%02d:%02d',
    floor($start_s / 3600),
    floor(($start_s % 3600) / 60),
    $start_s % 60
);

$cmd = "unset LD_LIBRARY_PATH && /usr/bin/ffmpeg -ss " . escapeshellarg($ffmpeg_start)
     . " -i " . escapeshellarg($video_path)
     . " -t "  . escapeshellarg((string)$duration)
     . " -c:v copy -c:a copy -y "
     . escapeshellarg($out_path_fs) . " 2>&1";

shell_exec($cmd);

if (file_exists($out_path_fs)) {
    echo json_encode(['success' => true, 'url' => $out_path_web, 'filename' => $out_name]);
} else {
    send_error('Error al generar el clip con FFmpeg.');
}
exit;
