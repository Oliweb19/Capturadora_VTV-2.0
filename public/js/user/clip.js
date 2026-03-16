/**
 * clip.js – Editor de clip: slider doble de rango y fetch a download_clip.php
 * Variables globales inyectadas por la vista:
 *   DOWNLOAD_CLIP_URL  – URL del endpoint
 *   BASE_FILENAME      – nombre base del archivo (sin ext)
 */
document.addEventListener('DOMContentLoaded', () => {
    const video        = document.getElementById('clip-video-player');
    const startSlider  = document.getElementById('start-slider');
    const endSlider    = document.getElementById('end-slider');
    const startLabel   = document.getElementById('start-label')?.querySelector('strong');
    const endLabel     = document.getElementById('end-label')?.querySelector('strong');
    const durLabel     = document.getElementById('duration-label')?.querySelector('strong');
    const startInput   = document.getElementById('start_time');
    const endInput     = document.getElementById('end_time');
    const selTrack     = document.querySelector('.range-track-selection');
    const clipForm     = document.getElementById('clip-form');
    const submitBtn    = document.getElementById('submit-clip-btn');

    // Modal de resultado
    const modalOverlay = document.getElementById('clip-modal');
    const modalVideo   = document.getElementById('modal-video-player');
    const modalDl      = document.getElementById('modal-download-btn');
    const modalClose   = document.getElementById('modal-close-btn');

    let maxDuration = 100;

    // ─── Helpers ─────────────────────────────────────────────────────────────
    function fmt(sec) {
        const m = Math.floor(sec / 60);
        const s = Math.floor(sec % 60);
        return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
    }

    function updateVisuals() {
        const start = parseFloat(startSlider.value);
        const end   = parseFloat(endSlider.value);
        const dur   = end - start;

        if (startLabel) startLabel.textContent = fmt(start);
        if (endLabel)   endLabel.textContent   = fmt(end);
        if (durLabel)   durLabel.textContent   = `${dur.toFixed(1)}s`;

        if (startInput) startInput.value = fmt(start);
        if (endInput)   endInput.value   = fmt(end);

        if (selTrack) {
            const sp = (start / maxDuration) * 100;
            const ep = (end   / maxDuration) * 100;
            selTrack.style.left  = `${sp}%`;
            selTrack.style.width = `${ep - sp}%`;
        }
    }

    // ─── Inicializar sliders cuando el video carga ────────────────────────────
    if (video) {
        video.addEventListener('durationchange', () => {
            const d = video.duration;
            if (d && isFinite(d) && d !== maxDuration) {
                maxDuration = d;
                startSlider.max = d;
                endSlider.max   = d;
                startSlider.value = 0;
                endSlider.value   = d;
                updateVisuals();
            }
        });

        video.addEventListener('timeupdate', () => {
            if (isFinite(video.duration) && video.duration > maxDuration) {
                maxDuration = video.duration;
                startSlider.max = maxDuration;
                endSlider.max   = maxDuration;
                if (parseFloat(endSlider.value) < maxDuration) {
                    endSlider.value = maxDuration;
                }
                updateVisuals();
            }
            const end = parseFloat(endSlider.value);
            if (video.currentTime > end) {
                video.pause();
                video.currentTime = parseFloat(startSlider.value);
            }
        });
    }

    // ─── Eventos de los sliders ───────────────────────────────────────────────
    if (startSlider) {
        startSlider.addEventListener('input', () => {
            let s = parseFloat(startSlider.value);
            const e = parseFloat(endSlider.value);
            if (s >= e) { startSlider.value = e - 0.1; s = parseFloat(startSlider.value); }
            if (video) video.currentTime = s;
            updateVisuals();
        });
    }

    if (endSlider) {
        endSlider.addEventListener('input', () => {
            const s = parseFloat(startSlider.value);
            let e   = parseFloat(endSlider.value);
            if (e <= s) { endSlider.value = s + 0.1; e = parseFloat(endSlider.value); }
            if (video) video.currentTime = e;
            updateVisuals();
        });
    }

    // ─── Submit: generar clip via fetch ──────────────────────────────────────
    if (submitBtn && clipForm) {
        submitBtn.addEventListener('click', async () => {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="material-symbols-outlined">hourglass_top</span> Generando...';

            const formData = new FormData(clipForm);

            try {
                const res = await fetch(
                    typeof DOWNLOAD_CLIP_URL !== 'undefined' ? DOWNLOAD_CLIP_URL : 'download_clip.php',
                    { method: 'POST', body: formData }
                );

                if (!res.ok) throw new Error(`Error del servidor: ${res.statusText}`);

                const data = await res.json();

                if (data.success) {
                    if (modalVideo) {
                        modalVideo.src = data.url;
                    }
                    if (modalDl) {
                        modalDl.href = data.url;
                        modalDl.setAttribute('download', data.filename);
                    }
                    if (modalOverlay) modalOverlay.style.display = 'flex';
                } else {
                    throw new Error(data.error || 'Error desconocido.');
                }
            } catch (err) {
                console.error('Error al generar el clip:', err);
                alert('Error al generar el clip: ' + err.message);
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span class="material-symbols-outlined">preview</span> Cortar Clip';
            }
        });
    }

    // ─── Cerrar modal del clip ────────────────────────────────────────────────
    if (modalClose) {
        modalClose.addEventListener('click', () => {
            if (modalOverlay) modalOverlay.style.display = 'none';
            if (modalVideo)  { modalVideo.pause(); modalVideo.src = ''; }
        });
    }

    if (modalOverlay) {
        modalOverlay.addEventListener('click', e => {
            if (e.target === modalOverlay) modalClose && modalClose.click();
        });
    }

    // Inicializar etiquetas
    updateVisuals();

});

// ─── Navbar scroll hide ─────────────────────────────────────────────────────
(function () {
    const navbar = document.getElementById('navbar-user');
    if (!navbar) return;
    let lastY = 0;
    window.addEventListener('scroll', () => {
        const y = window.pageYOffset;
        navbar.classList.toggle('menu-hidden', y > lastY && y > 100);
        lastY = Math.max(0, y);
    });
})();
