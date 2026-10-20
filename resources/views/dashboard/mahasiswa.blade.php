@extends('layouts.app')

@section('title', 'Beranda')
@section('page-title', 'Beranda')
@section('page-subtitle', 'Ringkasan laboratorium Jurusan Komputer dan Bisnis.')

@section('content')
@php
    $jam = (int) date('H');
    $sapaan = $jam < 11 ? 'Selamat pagi' : ($jam < 15 ? 'Selamat siang' : ($jam < 18 ? 'Selamat sore' : 'Selamat malam'));
    $labelHari = ['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat', 'sabtu' => 'Sabtu', 'minggu' => 'Minggu'];
    $warnaLab = ['buka' => ['#ecfdf3', '#146c43', 'bi-unlock'], 'tutup' => ['#f1f5f9', '#475569', 'bi-lock']];
@endphp

<div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
    @php $wl = $warnaLab[$lab['status']] ?? $warnaLab['tutup']; @endphp
    <div class="min-w-0">
        <h2 class="font-display fw-semibold mb-1" style="font-size:1.7rem;">{{ $sapaan }}, {{ explode(' ', $me['nama'])[0] }}.</h2>
        <div class="d-flex flex-wrap align-items-center gap-2" style="font-size:.85rem;color:var(--text-muted);">
            <span class="badge-status d-inline-flex align-items-center gap-2" style="background:{{ $wl[0] }};color:{{ $wl[1] }};">
                <i class="bi {{ $wl[2] }}"></i> Laboratorium {{ $lab['label'] }}
            </span>
            <span>{{ $lab['keterangan'] }} · Jam operasional {{ $lab['jam'] }} ({{ $labelHari[$hariIni] ?? ucfirst($hariIni) }})</span>
        </div>
    </div>
    <a href="{{ route('peminjaman.index') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
        <i class="bi bi-plus-lg"></i> Ajukan Peminjaman
    </a>
</div>

{{-- Peringatan: terlambat / mendekati jatuh tempo --}}
@if($ringkasan['terlambat'] > 0)
    <div class="alert alert-danger d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4" role="alert">
        <div><i class="bi bi-exclamation-triangle-fill me-1"></i>
            Ada <strong>{{ $ringkasan['terlambat'] }} peminjaman terlambat</strong> dikembalikan. Segera hubungi laboratorium.
        </div>
        <a href="{{ route('peminjaman.riwayat') }}" class="btn btn-outline-silab d-inline-flex align-items-center gap-1" style="font-size:.8rem;">
            <i class="bi bi-clock-history"></i> Lihat Riwayat
        </a>
    </div>
@elseif($ringkasan['dekat'] > 0)
    <div class="card-modern p-3 mb-4 d-flex flex-wrap align-items-center justify-content-between gap-2" style="background:var(--gold-tint);border-color:#EFE1C2;">
        <div style="font-size:.85rem;color:#6E6142;">
            <i class="bi bi-hourglass-split me-1"></i>
            <strong>{{ $ringkasan['dekat'] }} peminjaman</strong> mendekati jatuh tempo dalam 24 jam ke depan.
        </div>
        <a href="{{ route('peminjaman.riwayat') }}" class="btn btn-gold d-inline-flex align-items-center gap-2" style="font-size:.8rem;">
            <i class="bi bi-arrow-right"></i> Cek Riwayat
        </a>
    </div>
@endif

