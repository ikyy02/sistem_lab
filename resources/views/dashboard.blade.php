@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Sistem Manajemen Laboratorium Komputer Bisnis')

@section('content')

@php
    $user  = auth()->user();
    $total = max($stats['total_user'] ?? 1, 1);
@endphp

<!-- Welcome Banner -->
<div class="card-modern p-4 mb-4" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-color: #bbf7d0;">
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <div style="width:56px;height:56px;background:#16a34a;border-radius:14px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(22,163,74,0.3);flex-shrink:0;">
            <i class="bi bi-eyedropper fs-3 text-white"></i>
        </div>
        <div>
            <div style="font-size:0.82rem; color:#15803d; font-weight:600; margin-bottom:2px;">Selamat datang,</div>
            <div style="font-size:1.35rem; font-weight:800; color:#0f172a; line-height:1.2; margin-bottom:4px;">{{ $user->name }}</div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge rounded-pill px-3 py-1" style="background:#fff; color:#16a34a; font-size:0.75rem; font-weight:600; border:1px solid #bbf7d0;">
                    <i class="bi bi-person-badge me-1"></i>{{ $user->role_label }}
                </span>
                <span style="font-size:0.8rem; color:#16a34a; font-weight:500;">
                    <i class="bi bi-calendar3 me-1"></i>{{ now()->isoFormat('dddd, D MMMM Y') }}
                </span>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════
     Dashboard LABORAN (Super Admin)
══════════════════════════════════════════════ --}}
@if($user->isLaboran())

