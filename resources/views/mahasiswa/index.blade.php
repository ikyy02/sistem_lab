@extends('layouts.app')

@section('title', 'Data Mahasiswa')
@section('page-title', 'Data Mahasiswa')
@section('page-subtitle', 'Kelola data mahasiswa pengguna laboratorium komputer bisnis.')

@section('content')

@php
    // Laporan baris bermasalah dari proses import Excel (di-flash oleh controller).
    $report = session('import_report');

    // Pencarian/sorting/halaman yang sedang aktif, dibawa saat menghapus agar tampilan tidak kembali ke awal.
    $listQuery = request()->only(['search', 'sort', 'direction', 'per_page', 'page']);
@endphp

<!-- Page Header -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1" style="font-size:1.3rem; color:#1e293b;">Kelola Data Mahasiswa</h2>
        <p class="mb-0" style="font-size:0.83rem; color:#64748b;">Kelola data mahasiswa pengguna laboratorium komputer bisnis.</p>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <a href="{{ route('mahasiswa.template') }}" class="btn d-flex align-items-center gap-2 btn-toolbar-outline">
            <i class="bi bi-download"></i>
            Download Template Excel
        </a>
        <button type="button" class="btn d-flex align-items-center gap-2 btn-toolbar-green"
                data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="bi bi-file-earmark-excel"></i>
            Import Excel
        </button>
        <a href="{{ route('mahasiswa.create') }}" class="btn d-flex align-items-center gap-2" style="background:#16a34a;color:#fff;border-radius:10px;font-size:0.85rem;font-weight:600;padding:9px 18px;white-space:nowrap;">
            <i class="bi bi-plus-circle-fill"></i>
            Tambah Mahasiswa
        </a>
    </div>
</div>

<!-- Laporan import: baris bermasalah -->
@if($report && ! empty($report['errors']))
<div class="card-modern overflow-hidden mb-4 import-report" style="border-color:#fecdd3;">
    <div class="px-4 py-3 d-flex align-items-start gap-3" style="background:#fff1f2; border-bottom:1px solid #fecdd3;">
        <i class="bi bi-exclamation-triangle-fill" style="color:#e11d48; font-size:1.25rem; line-height:1.4;"></i>
        <div class="flex-grow-1">
            <div class="fw-bold" style="font-size:0.92rem; color:#9f1239;">
                Import dibatalkan: {{ $report['error_count'] }} dari {{ $report['total'] }} baris bermasalah
            </div>
            <div style="font-size:0.8rem; color:#be123c;">
                Tidak ada data yang disimpan. Perbaiki baris di bawah pada file Excel, lalu unggah ulang.
            </div>
        </div>
        <button type="button" class="btn-close" aria-label="Tutup laporan"
                onclick="this.closest('.import-report').remove()"></button>
    </div>

    <div class="table-responsive" style="max-height:360px; overflow-y:auto;">
        <table class="table table-sm mb-0" style="font-size:0.82rem;">
            <thead style="background:#f8fafc; color:#64748b; font-size:0.72rem; text-transform:uppercase; letter-spacing:0.04em; position:sticky; top:0;">
                <tr>
                    <th class="ps-4 py-2 fw-semibold border-0" style="width:90px;">Baris Excel</th>
                    <th class="py-2 fw-semibold border-0" style="width:140px;">NIM</th>
                    <th class="py-2 fw-semibold border-0" style="width:200px;">Nama</th>
                    <th class="py-2 pe-4 fw-semibold border-0">Alasan kesalahan</th>
                </tr>
            </thead>
            <tbody>
            @foreach($report['errors'] as $error)
                <tr style="border-top:1px solid #f1f5f9;">
                    <td class="ps-4 py-2 align-top fw-semibold" style="color:#e11d48;">{{ $error['row'] }}</td>
                    <td class="py-2 align-top" style="color:#475569;">{{ $error['nim'] !== '' ? $error['nim'] : '—' }}</td>
                    <td class="py-2 align-top" style="color:#475569;">{{ $error['nama'] !== '' ? $error['nama'] : '—' }}</td>
                    <td class="py-2 pe-4 align-top" style="color:#334155;">
                        <ul class="mb-0 ps-3">
                            @foreach($error['messages'] as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    @if($report['error_count'] > count($report['errors']))
    <div class="px-4 py-2" style="font-size:0.78rem; color:#94a3b8; border-top:1px solid #f1f5f9; background:#fafafa;">
        Menampilkan {{ count($report['errors']) }} dari {{ $report['error_count'] }} baris bermasalah.
        Perbaiki lalu unggah ulang untuk melihat sisanya.
    </div>
    @endif
</div>
@endif

<!-- Toolbar: Search, Sorting, Jumlah data per halaman -->
<form method="GET" action="{{ route('mahasiswa.index') }}" class="card-modern p-3 mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-lg-4">
            <label for="search" class="toolbar-label">Cari</label>
            <div class="input-group">
                <span class="input-group-text bg-white" style="border-radius:10px 0 0 10px; border-color:#e2e8f0; color:#94a3b8;">
                    <i class="bi bi-search"></i>
                </span>
                <input type="search" id="search" name="search" value="{{ $search }}" maxlength="100"
                       class="form-control" placeholder="NIM, nama, program studi, atau email"
                       style="border-radius:0 10px 10px 0; border-color:#e2e8f0; font-size:0.85rem;">
            </div>
        </div>

        <div class="col-6 col-lg-2">
            <label for="sort" class="toolbar-label">Urutkan berdasarkan</label>
            <select id="sort" name="sort" class="form-select toolbar-select" onchange="this.form.submit()">
                @foreach($sortOptions as $value => $label)
                    <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-6 col-lg-2">
            <label for="direction" class="toolbar-label">Arah</label>
            <select id="direction" name="direction" class="form-select toolbar-select" onchange="this.form.submit()">
                <option value="asc" @selected($direction === 'asc')>Ascending (A–Z)</option>
                <option value="desc" @selected($direction === 'desc')>Descending (Z–A)</option>
            </select>
        </div>

        <div class="col-6 col-lg-2">
            <label for="per_page" class="toolbar-label">Tampilkan</label>
            <select id="per_page" name="per_page" class="form-select toolbar-select" onchange="this.form.submit()">
                @foreach($perPageOptions as $option)
                    <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }} data</option>
                @endforeach
            </select>
        </div>

        <div class="col-6 col-lg-2 d-flex gap-2">
            <button type="submit" class="btn flex-fill" style="background:#16a34a; color:#fff; border-radius:10px; font-size:0.85rem; font-weight:600; padding:8px 14px;">
                Cari
            </button>
            @if($isFiltered)
                <a href="{{ route('mahasiswa.index') }}" class="btn" title="Hapus pencarian dan urutan"
                   style="background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; border-radius:10px; font-size:0.85rem; font-weight:600; padding:8px 14px;">
                    Reset
                </a>
            @endif
        </div>
    </div>
