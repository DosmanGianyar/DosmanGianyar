<!-- SIMS Universal Image Lightbox / Modal Viewer -->
<div id="sims-image-lightbox-modal"
     style="display:none; position:fixed; inset:0; z-index:999999; background:rgba(0,0,0,0.85); backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px); align-items:center; justify-content:center; padding:16px; transition: opacity 0.2s ease-in-out;">
    <div style="position:relative; max-width:92vw; max-height:92vh; display:flex; flex-direction:column; align-items:center;">
        <!-- Close Button -->
        <button type="button" id="sims-image-lightbox-close"
                style="position:absolute; top:-44px; right:0; background:rgba(255,255,255,0.25); color:#ffffff; border:none; border-radius:50%; width:38px; height:38px; cursor:pointer; font-size:22px; font-weight:bold; display:flex; align-items:center; justify-content:center; backdrop-filter:blur(4px); transition:all 0.2s;"
                title="Tutup (Esc)">
            ✕
        </button>
        <!-- Large Image -->
        <img id="sims-image-lightbox-img" src="" alt="Preview"
             style="max-width:100%; max-height:82vh; border-radius:12px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.7); object-fit:contain; border: 1px solid rgba(255,255,255,0.15);" />
        <!-- Download / Full View Link -->
        <a id="sims-image-lightbox-download" href="" target="_blank" download
           style="margin-top:14px; color:#ffffff; background:rgba(255,255,255,0.18); padding:8px 20px; border-radius:24px; text-decoration:none; font-size:13px; font-weight:600; backdrop-filter:blur(8px); border:1px solid rgba(255,255,255,0.3); display:inline-flex; align-items:center; gap:8px; transition:background 0.2s;">
            <svg style="width:16px; height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
            </svg>
            <span>Buka Gambar Ukuran Penuh</span>
        </a>
    </div>
</div>

<script>
(function() {
    if (window.simsLightboxInitialized) return;
    window.simsLightboxInitialized = true;

    function initLightbox() {
        const modal = document.getElementById('sims-image-lightbox-modal');
        const img = document.getElementById('sims-image-lightbox-img');
        const closeBtn = document.getElementById('sims-image-lightbox-close');
        const downloadLink = document.getElementById('sims-image-lightbox-download');

        if (!modal || !img || !closeBtn || !downloadLink) return;

        window.openSimsLightbox = function(src) {
            if (!src || src.startsWith('data:image/svg')) return;
            img.src = src;
            downloadLink.href = src;
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        };

        function closeLightbox() {
            modal.style.display = 'none';
            img.src = '';
            document.body.style.overflow = '';
        }

        closeBtn.addEventListener('click', closeLightbox);

        modal.addEventListener('click', function(e) {
            if (e.target === modal || e.target.parentElement === modal) {
                closeLightbox();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.style.display === 'flex') {
                closeLightbox();
            }
        });

        // Delegate click for any <img> in document
        document.addEventListener('click', function(e) {
            const target = e.target;
            if (target && target.tagName === 'IMG' && target.id !== 'sims-image-lightbox-img') {
                const src = target.currentSrc || target.src || target.getAttribute('src');
                if (src && !src.includes('data:image/svg') && !src.includes('favicon') && target.width > 20 && target.height > 20) {
                    target.style.cursor = 'pointer';
                    openSimsLightbox(src);
                }
            }
        });

        // Add cursor pointer & hover glow to all images automatically
        const style = document.createElement('style');
        style.innerHTML = `
            img:not(#sims-image-lightbox-img):hover {
                cursor: pointer !important;
                filter: brightness(0.95);
                transition: filter 0.15s ease-in-out;
            }
        `;
        document.head.appendChild(style);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLightbox);
    } else {
        initLightbox();
    }
})();
</script>
