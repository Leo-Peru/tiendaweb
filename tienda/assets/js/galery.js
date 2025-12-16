document.addEventListener('DOMContentLoaded', function () {

    (function () {
        const gallery = document.getElementById('product-gallery');
        if (!gallery) return;
        const viewport = gallery.querySelector('.tgw-viewport');
        const images = Array.from(gallery.querySelectorAll('.tgw-image'));
        const thumbs = Array.from(gallery.querySelectorAll('.tgw-thumb'));
        const btnPrev = gallery.querySelector('.tgw-prev');
        const btnNext = gallery.querySelector('.tgw-next');
        let index = parseInt(gallery.dataset.initial || 0, 10) || 0;
        const total = images.length;

        function show(i) {
            if (!images.length) return;
            if (i < 0) i = total - 1;
            if (i >= total) i = 0;
            index = i;

            // activar imagen
            images.forEach(img => img.classList.remove('active'));
            const img = images[index];
            if (img) img.classList.add('active');

            // thumbnails
            if (thumbs.length) {
                thumbs.forEach(t => {
                    t.classList.remove('active');
                    t.setAttribute('aria-selected', 'false');
                });
                const activeThumb = thumbs[index];
                if (activeThumb) {
                    activeThumb.classList.add('active');
                    activeThumb.setAttribute('aria-selected', 'true');
                    // asegurar visibilidad simple
                    try {
                        activeThumb.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                    } catch (e) { /* noop en navegadores viejos */ }
                }
            }
        }

        // events
        if (btnPrev) btnPrev.addEventListener('click', (e) => { e.preventDefault(); show(index - 1); });
        if (btnNext) btnNext.addEventListener('click', (e) => { e.preventDefault(); show(index + 1); });

        thumbs.forEach(t => {
            t.addEventListener('click', (e) => {
                const idx = parseInt(t.dataset.index, 10);
                if (!isNaN(idx)) show(idx);
            });
        });

        // keyboard support
        if (viewport) {
            viewport.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowLeft') show(index - 1);
                if (e.key === 'ArrowRight') show(index + 1);
                if (e.key === 'Escape') viewport.blur();
            });
        }

        // touch swipe (simple)
        let startX = 0, dx = 0;
        if (viewport) {
            viewport.addEventListener('touchstart', e => {
                startX = e.touches[0].clientX;
            }, { passive: true });
            viewport.addEventListener('touchmove', e => {
                dx = e.touches[0].clientX - startX;
            }, { passive: true });
            viewport.addEventListener('touchend', e => {
                if (Math.abs(dx) > 40) {
                    if (dx > 0) show(index - 1); else show(index + 1);
                }
                startX = 0; dx = 0;
            });
        }

        // init: ensure index in range
        if (index < 0 || index >= total) index = 0;
        show(index);

    })();
});