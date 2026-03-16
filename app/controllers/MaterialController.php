<?php
// ============================================================
// Controller: Material
// Gestiona la vista principal de videos del usuario
// ============================================================
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/VideoModel.php';
require_once __DIR__ . '/../controllers/AuthController.php';

/**
 * Muestra la cuadrícula de videos con búsqueda y paginación.
 */
function material_index(): void {
    auth_requerir_sesion();

    $base_dir = rtrim(VIDEO_BASE_DIR, '/') . '/';

    // Parámetros de búsqueda
    $search_date  = (isset($_GET['d']) && !empty(trim($_GET['d']))) ? trim($_GET['d']) : date('Y-m-d');
    $search_start = trim($_GET['start'] ?? '');
    $search_end   = trim($_GET['end']   ?? '');

    $valid_pp = [8, 16, 24, 32, 40];
    $per_page = isset($_GET['pp']) && in_array((int)$_GET['pp'], $valid_pp) ? (int)$_GET['pp'] : 8;

    $is_search_active = ($search_start !== '' || $search_end !== '');

    // Cargar y ordenar videos
    $all_videos = video_listar_por_fecha($base_dir, $search_date, $is_search_active);

    // Detectar grabación en curso (archivo modificado en los últimos 120s)
    $recording_file_path = null;
    if (!empty($all_videos)) {
        $newest = $is_search_active ? end($all_videos) : $all_videos[0];
        if (time() - filemtime($newest) < 120) {
            $recording_file_path = $newest;
        }
    }

    // Filtrar por rango horario
    $videos = video_filtrar_por_hora($all_videos, $search_start, $search_end);

    // Paginar
    $page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $result = video_paginar(array_values($videos), $per_page, $page);
    $page_videos  = $result['videos'];
    $total        = $result['total'];
    $total_pages  = $result['total_pages'];
    $page         = $result['page'];

    require __DIR__ . '/../views/user/material.php';
}
