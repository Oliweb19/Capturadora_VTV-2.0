<?php
// (MODIFICADO) Este script ahora GUARDA el clip y devuelve JSON.
date_default_timezone_set('America/Caracas');
header('Content-Type: application/json'); // ¡Importante!

// --- 1. Obtener y validar datos ---
$start_time_raw = isset($_POST['start_time']) ? $_POST['start_time'] : '00:00'; 
$end_time_raw = isset($_POST['end_time']) ? $_POST['end_time'] : '00:00';
$video_file_param = isset($_POST['video_path']) ? $_POST['video_path'] : '';

// --- Función para enviar errores en JSON ---
function send_error($message) {
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

if (empty($video_file_param)) {
    send_error('No se especificó el archivo de video.');
}

// Conversión de MM:SS a segundos
list($start_m, $start_s) = explode(':', $start_time_raw);
list($end_m, $end_s) = explode(':', $end_time_raw);
$start_seconds = (int)$start_m * 60 + (int)$start_s;
$end_seconds = (int)$end_m * 60 + (int)$end_s;
$duration = $end_seconds - $start_seconds;

if ($duration <= 0) {
    send_error('La duración del clip debe ser positiva.');
}
if ($duration > 600) { // Límite de 10 minutos
    send_error('El clip no puede durar más de 10 minutos.');
}

// --- 2. Preparar rutas y nombre de archivo ---
$safe_video_param = preg_replace('/[^a-zA-Z0-9_\-\.\/]/', '', $video_file_param);
$fs_video_path_base = rtrim(realpath(__DIR__ . '/../Videos/capturadora/clips/'), '/') . '/';
$video_path = $fs_video_path_base . $safe_video_param;

if (!file_exists($video_path)) {
    send_error('Archivo de video no encontrado en el servidor.');
}

// (NUEVO) Definir rutas de guardado
$fs_output_dir = rtrim(realpath(__DIR__ . '/../Videos/capturadora/clips_cortados/'), '/') . '/';
$web_output_dir = '/Videos/capturadora/clips_cortados/'; // Ruta web para el navegador

if (!is_dir($fs_output_dir)) {
    mkdir($fs_output_dir, 0777, true);
}
if (!is_writable($fs_output_dir)) {
    send_error('Error: El directorio de clips cortados no tiene permisos de escritura.');
}

$filename_base = basename($video_path, '.mp4');
$output_filename = $filename_base . '_clip_' . str_replace(':', '-', $start_time_raw) . '.mp4';

// Ruta completa donde se guardará el archivo
$output_path_fs = $fs_output_dir . $output_filename;
// Ruta web que se enviará al navegador
$output_path_web = $web_output_dir . $output_filename;


// Formato de inicio para FFmpeg
$ffmpeg_start = sprintf("%02d:%02d:%02d", floor($start_seconds / 3600), floor(($start_seconds % 3600) / 60), $start_seconds % 60);

// --- 3. Preparar el comando FFmpeg para GUARDAR ---
// (MODIFICADO) Quitamos los flags de streaming y ponemos la ruta de salida
$command = "unset LD_LIBRARY_PATH && /usr/bin/ffmpeg -ss " . escapeshellarg($ffmpeg_start) . 
           " -i " . escapeshellarg($video_path) .
           " -t " . escapeshellarg($duration) .
           " -c:v copy -c:a copy -y " . // -y para sobrescribir
           escapeshellarg($output_path_fs) . " 2>&1"; // 2>&1 para capturar errores

// --- 4. Ejecutar y enviar respuesta JSON ---
$output_de_ffmpeg = shell_exec($command);

if (file_exists($output_path_fs)) {
    // ¡Éxito! Enviar la URL al navegador
    echo json_encode([
        'success' => true,
        'url' => $output_path_web,
        'filename' => $output_filename
    ]);
} else {
    // ¡Fracaso! Enviar el error
    send_error('Error al generar el clip: ' . $output_de_ffmpeg);
}

// Terminar el script
exit;
?>