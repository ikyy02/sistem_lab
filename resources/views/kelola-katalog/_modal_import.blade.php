<!-- Modal Import -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form
            action="{{ route('kelola-katalog.import', $kategori) }}"
            method="POST"
            enctype="multipart/form-data"
            id="importForm"
            class="modal-content"
        >
            @csrf

            <div class="modal-header">
                <h5 class="modal-title fw-bold" style="font-size:1rem;">
                    Import Excel {{ $label }}
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Tutup"
                ></button>
            </div>

            <div class="modal-body p-4">
                <ol class="ps-3 mb-3" style="font-size:.84rem; line-height:1.8;">
                    <li>
                        Unduh template:
                        <a href="{{ route('kelola-katalog.template', ['kategori' => $kategori]) }}" class="fw-semibold">Template Excel (.xlsx)</a>.
                    </li>
                    <li>
                        Isi data mulai baris ke-2
                        ({{ implode(', ', array_keys(\App\Services\KatalogImportService::columns($kategori))) }}).
                    </li>
                    <li>
                        Pilih file lalu klik <strong>Import</strong>.
                        Data yang sudah ada tidak akan diubah.
                    </li>
                </ol>

                <label class="form-label" for="importFile">
                    File Excel <span class="text-danger">*</span>
                </label>

                <input
                    type="file"
                    id="importFile"
                    name="file"
                    accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel"
                    class="form-control @error('file') is-invalid @enderror"
                >

                @error('file')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror

                <div class="form-text mt-1">
                    Format yang diterima: .xlsx dan .xls. Maksimal 5 MB.
                    Maksimal {{ number_format(\App\Services\KatalogImportService::MAX_ROWS, 0, ',', '.') }} baris data.
                </div>

                <div
                    class="d-flex align-items-start gap-2 mt-3 p-3"
                    style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; font-size:.8rem; color:#64748b;"
                >
                    <i class="bi bi-info-circle-fill" style="color:#2563eb; margin-top:2px;"></i>
                    <span>
                        Semua baris diperiksa lebih dulu. Jika ada satu saja yang bermasalah,
                        tidak ada data yang disimpan dan baris yang salah akan ditampilkan beserta alasannya.
                        Gambar tidak dapat diimpor — unggah lewat tombol <strong>Edit</strong> setelah import.
                    </span>
                </div>
            </div>

            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-outline-silab"
                    data-bs-dismiss="modal"
                >
                    Batal
                </button>

                <button
                    type="submit"
                    id="importSubmit"
                    class="btn btn-primary d-flex align-items-center gap-2"
                >
                    <i class="bi bi-upload"></i> Import Data
                </button>
            </div>
        </form>
    </div>
</div>
