@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Kelola alat dan bahan laboratorium komputer dengan mudah dan efisien.')

@section('content')

<!-- Welcome Banner -->
<div class="card-modern p-4 mb-4" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-color: #bbf7d0;">
    <div class="d-flex align-items-center gap-3">
        <div style="width:56px;height:56px;background:#16a34a;border-radius:14px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(22,163,74,0.3);">
            <i class="bi bi-eyedropper fs-3 text-white"></i>
        </div>
        <div>
            <div style="font-size:0.82rem; color:#15803d; font-weight:600; margin-bottom:2px;">Selamat datang,</div>
            <div style="font-size:1.35rem; font-weight:800; color:#0f172a; line-height:1.2; margin-bottom:4px;">SILAB</div>
            <div style="font-size:0.85rem; color:#16a34a; font-weight:500;">Kelola alat dan bahan laboratorium komputer dengan mudah dan efisien.</div>
        </div>
    </div>
</div>
@php
    $totalAlat   = \App\Models\AlatBahan::where('jenis', 'alat')->count();
    $totalBahan  = \App\Models\AlatBahan::where('jenis', 'bahan')->count();
    $stokMinipis = \App\Models\AlatBahan::where('stok', '<=', 5)->count();
    $totalSemua  = \App\Models\AlatBahan::count();
@endphp

<!-- Stat Cards -->
<div class="row g-4 mb-4">

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#f0fdf4; color:#16a34a;">
                <i class="bi bi-tools"></i>
            </div>
            <div class="stat-value">{{ $totalAlat }}</div>
            <div class="stat-label">Total Alat</div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#eff6ff; color:#2563eb;">
                <i class="bi bi-droplet-fill"></i>
            </div>
            <div class="stat-value">{{ $totalBahan }}</div>
            <div class="stat-label">Total Bahan</div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fefce8; color:#ca8a04;">
                <i class="bi bi-arrow-up-right-square"></i>
            </div>
            <div class="stat-value">—</div>
            <div class="stat-label">Peminjaman Aktif</div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fff1f2; color:#e11d48;">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div class="stat-value">{{ $stokMinipis }}</div>
            <div class="stat-label">Stok Menipis <span class="fw-normal" style="font-size:0.75rem;">(≤ 5)</span></div>
        </div>
    </div>

</div>