</form>

<!-- Table Card -->
<div class="card-modern overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover mb-0" style="font-size:0.85rem;">
            <thead style="background:#f8fafc; color:#64748b; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.04em;">
                <tr>
                    <th class="ps-4 py-3 fw-semibold border-0" style="width:48px;">#</th>
                    <th class="py-3 fw-semibold border-0">NIM</th>
                    <th class="py-3 fw-semibold border-0">Nama</th>
                    <th class="py-3 fw-semibold border-0">Program Studi</th>
                    <th class="py-3 fw-semibold border-0">Email</th>
                    <th class="py-3 fw-semibold border-0">No. WhatsApp</th>
                    <th class="py-3 pe-4 fw-semibold border-0 text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($data as $item)
                <tr style="border-top:1px solid #f1f5f9;">
                    {{-- Nomor urut berlanjut antar halaman (bukan mulai dari 1 lagi di tiap halaman) --}}
                    <td class="ps-4 py-3 align-middle text-muted" style="font-size:0.78rem;">{{ $data->firstItem() + $loop->index }}</td>

                    <td class="py-3 align-middle" style="color:#475569;">{{ $item->nim }}</td>

                    <td class="py-3 align-middle">
                        <span class="fw-semibold" style="color:#1e293b;">{{ $item->nama }}</span>
                    </td>

                    <td class="py-3 align-middle" style="color:#475569;">{{ $item->program_studi }}</td>

                    <td class="py-3 align-middle" style="color:#475569;">{{ $item->email }}</td>

                    <td class="py-3 align-middle" style="color:#475569;">{{ $item->no_whatsapp }}</td>

                    <td class="py-3 pe-4 align-middle text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <a href="{{ route('mahasiswa.show', $item) }}"
                               class="btn btn-sm d-flex align-items-center gap-1"
                               style="background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe; border-radius:8px; font-size:0.78rem; font-weight:600; padding:5px 12px; white-space:nowrap;">
                                <i class="bi bi-eye" style="font-size:0.7rem;"></i> Detail
                            </a>
                            <a href="{{ route('mahasiswa.edit', $item) }}"
                               class="btn btn-sm d-flex align-items-center gap-1"
                               style="background:#fefce8; color:#ca8a04; border:1px solid #fde68a; border-radius:8px; font-size:0.78rem; font-weight:600; padding:5px 12px; white-space:nowrap;">
                                <i class="bi bi-pencil" style="font-size:0.7rem;"></i> Edit
                            </a>
                            <form action="{{ route('mahasiswa.destroy', array_merge(['mahasiswa' => $item], $listQuery)) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm(@js('Yakin ingin menghapus data ' . $item->nama . '?'))">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="btn btn-sm d-flex align-items-center gap-1"
                                        style="background:#fff1f2; color:#e11d48; border:1px solid #fecdd3; border-radius:8px; font-size:0.78rem; font-weight:600; padding:5px 12px; white-space:nowrap;">
                                    <i class="bi bi-trash" style="font-size:0.7rem;"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <div class="text-center py-5" style="color:#94a3b8;">
                            <div class="mb-3">
                                <span style="display:inline-flex;align-items:center;justify-content:center;width:64px;height:64px;background:#f1f5f9;border-radius:50%;">
                                    <i class="bi {{ $search !== '' ? 'bi-search' : 'bi-person-lines-fill' }} fs-2" style="color:#cbd5e1;"></i>
                                </span>
                            </div>

                            @if($search !== '')
                                <div class="fw-semibold mb-1" style="font-size:0.95rem; color:#475569;">Data tidak ditemukan</div>
                                <div class="mb-4" style="font-size:0.83rem; color:#94a3b8;">
                                    Tidak ada mahasiswa yang cocok dengan “{{ $search }}”. Coba kata kunci lain.
                                </div>
                                <a href="{{ route('mahasiswa.index') }}" class="btn d-inline-flex align-items-center gap-2" style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;border-radius:10px;font-size:0.83rem;font-weight:600;padding:9px 20px;">
                                    Hapus pencarian
                                </a>
                            @else
                                <div class="fw-semibold mb-1" style="font-size:0.95rem; color:#475569;">Belum ada data</div>
                                <div class="mb-4" style="font-size:0.83rem; color:#94a3b8;">Tambahkan mahasiswa satu per satu, atau impor banyak data sekaligus dari Excel.</div>
                                <div class="d-flex flex-wrap justify-content-center gap-2">
                                    <a href="{{ route('mahasiswa.create') }}" class="btn d-inline-flex align-items-center gap-2" style="background:#16a34a;color:#fff;border-radius:10px;font-size:0.83rem;font-weight:600;padding:9px 20px;">
                                        <i class="bi bi-plus-circle-fill"></i> Tambah Mahasiswa
                                    </a>
                                    <button type="button" class="btn d-inline-flex align-items-center gap-2 btn-toolbar-green"
                                            data-bs-toggle="modal" data-bs-target="#importModal">
                                        <i class="bi bi-file-earmark-excel"></i> Import Excel
                                    </button>
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <!-- Footer tabel: ringkasan + pagination -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 px-4 py-3" style="border-top:1px solid #f1f5f9; background:#fafafa;">
        <p class="mb-0" style="font-size:0.78rem; color:#94a3b8;">
            @if($data->total() > 0)
                Menampilkan {{ $data->firstItem() }}–{{ $data->lastItem() }} dari {{ $data->total() }} data
            @else
                Tidak ada data untuk ditampilkan
            @endif
        </p>
        <div class="pagination-modern">
            {{ $data->onEachSide(1)->links('pagination.silab') }}
        </div>
    </div>
