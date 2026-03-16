<?php
/**
 * Vista: Clip Editor
 * Variables disponibles:
 *   $video_for_editor  array|null  (si hay video seleccionado)
 *   $page_videos       array       (resultados de búsqueda)
 *   $total_pages       int
 *   $page              int
 *   $q_date            string
 *   $q_time            string
 *   $base_dir          string
 */
$head_data = [
    'title' => 'Capturadora VTV – Editor de Clip',
    'css'   => [
        'components/navbar.css',
        'components/video_grid.css',
        'components/clip_editor.css',
        'components/pagination.css',
        'components/form.css',
        'components/modal.css',
    ],
];
require __DIR__ . '/../layouts/head.php';
require __DIR__ . '/../layouts/navbar_user.php';
?>

<div class="page-wrapper">
    <main class="main-content" style="margin-top:160px; max-width:1000px; margin-left:auto; margin-right:auto; padding:20px;">

        <h1>Hacer Clip de Video</h1>

        <?php if ($video_for_editor !== null): ?>
            <!-- ===================== MODO EDITOR ===================== -->
            <p>
                <a href="<?= BASE_URL ?>/public/user/clip.php" class="back-link">
                    <span class="material-symbols-outlined">arrow_back</span> Volver al Buscador
                </a>
            </p>
            <h2>Recortar: <?= htmlspecialchars($video_for_editor['name']) ?></h2>

            <div class="clip-editor">
                <video id="clip-video-player"
                       src="<?= htmlspecialchars($video_for_editor['web_src']) ?>"
                       controls preload="metadata"></video>

                <form id="clip-form" class="clip-form-controls">
                    <input type="hidden" name="video_path" value="<?= htmlspecialchars($video_for_editor['safe_param']) ?>">
                    <input type="hidden" id="start_time" name="start_time" value="00:00">
                    <input type="hidden" id="end_time"   name="end_time"   value="00:00">

                    <div class="trim-controls">
                        <div class="trim-labels">
                            <span id="start-label">Inicio: <strong>00:00</strong></span>
                            <span id="duration-label">Duración: <strong>0s</strong></span>
                            <span id="end-label">Fin: <strong>00:00</strong></span>
                        </div>
                        <div class="range-slider-container">
                            <div class="range-track-selection"></div>
                            <input type="range" id="start-slider" min="0" max="100" step="0.1" value="0">
                            <input type="range" id="end-slider"   min="0" max="100" step="0.1" value="100">
                        </div>
                    </div>

                    <button type="button" id="submit-clip-btn" class="btn-primary">
                        <span class="material-symbols-outlined">preview</span> Cortar Clip
                    </button>
                </form>
            </div>

            <!-- Modal resultado -->
            <div id="clip-modal" class="modal-overlay" style="display:none;">
                <div class="modal-content clip-modal-content">
                    <button id="modal-close-btn" class="modal-close">&times;</button>
                    <h2>Clip Generado</h2>
                    <video id="modal-video-player" controls autoplay loop></video>
                    <a id="modal-download-btn" href="#" download="clip.mp4" class="btn-primary btn-download-clip">
                        <span class="material-symbols-outlined">download</span> Descargar Clip
                    </a>
                </div>
            </div>

            <script>
                const DOWNLOAD_CLIP_URL = '<?= BASE_URL ?>/public/api/download_clip.php';
                const BASE_FILENAME     = <?= json_encode($video_for_editor['base_js']) ?>;
            </script>
            <script src="<?= BASE_URL ?>/public/js/user/clip.js"></script>

        <?php else: ?>
            <!-- ===================== MODO BUSCADOR ===================== -->
            <h2>Buscar Video por Fecha y Hora</h2>

            <form action="<?= BASE_URL ?>/public/user/clip.php" method="GET" class="search-form-clip">
                <input type="date" name="q_date" value="<?= htmlspecialchars($q_date) ?>" placeholder="Fecha" required>
                <input type="text" name="q_time" value="<?= htmlspecialchars($q_time) ?>" placeholder="Hora (ej: 10, 10-30)">
                <button type="submit" class="btn-primary">Buscar</button>
                <?php if ($q_date !== '' || $q_time !== ''): ?>
                    <a href="<?= BASE_URL ?>/public/user/clip.php" class="btn-secondary">Limpiar</a>
                <?php endif; ?>
            </form>

            <?php if ($q_date !== '' && empty($page_videos)): ?>
                <p style="color:var(--bg);">No se encontraron videos para la fecha <strong><?= htmlspecialchars($q_date) ?></strong>.</p>
            <?php elseif (!empty($page_videos)): ?>
                <div class="video-grid">
                    <?php foreach ($page_videos as $video):
                        $filename = basename($video);
                        $name     = video_safe_name($video);

                        $rel_path = ltrim(
                            strpos($video, $base_dir) === 0
                                ? substr($video, strlen($base_dir))
                                : basename($video),
                            '/'
                        );
                        $thumb_url = video_thumb_url($video, $base_dir, $q_date, BASE_URL . '/img/video-placeholder.svg');
                    ?>
                        <div class="video-card" data-file="<?= htmlspecialchars($rel_path) ?>">
                            <img class="card-thumb" src="<?= htmlspecialchars($thumb_url) ?>"
                                 loading="lazy" alt="<?= htmlspecialchars($name) ?>"
                                 onerror="this.src='<?= BASE_URL ?>/img/video-placeholder.svg'">
                            <span class="card-title"><?= htmlspecialchars($name) ?></span>
                            <a href="<?= BASE_URL ?>/public/user/clip.php?video_path=<?= rawurlencode($rel_path) ?>"
                               class="btn-secondary">Seleccionar</a>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Paginación -->
                <?php if ($total_pages > 1):
                    $extra = ['q_date' => $q_date, 'q_time' => $q_time];
                ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?<?= http_build_query(array_merge($extra, ['page' => $page - 1])) ?>" class="page-link">&laquo;</a>
                    <?php endif; ?>
                    <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                        <a href="?<?= http_build_query(array_merge($extra, ['page' => $p])) ?>"
                           class="page-link <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
                    <?php endfor; ?>
                    <?php if ($page < $total_pages): ?>
                        <a href="?<?= http_build_query(array_merge($extra, ['page' => $page + 1])) ?>" class="page-link">&raquo;</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <p style="color:var(--muted);">Ingrese una fecha para comenzar la búsqueda.</p>
            <?php endif; ?>
        <?php endif; ?>

    </main>
</div>
</body>
</html>
