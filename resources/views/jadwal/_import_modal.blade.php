<div class="modal fade" id="jadwalImportModal" aria-labelledby="jadwalImportModal-title" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('jadwal.import') }}" method="POST" enctype="multipart/form-data" id="jadwalImportForm" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 id="jadwalImportModal-title" class="modal-title fw-bold">Import Jadwal Perkuliahan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-4">
                <ol class="ps-3 mb-3" style="font-size:.9rem;line-height:1.8;">
                    <li><a href="{{ route('jadwal.template') }}" class="fw-semibold">Unduh template Excel</a> yang berisi pilihan data master prodi.</li>
                    <li>Isi sheet <strong>Data</strong> mulai baris ke-2. Jangan ubah judul kolom.</li>
                    <li>Unggah file .xlsx atau .xls, maksimal 2 MB dan 500 jadwal.</li>
                </ol>
                <p class="form-text">Jika ada baris tidak valid atau bentrok ruangan/waktu, seluruh impor dibatalkan. Jadwal yang berhasil masuk ke periode berjalan.</p>
                <label class="form-label" for="jadwalImportFile">File Excel <span class="text-danger">*</span></label>
                <input type="file" id="jadwalImportFile" name="file" accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel" class="form-control @error('file') is-invalid @enderror">
                @error('file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-silab" data-bs-dismiss="modal">Batal</button>
                <button type="submit" id="jadwalImportSubmit" class="btn btn-primary">Import Jadwal</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if(session('open_jadwal_import') || $errors->has('file'))
            bootstrap.Modal.getOrCreateInstance(document.getElementById('jadwalImportModal')).show();
        @endif
        const form = document.getElementById('jadwalImportForm');
        const button = document.getElementById('jadwalImportSubmit');
        const label = button.innerHTML;
        form.addEventListener('submit', function () { button.disabled = true; button.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Memproses...'; });
        window.addEventListener('pageshow', function (event) { if (event.persisted) { button.disabled = false; button.innerHTML = label; } });
    });
</script>
@endpush