</div>

<!-- Modal Import Excel -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('mahasiswa.import') }}" method="POST" enctype="multipart/form-data" id="importForm"
              class="modal-content" style="border-radius:14px; border:1px solid #e2e8f0;">
            @csrf

            <div class="modal-header" style="border-bottom:1px solid #e2e8f0; background:#fafafa; border-radius:14px 14px 0 0;">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:38px;height:38px;background:#f0fdf4;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#16a34a;flex-shrink:0;">
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
                        <a href="{{ route('mahasiswa.template') }}" style="color:#16a34a; font-weight:600; text-decoration:none;">Unduh template Excel</a>.
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
                        style="background:#16a34a; color:#fff; border-radius:10px; font-size:0.85rem; font-weight:600; padding:9px 22px;">
                    <i class="bi bi-upload"></i> Import
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* Toolbar */
    .toolbar-label {
        display: block;
        margin-bottom: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        color: #64748b;
    }
    .toolbar-select {
        border-radius: 10px;
        border-color: #e2e8f0;
        font-size: 0.85rem;
    }
    .btn-toolbar-outline {
        background: #fff;
        color: #475569;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 600;
        padding: 9px 16px;
        white-space: nowrap;
    }
    .btn-toolbar-outline:hover { background: #f8fafc; color: #1e293b; }
    .btn-toolbar-green {
        background: #f0fdf4;
        color: #15803d;
        border: 1px solid #bbf7d0;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 600;
        padding: 9px 16px;
        white-space: nowrap;
    }
    .btn-toolbar-green:hover { background: #dcfce7; color: #166534; }

    /* Override Bootstrap pagination */
    .pagination-modern .pagination {
        margin: 0;
        gap: 4px;
    }
    .pagination-modern .page-item .page-link {
        border-radius: 8px !important;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: 0.8rem;
        padding: 5px 11px;
        font-weight: 500;
    }
    .pagination-modern .page-item.active .page-link {
        background: #16a34a;
        border-color: #16a34a;
        color: #fff;
    }
    .pagination-modern .page-item .page-link:hover {
        background: #f1f5f9;
        color: #1e293b;
    }
    .pagination-modern .page-item.disabled .page-link {
        color: #cbd5e1;
        background: transparent;
    }
</style>
@endpush

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
