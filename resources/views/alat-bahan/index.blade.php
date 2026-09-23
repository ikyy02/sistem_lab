@extends('layouts.app')

@section('title', 'Alat & Bahan')
@section('page-title', 'Alat & Bahan')
@section('page-subtitle', 'Kelola inventaris alat dan bahan laboratorium komputer bisnis.')

@section('content')

<!-- Page Header -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1" style="font-size:1.3rem; color:#1e293b;">Kelola Alat &amp; Bahan</h2>
        <p class="mb-0" style="font-size:0.83rem; color:#64748b;">Kelola inventaris alat dan bahan laboratorium komputer bisnis.</p>
    </div>
    <a href="{{ route('alat-bahan.create') }}" class="btn d-flex align-items-center gap-2" style="background:#16a34a;color:#fff;border-radius:10px;font-size:0.85rem;font-weight:600;padding:9px 18px;white-space:nowrap;">
        <i class="bi bi-plus-circle-fill"></i>
        Tambah Data
    </a>
</div>

<!-- Filter Bar (GET — filter jenis aktif) -->
<div class="card-modern p-3 mb-4">
    <div class="row g-2 align-items-center">
        <div class="col-12 col-md-6 col-lg-4">
            <div class="input-group" style="border-radius:10px; overflow:hidden;">
                <span class="input-group-text bg-white border-end-0" style="border-color:#e2e8f0; color:#94a3b8;">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" class="form-control border-start-0 ps-0" placeholder="Cari nama alat, bahan, atau ruangan..." style="border-color:#e2e8f0; font-size:0.85rem;" disabled>
            </div>
        </div>
        <div class="col-6 col-md-3 col-lg-2">
            <form action="{{ route('alat-bahan.index') }}" method="GET">
                <select name="jenis" class="form-select" onchange="this.form.submit()" style="font-size:0.85rem; border-color:#e2e8f0; border-radius:10px; color:#64748b;">
                    <option value="" {{ $jenis === null || $jenis === '' ? 'selected' : '' }}>Semua</option>
                    <option value="alat" {{ $jenis === 'alat' ? 'selected' : '' }}>Alat</option>
                    <option value="bahan" {{ $jenis === 'bahan' ? 'selected' : '' }}>Bahan</option>
                    <option value="ruangan" {{ $jenis === 'ruangan' ? 'selected' : '' }}>Ruangan</option>
                </select>
            </form>
        </div>
        <div class="col-6 col-md-3 col-lg-2">
            <select class="form-select" style="font-size:0.85rem; border-color:#e2e8f0; border-radius:10px; color:#64748b;" disabled>
                <option>Semua Kondisi</option>
                <option>Baik</option>
                <option>Rusak</option>
            </select>
        </div>
        <div class="col-12 col-lg-4 d-flex justify-content-lg-end">
            @php
                $filterLabel = match ($jenis) {
                    'alat'    => 'Alat',
                    'bahan'   => 'Bahan',
                    'ruangan' => 'Ruangan',
                    default   => 'Semua',
                };
            @endphp
            <div class="d-flex align-items-center gap-2 flex-wrap justify-content-lg-end">
                <span class="badge d-flex align-items-center gap-1 px-3 py-2" style="background:#f1f5f9; color:#64748b; font-size:0.75rem; border-radius:8px; font-weight:500;">
                    <i class="bi bi-filter-left"></i> Menampilkan: {{ $filterLabel }}
                </span>
                @if(in_array($jenis, \App\Models\AlatBahan::JENIS))
                    <a href="{{ route('alat-bahan.index') }}" class="badge text-decoration-none d-inline-flex align-items-center gap-1 px-3 py-2" style="background:#fff1f2; color:#e11d48; font-size:0.75rem; border-radius:8px; font-weight:500;" title="Reset filter">
                        <i class="bi bi-x-circle"></i> Reset
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="card-modern overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover mb-0" style="font-size:0.85rem;">
            <thead style="background:#f8fafc; color:#64748b; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.04em;">
                <tr>
                    <th class="ps-4 py-3 fw-semibold border-0" style="width:48px;">#</th>
                    <th class="py-3 fw-semibold border-0">Nama</th>
                    <th class="py-3 fw-semibold border-0">Jenis</th>
                    <th class="py-3 fw-semibold border-0">Satuan</th>
                    <th class="py-3 fw-semibold border-0">Stok</th>
                    <th class="py-3 fw-semibold border-0">Kondisi</th>
                    <th class="py-3 fw-semibold border-0">Keterangan</th>
                    <th class="py-3 pe-4 fw-semibold border-0 text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($data as $item)
                <tr style="border-top:1px solid #f1f5f9;">
                    <td class="ps-4 py-3 align-middle text-muted" style="font-size:0.78rem;">{{ $loop->iteration }}</td>

                    <td class="py-3 align-middle">
                        <span class="fw-semibold" style="color:#1e293b;">{{ $item->nama }}</span>
                    </td>

                    <td class="py-3 align-middle">
                        @if($item->jenis == 'alat')
                            <span class="badge rounded-pill d-inline-flex align-items-center gap-1" style="background:#f0fdf4; color:#16a34a; font-size:0.73rem; font-weight:600; padding:5px 12px;">
                                <i class="bi bi-tools" style="font-size:0.65rem;"></i> Alat
                            </span>
                        @elseif($item->jenis == 'bahan')
                            <span class="badge rounded-pill d-inline-flex align-items-center gap-1" style="background:#eff6ff; color:#2563eb; font-size:0.73rem; font-weight:600; padding:5px 12px;">
                                <i class="bi bi-droplet" style="font-size:0.65rem;"></i> Bahan
                            </span>
                        @else
                            <span class="badge rounded-pill d-inline-flex align-items-center gap-1" style="background:#f5f3ff; color:#7c3aed; font-size:0.73rem; font-weight:600; padding:5px 12px;">
                                <i class="bi bi-door-open" style="font-size:0.65rem;"></i> Ruangan
                            </span>
                        @endif
                    </td>

                    <td class="py-3 align-middle" style="color:#475569;">{{ $item->satuan }}</td>

                    <td class="py-3 align-middle">
                        @if($item->stok <= 0)
                            <span class="badge rounded-pill" style="background:#fff1f2; color:#e11d48; font-size:0.73rem; font-weight:600; padding:5px 12px;">
                                {{ $item->stok }}
                            </span>
                        @elseif($item->stok <= 5)
                            <span class="badge rounded-pill" style="background:#fefce8; color:#ca8a04; font-size:0.73rem; font-weight:600; padding:5px 12px;">
                                {{ $item->stok }}
                            </span>
                        @else
                            <span class="badge rounded-pill" style="background:#f0fdf4; color:#16a34a; font-size:0.73rem; font-weight:600; padding:5px 12px;">
                                {{ $item->stok }}
                            </span>
                        @endif
                    </td>

                    <td class="py-3 align-middle">
                        @if($item->kondisi)
                            <span class="badge rounded-pill" style="background:#f8fafc; color:#475569; font-size:0.73rem; font-weight:500; padding:5px 12px; border:1px solid #e2e8f0;">
                                {{ $item->kondisi }}
                            </span>
                        @else
                            <span style="color:#cbd5e1;">—</span>
                        @endif
                    </td>

                    <td class="py-3 align-middle" style="color:#64748b; max-width:180px;">
                        <span class="d-inline-block text-truncate" style="max-width:160px;" title="{{ $item->keterangan }}">
                            {{ $item->keterangan ?? '—' }}
                        </span>
                    </td>

                    <td class="py-3 pe-4 align-middle text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <a href="{{ route('alat-bahan.edit', $item) }}"
                               class="btn btn-sm d-flex align-items-center gap-1"
                               style="background:#fefce8; color:#ca8a04; border:1px solid #fde68a; border-radius:8px; font-size:0.78rem; font-weight:600; padding:5px 12px; white-space:nowrap;">
                                <i class="bi bi-pencil" style="font-size:0.7rem;"></i> Edit
                            </a>
                            <form action="{{ route('alat-bahan.destroy', $item) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Yakin ingin menghapus data \'{{ addslashes($item->nama) }}\'?')">
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
                    <td colspan="8">
                        <div class="text-center py-5" style="color:#94a3b8;">
                            <div class="mb-3">
                                <span style="display:inline-flex;align-items:center;justify-content:center;width:64px;height:64px;background:#f1f5f9;border-radius:50%;">
                                    <i class="bi bi-inbox fs-2" style="color:#cbd5e1;"></i>
                                </span>
                            </div>
                            <div class="fw-semibold mb-1" style="font-size:0.95rem; color:#475569;">Belum ada data</div>
                            <div class="mb-4" style="font-size:0.83rem; color:#94a3b8;">Belum ada alat atau bahan yang tersedia.</div>
                            <a href="{{ route('alat-bahan.create') }}" class="btn d-inline-flex align-items-center gap-2" style="background:#16a34a;color:#fff;border-radius:10px;font-size:0.83rem;font-weight:600;padding:9px 20px;">
                                <i class="bi bi-plus-circle-fill"></i> Tambah Data
                            </a>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($data->hasPages())
    <div class="d-flex align-items-center justify-content-between px-4 py-3" style="border-top:1px solid #f1f5f9; background:#fafafa;">
        <p class="mb-0" style="font-size:0.78rem; color:#94a3b8;">
            Menampilkan {{ $data->firstItem() }}–{{ $data->lastItem() }} dari {{ $data->total() }} data
        </p>
        <div class="pagination-modern">
            {{ $data->links() }}
        </div>
    </div>
    @endif
</div>

@endsection

@push('styles')
<style>
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