<div class="row g-4 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#eff6ff; color:#2563eb;">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="stat-value">{{ $stats['total_user'] ?? 0 }}</div>
            <div class="stat-label">Total User</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#ecfdf5; color:#059669;">
                <i class="bi bi-person-check-fill"></i>
            </div>
            <div class="stat-value">{{ $stats['user_aktif'] ?? 0 }}</div>
            <div class="stat-label">User Aktif</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#f0fdf4; color:#16a34a;">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
            <div class="stat-value">{{ $stats['total_mahasiswa'] ?? 0 }}</div>
            <div class="stat-label">Mahasiswa</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fefce8; color:#ca8a04;">
                <i class="bi bi-person-badge-fill"></i>
            </div>
            <div class="stat-value">{{ $stats['total_dosen'] ?? 0 }}</div>
            <div class="stat-label">Dosen</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-modern p-4 h-100">
            <h6 class="fw-bold mb-3" style="font-size:0.9rem;">
                <i class="bi bi-grid-3x3-gap text-success me-1"></i> Menu Cepat
            </h6>
            <div class="row g-3">
                <div class="col-6 col-sm-4">
                    <a href="{{ route('admin.users.index') }}" class="text-decoration-none d-block h-100">
                        <div class="text-center p-3 rounded border h-100" style="border-color:#e2e8f0; transition:all 0.2s; background:#fff;" onmouseover="this.style.borderColor='#16a34a';this.style.background='#f0fdf4'" onmouseout="this.style.borderColor='#e2e8f0';this.style.background='#fff'">
                            <i class="bi bi-shield-check text-success fs-2 mb-2 d-block"></i>
                            <span class="fw-semibold" style="font-size:0.8rem;">Admin User</span>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-sm-4">
                    <a href="{{ route('users.index') }}" class="text-decoration-none d-block h-100">
                        <div class="text-center p-3 rounded border h-100" style="border-color:#e2e8f0; transition:all 0.2s; background:#fff;" onmouseover="this.style.borderColor='#16a34a';this.style.background='#f0fdf4'" onmouseout="this.style.borderColor='#e2e8f0';this.style.background='#fff'">
                            <i class="bi bi-people-fill text-primary fs-2 mb-2 d-block"></i>
                            <span class="fw-semibold" style="font-size:0.8rem;">Kelola User</span>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-sm-4">
                    <a href="#" class="text-decoration-none d-block h-100">
                        <div class="text-center p-3 rounded border h-100" style="border-color:#e2e8f0; background:#fafafa;">
                            <i class="bi bi-building text-warning fs-2 mb-2 d-block"></i>
                            <span class="fw-semibold" style="font-size:0.8rem;">Kelola Laboratorium</span>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-sm-4">
                    <a href="#" class="text-decoration-none d-block h-100">
                        <div class="text-center p-3 rounded border h-100" style="border-color:#e2e8f0; background:#fafafa;">
                            <i class="bi bi-clipboard2-data text-info fs-2 mb-2 d-block"></i>
                            <span class="fw-semibold" style="font-size:0.8rem;">Data Peminjaman</span>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-sm-4">
                    <a href="#" class="text-decoration-none d-block h-100">
                        <div class="text-center p-3 rounded border h-100" style="border-color:#e2e8f0; background:#fafafa;">
                            <i class="bi bi-check2-square text-danger fs-2 mb-2 d-block"></i>
                            <span class="fw-semibold" style="font-size:0.8rem;">Verifikasi Pengajuan</span>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-sm-4">
                    <a href="#" class="text-decoration-none d-block h-100">
                        <div class="text-center p-3 rounded border h-100" style="border-color:#e2e8f0; background:#fafafa;">
                            <i class="bi bi-calendar3 text-secondary fs-2 mb-2 d-block"></i>
                            <span class="fw-semibold" style="font-size:0.8rem;">Kelola Jadwal</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-modern p-4">
            <h6 class="fw-bold mb-3" style="font-size:0.9rem;">
                <i class="bi bi-pie-chart text-success me-1"></i> Komposisi User
            </h6>
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-1" style="font-size:0.82rem;">
                    <span>Mahasiswa</span>
                    <strong>{{ $stats['total_mahasiswa'] ?? 0 }}</strong>
                </div>
                <div class="progress" style="height:6px;">
                    <div class="progress-bar" style="background:#16a34a; width:{{ (($stats['total_mahasiswa'] ?? 0) / $total) * 100 }}%"></div>
                </div>
            </div>
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-1" style="font-size:0.82rem;">
                    <span>Dosen</span>
                    <strong>{{ $stats['total_dosen'] ?? 0 }}</strong>
                </div>
                <div class="progress" style="height:6px;">
                    <div class="progress-bar" style="background:#eab308; width:{{ (($stats['total_dosen'] ?? 0) / $total) * 100 }}%"></div>
                </div>
            </div>
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-1" style="font-size:0.82rem;">
                    <span>Staf Prodi</span>
                    <strong>{{ $stats['total_staf'] ?? 0 }}</strong>
                </div>
                <div class="progress" style="height:6px;">
                    <div class="progress-bar" style="background:#2563eb; width:{{ (($stats['total_staf'] ?? 0) / $total) * 100 }}%"></div>
                </div>
            </div>
            <hr style="border-color:#f1f5f9;">
            <div class="d-flex justify-content-between" style="font-size:0.82rem;">
                <span class="text-success"><i class="bi bi-circle-fill me-1" style="font-size:0.5rem;"></i>Aktif: {{ $stats['user_aktif'] ?? 0 }}</span>
                <span class="text-danger"><i class="bi bi-circle-fill me-1" style="font-size:0.5rem;"></i>Nonaktif: {{ $stats['user_nonaktif'] ?? 0 }}</span>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════
     Dashboard STAF PRODI
══════════════════════════════════════════════ --}}
@elseif($user->isStafProdi())

<div class="row g-4 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fefce8; color:#ca8a04;">
                <i class="bi bi-inbox"></i>
            </div>
            <div class="stat-value">0</div>
            <div class="stat-label">Pengajuan Masuk</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#ecfdf5; color:#059669;">
                <i class="bi bi-check2-circle"></i>
            </div>
            <div class="stat-value">0</div>
            <div class="stat-label">Disetujui</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fff1f2; color:#e11d48;">
                <i class="bi bi-x-circle"></i>
            </div>
            <div class="stat-value">0</div>
            <div class="stat-label">Ditolak</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#eff6ff; color:#2563eb;">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div class="stat-value">0</div>
            <div class="stat-label">Jadwal Hari Ini</div>
        </div>
    </div>
