<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="previewTitle" style="font-size:1rem;">Preview Gambar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body text-center p-3" style="background:#f6f4f1;">
                <img id="previewImg" src="" alt="Preview" style="max-width:100%;max-height:70vh;border-radius:8px;">
            </div>
        </div>
    </div>
</div>
<script>
    function previewImage(src, title) {
        document.getElementById('previewImg').src = src;
        document.getElementById('previewTitle').textContent = title || 'Preview Gambar';
        bootstrap.Modal.getOrCreateInstance(document.getElementById('previewModal')).show();
    }
</script>