<!-- Content Row -->
<div class="row g-4">

    <!-- Inventaris Terkini -->
    <div class="col-12 col-lg-7">
        <div class="card-modern p-0 overflow-hidden">
            <div class="d-flex align-items-center justify-content-between px-4 py-3" style="border-bottom:1px solid #e2e8f0;">
                <div>
                    <h6 class="fw-700 mb-0" style="font-size:0.9rem; font-weight:700;">Inventaris Terkini</h6>
                    <p class="mb-0" style="font-size:0.75rem; color:#64748b;">5 data terakhir yang diperbarui</p>
                </div>
                <a href="{{ route('alat-bahan.index') }}" class="btn btn-sm btn-outline-secondary" style="font-size:0.78rem; border-radius:8px;">
                    Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="font-size:0.84rem;">
                    <thead style="background:#f8fafc; color:#64748b; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.04em;">
                        <tr>
                            <th class="ps-4 py-3 fw-600 border-0">Nama</th>
                            <th class="py-3 fw-600 border-0">Jenis</th>
                            <th class="py-3 fw-600 border-0">Stok</th>
                            <th class="py-3 fw-600 border-0">Kondisi</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse(\App\Models\AlatBahan::latest()->take(5)->get() as $item)
                        <tr>
                            <td class="ps-4 py-3 align-middle fw-semibold" style="color:#1e293b;">{{ $item->nama }}</td>
                            <td class="py-3 align-middle">
                                @if($item->jenis == 'alat')
                                    <span class="badge rounded-pill" style="background:#f0fdf4; color:#16a34a; font-size:0.73rem; font-weight:600; padding:4px 10px;">
                                        <i class="bi bi-tools me-1"></i>Alat
                                    </span>
                                @else
                                    <span class="badge rounded-pill" style="background:#eff6ff; color:#2563eb; font-size:0.73rem; font-weight:600; padding:4px 10px;">
                                        <i class="bi bi-droplet me-1"></i>Bahan
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 align-middle">
                                @if($item->stok <= 0)
                                    <span class="badge rounded-pill" style="background:#fff1f2; color:#e11d48; font-size:0.73rem; padding:4px 10px;">{{ $item->stok }}</span>
                                @elseif($item->stok <= 5)
                                    <span class="badge rounded-pill" style="background:#fefce8; color:#ca8a04; font-size:0.73rem; padding:4px 10px;">{{ $item->stok }}</span>
                                @else
                                    <span class="badge rounded-pill" style="background:#f0fdf4; color:#16a34a; font-size:0.73rem; padding:4px 10px;">{{ $item->stok }}</span>
                                @endif
                            </td>
                            <td class="py-3 align-middle" style="color:#64748b;">{{ $item->kondisi ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5" style="color:#94a3b8;">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                Belum ada data inventaris.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Panel Kanan -->
    <div class="col-12 col-lg-5">

        <!-- Ringkasan Stok -->
        <div class="card-modern p-4 mb-4">
            <h6 class="fw-bold mb-3" style="font-size:0.9rem;">Ringkasan Stok</h6>

            <div class="d-flex align-items-center justify-content-between mb-3 pb-3" style="border-bottom:1px solid #f1f5f9;">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:36px;height:36px;background:#f0fdf4;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#16a34a;">
                        <i class="bi bi-tools"></i>
                    </div>
                    <div>
                        <div style="font-size:0.83rem;font-weight:600;color:#1e293b;">Alat</div>
                        <div style="font-size:0.75rem;color:#64748b;">Total inventaris</div>
                    </div>
                </div>
                <span class="fw-bold" style="font-size:1.1rem; color:#16a34a;">{{ $totalAlat }}</span>
            </div>

            <div class="d-flex align-items-center justify-content-between mb-3 pb-3" style="border-bottom:1px solid #f1f5f9;">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:36px;height:36px;background:#eff6ff;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#2563eb;">
                        <i class="bi bi-droplet-fill"></i>
                    </div>
                    <div>
                        <div style="font-size:0.83rem;font-weight:600;color:#1e293b;">Bahan</div>
                        <div style="font-size:0.75rem;color:#64748b;">Total inventaris</div>
                    </div>
                </div>
                <span class="fw-bold" style="font-size:1.1rem; color:#2563eb;">{{ $totalBahan }}</span>
            </div>

            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:36px;height:36px;background:#fff1f2;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#e11d48;">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div>
                        <div style="font-size:0.83rem;font-weight:600;color:#1e293b;">Stok Menipis</div>
                        <div style="font-size:0.75rem;color:#64748b;">Stok ≤ 5 unit</div>
                    </div>
                </div>
                <span class="fw-bold" style="font-size:1.1rem; color:#e11d48;">{{ $stokMinipis }}</span>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card-modern p-4">
            <h6 class="fw-bold mb-3" style="font-size:0.9rem;">Aksi Cepat</h6>
            <div class="d-grid gap-2">
                <a href="{{ route('alat-bahan.create') }}" class="btn d-flex align-items-center gap-2" style="background:#16a34a;color:#fff;border-radius:10px;font-size:0.85rem;font-weight:600;padding:10px 16px;">
                    <i class="bi bi-plus-circle-fill"></i>
                    Tambah Alat / Bahan
                </a>
                <a href="{{ route('alat-bahan.index') }}" class="btn d-flex align-items-center gap-2" style="background:#f1f5f9;color:#475569;border-radius:10px;font-size:0.85rem;font-weight:600;padding:10px 16px; border:1px solid #e2e8f0;">
                    <i class="bi bi-list-ul"></i>
                    Lihat Semua Inventaris
                </a>
            </div>
        </div>

    </div>
</div>
@endsection
