<!-- Modal Import Excel -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('mahasiswa.import') }}" method="POST" enctype="multipart/form-data" id="importForm"
              class="modal-content" style="border-radius:14px; border:1px solid #e2e8f0;">
            @csrf

            <div class="modal-header" style="border-bottom:1px solid #e2e8f0; background:#fafafa; border-radius:14px 14px 0 0;">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:38px;height:38px;background:#D9EAFD;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#9AA6B2;flex-shrink:0;">
                        <i class="bi bi-file-earmark-excel-fill"></i>
                    </div>
                    <div>
                        <div class="fw-bold" id="importModalLabel" style="font-size:0.95rem; color:#1e293b;">Import Data Mahasiswa</div>
                        <div style="font-size:0.75rem; color:#64748b;">Tambahkan banyak mahasiswa sekaligus dari file Excel</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <div class="modal-body p-4">
                <ol class="ps-3 mb-4" style="font-size:0.84rem; color:#475569; line-height:1.7;">
                    <li>
                        <a href="{{ route('mahasiswa.template') }}" style="color:#9AA6B2; font-weight:600; text-decoration:none;">Unduh template Excel</a>.
                    </li>
                    <li>Isi data mulai dari baris ke-2 (NIM, Nama, Program Studi, No WhatsApp, Email).</li>
                    <li>Pilih file yang sudah diisi di bawah ini, lalu klik <strong>Import</strong>.</li>
                </ol>

                <label for="importFile" class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">
                    File Excel <span class="text-danger">*</span>
                </label>
                <input type="file" id="importFile" name="file" accept=".xlsx,.xls"
                       class="form-control @error('file') is-invalid @enderror"
                       style="border-radius:10px; border-color:#e2e8f0; font-size:0.85rem;">
                @error('file')
                    <div class="invalid-feedback d-flex align-items-center gap-1 mt-1" style="font-size:0.78rem;">
                        <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                    </div>
                @enderror
                <div class="mt-1" style="font-size:0.75rem; color:#94a3b8;">Format .xlsx atau .xls, maksimal 2 MB dan {{ number_format(\App\Services\MahasiswaImportService::MAX_ROWS, 0, ',', '.') }} baris.</div>

                <div class="d-flex align-items-start gap-2 mt-3 p-3" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; font-size:0.78rem; color:#64748b;">
                    <i class="bi bi-info-circle-fill" style="color:#2563eb; margin-top:2px;"></i>
                    <span>Semua baris diperiksa lebih dulu. Jika ada satu saja yang bermasalah, tidak ada data yang disimpan dan baris yang salah akan ditampilkan beserta alasannya.</span>
                </div>
            </div>

            <div class="modal-footer" style="border-top:1px solid #e2e8f0;">
                <button type="button" class="btn" data-bs-dismiss="modal"
                        style="background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; border-radius:10px; font-size:0.85rem; font-weight:600; padding:9px 20px;">
                    Batal
                </button>
                <button type="submit" id="importSubmit" class="btn d-flex align-items-center gap-2"
                        style="background:#9AA6B2; color:#fff; border-radius:10px; font-size:0.85rem; font-weight:600; padding:9px 22px;">
                    <i class="bi bi-upload"></i> Import
                </button>
            </div>
        </form>
    </div>
</div>


@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if($errors->has('file'))
            // File ditolak validasi: buka kembali modal agar pesan kesalahannya terlihat.
            new bootstrap.Modal(document.getElementById('importModal')).show();
        @endif

        // Cegah klik ganda saat file sedang diproses (klik kedua akan dianggap NIM duplikat).
        var importForm = document.getElementById('importForm');
        var importSubmit = document.getElementById('importSubmit');
        var importLabel = importSubmit.innerHTML;

        importForm.addEventListener('submit', function () {
            importSubmit.disabled = true;
            importSubmit.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Memproses...';
        });

        // Tombol Back pada browser mengembalikan halaman dari cache dengan tombol masih nonaktif.
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                importSubmit.disabled = false;
                importSubmit.innerHTML = importLabel;
            }
        });
    });
</script>
@endpush
