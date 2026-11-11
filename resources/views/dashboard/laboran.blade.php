@extends('layouts.app')

@section('title', 'Beranda')
@section('page-title', 'Beranda')
@section('page-subtitle', 'Ringkasan operasional laboratorium')

@push('styles')
<style>
    .dash-stat { display: block; height: 100%; padding: 16px; color: var(--text-primary); text-decoration: none; transition: border-color 150ms var(--ease-standard), background-color 150ms var(--ease-standard); }
    .dash-stat:hover { border-color: var(--border-strong); background: var(--bg-subtle); color: var(--text-primary); }
    .dash-stat .k { display: flex; align-items: center; justify-content: space-between; gap: 8px; font-size: var(--fs-sm); color: var(--text-secondary); }
    .dash-stat .n { font-size: 28px; font-weight: 600; line-height: 1.2; margin-top: 4px; font-variant-numeric: tabular-nums; }
    .dash-stat .n.perlu { color: var(--brand); }
    .dash-stat .n.bahaya { color: var(--danger-text); }
    .dash-stat .n.nol { color: var(--text-muted); }
    .dash-list { margin: 0; padding: 0; list-style: none; }
    .dash-list a { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 10px 0; border-top: 1px solid var(--border); color: var(--text-primary); text-decoration: none; }
    .dash-list li:first-child a { border-top: 0; }
    .dash-list a:hover .judul { text-decoration: underline; }
    .dash-list .isi { min-width: 0; flex: 1 1 auto; }
    .dash-list .meta { font-size: var(--fs-xs); color: var(--text-secondary); }
    .dash-kosong { padding: 24px 0; text-align: center; color: var(--text-secondary); }
</style>
@endpush

@section('content')
@php
    $jam = (int) date('H');
    $sapaan = $jam < 11 ? 'Selamat pagi' : ($jam < 15 ? 'Selamat siang' : ($jam < 18 ? 'Selamat sore' : 'Selamat malam'));
@endphp

<div class="mb-4">
    <h2 class="greet mb-1">{{ $sapaan }}, <em>{{ explode(' ', $me['nama'])[0] }}</em>.</h2>
    <p class="mb-0" style="color:var(--text-secondary);">
        @if($menunggu > 0 || $jumlahTerlambat > 0)
            Ada pekerjaan yang menunggu tindakan Anda.
        @else
            Tidak ada antrean yang perlu ditangani saat ini.
        @endif
    </p>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <a href="{{ route('pengajuan.index') }}" class="card-modern dash-stat">
            <div class="k"><span>Pengajuan menunggu persetujuan</span><i class="bi bi-chevron-right" aria-hidden="true"></i></div>
            <div class="n {{ $menunggu > 0 ? 'perlu' : 'nol' }}">{{ $menunggu }}</div>
        </a>
    </div>
    <div class="col-6 col-xl-3">
        <a href="{{ route('pengembalian.index') }}" class="card-modern dash-stat">
            <div class="k"><span>Peminjaman sedang berlangsung</span><i class="bi bi-chevron-right" aria-hidden="true"></i></div>
            <div class="n {{ $berlangsung > 0 ? '' : 'nol' }}">{{ $berlangsung }}</div>
        </a>
    </div>
    <div class="col-6 col-xl-3">
        <a href="{{ route('pengembalian.index') }}" class="card-modern dash-stat">
            <div class="k"><span>Peminjaman terlambat</span><i class="bi bi-chevron-right" aria-hidden="true"></i></div>
            <div class="n {{ $jumlahTerlambat > 0 ? 'bahaya' : 'nol' }}">{{ $jumlahTerlambat }}</div>
        </a>
    </div>
    <div class="col-6 col-xl-3">
        <a href="{{ route('pengadaan-tpk.index') }}" class="card-modern dash-stat">
            <div class="k"><span>Stok menipis (2 atau kurang)</span><i class="bi bi-chevron-right" aria-hidden="true"></i></div>
            <div class="n {{ $stokMenipis > 0 ? 'bahaya' : 'nol' }}">{{ $stokMenipis }}</div>
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-6">
        <section class="card-modern p-3 p-md-4 h-100" aria-labelledby="h-antrean">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h3 class="h-card" id="h-antrean">Antrean persetujuan</h3>
                <a href="{{ route('pengajuan.index') }}" style="font-size:var(--fs-sm);">Lihat semua</a>
            </div>
            @if($antrean->isEmpty())
                <div class="dash-kosong">Tidak ada pengajuan yang menunggu.</div>
            @else
                <ul class="dash-list">
                    @foreach($antrean as $p)
                        <li>
                            <a href="{{ route('peminjaman.show', $p->id_peminjaman) }}">
                                <span class="isi">
                                    <span class="d-block fw-semibold text-truncate judul">#{{ $p->id_peminjaman }} &middot; {{ $p->nama_peminjam }}</span>
                                    <span class="d-block text-truncate meta">{{ $p->ringkasan }}</span>
                                </span>
                                <span class="meta text-nowrap">{{ $p->tanggal_pengajuan?->format('d/m/Y H:i') }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
    <div class="col-12 col-lg-6">
        <section class="card-modern p-3 p-md-4 h-100" aria-labelledby="h-terlambat">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h3 class="h-card" id="h-terlambat">Peminjaman terlambat</h3>
                <a href="{{ route('pengembalian.index') }}" style="font-size:var(--fs-sm);">Kelola peminjaman</a>
            </div>
            @if($daftarTerlambat->isEmpty())
                <div class="dash-kosong">Tidak ada peminjaman yang melewati batas kembali.</div>
            @else
                <ul class="dash-list">
                    @foreach($daftarTerlambat as $p)
                        <li>
                            <a href="{{ route('peminjaman.show', $p->id_peminjaman) }}">
                                <span class="isi">
                                    <span class="d-block fw-semibold text-truncate judul">#{{ $p->id_peminjaman }} &middot; {{ $p->nama_peminjam }}</span>
                                    <span class="d-block text-truncate meta">{{ $p->ringkasan }}</span>
                                </span>
                                <span class="meta text-nowrap" style="color:var(--danger-text);">Batas {{ $p->tanggal_rencana_kembali?->format('d/m/Y H:i') }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
</div>
@endsection
