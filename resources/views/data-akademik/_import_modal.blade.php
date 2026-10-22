@php
    $kolomHint = implode(', ', array_keys(\App\Services\AkademikImportService::columns($tab)));
@endphp

<!-- Modal Import -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('data-akademik.import', ['tab' => $tab]) }}" method="POST" enctype="multipart/form-data" id="importForm" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold" style="font-size: 1rem;">Import Excel {{ $label }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <div class="modal-body p-4">
                <ol class="ps-3 mb-3" style="font-size: .84rem; line-height: 1.8;">
                    <li>Unduh template: <a href="{{ route('data-akademik.template', ['tab' => $tab]) }}" class="fw-semibold">Template Excel (.xlsx)</a>.</li>
                    <li>Isi data mulai baris ke-2 ({{ $kolomHint }}).</li>
                    <li>Pilih file lalu klik <strong>Import</strong>. Data yang sudah ada tidak akan diubah.</li>
                </ol>

                <label class="form-label" for="importFile">File Excel <span class="text-danger">*</span></label>
                <input type="file" id="importFile" name="file"
                       accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel"
                       class="form-control @error('file') is-invalid @enderror">
                @error('file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

                <div class="form-text">
                    Format yang diterima: .xlsx dan .xls. Maksimal 2 MB. Jika ada satu baris bermasalah, tidak ada data yang disimpan.
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-silab" data-bs-dismiss="modal">Batal</button>
                <button type="submit" id="importSubmit" class="btn btn-primary">Import</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if ($errors->has('file'))
            bootstrap.Modal.getOrCreateInstance(document.getElementById('importModal')).show();
        @endif
        var f = document.getElementById('importForm'), b = document.getElementById('importSubmit'), label = b.innerHTML;
        // Kunci tombol saat proses berjalan; pulihkan bila halaman kembali dari cache browser.
        f.addEventListener('submit', function () {
            b.disabled = true;
            b.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Memproses...';
        });
        window.addEventListener('pageshow', function (e) { if (e.persisted) { b.disabled = false; b.innerHTML = label; } });
    });
</script>
@endpush