</div>

<div class="card-modern p-4">
    <h6 class="fw-bold mb-3" style="font-size:0.9rem;">
        <i class="bi bi-grid-3x3-gap text-success me-1"></i> Menu Cepat
    </h6>
    <div class="row g-3">
        <div class="col-6 col-sm-3">
            <div class="text-center p-3 rounded border" style="border-color:#e2e8f0; background:#fafafa;">
                <i class="bi bi-clipboard2-data text-warning fs-2 mb-2 d-block"></i>
                <span class="fw-semibold" style="font-size:0.8rem;">Data Peminjaman</span>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="text-center p-3 rounded border" style="border-color:#e2e8f0; background:#fafafa;">
                <i class="bi bi-check2-square text-success fs-2 mb-2 d-block"></i>
                <span class="fw-semibold" style="font-size:0.8rem;">Verifikasi Peminjaman</span>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="text-center p-3 rounded border" style="border-color:#e2e8f0; background:#fafafa;">
                <i class="bi bi-calendar3 text-info fs-2 mb-2 d-block"></i>
                <span class="fw-semibold" style="font-size:0.8rem;">Jadwal Lab</span>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <a href="{{ route('users.index') }}" class="text-decoration-none">
                <div class="text-center p-3 rounded border" style="border-color:#e2e8f0; transition:all 0.2s; background:#fff;" onmouseover="this.style.borderColor='#16a34a';this.style.background='#f0fdf4'" onmouseout="this.style.borderColor='#e2e8f0';this.style.background='#fff'">
                    <i class="bi bi-people text-primary fs-2 mb-2 d-block"></i>
                    <span class="fw-semibold" style="font-size:0.8rem;">Data User</span>
                </div>
            </a>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════
     Dashboard DOSEN & MAHASISWA
══════════════════════════════════════════════ --}}
@else

<div class="row g-4 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#eff6ff; color:#2563eb;">
                <i class="bi bi-file-earmark-text"></i>
            </div>
            <div class="stat-value">0</div>
            <div class="stat-label">Pengajuan Saya</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fefce8; color:#ca8a04;">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div class="stat-value">0</div>
            <div class="stat-label">Menunggu Verifikasi</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#ecfdf5; color:#059669;">
                <i class="bi bi-check2-circle"></i>
            </div>
            <div class="stat-value">0</div>
            <div class="stat-label">Disetujui</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#f3e8ff; color:#7c3aed;">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="stat-value">0</div>
            <div class="stat-label">Riwayat</div>
        </div>
    </div>
</div>

<div class="card-modern p-4">
    <h6 class="fw-bold mb-3" style="font-size:0.9rem;">
        <i class="bi bi-grid-3x3-gap text-success me-1"></i> Menu Cepat
    </h6>
    <div class="row g-3">
        <div class="col-6 col-sm-3">
            <div class="text-center p-3 rounded border" style="border-color:#e2e8f0; background:#fafafa;">
                <i class="bi bi-info-circle text-info fs-2 mb-2 d-block"></i>
                <span class="fw-semibold" style="font-size:0.8rem;">Informasi Lab</span>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="text-center p-3 rounded border" style="border-color:#e2e8f0; background:#fafafa;">
                <i class="bi bi-calendar3 text-primary fs-2 mb-2 d-block"></i>
                <span class="fw-semibold" style="font-size:0.8rem;">Jadwal Peminjaman</span>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="text-center p-3 rounded border" style="border-color:#e2e8f0; background:#fafafa;">
                <i class="bi bi-plus-circle text-success fs-2 mb-2 d-block"></i>
                <span class="fw-semibold" style="font-size:0.8rem;">Ajukan Peminjaman</span>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="text-center p-3 rounded border" style="border-color:#e2e8f0; background:#fafafa;">
                <i class="bi bi-clock-history text-warning fs-2 mb-2 d-block"></i>
                <span class="fw-semibold" style="font-size:0.8rem;">Riwayat Peminjaman</span>
            </div>
        </div>
    </div>
</div>

@endif

@endsection