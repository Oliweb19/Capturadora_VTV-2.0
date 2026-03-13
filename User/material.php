<?php
    session_start();
	$conexion = mysqli_connect('localhost', 'root', 'S3rvic10s.vtv', 'capturadora');
											
	if (isset($_SESSION['usuario'])) {
		// El usuario está logueado
	}
	else{ 
		header('Location:../index.php');
        exit;
    }

?> 
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Capturadora VTV</title>
    <link rel="stylesheet" href="../css/style.css"> 
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <link rel="shourt icon" href="../img/favicon.ico">
    <style>
        /* Hacemos que el cursor indique que es clickeable */
        .archivo {
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        /* Ajuste para que el botón de descarga ocupe todo el ancho si está solo */
        .archivo-botones {
            display: flex;
            width: 100%;
            margin-top: 10px;
        }
        .btn-descargar {
            flex: 1;
            text-align: center;
            justify-content: center;
            z-index: 10;
        }
        /* Estilos para el formulario de búsqueda por rango */
        .search-form {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            padding: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .search-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .search-group label {
            font-weight: 500;
            color: #fff;
            font-size: 0.9em;
        }
        .search-form input[type="time"],
        .search-form input[type="date"] {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
        }
        .search-form button {
            padding: 8px 16px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .search-form button:hover {
            background-color: #0056b3;
        }
        .search-form a {
            color: #fff;
            text-decoration: none;
            font-size: 0.9em;
        }
        .search-form a:hover {
            text-decoration: underline;
        }
    </style>
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

        <div class="cont-diario">
            <h1>Material</h1>

            <div id="player-view" class="player-container" style="display:none;">
                <div class="video-main">
                    <button id="player-close-btn"><span class="material-symbols-outlined">arrow_back</span> Volver a la cuadrícula</button>
                    <video id="main-video-player" src="" controls autoplay width="100%"></video>
                    
                    <div class="player-controls-bar">
                        <h2 id="player-title"></h2>
                        <a id="player-clip-btn" href="#" class="btn-clip">
                            <span class="material-symbols-outlined">content_cut</span> Recortar
                        </a>
                    </div>

                </div>
                <div class="video-sidebar">
                    <h3>Siguientes:</h3>
                    <div id="sidebar-video-list">
                        </div>
                </div>
            </div>

            <div id="grid-view">
                <?php
                date_default_timezone_set('America/Caracas');
                $base_dir = __DIR__ . '/../Videos/capturadora/clips/';
                $base_dir = rtrim(realpath($base_dir) ?: $base_dir, '/') . '/';

                // --- Parámetros de búsqueda ---
                $search_date = isset($_GET['d']) && !empty(trim($_GET['d'])) ? trim($_GET['d']) : date('Y-m-d');
                $search_start = isset($_GET['start']) ? trim($_GET['start']) : '';
                $search_end = isset($_GET['end']) ? trim($_GET['end']) : '';

                $valid_pp = [8, 16, 24, 32, 40];
                $perPage = isset($_GET['pp']) && in_array((int)$_GET['pp'], $valid_pp) ? (int)$_GET['pp'] : 8;

                // --- Funciones auxiliares ---
                function render_pagination($baseUrl, $page, $totalPages, $extraParams = []) {
                    if ($totalPages <= 1) return;
                    $q = function($p) use ($baseUrl, $extraParams) {
                        $params = array_merge($extraParams, ['page' => $p]);
                        return $baseUrl . '?' . http_build_query($params);
                    };
                    echo '<div class="paginador" style="margin-top:20px;">';
                    if ($page > 1) {
                        echo '<a class="page-link" href="'.htmlspecialchars($q($page-1)).'">&laquo; Anterior</a>';
                    }
                    $window = 1;
                    $start = max(1, $page - $window);
                    $end = min($totalPages, $page + $window);
                    
                    if ($start > 1) {
                        echo '<a class="page-link" href="'.htmlspecialchars($q(1)).'">1</a><span style="margin:0 6px">...</span>';
                    }
                    for ($p = $start; $p <= $end; $p++) {
                        if ($p === $page) {
                            echo '<strong style="margin:0 6px;">'.$p.'</strong>';
                        } else {
                            echo '<a class="page-link" href="'.htmlspecialchars($q($p)).'" style="margin:0 6px;">'.$p.'</a>';
                        }
                    }
                    if ($end < $totalPages) {
                        echo '<span style="margin:0 6px">...</span><a class="page-link" href="'.htmlspecialchars($q($totalPages)).'">'.$totalPages.'</a>';
                    }
                    if ($page < $totalPages) {
                        echo '<a class="page-link" href="'.htmlspecialchars($q($page+1)).'">Siguiente &raquo;</a>';
                    }
                    echo '</div>';
                }
                
                function safe_name($path) {
                    return htmlspecialchars(basename($path));
                }

                function formatFilenameToTime(string $filename): string {
                    $nombre_base = pathinfo($filename, PATHINFO_FILENAME);
                    $pos = strpos($nombre_base, '_');
                    
                    if ($pos !== false) {
                        $hora_part = substr($nombre_base, $pos + 1); 
                    } else {
                        $hora_part = $nombre_base; 
                    }

                    $hora_militar = str_replace('-', ':', $hora_part);
                    $timestamp = strtotime($hora_militar);
                    
                    if ($timestamp !== false) {
                        return date('h:i:s A', $timestamp); 
                    }
                    return $hora_militar; 
                }

                function getRawTimeFromFilename(string $filename): string {
                    $nombre_base = pathinfo($filename, PATHINFO_FILENAME);
                    $pos = strpos($nombre_base, '_');
                    if ($pos !== false) {
                        $hora_part = substr($nombre_base, $pos + 1); 
                        return str_replace('-', ':', $hora_part);
                    }
                    return "00:00:00";
                }
                ?>

                <form action="material.php" method="GET" class="search-form">
                    <div class="search-group">
                        <label for="search-date">Fecha:</label>
                        <input type="date" id="search-date" name="d" value="<?php echo htmlspecialchars($search_date); ?>" required>
                    </div>
                    
                    <div class="search-group">
                        <label for="search-start">Desde:</label>
                        <input type="time" id="search-start" name="start" value="<?php echo htmlspecialchars($search_start); ?>">
                    </div>

                    <div class="search-group">
                        <label for="search-end">Hasta:</label>
                        <input type="time" id="search-end" name="end" value="<?php echo htmlspecialchars($search_end); ?>">
                    </div>

                    <button type="submit">Buscar</button>
                    <a href="material.php">Limpiar</a>
                    
                    <label for="per-page" style="margin-left: auto;">Mostrar:</label>
                    <select name="pp" id="per-page" onchange="this.form.submit()">
                        <?php
                        foreach ($valid_pp as $opt) {
                            $selected_attr = ($opt == $perPage) ? ' selected' : '';
                            echo "<option value=\"$opt\"$selected_attr>$opt</option>";
                        }
                        ?>
                    </select>
                </form>

                <?php
                // --- Carga de Videos ---
                $all_videos = [];
                $videos = [];
                $pageVideos = [];
                $total = 0;
                $totalPages = 1;
                $page = 1;

                $date_dir = $base_dir . $search_date . '/';
                if (is_dir($date_dir)) {
                    $all_videos = array_merge(glob($date_dir . '*.mp4') ?: [], glob($date_dir . '*.webm') ?: []);
                } else {
                    $all_videos = glob($base_dir . $search_date . '*.{mp4,webm}', GLOB_BRACE) ?: [];
                }
                
                // --- ORDENAMIENTO DE VIDEOS ---
                if (!empty($all_videos)) {
                    // Variable para saber si estamos en modo búsqueda
                    $is_search_active = (!empty($search_start) || !empty($search_end));

                    usort($all_videos, function($a, $b) use ($is_search_active) {
                        if ($is_search_active) {
                            // MODO BÚSQUEDA: Ascendente (De la hora INICIO -> hacia delante)
                            // return filemtime($a) - filemtime($b); // Por fecha modificación
                            
                            // MEJOR: Por nombre (hora) para ser más precisos cronológicamente si la fecha mod fallara
                            return strcmp(basename($a), basename($b));
                        } else {
                            // MODO NORMAL: Descendente (Lo más reciente primero)
                            return filemtime($b) - filemtime($a);
                        }
                    });
                }

                $recording_file_path = null;
                // Detectar grabación solo si estamos viendo lo más reciente (sin filtros o si el filtro incluye ahora)
                if (!empty($all_videos)) {
                    // Si ordenamos ascendente (búsqueda), el más reciente está al final
                    $is_search_active = (!empty($search_start) || !empty($search_end));
                    $newest_file_path = $is_search_active ? end($all_videos) : $all_videos[0];
                    
                    if (time() - filemtime($newest_file_path) < 120) {
                        $recording_file_path = $newest_file_path;
                    }
                }

                // --- LÓGICA DE FILTRADO EXACTA ---
                $videos = $all_videos;

                if (!empty($search_start) || !empty($search_end)) {
                    $filtered_videos = [];
                    
                    foreach ($all_videos as $video) {
                        $filename = basename($video);
                        $fileTime = getRawTimeFromFilename($filename); // Ej: "14:30:15"
                        
                        $include = true;

                        // 1. Filtro DESDE (Inclusivo: >= HH:MM:00)
                        if (!empty($search_start)) {
                            if ($fileTime < $search_start . ":00") {
                                $include = false;
                            }
                        }

                        // 2. Filtro HASTA (Inclusivo: <= HH:MM:59)
                        if ($include && !empty($search_end)) {
                            if ($fileTime > $search_end . ":59") {
                                $include = false;
                            }
                        }

                        if ($include) {
                            $filtered_videos[] = $video;
                        }
                    }
                    $videos = $filtered_videos;
                }

                $total = count($videos);
                $totalPages = $total > 0 ? (int)ceil($total / $perPage) : 1;
                $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
                if ($page > $totalPages) $page = $totalPages;
                $offset = ($page - 1) * $perPage;
                $pageVideos = array_slice($videos, $offset, $perPage);
                ?>


                <div class="cont-archivos">
                    <?php 
                    if (empty($videos) && (!empty($search_start) || !empty($search_end))): 
                        $msg = "No se encontraron videos";
                        if (!empty($search_start)) $msg .= " desde las " . date('h:i A', strtotime($search_start));
                        if (!empty($search_end)) $msg .= " hasta las " . date('h:i A', strtotime($search_end));
                        $msg .= " en la fecha ($search_date).";
                    ?>
                        <p style="color: var(--bg); grid-column: 1 / -1; padding: 20px;"><?php echo $msg; ?></p>
                        
                    <?php elseif (empty($videos)): ?>
                        <p style="color: var(--bg); grid-column: 1 / -1; padding: 20px;">No se encontraron videos para la fecha (<?php echo $search_date; ?>).</p>
                    <?php else: ?>

                        <?php foreach ($pageVideos as $video): ?>
                            <?php
                                $filename = basename($video);
                                $originalName = safe_name($video);
                                $displayName = formatFilenameToTime($filename); 
                                
                                // Tarjeta de GRABANDO...
                                if ($recording_file_path !== null && $video === $recording_file_path): 
                            ?>
                                <div class="archivo" data-file="" data-preview="">
                                    <div class="archivo-grabando">
                                        <span class="material-symbols-outlined">fiber_manual_record</span>
                                        <span>Grabando...</span>
                                    </div>
                                    <span class="nombre-archivo"><?php echo $displayName; ?></span>
                                </div>
                            
                            <?php else: ?>
                                <?php
                                if (strpos($video, $base_dir) === 0) {
                                    $relativeVideoPath = ltrim(substr($video, strlen($base_dir)), '/');
                                } else {
                                    $relativeVideoPath = basename($video);
                                }
                                $web_src = 'stream.php?file=' . rawurlencode($relativeVideoPath);

                                $thumbNameNoExt = pathinfo($filename, PATHINFO_FILENAME);
                                $try1 = dirname($video) . '/' . $thumbNameNoExt . '.png';
                                $capturadoraParent = dirname(rtrim($base_dir, '/')) . '/';
                                $try2 = $capturadoraParent . 'miniaturas/' . $thumbNameNoExt . '.png';
                                $try3 = $capturadoraParent . 'miniaturas/' . $search_date . '/' . $thumbNameNoExt . '.png';

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

                                $dataFile = htmlspecialchars($relativeVideoPath);
                                $nameForTitle = $displayName; 

                                $previewFs1 = dirname($video) . '/' . $thumbNameNoExt . '.webm';
                                $previewFs2 = dirname(dirname($base_dir)) . '/miniaturas/' . $thumbNameNoExt . '.webm';
                                $previewUrl = $web_src;
                                if (file_exists($previewFs1)) {
                                    $relativePreview = ltrim(str_replace(dirname(dirname($base_dir)) . '/', '', $previewFs1), '/');
                                    $previewUrl = '/Videos/capturadora/' . str_replace('%2F','/', rawurlencode($relativePreview));
                                } elseif (file_exists($previewFs2)) {
                                    $relativePreview = ltrim(str_replace(dirname(dirname($base_dir)) . '/', '', $previewFs2), '/');
                                    $previewUrl = '/Videos/capturadora/' . str_replace('%2F','/', rawurlencode($relativePreview));
                                }
                                ?>

                                <div class="archivo" 
                                     data-file="<?php echo $dataFile; ?>" 
                                     data-preview="<?php echo htmlspecialchars($previewUrl); ?>"
                                     data-url="<?php echo htmlspecialchars($web_src); ?>"
                                     data-title="<?php echo $nameForTitle; ?>">
                                    
                                    <img class="thumb" src="<?php echo htmlspecialchars($thumbUrl); ?>" loading="lazy" alt="<?php echo $nameForTitle; ?>" onerror="this.src='../img/video-placeholder.svg'">
                                    <span class="nombre-archivo"><?php echo $nameForTitle; ?></span>
                                    
                                    <div class="archivo-botones">
                                        <a href="<?php echo htmlspecialchars($web_src); ?>" 
                                           download="<?php echo $originalName; ?>" 
                                           class="archivo-btn btn-descargar">Descargar</a>
                                    </div>
                                </div>

                            <?php 
                            endif; 
                        endforeach; 
                    endif; 
                    ?>
                </div>
                
                <?php 
                    // Renderizar paginación con persistencia de parámetros
                    if ($total > $perPage) {
                        $extra_params = ['d' => $search_date];
                        if (!empty($search_start)) $extra_params['start'] = $search_start;
                        if (!empty($search_end)) $extra_params['end'] = $search_end;
                        if ($perPage != 8) $extra_params['pp'] = $perPage;
                        render_pagination('material.php', $page, $totalPages, $extra_params);
                    }
                ?>
            </div> </div>
    </div>

    <script>
    document.querySelectorAll('.archivo').forEach(container => {
        let preview = null;
        let previewTimeout = null;

        container.addEventListener('pointerenter', function (e) {
            if (e.target.closest('.archivo-botones')) return;
            if (preview) return;
            const file = this.getAttribute('data-file');
            if (!file) return; 
            
            const previewAttr = this.getAttribute('data-preview');
            const src = previewAttr ? previewAttr : ('stream.php?file=' + encodeURIComponent(file) + '&preview=1');
            
            preview = document.createElement('video');
            preview.preload = 'none';
            preview.muted = true;
            preview.playsInline = true;
            preview.autoplay = true;
            preview.loop = true;
            preview.className = 'preview-video-js';

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

    document.addEventListener('DOMContentLoaded', function() {
        const gridView = document.getElementById('grid-view');
        const playerView = document.getElementById('player-view');
        const mainPlayer = document.getElementById('main-video-player');
        const playerTitle = document.getElementById('player-title');
        const sidebarList = document.getElementById('sidebar-video-list');
        const closeBtn = document.getElementById('player-close-btn');
        const clipBtn = document.getElementById('player-clip-btn');

        closeBtn.addEventListener('click', function() {
            gridView.style.display = 'block';
            playerView.style.display = 'none';
            mainPlayer.pause();
            mainPlayer.src = '';
            clipBtn.style.display = 'none';
        });

        function loadVideo(url, title, dataFile) {
            mainPlayer.src = url;
            playerTitle.textContent = title;
            mainPlayer.play().catch(e => console.error("Error al reproducir:", e));
            buildSidebarList(dataFile); 

            if (dataFile) {
                clipBtn.href = 'Clip.php?video_path=' + encodeURIComponent(dataFile);
                clipBtn.style.display = 'inline-block'; 
            } else {
                clipBtn.href = '#';
                clipBtn.style.display = 'none'; 
            }
        }

        function buildSidebarList(currentDataFile) {
            sidebarList.innerHTML = ''; 
            
            document.querySelectorAll('#grid-view .archivo').forEach(item => {
                const dataFile = item.getAttribute('data-file');
                if (!dataFile) return; 

                const thumbSrc = item.querySelector('img.thumb')?.src || '../img/video-placeholder.svg';
                const name = item.getAttribute('data-title'); 
                const url = item.getAttribute('data-url');

                const sidebarItem = document.createElement('div');
                sidebarItem.className = 'sidebar-item';
                if (dataFile === currentDataFile) {
                    sidebarItem.classList.add('playing');
                }
                sidebarItem.innerHTML = `
                    <img src="${thumbSrc}" alt="">
                    <span>${name}</span>
                `;
                
                sidebarItem.addEventListener('click', function() {
                    loadVideo(url, name, dataFile);
                });

                sidebarList.appendChild(sidebarItem);
            });
        }

        document.querySelectorAll('.archivo').forEach(card => {
            card.addEventListener('click', function(e) {
                if (e.target.closest('.btn-descargar')) {
                    return;
                }
                const url = this.getAttribute('data-url');
                const title = this.getAttribute('data-title');
                const dataFile = this.getAttribute('data-file');
                if (url) {
                    gridView.style.display = 'none';
                    playerView.style.display = 'block';
                    window.scrollTo(0, 0); 
                    loadVideo(url, title, dataFile);
                }
            });
        });

        document.querySelectorAll('.btn-descargar').forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation(); 
            });
        });
    });
    
    const menu = document.querySelector('.menu-capturadora');
    let lastScrollTop = 0; 

    window.addEventListener('scroll', function() {
        let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        if (scrollTop > lastScrollTop && scrollTop > 100) { 
            menu.classList.add('menu-hidden');
        } else {
            menu.classList.remove('menu-hidden');
        }
        lastScrollTop = Math.max(0, scrollTop); 
    });
    </script>
    </div>
</body>
</html>