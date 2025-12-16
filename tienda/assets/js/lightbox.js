document.addEventListener('DOMContentLoaded', function () {
    (function () {
        // selector para imágenes de la galería (adapta si usas otra clase)
        const gallery = document.getElementById('product-gallery');
        if (!gallery) return;

        const modal = document.getElementById('tgw-zoom-modal');
        const modalImg = document.getElementById('tgw-zoom-image');
        const stage = modal.querySelector('.tgw-zoom-stage');
        const toolbar = modal.querySelector('.tgw-zoom-toolbar');

        // imágenes desde la galería
        const imgs = Array.from(gallery.querySelectorAll('.tgw-image'));
        // si no hay imgs usar thumbnails
        if (!imgs.length) return;

        // estado
        let scale = 1;
        let minScale = 1;
        let maxScale = 4;
        let startX = 0, startY = 0, originX = 0, originY = 0;
        let dragging = false;

        // abrir modal con la imagen index i
        function open(i) {
            const src = imgs[i].getAttribute('src');
            modalImg.src = src;
            modalImg.alt = imgs[i].alt || '';
            scale = 1;
            originX = 0; originY = 0;
            applyTransform();
            modal.classList.add('open');
            modal.setAttribute('aria-hidden', 'false');
            stage.focus();
        }

        function close() {
            modal.classList.remove('open');
            modal.setAttribute('aria-hidden', 'true');
            modalImg.src = '';
        }

        function applyTransform() {
            modalImg.style.transform = `translate(${originX}px, ${originY}px) scale(${scale})`;
        }

        function zoomBy(delta) {
            const prev = scale;
            scale = Math.min(maxScale, Math.max(minScale, scale + delta));
            // ajustar origin para zoom hacia el centro del viewport
            applyTransform();
        }

        // handlers toolbar
        toolbar.addEventListener('click', (e) => {
            const act = e.target.closest('button[data-action]');
            if (!act) return;
            const action = act.getAttribute('data-action');
            if (action === 'close') return close();
            if (action === 'zoom-in') { zoomBy(0.25); }
            if (action === 'zoom-out') { zoomBy(-0.25); }
            if (action === 'reset') { scale = 1; originX = 0; originY = 0; applyTransform(); }
            if (action === 'download') {
                const url = modalImg.src;
                const a = document.createElement('a');
                a.href = url;
                a.download = url.split('/').pop() || 'image';
                document.body.appendChild(a);
                a.click();
                a.remove();
            }
        });

        // abrir modal al click en imagen principal o thumbnails
        imgs.forEach(img => {
            img.style.cursor = 'zoom-in';
            img.addEventListener('click', () => {
                const idx = parseInt(img.dataset.index || 0, 10);
                open(idx);
            });
        });

        // cerrar al click fuera de la imagen
        modal.addEventListener('click', (e) => {
            if (e.target === modal || e.target === stage) close();
        });

        // teclado: +, -, esc, arrow keys to pan when zoomed
        document.addEventListener('keydown', (e) => {
            if (modal.classList.contains('open')) {
                if (e.key === 'Escape') return close();
                if (e.key === '+' || e.key === '=') { zoomBy(0.25); e.preventDefault(); }
                if (e.key === '-') { zoomBy(-0.25); e.preventDefault(); }
                if (e.key === 'ArrowLeft' && scale > 1) { originX += 40; applyTransform(); }
                if (e.key === 'ArrowRight' && scale > 1) { originX -= 40; applyTransform(); }
                if (e.key === 'ArrowUp' && scale > 1) { originY += 40; applyTransform(); }
                if (e.key === 'ArrowDown' && scale > 1) { originY -= 40; applyTransform(); }
            }
        });

        // rueda del ratón sobre la stage = zoom
        stage.addEventListener('wheel', (e) => {
            if (!modal.classList.contains('open')) return;
            e.preventDefault();
            const delta = Math.sign(e.deltaY) * -0.15; // wheel up -> zoom in
            zoomBy(delta);
        }, { passive: false });

        // drag para pan
        modalImg.addEventListener('pointerdown', (e) => {
            if (scale <= 1) return;
            dragging = true;
            modalImg.classList.add('dragging');
            modalImg.setPointerCapture(e.pointerId);
            startX = e.clientX;
            startY = e.clientY;
        });

        modalImg.addEventListener('pointermove', (e) => {
            if (!dragging) return;
            const dx = e.clientX - startX;
            const dy = e.clientY - startY;
            startX = e.clientX;
            startY = e.clientY;
            originX += dx;
            originY += dy;
            applyTransform();
        });

        modalImg.addEventListener('pointerup', (e) => {
            dragging = false;
            modalImg.classList.remove('dragging');
            try { modalImg.releasePointerCapture(e.pointerId); } catch (err) { }
        });
        modalImg.addEventListener('pointercancel', () => { dragging = false; modalImg.classList.remove('dragging'); });

    })();
});