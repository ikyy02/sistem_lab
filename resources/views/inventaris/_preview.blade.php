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
<style>
    .thumb { width:56px; height:56px; object-fit:cover; border-radius:8px; border:1px solid var(--border-color); cursor:zoom-in; background:#fff; transition:transform .15s, box-shadow .15s; }
    .thumb:hover { transform:scale(1.06); box-shadow:0 4px 12px rgba(16,24,40,.18); }
    .thumb-empty { width:56px; height:56px; border-radius:8px; border:1px dashed #d5cbc4; display:inline-flex; align-items:center; justify-content:center; color:#b9aca3; background:#faf8f6; }
</style>