{{-- Ringkasan pengajuan --}}
<div class="row g-3 row-cols-2 row-cols-lg-5 mb-4">
    <div class="col">
        <div class="card-modern p-3 h-100">
            <div class="fw-bold" style="font-size:1.3rem;">{{ $ringkasan['menunggu'] }}</div>
            <div style="font-size:.8rem;color:var(--text-muted);">Menunggu persetujuan</div>
        </div>
    </div>
    <div class="col">
        <div class="card-modern p-3 h-100">
            <div class="fw-bold" style="font-size:1.3rem;">{{ $ringkasan['disetujui'] }}</div>
            <div style="font-size:.8rem;color:var(--text-muted);">Pengajuan disetujui</div>
        </div>
    </div>
    <div class="col">
        <div class="card-modern p-3 h-100">
            <div class="fw-bold" style="font-size:1.3rem;">{{ $ringkasan['ditolak'] }}</div>
            <div style="font-size:.8rem;color:var(--text-muted);">Pengajuan ditolak</div>
        </div>
    </div>
    <div class="col">
        <div class="card-modern p-3 h-100">
            <div class="fw-bold" style="font-size:1.3rem;">{{ $ringkasan['selesai'] }}</div>
            <div style="font-size:.8rem;color:var(--text-muted);">Pengajuan selesai</div>
        </div>
    </div>
    <div class="col">
        <div class="card-modern p-3 h-100">
            <div class="fw-bold" style="font-size:1.3rem;">{{ $ringkasan['total'] }}</div>
            <div style="font-size:.8rem;color:var(--text-muted);">Total riwayat Peminjaman</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    {{-- Harus dikembalikan --}}
    <div class="col-12 col-xl-7">
        <div class="card-modern p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h3 class="font-display fw-semibold mb-0" style="font-size:1.05rem;">Harus dikembalikan</h3>
                <span style="font-size:.75rem;color:var(--text-muted);">{{ $harusDikembalikan->count() }} aktif</span>
            </div>
            @if($harusDikembalikan->isEmpty())
                <div class="text-center py-4" style="color:var(--text-muted);font-size:.85rem;">
                    <i class="bi bi-check-circle fs-2 d-block mb-2" style="color:#c9bdb4;"></i>
                    Tidak ada peminjaman yang harus dikembalikan.
                </div>
            @else
                <div class="list-group list-group-flush">
                    @foreach($harusDikembalikan as $p)
                        @php
                            $d = now()->diff($p->tanggal_rencana_kembali);
                            if ($p->terlambat) {
                                $waktu = $d->d > 0 ? "Terlambat {$d->d} hari" : ($d->h > 0 ? "Terlambat {$d->h} jam" : 'Terlambat kurang dari 1 jam');
                            } else {
                                $waktu = $d->d > 0 ? "Sisa {$d->d} hari" : ($d->h > 0 ? "Sisa {$d->h} jam" : 'Sisa kurang dari 1 jam');
                            }
                        @endphp
                        <a href="{{ route('peminjaman.show', $p->id_peminjaman) }}" class="list-group-item list-group-item-action px-0 py-3 d-flex flex-wrap align-items-center gap-3">
                            <div class="min-w-0 flex-grow-1">
                                <div class="fw-semibold text-truncate" style="font-size:.88rem;">#{{ $p->id_peminjaman }} · {{ $p->ringkasan }}</div>
                                <div style="font-size:.75rem;color:var(--text-muted);">
                                    Batas kembali <strong>{{ $p->tanggal_rencana_kembali?->format('d/m/Y H:i') }}</strong>
                                </div>
                            </div>
                            <div class="text-end">
                                <div>@include('peminjaman._status', ['status' => $p->status, 'terlambat' => $p->terlambat])</div>
                                <div style="font-size:.75rem;color:{{ $p->terlambat ? '#b42318' : 'var(--text-muted)' }};">{{ $waktu }}</div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Pembaruan terbaru (turunan dari peminjaman, tanpa tabel notifikasi) --}}
    <div class="col-12 col-xl-5">
        <div class="card-modern p-4 h-100">
            <h3 class="font-display fw-semibold mb-3" style="font-size:1.05rem;">Pembaruan terbaru</h3>
            @if($pembaruan->isEmpty())
                <div class="text-center py-4" style="color:var(--text-muted);font-size:.85rem;">
                    <i class="bi bi-bell fs-2 d-block mb-2" style="color:#c9bdb4;"></i>
                    Belum ada pengajuan yang diproses dalam 7 hari terakhir.
                </div>
            @else
                <div class="list-group list-group-flush">
                    @foreach($pembaruan as $p)
                        <div class="list-group-item px-0 py-3">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <a href="{{ route('peminjaman.show', $p->id_peminjaman) }}" class="fw-semibold text-truncate text-decoration-none" style="font-size:.86rem;">#{{ $p->id_peminjaman }} · {{ $p->ringkasan }}</a>
                                @include('peminjaman._status', ['status' => $p->status, 'terlambat' => $p->terlambat])
                            </div>
                            <div style="font-size:.75rem;color:var(--text-muted);">Diproses {{ $p->tanggal_persetujuan?->format('d/m/Y H:i') }}</div>
                            @if($p->status === 'ditolak' && $p->alasan_ditolak)
                                <div class="mt-1" style="font-size:.8rem;color:#b42318;">
                                    <i class="bi bi-x-octagon me-1"></i>{{ $p->alasan_ditolak }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    {{-- Pengajuan terbaru --}}
    <div class="col-12">
        <div class="card-modern p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h3 class="font-display fw-semibold mb-0" style="font-size:1.05rem;">Pengajuan terbaru</h3>
                <a href="{{ route('peminjaman.riwayat') }}" style="font-size:.8rem;">Lihat semua</a>
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
                                <div style="font-size:.75rem;color:var(--text-muted);">{{ $p->tanggal_pengajuan?->format('d/m/Y H:i') }}</div>
                            </div>
                            @include('peminjaman._status', ['status' => $p->status, 'terlambat' => $p->terlambat])
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
