<?php
// ============================================================
// Model: Video
// Helpers para manejo de archivos de video en el filesystem
// ============================================================

/**
 * Retorna el nombre de archivo escapado para HTML.
 */
function video_safe_name(string $path): string {
    return htmlspecialchars(basename($path));
}

/**
 * Convierte el nombre de archivo (ej: captura_20260316_163015_...mf4)
 * a formato legible de hora (ej: 04:30:15 PM).
 */
function video_formatear_hora(string $filename): string {
    $base = pathinfo($filename, PATHINFO_FILENAME);
    // Asume formato: captura_YYYYMMDD_HHMMSS
    $parts = explode('_', $base);
    if (count($parts) >= 3 && strlen($parts[2]) >= 6) {
        $hora_str = substr($parts[2], 0, 6); // HHMMSS
        $hora_format = substr($hora_str, 0, 2) . ':' . substr($hora_str, 2, 2) . ':' . substr($hora_str, 4, 2);
        $ts = strtotime($hora_format);
        return ($ts !== false) ? date('h:i:s A', $ts) : $hora_format;
    }
    
    // Fallback viejo formato (camara_14-30-15)
    $pos  = strpos($base, '_');
    $hora = ($pos !== false) ? substr($base, $pos + 1) : $base;
    $hora_militar = str_replace('-', ':', $hora);
    $ts = strtotime($hora_militar);
    return ($ts !== false) ? date('h:i:s A', $ts) : $hora_militar;
}

/**
 * Extrae la hora en formato HH:MM:SS del nombre de archivo para comparaciones.
 */
function video_hora_raw(string $filename): string {
    $base = pathinfo($filename, PATHINFO_FILENAME);
    
    // Asume formato: captura_YYYYMMDD_HHMMSS
    $parts = explode('_', $base);
    if (count($parts) >= 3 && strlen($parts[2]) >= 6) {
        $hora_str = substr($parts[2], 0, 6); // HHMMSS
        return substr($hora_str, 0, 2) . ':' . substr($hora_str, 2, 2) . ':' . substr($hora_str, 4, 2);
    }

    $pos  = strpos($base, '_');
    if ($pos !== false) {
        return str_replace('-', ':', substr($base, $pos + 1));
    }
    return '00:00:00';
}

/**
 * Obtiene todos los videos de una fecha.
 * $base_dir debe terminar en '/'.
 * $fecha esperado en YYYY-MM-DD.
 * Retorna array de rutas absolutas.
 */
function video_listar_por_fecha(string $base_dir, string $fecha, bool $ascendente = false): array {
    $fecha_compacta = str_replace('-', '', $fecha); // YYYYMMDD
    
    // Primero, buscamos en formato de archivo de capturadora (la mayoría estarán aquí en la O:)
    $videos = glob($base_dir . '*_' . $fecha_compacta . '_*.{mp4,webm}', GLOB_BRACE) ?: [];
    
    // Fallback: buscar en subcarpeta YYYY-MM-DD vieja
    $date_dir = rtrim($base_dir, '/') . '/' . $fecha . '/';
    if (is_dir($date_dir)) {
        $videos = array_merge(
            $videos,
            glob($date_dir . '*.mp4')  ?: [],
            glob($date_dir . '*.webm') ?: []
        );
    }
    
    // Fallback: subcarpeta YYYYMMDD
    $date_dir_compact = rtrim($base_dir, '/') . '/' . $fecha_compacta . '/';
    if (is_dir($date_dir_compact)) {
        $videos = array_merge(
            $videos,
            glob($date_dir_compact . '*.mp4')  ?: [],
            glob($date_dir_compact . '*.webm') ?: []
        );
    }

    if (!empty($videos)) {
        $videos = array_unique($videos); // Evitar duplicados si hay cruces
        usort($videos, function($a, $b) use ($ascendente) {
            return $ascendente
                ? strcmp(basename($a), basename($b))
                : filemtime($b) - filemtime($a);
        });
    }

    return $videos;
}

/**
 * Filtra videos por rango horario (start y end en formato HH:MM).
 */
function video_filtrar_por_hora(array $videos, string $start, string $end): array {
    if ($start === '' && $end === '') {
        return $videos;
    }
    return array_filter($videos, function($v) use ($start, $end) {
        $t = video_hora_raw(basename($v));
        if ($start !== '' && $t < $start . ':00') return false;
        if ($end   !== '' && $t > $end   . ':59') return false;
        return true;
    });
}

/**
 * Pagina un array de videos.
 * Retorna ['videos' => [...], 'total' => int, 'total_pages' => int, 'page' => int]
 */
function video_paginar(array $videos, int $per_page, int $page): array {
    $total       = count($videos);
    $total_pages = $total > 0 ? (int) ceil($total / $per_page) : 1;
    $page        = max(1, min($page, $total_pages));
    $offset      = ($page - 1) * $per_page;
    return [
        'videos'      => array_slice($videos, $offset, $per_page),
        'total'       => $total,
        'total_pages' => $total_pages,
        'page'        => $page,
    ];
}

/**
 * Busca la URL de la miniatura de un video.
 * Retorna URL web si la encuentra, o la URL del placeholder.
 */
function video_thumb_url(string $video_path, string $base_dir, string $fecha, string $placeholder): string {
    $filename     = basename($video_path);
    $name_no_ext  = pathinfo($filename, PATHINFO_FILENAME);
    $capturadora  = dirname(rtrim($base_dir, '/')) . '/';

    $try1 = dirname($video_path) . '/' . $name_no_ext . '.png';
    $try2 = $capturadora . 'miniaturas/' . $name_no_ext . '.png';
    $try3 = $capturadora . 'miniaturas/' . $fecha . '/' . $name_no_ext . '.png';

    $found = '';
    if (file_exists($try1))      $found = $try1;
    elseif (file_exists($try2))  $found = $try2;
    elseif (file_exists($try3))  $found = $try3;

    if ($found !== '') {
        $relative = ltrim(str_replace(rtrim($capturadora, '/'), '', $found), '/');
        return '/Videos/capturadora/' . str_replace('%2F', '/', rawurlencode($relative));
    }

    return $placeholder;
}
