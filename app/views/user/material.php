<?php
/**
 * Vista: Material (Panel de usuario)
 * Variables disponibles:
 *   $base_dir, $search_date, $search_start, $search_end,
 *   $per_page, $valid_pp, $page_videos, $total, $total_pages,
 *   $page, $recording_file_path, $is_search_active
 */
$head_data = [
    'title' => 'Capturadora VTV – Material',
    'css'   => [
        'components/navbar.css',
        'components/video_grid.css',
        'components/player.css',
        'components/pagination.css',
        'components/form.css',
    ],
];
require __DIR__ . '/../layouts/head.php';
require __DIR__ . '/../layouts/navbar_user.php';
?>

<div class="page-wrapper">
    <main class="main-content" style="margin-top: 160px;">
        <h1>Material</h1>

        <!-- ===== Player (oculto por defecto) ===== -->
        <div id="player-view" class="player-container" style="display:none;">
            <div class="video-main">
                <button id="player-close-btn">
                    <span class="material-symbols-outlined">arrow_back</span> Volver
                </button>
                <video id="main-video-player" src="" controls autoplay width="100%"></video>
                <div class="player-controls-bar">
                    <h2 id="player-title"></h2>
                    <a id="player-clip-btn" href="#" class="btn-clip" style="display:none;">
                        <span class="material-symbols-outlined">content_cut</span> Recortar
                    </a>
                </div>
            </div>
            <div class="video-sidebar">
                <h3>Siguientes:</h3>
                <div id="sidebar-video-list"></div>
            </div>
        </div>

        <!-- ===== Grid de videos ===== -->
        <div id="grid-view">

            <!-- Formulario de búsqueda -->
            <form action="<?= BASE_URL ?>/public/user/material.php" method="GET" class="search-form-user">
                <div class="search-group">
                    <label for="search-date">Fecha:</label>
                    <input type="date" id="search-date" name="d" value="<?= htmlspecialchars($search_date) ?>" required>
                </div>
                <div class="search-group">
                    <label for="search-start">Desde:</label>
                    <input type="time" id="search-start" name="start" value="<?= htmlspecialchars($search_start) ?>">
                </div>
                <div class="search-group">
                    <label for="search-end">Hasta:</label>
                    <input type="time" id="search-end" name="end" value="<?= htmlspecialchars($search_end) ?>">
                </div>
                <button type="submit" class="btn-primary">Buscar</button>
                <a href="<?= BASE_URL ?>/public/user/material.php" class="btn-secondary">Limpiar</a>
                <label for="per-page" style="margin-left:auto;">Mostrar:</label>
                <select name="pp" id="per-page" onchange="this.form.submit()">
                    <?php foreach ($valid_pp as $opt): ?>
                        <option value="<?= $opt ?>"<?= $opt === $per_page ? ' selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </form>

            <!-- Grid -->
            <div class="video-grid">
                <?php if (empty($page_videos) && $is_search_active): ?>
                    <p class="grid-empty">No se encontraron videos en el rango especificado.</p>
                <?php elseif (empty($page_videos)): ?>
                    <p class="grid-empty">No se encontraron videos para la fecha (<?= htmlspecialchars($search_date) ?>).</p>
                <?php else: ?>
                    <?php foreach ($page_videos as $video):
                        $filename    = basename($video);
                        $orig_name   = video_safe_name($video);
                        $display     = video_formatear_hora($filename);

                        // Tarjeta "Grabando..."
                        if ($recording_file_path !== null && $video === $recording_file_path): ?>
                            <div class="video-card" data-file="" data-preview="">
                                <div class="card-recording">
                                    <span class="material-symbols-outlined">fiber_manual_record</span>
                                    <span>Grabando...</span>
                                </div>
                                <span class="card-title"><?= htmlspecialchars($display) ?></span>
                            </div>
                        <?php else:
                            $rel_path = ltrim(
                                strpos($video, $base_dir) === 0
                                    ? substr($video, strlen($base_dir))
                                    : basename($video),
                                '/'
                            );
                            $web_src   = BASE_URL . '/public/api/stream.php?file=' . rawurlencode($rel_path);
                            $thumb_url = video_thumb_url($video, $base_dir, $search_date, BASE_URL . '/img/video-placeholder.svg');
                        ?>
                            <div class="video-card"
                                 data-file="<?= htmlspecialchars($rel_path) ?>"
                                 data-url="<?= htmlspecialchars($web_src) ?>"
                                 data-title="<?= htmlspecialchars($display) ?>"
                                 data-preview="<?= htmlspecialchars($web_src) ?>">
                                <img class="card-thumb" src="<?= htmlspecialchars($thumb_url) ?>"
                                     loading="lazy" alt="<?= htmlspecialchars($display) ?>"
                                     onerror="this.src='<?= BASE_URL ?>/img/video-placeholder.svg'">
                                <span class="card-title"><?= htmlspecialchars($display) ?></span>
                                <div class="card-actions">
                                    <a href="<?= htmlspecialchars($web_src) ?>"
                                       download="<?= htmlspecialchars($orig_name) ?>"
                                       class="btn-secondary btn-download">Descargar</a>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Paginación -->
            <?php if ($total > $per_page):
                $extra = ['d' => $search_date];
                if ($search_start !== '') $extra['start'] = $search_start;
                if ($search_end   !== '') $extra['end']   = $search_end;
                if ($per_page !== 8)     $extra['pp']     = $per_page;
            ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?<?= http_build_query(array_merge($extra, ['page' => $page - 1])) ?>" class="page-link">&laquo; Anterior</a>
                <?php endif; ?>
                <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                    <a href="?<?= http_build_query(array_merge($extra, ['page' => $p])) ?>"
                       class="page-link <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
                <?php endfor; ?>
                <?php if ($page < $total_pages): ?>
                    <a href="?<?= http_build_query(array_merge($extra, ['page' => $page + 1])) ?>" class="page-link">Siguiente &raquo;</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div><!-- /grid-view -->
    </main>
</div>

<script>
    const BASE_URL = '<?= BASE_URL ?>';
    const STREAM_BASE = '<?= BASE_URL ?>/public/api/stream.php';
    const CLIP_BASE = '<?= BASE_URL ?>/public/user/clip.php';
</script>
<script src="<?= BASE_URL ?>/public/js/user/material.js"></script>
</body>
</html>
