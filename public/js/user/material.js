/**
 * material.js – Panel de usuario: preview hover, player inline y scroll del navbar.
 */
document.addEventListener('DOMContentLoaded', () => {

    // ─── Referencias del Player ──────────────────────────────────────────────
    const gridView    = document.getElementById('grid-view');
    const playerView  = document.getElementById('player-view');
    const mainPlayer  = document.getElementById('main-video-player');
    const playerTitle = document.getElementById('player-title');
    const sidebarList = document.getElementById('sidebar-video-list');
    const closeBtn    = document.getElementById('player-close-btn');
    const clipBtn     = document.getElementById('player-clip-btn');

    // ─── Cerrar player ───────────────────────────────────────────────────────
    closeBtn.addEventListener('click', () => {
        gridView.style.display   = '';
        playerView.style.display = 'none';
        mainPlayer.pause();
        mainPlayer.src = '';
        if (clipBtn) clipBtn.style.display = 'none';
    });

    // ─── Cargar video en el player ───────────────────────────────────────────
    function loadVideo(url, title, dataFile) {
        mainPlayer.src = url;
        playerTitle.textContent = title;
        mainPlayer.play().catch(() => {});
        buildSidebarList(dataFile);

        if (clipBtn) {
            if (dataFile) {
                clipBtn.href = (typeof CLIP_BASE !== 'undefined' ? CLIP_BASE : '#')
                    + '?video_path=' + encodeURIComponent(dataFile);
                clipBtn.style.display = 'inline-flex';
            } else {
                clipBtn.href = '#';
                clipBtn.style.display = 'none';
            }
        }
    }

    // ─── Construir sidebar de videos relacionados ────────────────────────────
    function buildSidebarList(current) {
        sidebarList.innerHTML = '';
        document.querySelectorAll('#grid-view .video-card').forEach(card => {
            const file = card.dataset.file;
            if (!file) return;

            const thumbEl = card.querySelector('img.card-thumb');
            const thumb   = thumbEl ? thumbEl.src : '';
            const name    = card.dataset.title || '';
            const url     = card.dataset.url   || '';

            const item = document.createElement('div');
            item.className = 'sidebar-item' + (file === current ? ' playing' : '');
            item.innerHTML = `<img src="${thumb}" alt=""><span>${name}</span>`;
            item.addEventListener('click', () => loadVideo(url, name, file));
            sidebarList.appendChild(item);
        });
    }

    // ─── Abrir player al hacer clic en la tarjeta ────────────────────────────
    document.querySelectorAll('.video-card').forEach(card => {
        card.addEventListener('click', function (e) {
            if (e.target.closest('.btn-download, .card-actions')) return;
            const url   = this.dataset.url;
            const title = this.dataset.title;
            const file  = this.dataset.file;
            if (!url) return;

            gridView.style.display   = 'none';
            playerView.style.display = 'flex';
            window.scrollTo(0, 0);
            loadVideo(url, title, file);
        });
    });

    // Evitar que el botón de descarga dispare el player
    document.querySelectorAll('.btn-download').forEach(btn => {
        btn.addEventListener('click', e => e.stopPropagation());
    });

    // ─── Preview en hover ────────────────────────────────────────────────────
    document.querySelectorAll('.video-card').forEach(card => {
        let preview = null;
        let timeout = null;

        card.addEventListener('pointerenter', function (e) {
            if (e.target.closest('.card-actions')) return;
            if (preview) return;
            const file = this.dataset.file;
            if (!file) return;

            const src = (typeof STREAM_BASE !== 'undefined' ? STREAM_BASE : '/stream.php')
                + '?file=' + encodeURIComponent(file);

            preview = document.createElement('video');
            preview.muted     = true;
            preview.playsInline = true;
            preview.autoplay  = true;
            preview.loop      = true;
            preview.className = 'card-thumb preview-video-js';

            const img = this.querySelector('img.card-thumb');
            if (img) img.style.display = 'none';
            this.insertBefore(preview, this.firstChild);

            preview.src = src;
            preview.load();
            preview.play().catch(() => {});

            timeout = setTimeout(() => {
                if (preview) { preview.pause(); preview.remove(); preview = null; }
                if (img) img.style.display = '';
            }, 5000);
        });

        card.addEventListener('pointerleave', function () {
            if (timeout) { clearTimeout(timeout); timeout = null; }
            if (preview) { preview.pause(); preview.remove(); preview = null; }
            const img = this.querySelector('img.card-thumb');
            if (img) img.style.display = '';
        });
    });

});

// ─── Ocultar navbar al bajar scroll ─────────────────────────────────────────
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
