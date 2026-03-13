<?php
    session_start();
	$conexion = mysqli_connect('localhost', 'root', 'S3rvic10s.vtv', 'capturadora');
											
	if (isset($_SESSION['usuario'])) {
		//echo $_SESSION['usuario'];
	}
	else{ 
		header('Location:../index.php');
    }

?> 
<!DOCTYPE html> 
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Capturadora VTV - Hacer Clip</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <link rel="shourt icon" href="../img/favicon.ico">
</head>
<body>
    <div class="cont-dir">
        <div class="menu-capturadora">
            <div class="logo">
                <img src="../img/vtv.png" alt="Capturadora VTV" srcset="">
                </div>
            <div class="opciones">
                <a href="material.php">Inicio</a>
                <?php if ($_SESSION['privilegio'] == 'A') { ?>
                    <a href="../Admin/usuarios.php">Administrar</a>
                <?php } ?>
                <a href="../BD/cerrar.php" id="btn-salir-user"><span class="material-symbols-outlined">login</span></a>
            </div>
        </div>
        <div class="cont-diario" style="max-width: 1000px;">
            <h1>Hacer Clip de Video</h1>

            <?php
            date_default_timezone_set('America/Caracas');
            
            // Variables de estado
            $video_file_param = isset($_GET['video_path']) ? $_GET['video_path'] : '';
            
            // Variables de Búsqueda
            $q_date = isset($_GET['q_date']) ? $_GET['q_date'] : ''; 
            $q_time = isset($_GET['q_time']) ? $_GET['q_time'] : ''; 

            // --- Funciones auxiliares ---
            function safe_name($path) {
                return htmlspecialchars(basename($path));
            }

            // Paginación
            $perPage = 12;
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            
            function build_search_url($page, $q_date, $q_time) {
                $params = ['page' => $page];
                if (!empty($q_date)) $params['q_date'] = $q_date;
                if (!empty($q_time)) $params['q_time'] = $q_time;
                return '?' . http_build_query($params);
            }
            // --- Fin Funciones auxiliares ---
            
            
            // =========================================================
            // LÓGICA 1: MOSTRAR FORMULARIO DE CORTE (Video Seleccionado)
            // =========================================================
            if (!empty($video_file_param)) {
                
                $safe_video_param = preg_replace('/[^a-zA-Z0-9_\-\.\/]/', '', $video_file_param);
                
                $fs_video_path_base = rtrim(realpath(__DIR__ . '/../Videos/capturadora/clips/'), '/') . '/';
                $video_path = $fs_video_path_base . $safe_video_param;
                
                // URL del video para el reproductor HTML
                $web_video_src = 'stream.php?file=' . rawurlencode($video_file_param);
                
                // Obtener el nombre base del archivo para el JS
                $base_filename_for_js = basename($video_path, '.mp4');


                if (file_exists($video_path)) {
                    
                    // --- Renderizar Vista de Corte ---
                    echo '<p><a href="material.php" class="back-link"><span class="material-symbols-outlined">arrow_back</span>Volver al Buscador</a></p>';
                    echo '<h2>Recortar video: ' . safe_name($video_path) . '</h2>';

                    ?>
                    <div class="clip-visual-editor">
                        <video id="clip-video-player" src="<?php echo htmlspecialchars($web_video_src); ?>" controls preload="metadata"></video>
                        
                        <form action="download_clip.php" method="POST" class="clip-form" id="clip-form">
                            
                            <input type="hidden" name="video_path" value="<?php echo htmlspecialchars($safe_video_param); ?>">

                            <input type="hidden" id="start_time" name="start_time" value="00:00">
                            <input type="hidden" id="end_time" name="end_time" value="00:00">

                            <div class="trim-controls">
                                <div class="trim-labels">
                                    <span id="start-label">Inicio: <strong>00:00</strong></span>
                                    <span id="duration-label">Duración: <strong>0s</strong></span>
                                    <span id="end-label">Fin: <strong>00:00</strong></span>
                                </div>
                                <div class="range-slider-container">
                                    <div class="range-track-selection"></div>
                                    <input type="range" id="start-slider" min="0" max="100" step="0.1" value="0">
                                    <input type="range" id="end-slider" min="0" max="100" step="0.1" value="100">
                                </div>
                            </div>
                            
                            <button type="button" id="submit-clip-btn" style="margin-top: 20px;">
                                <span class="material-symbols-outlined" style="vertical-align: middle; font-size: 1.2em;">preview</span>
                                Cortar Clip
                            </button>
                        </form>
                    </div>

                    <div id="clip-modal" class="modal-overlay">
                        <div class="modal-content">
                            <button id="modal-close-btn" class="modal-close">&times;</button>
                            <h2>Clip Generado</h2>
                            <video id="modal-video-player" controls autoplay loop></video>
                            <a id="modal-download-btn" href="#" download="clip.mp4" class="archivo-download">
                                <span class="material-symbols-outlined" style="vertical-align: middle;">download</span>
                                Descargar Clip
                            </a>
                        </div>
                    </div>


                    <script>
                        // Pasamos el nombre del archivo de PHP a JS
                        const baseFilename = <?php echo json_encode($base_filename_for_js); ?>;

                        document.addEventListener('DOMContentLoaded', () => {
                            // Referencias del editor
                            const video = document.getElementById('clip-video-player');
                            const startSlider = document.getElementById('start-slider');
                            const endSlider = document.getElementById('end-slider');
                            
                            const startLabel = document.getElementById('start-label').querySelector('strong');
                            const endLabel = document.getElementById('end-label').querySelector('strong');
                            const durationLabel = document.getElementById('duration-label').querySelector('strong');
                            
                            const startTimeInput = document.getElementById('start_time');
                            const endTimeInput = document.getElementById('end_time');

                            const selectionTrack = document.querySelector('.range-track-selection');

                            let maxDuration = 100;
                            
                            // (ELIMINADO) 'currentObjectUrl' ya no es necesario

                            // --- Funciones (formatTime, updateVisuals) ---
                            function formatTime(totalSeconds) {
                                const minutes = Math.floor(totalSeconds / 60);
                                const seconds = Math.floor(totalSeconds % 60);
                                return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                            }

                            function updateVisuals() {
                                const start = parseFloat(startSlider.value);
                                const end = parseFloat(endSlider.value);
                                const duration = end - start;
                                startLabel.textContent = formatTime(start);
                                endLabel.textContent = formatTime(end);
                                durationLabel.textContent = `${duration.toFixed(1)}s`;
                                startTimeInput.value = formatTime(start);
                                endTimeInput.value = formatTime(end);
                                const startPercent = (start / maxDuration) * 100;
                                const endPercent = (end / maxDuration) * 100;
                                selectionTrack.style.left = `${startPercent}%`;
                                selectionTrack.style.width = `${endPercent - startPercent}%`;
                            }

                            // --- (LA CORRECCIÓN) Usar 'durationchange' ---
                            video.addEventListener('durationchange', () => {
    
                                // A veces 'durationchange' se dispara con NaN o Infinity, 
                                // especialmente con streams.
                                // Nos aseguramos de que sea un número finito y positivo.
                                const newDuration = video.duration;
                                
                                if (newDuration && isFinite(newDuration)) {
                                    
                                    // Solo actualizamos si la duración es realmente diferente
                                    // al 'maxDuration' que ya teníamos (para no hacer trabajo extra).
                                    if (newDuration !== maxDuration) {
                                    
                                        maxDuration = newDuration;
                                        startSlider.max = maxDuration;
                                        endSlider.max = maxDuration;
                                        
                                        // Importante: Reiniciar los valores de los sliders
                                        // al valor completo del video.
                                        startSlider.value = 0;
                                        endSlider.value = maxDuration;
                                        
                                        updateVisuals();
                                    }
                                }
                            });

                            // --- Eventos de los sliders (sin cambios) ---
                            startSlider.addEventListener('input', () => {
                                let start = parseFloat(startSlider.value);
                                let end = parseFloat(endSlider.value);
                                if (start >= end) {
                                    startSlider.value = end - 0.1;
                                    start = parseFloat(startSlider.value);
                                }
                                video.currentTime = start;
                                updateVisuals();
                            });

                            endSlider.addEventListener('input', () => {
                                let start = parseFloat(startSlider.value);
                                let end = parseFloat(endSlider.value);
                                if (end <= start) {
                                    endSlider.value = start + 0.1;
                                    end = parseFloat(endSlider.value);
                                }
                                video.currentTime = end;
                                updateVisuals();
                            });
                            
                            video.addEventListener('timeupdate', () => {
    
                                // --- INICIO DE LA NUEVA LÓGICA (PLAN B) ---
                                // Revisa constantemente si la duración del video es mayor
                                // que la que tiene el slider actualmente.
                                if (isFinite(video.duration) && video.duration > maxDuration) {
                                    
                                    maxDuration = video.duration;
                                    startSlider.max = maxDuration;
                                    endSlider.max = maxDuration;
                                    
                                    // Si el slider de fin estaba en el máximo, lo actualizamos
                                    if (parseFloat(endSlider.value) < maxDuration) {
                                        endSlider.value = maxDuration;
                                    }

                                    updateVisuals(); // Actualizamos las etiquetas
                                }
                                // --- FIN DE LA NUEVA LÓGICA ---


                                const start = parseFloat(startSlider.value);
                                const end = parseFloat(endSlider.value);
                                
                                if (video.currentTime > end) {
                                    video.pause();
                                    video.currentTime = start; // Volver al inicio del clip
                                }
                            });

                            // --- (MODIFICADO) Lógica del Modal y Fetch para recibir JSON ---
                            const clipForm = document.getElementById('clip-form');
                            const submitBtn = document.getElementById('submit-clip-btn');
                            
                            // Referencias del Modal
                            const modalOverlay = document.getElementById('clip-modal');
                            const modalVideo = document.getElementById('modal-video-player');
                            const modalDownload = document.getElementById('modal-download-btn');
                            const modalClose = document.getElementById('modal-close-btn');

                            // Listener para el botón de "Previsualizar"
                            submitBtn.addEventListener('click', async () => {
                                // 1. Deshabilitar botón y mostrar carga
                                submitBtn.disabled = true;
                                submitBtn.innerHTML = '<span class="material-symbols-outlined" style="vertical-align: middle; font-size: 1.2em;">hourglass_top</span> Generando, por favor espere...';

                                // 2. Preparar los datos del formulario
                                const formData = new FormData(clipForm);
                                
                                try {
                                    // 3. Llamar a download_clip.php en segundo plano
                                    const response = await fetch('download_clip.php', {
                                        method: 'POST',
                                        body: formData
                                    });

                                    if (!response.ok) {
                                        throw new Error(`Error del servidor: ${response.statusText}`);
                                    }

                                    // 4. (MODIFICADO) Esperar una respuesta JSON, no un Blob
                                    const data = await response.json();

                                    // 5. (MODIFICADO) Comprobar si el JSON indica éxito
                                    if (data.success) {
                                        // 6. Configurar el modal con la URL del archivo guardado
                                        modalVideo.src = data.url;
                                        modalDownload.href = data.url;
                                        modalDownload.setAttribute('download', data.filename);
                                        
                                        // 7. Mostrar el modal
                                        modalOverlay.style.display = 'flex';
                                    } else {
                                        // Si el JSON reporta un error (ej. de ffmpeg)
                                        throw new Error(data.error);
                                    }

                                } catch (error) {
                                    console.error('Error al generar el clip:', error);
                                    alert('Hubo un error al generar el clip: ' + error.message);
                                } finally {
                                    // 8. Reactivar el botón sin importar si falló o no
                                    submitBtn.disabled = false;
                                    submitBtn.innerHTML = '<span class="material-symbols-outlined" style="vertical-align: middle;">preview</span> Previsualizar Clip';
                                }
                            });

                            // Lógica para cerrar el modal
                            modalClose.addEventListener('click', () => {
                                modalOverlay.style.display = 'none';
                                modalVideo.pause(); // Pausar el video
                                modalVideo.src = ''; // Limpiar la fuente
                                
                                // (MODIFICADO) Ya no necesitamos limpiar el Blob
                            });

                            // Cerrar si hace clic fuera del contenido
                            modalOverlay.addEventListener('click', (e) => {
                                if (e.target === modalOverlay) {
                                    modalClose.click();
                                }
                            });
                        });
                    </script>

                <?php 
                } else {
                    echo '<p class="error">Error: Archivo de video no encontrado en ' . htmlspecialchars($video_path) . '</p>';
                }
            
            // =========================================================
            // LÓGICA 2: MOSTRAR BUSCADOR Y RESULTADOS (No hay video seleccionado)
            // =========================================================
            } else { 
                echo '<h2>Buscar Video por Fecha y Hora</h2>';
                
                // --- Formulario de Búsqueda ---
                ?>
                <form action="Clip.php" method="GET" class="search-form">
                    <input type="date" name="q_date" value="<?php echo htmlspecialchars($q_date); ?>" placeholder="Fecha (AAAA-MM-DD)" required>
                    <input type="text" name="q_time" value="<?php echo htmlspecialchars($q_time); ?>" placeholder="Hora (ej: 10, 10-30)">
                    <button type="submit">Buscar</button>
                    <?php if (!empty($q_date) || !empty($q_time)): ?>
                        <a href="Clip.php">Limpiar Búsqueda</a>
                    <?php endif; ?>
                </form>
                <?php
                // --- Fin Formulario de Búsqueda ---

                $fs_base_dir = __DIR__ . '/../Videos/capturadora/clips/';
                $fs_base_dir = rtrim(realpath($fs_base_dir) ?: $fs_base_dir, '/') . '/';

                $videos = [];
                if (!empty($q_date)) {
                    
                    $search_dir = $fs_base_dir . $q_date . '/';
                    
                    if (is_dir($search_dir)) {
                        $all_videos = glob($search_dir . '*.mp4') ?: [];
                        
                        if (!empty($all_videos)) {
                            usort($all_videos, function($a, $b) {
                                return filemtime($b) - filemtime($a);
                            });
                        }

                        // Lógica de filtrado por hora
                        $videos = $all_videos;
                        if (!empty($q_time)) {
                            $search_time_safe = preg_replace('/[^0-9-]/', '', $q_time);
                            if (!empty($search_time_safe)) {
                                $filtered_videos = [];
                                foreach ($all_videos as $video) {
                                    $filename = basename($video);
                                    if (str_contains($filename, $search_time_safe)) {
                                        $filtered_videos[] = $video;
                                    }
                                }
                                $videos = $filtered_videos;
                            }
                        }

                        // Paginación
                        $total = count($videos);
                        $totalPages = $total > 0 ? (int)ceil($total / $perPage) : 1;
                        if ($page > $totalPages) $page = $totalPages;
                        $offset = ($page - 1) * $perPage;
                        $pageVideos = array_slice($videos, $offset, $perPage);
                        
                        // --- Mostrar Resultados ---
                        if (empty($videos)): ?>
                            <p>No se encontraron videos para la fecha <strong><?php echo htmlspecialchars($q_date); ?></strong><?php if (!empty($q_time)) echo " y hora <strong>" . htmlspecialchars($q_time) . "</strong>"; ?>.</p>
                        <?php else: ?>
                            <div class="cont-archivos">
                                <?php foreach ($pageVideos as $video): 
                                
                                    $filename = basename($video);
                                    $name = safe_name($video);

                                    if (strpos($video, $fs_base_dir) === 0) {
                                        $relativeVideoPath = ltrim(substr($video, strlen($fs_base_dir)), '/');
                                    } else {
                                        $relativeVideoPath = basename($video);
                                    }
                                    
                                    // Buscar miniatura...
                                    $thumbNameNoExt = pathinfo($filename, PATHINFO_FILENAME);
                                    $try1 = dirname($video) . '/' . $thumbNameNoExt . '.png';
                                    $capturadoraParent = dirname(rtrim($fs_base_dir, '/')) . '/';
                                    $try2 = $capturadoraParent . 'miniaturas/' . $thumbNameNoExt . '.png';
                                    $try3 = $capturadoraParent . 'miniaturas/' . $q_date . '/' . $thumbNameNoExt . '.png';

                                    $foundThumbFs = '';
                                    if (file_exists($try1)) $foundThumbFs = $try1;
                                    elseif (file_exists($try2)) $foundThumbFs = $try2;
                                    elseif (file_exists($try3)) $foundThumbFs = $try3;

                                    if ($foundThumbFs !== '') {
                                        $relative = str_replace(rtrim($capturadoraParent, '/'), '', $foundThumbFs);
                                        $relative = ltrim($relative, '/');
                                        $thumbUrl = '/Videos/capturadora/' . str_replace('%2F','/', rawurlencode($relative));
                                    } else {
                                        $thumbUrl = '../img/video-placeholder.svg';
                                    }
                                ?>
                                
                                <div class="archivo" data-file="<?php echo htmlspecialchars($relativeVideoPath); ?>">
                                    <img class="thumb" src="<?php echo htmlspecialchars($thumbUrl); ?>" loading="lazy" alt="<?php echo $name; ?>" onerror="this.src='../img/video-placeholder.svg'" style="cursor:pointer;">
                                    <span class="nombre-archivo"><?php echo $name; ?></span>
                                    
                                    <a href="Clip.php?video_path=<?php echo rawurlencode($relativeVideoPath); ?>" 
                                       class="archivo-download" style="background: var(--color-secondary);">Seleccionar</a>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <?php 
                            // Renderizar paginador
                            $extra_params = ['q_date' => $q_date, 'q_time' => $q_time];
                            if ($totalPages > 1) {
                                echo '<div class="paginador">';
                                $q_func = function($p) use ($extra_params) {
                                    $params = array_merge($extra_params, ['page' => $p]);
                                    return 'Clip.php?' . http_build_query($params);
                                };
                                
                                if ($page > 1) echo '<a class="page-link" href="'.htmlspecialchars($q_func($page-1)).'">&laquo; Anterior</a>';

                                $window = 1;
                                $start = max(1, $page - $window);
                                $end = min($totalPages, $page + $window);
                                if ($end - $start + 1 < 3) {
                                    if ($start === 1) $end = min($totalPages, $start + 2);
                                    elseif ($end === $totalPages) $start = max(1, $end - 2);
                                }

                                if ($start > 1) echo '<a class="page-link" href="'.htmlspecialchars($q_func(1)).'">1</a><span style="margin:0 6px">...</span>';
                                for ($p = $start; $p <= $end; $p++) {
                                    if ($p === $page) echo '<strong style="margin:0 6px;">'.$p.'</strong>';
                                    else echo '<a class="page-link" href="'.htmlspecialchars($q_func($p)).'" style="margin:0 6px;">'.$p.'</a>';
                                }
                                if ($end < $totalPages) echo '<span style="margin:0 6px">...</span><a class="page-link" href="'.htmlspecialchars($q_func($totalPages)).'">'.$totalPages.'</a>';
                                if ($page < $totalPages) echo '<a class="page-link" href="'.htmlspecialchars($q_func($page+1)).'">Siguiente &raquo;</a>';
                                
                                echo '</div>';
                            }
                            ?>
                        <?php endif;
                        
                    } else {
                        echo '<p>No se encontró la carpeta para la fecha: <strong>' . htmlspecialchars($q_date) . '</strong>.</p>';
                    }
                } else {
                    echo '<p>Ingrese una fecha para comenzar la búsqueda de videos.</p>';
                }

            } // Fin LÓGICA 2
            ?>

        </div>
    </div>
    
    <script>
    document.querySelectorAll('.cont-archivos .archivo').forEach(container => {
        let preview = null;
        let previewTimeout = null;

        container.addEventListener('pointerenter', function (e) {
            // No activar si el mouse está sobre los botones
            if (e.target.classList.contains('archivo-download')) return;
            
            if (preview) return;
            const file = this.getAttribute('data-file');
            if (!file) return; 
            
            const src = 'stream.php?file=' + encodeURIComponent(file);

            preview = document.createElement('video');
            preview.preload = 'none';
            preview.muted = true;
            preview.playsInline = true;
            preview.autoplay = true;
            preview.loop = true;
            preview.className = 'preview-video-js'; // Asegúrate de tener esta clase en tu CSS

            const img = this.querySelector('img.thumb');
            if (img) img.style.display = 'none'; 
            this.insertBefore(preview, this.firstChild); 

            preview.src = src;
            preview.load();
            preview.play().catch(()=>{/* ignore */});

            previewTimeout = setTimeout(() => {
                if (preview) {
                    try { preview.pause(); } catch(e) {}
                    try { preview.remove(); } catch(e) {}
                    preview = null;
                }
                if (img) img.style.display = ''; 
            }, 5000); 
        });

        container.addEventListener('pointerleave', function () {
            if (previewTimeout) {
                clearTimeout(previewTimeout);
                previewTimeout = null;
            }
            if (preview) {
                try { preview.pause(); } catch(e) {}
                try { preview.remove(); } catch(e) {}
                preview = null;
            }
            const imgLeave = this.querySelector('img.thumb');
            if (imgLeave) imgLeave.style.display = ''; 
        });
    });
    const menu = document.querySelector('.menu-capturadora');
    let lastScrollTop = 0;

    window.addEventListener('scroll', function() {
        let scrollTop = window.pageYOffset || document.documentElement.scrollTop;

        if (scrollTop > lastScrollTop && scrollTop > 100) { 
            // Si bajamos más de 100px, ocultamos el menú
            menu.classList.add('menu-hidden');
        } else {
            // Si subimos, lo mostramos
            menu.classList.remove('menu-hidden');
        }
        lastScrollTop = Math.max(0, scrollTop); 
    });
    </script>
</body>
</html>