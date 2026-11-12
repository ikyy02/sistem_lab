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
    <h2 class="greet mb-1">{{ $sapaan }}, <em>{{ explode(' ', $me['nama'])[0] }}</em>.</h2>
    <p class="mb-0" style="color:var(--text-secondary);font-size:.875rem;">{{ $isAdmin ? 'Ringkasan pengajuan dan peminjaman yang perlu ditangani.' : 'Ringkasan pengajuan peminjaman Anda dan pengajuan yang perlu Anda proses.' }}</p>
</div>

@if($isAdmin)
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6">
            <a href="{{ route('pengajuan.index') }}" class="card-modern p-3 h-100 text-decoration-none d-block">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:42px;height:42px;border-radius:8px;background:var(--accent-soft);color:var(--accent);display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-hourglass-split fs-5"></i>
                    </div>
                    <div>
                        <div class="stat-num">{{ $menunggu }}</div>
                        <div style="font-size:.8rem;color:var(--text-secondary);">Pengajuan menunggu persetujuan</div>
                    </div>
                    <i class="bi bi-chevron-right ms-auto" style="color:var(--text-muted);"></i>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-6">
            <a href="{{ route('pengembalian.index') }}" class="card-modern p-3 h-100 text-decoration-none d-block">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:42px;height:42px;border-radius:8px;background:rgba(16,185,129,.1);color:#10b981;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-arrow-left-right fs-5"></i>
                    </div>
                    <div>
                        <div class="stat-num">{{ $berlangsung }}</div>
                        <div style="font-size:.8rem;color:var(--text-secondary);">Peminjaman sedang berlangsung</div>
                    </div>
                    <i class="bi bi-chevron-right ms-auto" style="color:var(--text-muted);"></i>
                </div>
            </a>
        </div>
    </div>
    <div class="card-modern p-3 d-flex align-items-center gap-2" style="background:var(--bg-subtle);border-left:3px solid var(--accent);">
        <i class="bi bi-info-circle" style="color:var(--accent);"></i>
        <div style="font-size:.82rem;color:var(--text-secondary);">
            Setujui atau tolak pengajuan dari menu <strong>Pengajuan Peminjaman</strong>; catat pengembalian barang dari menu <strong>Pengembalian</strong>.
        </div>
    </div>
@else
    @if(($menunggu ?? 0) > 0)
    <div class="card-modern p-3 mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3" style="background:var(--warning-bg);border:1px solid rgba(168,116,26,.2);border-left:4px solid var(--warning);">
        <div class="d-flex align-items-center gap-3">
            <div style="width:40px;height:40px;border-radius:8px;background:rgba(168,116,26,.15);color:var(--warning);display:flex;align-items:center;justify-content:center;">
                <i class="bi bi-hourglass-split fs-5"></i>
            </div>
            <div>
                <div class="fw-semibold" style="font-size:.9rem;color:var(--text-dark);">Menunggu Persetujuan Anda</div>
                <div style="font-size:.82rem;color:var(--text-secondary);">Ada <strong>{{ $menunggu }} pengajuan peminjaman</strong> mahasiswa yang perlu ditinjau.</div>
            </div>
        </div>
        <a href="{{ route('pengajuan.index') }}" class="btn btn-primary d-flex align-items-center gap-2" style="font-size:.84rem;">
            <i class="bi bi-clipboard2-check"></i> Tinjau Pengajuan
        </a>
    </div>
    @endif

    {{-- Kartu Ringkas Dosen: Bersih dan Fokus --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <a href="{{ route('pengajuan.index') }}" class="card-modern p-3 h-100 text-decoration-none d-block">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:42px;height:42px;border-radius:8px;background:var(--accent-soft);color:var(--accent);display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-clipboard-check fs-5"></i>
                    </div>
                    <div>
                        <div class="stat-num">{{ $menunggu ?? 0 }}</div>
                        <div style="font-size:.8rem;color:var(--text-secondary);">Perlu Persetujuan</div>
                    </div>
                    <i class="bi bi-chevron-right ms-auto" style="color:var(--text-muted);"></i>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-4">
            <a href="{{ route('peminjaman.saya') }}" class="card-modern p-3 h-100 text-decoration-none d-block">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:42px;height:42px;border-radius:8px;background:rgba(16,185,129,.1);color:#10b981;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-calendar-check fs-5"></i>
                    </div>
                    <div>
                        <div class="stat-num">{{ $disetujui }}</div>
                        <div style="font-size:.8rem;color:var(--text-secondary);">Peminjaman Disetujui</div>
                    </div>
                    <i class="bi bi-chevron-right ms-auto" style="color:var(--text-muted);"></i>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-4 d-flex">
            <a href="{{ route('peminjaman.index') }}" class="btn btn-primary w-100 h-100 p-3 d-flex align-items-center justify-content-center gap-2" style="font-size:.9rem;">
                <i class="bi bi-plus-lg fs-5"></i> Ajukan Peminjaman
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-7">
            <div class="card-modern p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h3 class="font-display fw-semibold mb-0" style="font-size:1.05rem;">Pengajuan terbaru</h3>
                        <div style="font-size:.78rem;color:var(--text-muted);">Aktivitas pengajuan pembelajaran Anda</div>
                    </div>
                    <a href="{{ route('peminjaman.saya') }}" class="btn btn-outline-silab btn-sm" style="font-size:.75rem;">Lihat Semua</a>
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
        <div class="col-12 col-lg-5">
            <div class="card-modern p-4 h-100">
                <div class="mb-3">
                    <h3 class="font-display fw-semibold mb-0" style="font-size:1.05rem;">Jadwal Terdekat</h3>
                    <div style="font-size:.78rem;color:var(--text-muted);">Peminjaman mendatang yang telah disetujui</div>
                </div>
                @if($terdekat)
                    <div class="p-3 rounded-3" style="background:var(--bg-subtle);border:1px solid var(--border);">
                        <div class="d-flex align-items-start gap-3">
                            <div style="width:40px;height:40px;border-radius:8px;background:var(--primary-soft);color:var(--ink);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="bi bi-calendar-event fs-5"></i>
                            </div>
                            <div class="min-w-0 flex-grow-1">
                                <div class="fw-semibold text-truncate" style="font-size:.9rem;">Peminjaman #{{ $terdekat->id_peminjaman }}</div>
                                <div class="mt-1" style="font-size:.8rem;color:var(--text-secondary);">
                                    <div><i class="bi bi-clock me-1"></i> {{ $terdekat->tanggal_peminjaman?->format('d/m/Y H:i') }}</div>
                                    <div><i class="bi bi-arrow-return-left me-1"></i> s.d. {{ $terdekat->tanggal_rencana_kembali?->format('d/m/Y H:i') }}</div>
                                </div>
                                <div class="text-truncate mt-2 fw-medium" style="font-size:.82rem;color:var(--text-dark);" title="{{ $terdekat->ringkasan }}">
                                    {{ $terdekat->ringkasan }}
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('peminjaman.show', $terdekat->id_peminjaman) }}" class="btn btn-outline-silab btn-sm d-inline-flex align-items-center gap-1" style="font-size:.78rem;">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-4" style="color:var(--text-muted);font-size:.85rem;">
                        <i class="bi bi-calendar-x fs-2 d-block mb-2" style="color:#c9bdb4;"></i>
                        Belum ada jadwal peminjaman mendatang.
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif
@endsection
