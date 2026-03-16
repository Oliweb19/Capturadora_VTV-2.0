<?php
// ============================================================
// Controller: Clip
// Gestiona la búsqueda y selección de videos para recortar
// ============================================================
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/VideoModel.php';
require_once __DIR__ . '/../controllers/AuthController.php';

/**
 * Muestra el buscador de clips o el editor si ya hay un video seleccionado.
 */
function clip_index(): void {
    auth_requerir_sesion();

    $base_dir = rtrim(VIDEO_BASE_DIR, '/') . '/';

    $video_file_param = trim($_GET['video_path'] ?? '');
    $q_date           = trim($_GET['q_date']     ?? '');
    $q_time           = trim($_GET['q_time']     ?? '');
    $per_page         = 12;
    $page             = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

    // ---- Modo editor (video ya seleccionado) ----
    $video_for_editor = null;
    if ($video_file_param !== '') {
        $safe_param   = preg_replace('/[^a-zA-Z0-9_\-\.\/]/', '', $video_file_param);
        $video_path   = $base_dir . $safe_param;
        $web_video_src     = BASE_URL . '/public/api/stream.php?file=' . rawurlencode($safe_param);
        $base_filename_js  = basename($video_path, '.mp4');

        if (file_exists($video_path)) {
            $video_for_editor = [
                'path'        => $video_path,
                'web_src'     => $web_video_src,
                'base_js'     => $base_filename_js,
                'safe_param'  => $safe_param,
                'name'        => video_safe_name($video_path),
            ];
        }
    }

    // ---- Modo buscador ----
    $page_videos  = [];
    $total_pages  = 1;

    if ($video_for_editor === null && $q_date !== '') {
        $all_videos = video_listar_por_fecha($base_dir, $q_date);
        
        if (!empty($all_videos)) {
            // Filtro por hora
            if ($q_time !== '') {
                $search_time_safe = preg_replace('/[^0-9-:]/', '', $q_time);
                if ($search_time_safe !== '') {
                    $search_time_compact = str_replace(['-', ':'], '', $search_time_safe);
                    $all_videos = array_filter($all_videos, function($v) use ($search_time_compact, $search_time_safe) {
                        return str_contains(basename($v), $search_time_compact) || str_contains(basename($v), $search_time_safe);
                    });
                    $all_videos = array_values($all_videos);
                }
            }

            $result      = video_paginar($all_videos, $per_page, $page);
            $page_videos = $result['videos'];
            $total_pages = $result['total_pages'];
            $page        = $result['page'];
        }
    }

    require __DIR__ . '/../views/user/clip.php';
}
