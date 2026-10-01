@extends('layouts.app')

@section('title', 'Beranda')
@section('page-title', 'Beranda')
@section('page-subtitle', 'Ringkasan laboratorium Jurusan Komputer dan Bisnis.')

@section('content')
@php
    $jam = (int) date('H');
    $sapaan = $jam < 11 ? 'Selamat pagi' : ($jam < 15 ? 'Selamat siang' : ($jam < 18 ? 'Selamat sore' : 'Selamat malam'));
@endphp

<div class="mb-4">
    <h2 class="font-display fw-semibold mb-1" style="font-size:1.7rem;">{{ $sapaan }}, {{ explode(' ', $me['nama'])[0] }}.</h2>
    <p class="mb-0" style="color:var(--text-muted);font-size:.9rem;">Modul Peminjaman sedang dikembangkan, sehingga ringkasan di bawah belum menampilkan data.</p>
</div>

@if($isAdmin)
    <div class="row g-3">
        <div class="col-12 col-md-6">
            <div class="card-modern p-4 h-100">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:44px;height:44px;border-radius:10px;background:var(--gold-tint);color:var(--gold);display:flex;align-items:center;justify-content:center;"><i class="bi bi-hourglass-split fs-5"></i></div>
                    <div>
                        <div class="fw-bold" style="font-size:1.1rem;color:var(--text-muted);">Belum tersedia</div>
                        <div style="font-size:.8rem;color:var(--text-muted);">Pengajuan menunggu persetujuan</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card-modern p-4 h-100">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:44px;height:44px;border-radius:10px;background:var(--primary-soft);color:var(--ink);display:flex;align-items:center;justify-content:center;"><i class="bi bi-arrow-left-right fs-5"></i></div>
                    <div>
                        <div class="fw-bold" style="font-size:1.1rem;color:var(--text-muted);">Belum tersedia</div>
                        <div style="font-size:.8rem;color:var(--text-muted);">Peminjaman sedang berlangsung</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-modern p-4 mt-3" style="background:var(--gold-tint);border-color:#EFE1C2;">
        <div style="font-size:.82rem;color:#6E6142;">Angka di atas akan aktif setelah modul <strong>Peminjaman</strong> dibangun. Pengajuan dan Pengembalian tetap diakses lewat menunya masing-masing, bukan dari dashboard.</div>
    </div>
@else
    <div class="row g-3 mb-3">
        <div class="col-6 col-lg-3">
            <div class="card-modern p-3 h-100">
                <div class="fw-bold" style="font-size:1.3rem;color:var(--text-muted);">—</div>
                <div style="font-size:.78rem;color:var(--text-muted);">Pengajuan disetujui</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-modern p-3 h-100">
                <div class="fw-bold" style="font-size:1.3rem;color:var(--text-muted);">—</div>
                <div style="font-size:.78rem;color:var(--text-muted);">Pengajuan selesai</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-modern p-3 h-100">
                <div class="fw-bold" style="font-size:1.3rem;color:var(--text-muted);">—</div>
                <div style="font-size:.78rem;color:var(--text-muted);">Total riwayat Peminjaman</div>
            </div>
        </div>
        <div class="col-6 col-lg-3 d-flex">
            <button type="button" class="btn btn-primary w-100 h-100" disabled title="Menyusul setelah modul Peminjaman tersedia">
                <i class="bi bi-plus-lg"></i> Ajukan Peminjaman
            </button>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <div class="card-modern p-4 h-100">
                <h3 class="font-display fw-semibold mb-2" style="font-size:1.05rem;">Pengajuan terbaru</h3>
                <div class="text-center py-4" style="color:var(--text-muted);font-size:.85rem;">
                    <i class="bi bi-inbox fs-2 d-block mb-2" style="color:#c9bdb4;"></i>
                    Belum ada data — modul Peminjaman sedang dikembangkan.
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card-modern p-4 h-100">
                <h3 class="font-display fw-semibold mb-2" style="font-size:1.05rem;">Peminjaman terdekat</h3>
                <div class="text-center py-4" style="color:var(--text-muted);font-size:.85rem;">
                    <i class="bi bi-calendar-event fs-2 d-block mb-2" style="color:#c9bdb4;"></i>
                    Belum ada jadwal Peminjaman mendatang.
                </div>
            </div>
        </div>
    </div>
@endif
@endsection
