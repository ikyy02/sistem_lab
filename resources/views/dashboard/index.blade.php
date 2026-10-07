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
    <p class="mb-0" style="color:var(--text-muted);font-size:.9rem;">Ringkasan aktivitas peminjaman laboratorium hari ini.</p>
</div>

@if($isAdmin)
    <div class="row g-3">
        <div class="col-12 col-md-6">
            <a href="{{ route('pengajuan.index') }}" class="card-modern p-4 h-100 text-decoration-none d-block">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:44px;height:44px;border-radius:10px;background:var(--gold-tint);color:var(--gold);display:flex;align-items:center;justify-content:center;"><i class="bi bi-hourglass-split fs-5"></i></div>
                    <div>
                        <div class="fw-bold" style="font-size:1.4rem;">{{ $menunggu }}</div>
                        <div style="font-size:.8rem;color:var(--text-muted);">Pengajuan menunggu persetujuan</div>
                    </div>
                    <i class="bi bi-chevron-right ms-auto" style="color:var(--text-muted);"></i>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-6">
            <a href="{{ route('pengembalian.index') }}" class="card-modern p-4 h-100 text-decoration-none d-block">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:44px;height:44px;border-radius:10px;background:var(--primary-soft);color:var(--ink);display:flex;align-items:center;justify-content:center;"><i class="bi bi-arrow-left-right fs-5"></i></div>
                    <div>
                        <div class="fw-bold" style="font-size:1.4rem;">{{ $berlangsung }}</div>
                        <div style="font-size:.8rem;color:var(--text-muted);">Peminjaman sedang berlangsung</div>
                    </div>
                    <i class="bi bi-chevron-right ms-auto" style="color:var(--text-muted);"></i>
                </div>
            </a>
        </div>
    </div>
    <div class="card-modern p-4 mt-3" style="background:var(--gold-tint);border-color:#EFE1C2;">
        <div style="font-size:.82rem;color:#6E6142;">
            <i class="bi bi-info-circle me-1"></i>
            Setujui pengajuan dari menu <strong>Pengajuan Peminjaman</strong>; catat pengembalian barang dari menu <strong>Pengembalian</strong>.
            {{ $tertutup }} pengajuan berstatus ditolak.
        </div>
    </div>
@else
    @if(($menunggu ?? 0) > 0)
    <div class="card-modern p-3 mb-3 d-flex flex-wrap align-items-center justify-content-between gap-2" style="background:var(--gold-tint);border-color:#EFE1C2;">
        <div style="font-size:.85rem;color:#6E6142;">
            <i class="bi bi-hourglass-split me-1"></i>
            Ada <strong>{{ $menunggu }} pengajuan peminjaman</strong> menunggu persetujuan Anda.
        </div>
        <a href="{{ route('pengajuan.index') }}" class="btn btn-gold d-flex align-items-center gap-2" style="font-size:.8rem;">
            <i class="bi bi-clipboard2-check"></i> Proses Pengajuan
        </a>
    </div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-6 col-lg-3">
            <div class="card-modern p-3 h-100">
                <div class="fw-bold" style="font-size:1.3rem;">{{ $disetujui }}</div>
                <div style="font-size:.78rem;color:var(--text-muted);">Pengajuan disetujui</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-modern p-3 h-100">
                <div class="fw-bold" style="font-size:1.3rem;">{{ $selesai }}</div>
                <div style="font-size:.78rem;color:var(--text-muted);">Pengajuan selesai</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-modern p-3 h-100">
                <div class="fw-bold" style="font-size:1.3rem;">{{ $total }}</div>
                <div style="font-size:.78rem;color:var(--text-muted);">Total riwayat Peminjaman</div>
            </div>
        </div>
        <div class="col-6 col-lg-3 d-flex">
            <a href="{{ route('peminjaman.index') }}" class="btn btn-primary w-100 h-100 d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-plus-lg"></i> Ajukan Peminjaman
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <div class="card-modern p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h3 class="font-display fw-semibold mb-0" style="font-size:1.05rem;">Pengajuan terbaru</h3>
                    <a href="{{ route('peminjaman.riwayat') }}" style="font-size:.78rem;">Lihat semua</a>
                </div>
                @if($terbaru->isEmpty())
                    <div class="text-center py-4" style="color:var(--text-muted);font-size:.85rem;">
                        <i class="bi bi-inbox fs-2 d-block mb-2" style="color:#c9bdb4;"></i>
                        Belum ada pengajuan peminjaman.
                    </div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($terbaru as $p)
                        <a href="{{ route('peminjaman.show', $p->id_peminjaman) }}" class="list-group-item list-group-item-action px-0 py-2 d-flex align-items-center gap-3">
                            <div class="min-w-0 flex-grow-1">
                                <div class="fw-semibold text-truncate" style="font-size:.86rem;">#{{ $p->id_peminjaman }} · {{ $p->ringkasan }}</div>
                                <div style="font-size:.73rem;color:var(--text-muted);">{{ $p->tanggal_pengajuan?->format('d/m/Y H:i') }}</div>
                            </div>
                            @include('peminjaman._status', ['status' => $p->status, 'terlambat' => $p->terlambat])
                        </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card-modern p-4 h-100">
                <h3 class="font-display fw-semibold mb-2" style="font-size:1.05rem;">Peminjaman terdekat</h3>
                @if($terdekat)
                    <div class="d-flex align-items-start gap-3">
                        <div style="width:44px;height:44px;border-radius:10px;background:var(--primary-soft);color:var(--ink);display:flex;align-items:center;justify-content:center;"><i class="bi bi-calendar-event fs-5"></i></div>
                        <div class="min-w-0">
                            <div class="fw-semibold" style="font-size:.9rem;">Peminjaman #{{ $terdekat->id_peminjaman }}</div>
                            <div style="font-size:.8rem;color:var(--text-muted);">Mulai {{ $terdekat->tanggal_peminjaman?->format('d/m/Y H:i') }} · Batas kembali {{ $terdekat->tanggal_rencana_kembali?->format('d/m/Y H:i') }}</div>
                            <div class="text-truncate" style="font-size:.8rem;" title="{{ $terdekat->ringkasan }}">{{ $terdekat->ringkasan }}</div>
                            <a href="{{ route('peminjaman.show', $terdekat->id_peminjaman) }}" class="btn btn-outline-silab mt-2 d-inline-flex align-items-center gap-1" style="font-size:.78rem;">
                                <i class="bi bi-eye"></i> Lihat Detail
                            </a>
                        </div>
                    </div>
                @else
                    <div class="text-center py-4" style="color:var(--text-muted);font-size:.85rem;">
                        <i class="bi bi-calendar-x fs-2 d-block mb-2" style="color:#c9bdb4;"></i>
                        Belum ada jadwal Peminjaman mendatang.
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif
@endsection
