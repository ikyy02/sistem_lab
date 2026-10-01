@php
    $isMhs = $kategori === 'mahasiswa';

    $tplRoute = fn ($fmt) => $isMhs
        ? route('mahasiswa.template', ['format' => $fmt])
        : route('kelola-user.template', [
            'kategori' => $kategori,
            'format' => $fmt
        ]);

    $importAction = $isMhs
        ? route('mahasiswa.import')
        : route('kelola-user.import', $kategori);

    $kolomHint = $isMhs
        ? 'NIM, Nama, Program Studi, No WhatsApp, Email'
        : implode(', ', array_keys(
            \App\Services\UserImportService::columns($kategori)
        ));
@endphp

<!-- Modal Import -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">

        <form
            action="{{ $importAction }}"
            method="POST"
            enctype="multipart/form-data"
            id="importForm"
            class="modal-content"
        >
            @csrf

            <div class="modal-header">
                <h5 class="modal-title fw-bold" style="font-size: 1rem;">
                    Import Data {{ $label }}
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Tutup"
                ></button>
            </div>

            <div class="modal-body p-4">

                <ol class="ps-3 mb-3" style="font-size: .84rem; line-height: 1.8;">

                    <li>
                        Unduh template:
                        <a href="{{ $tplRoute('xlsx') }}" class="fw-semibold">
                            Excel (.xlsx)
                        </a>
                        atau
                        <a href="{{ $tplRoute('csv') }}" class="fw-semibold">
                            CSV
                        </a>.
                    </li>

                    <li>
                        Isi data mulai baris ke-2
                        ({{ $kolomHint }}

                        @if (!$isMhs)
                            , Password
                        @endif
                        ).
                    </li>

                    <li>
                        Pilih file lalu klik <strong>Import</strong>.
                        Data yang sudah ada tidak akan diubah.
                    </li>

                </ol>

                <label class="form-label" for="importFile">
                    File Excel / CSV
                    <span class="text-danger">*</span>
                </label>

                <input
                    type="file"
                    id="importFile"
                    name="file"
                    accept=".xlsx,.xls,.csv"
                    class="form-control @error('file') is-invalid @enderror"
                >

                @error('file')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                @enderror

                <div class="form-text">
                    Maksimal 2 MB. Jika ada satu baris bermasalah,
                    tidak ada data yang disimpan.
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
                    class="btn btn-primary"
                >
                    Import
                </button>

            </div>

        </form>
    </div>
</div>

@push('scripts')

<script>
    document.addEventListener('DOMContentLoaded', function () {

        @if ($errors->has('file'))
            bootstrap.Modal
                .getOrCreateInstance(
                    document.getElementById('importModal')
                )
                .show();
        @endif

        var importForm = document.getElementById('importForm');
        var importSubmit = document.getElementById('importSubmit');
        var importLabel = importSubmit.innerHTML;

        importForm.addEventListener('submit', function () {
            importSubmit.disabled = true;

            importSubmit.innerHTML =
                '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Memproses...';
        });

        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                importSubmit.disabled = false;
                importSubmit.innerHTML = importLabel;
            }
        });

    });
</script>

@endpush