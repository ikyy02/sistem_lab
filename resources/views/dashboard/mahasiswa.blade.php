@extends('layouts.app')

@section('title', 'Beranda')
@section('page-title', 'Beranda')
@section('page-subtitle', 'Ringkasan laboratorium Jurusan Komputer dan Bisnis.')

@section('content')
@php
    $jam = (int) date('H');
    $sapaan = $jam < 11 ? 'Selamat pagi' : ($jam < 15 ? 'Selamat siang' : ($jam < 18 ? 'Selamat sore' : 'Selamat malam'));
    $labelHari = ['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat', 'sabtu' => 'Sabtu', 'minggu' => 'Minggu'];
    $labelSemester = ['ganjil' => 'Ganjil', 'genap' => 'Genap'];
    $warnaLab = ['buka' => ['#ecfdf3', '#146c43', 'bi-unlock'], 'tutup' => ['#f1f5f9', '#475569', 'bi-lock']];
@endphp

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="font-display fw-semibold mb-1" style="font-size:1.7rem;">{{ $sapaan }}, {{ explode(' ', $me['nama'])[0] }}.</h2>
        <p class="mb-0" style="color:var(--text-muted);font-size:.9rem;">Ringkasan aktivitas peminjaman dan jadwal kuliah Anda hari ini.</p>
    </div>
    @php $wl = $warnaLab[$lab['status']] ?? $warnaLab['tutup']; @endphp
    <span class="badge-status d-inline-flex align-items-center gap-2" style="background:{{ $wl[0] }};color:{{ $wl[1] }};">
        <i class="bi {{ $wl[2] }}"></i> Laboratorium {{ $lab['label'] }}
    </span>
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

{{-- Kartu statistik --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xxl-2">
        <div class="card-modern p-3 h-100">
            <div class="fw-bold" style="font-size:1.3rem;">{{ $ringkasan['menunggu'] }}</div>
            <div style="font-size:.78rem;color:var(--text-muted);">Menunggu persetujuan</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xxl-2">
        <div class="card-modern p-3 h-100">
            <div class="fw-bold" style="font-size:1.3rem;">{{ $ringkasan['disetujui'] }}</div>
            <div style="font-size:.78rem;color:var(--text-muted);">Pengajuan disetujui</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xxl-2">
        <div class="card-modern p-3 h-100">
            <div class="fw-bold" style="font-size:1.3rem;">{{ $ringkasan['ditolak'] }}</div>
            <div style="font-size:.78rem;color:var(--text-muted);">Pengajuan ditolak</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xxl-2">
        <div class="card-modern p-3 h-100">
            <div class="fw-bold" style="font-size:1.3rem;">{{ $ringkasan['selesai'] }}</div>
            <div style="font-size:.78rem;color:var(--text-muted);">Pengajuan selesai</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xxl-2">
        <div class="card-modern p-3 h-100">
            <div class="fw-bold" style="font-size:1.3rem;">{{ $ringkasan['total'] }}</div>
            <div style="font-size:.78rem;color:var(--text-muted);">Total riwayat Peminjaman</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xxl-2 d-flex">
        <a href="{{ route('peminjaman.index') }}" class="btn btn-primary w-100 h-100 d-flex align-items-center justify-content-center gap-2">
            <i class="bi bi-plus-lg"></i> Ajukan Peminjaman
        </a>
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
                                <div style="font-size:.73rem;color:{{ $p->terlambat ? '#b42318' : 'var(--text-muted)' }};">{{ $waktu }}</div>
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
                            <div style="font-size:.73rem;color:var(--text-muted);">Diproses {{ $p->tanggal_persetujuan?->format('d/m/Y H:i') }}</div>
                            @if($p->status === 'ditolak' && $p->alasan_ditolak)
                                <div class="mt-1" style="font-size:.76rem;color:#b42318;">
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
    {{-- Jadwal kuliah hari ini dan minggu ini --}}
    <div class="col-12 col-xl-7">
        <div class="card-modern p-4 h-100">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <h3 class="font-display fw-semibold mb-0" style="font-size:1.05rem;">Jadwal kuliah hari ini dan minggu ini</h3>
                @if($keanggotaan->isNotEmpty())
                    <span class="badge-status" style="background:var(--primary-soft);color:var(--ink);">
                        {{ $keanggotaan->pluck('kelas.nama_kelas')->filter()->implode(', ') }} ·
                        {{ $keanggotaan->first()->tahun_akademik }} · {{ $labelSemester[$keanggotaan->first()->semester] ?? ucfirst($keanggotaan->first()->semester) }}
                    </span>
                @endif
            </div>

            @if($keanggotaan->isEmpty())
                <div class="text-center py-4" style="color:var(--text-muted);font-size:.85rem;">
                    <i class="bi bi-people fs-2 d-block mb-2" style="color:#c9bdb4;"></i>
                    <div class="fw-semibold mb-1" style="color:var(--text-dark);">Belum ada kelas</div>
                    Anda belum terdaftar pada kelas mana pun periode ini. Hubungi laboratorium untuk pengaturan kelas.
                </div>
            @elseif($jadwal->isEmpty())
                <div class="text-center py-4" style="color:var(--text-muted);font-size:.85rem;">
                    <i class="bi bi-calendar-x fs-2 d-block mb-2" style="color:#c9bdb4;"></i>
                    Tidak ada jadwal kuliah hari ini dan sisa minggu ini.
                </div>
            @else
                <div class="list-group list-group-flush">
                    @foreach($jadwal as $j)
                        <div class="list-group-item px-0 py-3 d-flex flex-wrap align-items-center gap-3">
                            <div style="width:46px;height:46px;border-radius:10px;background:var(--primary-soft);color:var(--ink);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="bi bi-mortarboard fs-5"></i>
                            </div>
                            <div class="min-w-0 flex-grow-1">
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <span class="fw-semibold" style="font-size:.88rem;">{{ $labelHari[$j->hari] ?? ucfirst($j->hari) }}</span>
                                    @if($j->hari === $hariIni)
                                        <span class="badge-status" style="background:#ecfdf3;color:#146c43;">Hari ini</span>
                                    @endif
                                    <span style="font-size:.78rem;color:var(--text-muted);">{{ substr($j->jam_mulai, 0, 5) }}–{{ substr($j->jam_selesai, 0, 5) }}</span>
                                </div>
                                <div class="text-truncate" style="font-size:.78rem;color:var(--text-muted);" title="{{ $j->ruangan?->nama_ruangan }} · {{ $j->dosen?->nama }}">
                                    {{ $j->ruangan?->nama_ruangan ?? '—' }} · {{ $j->dosen?->nama ?? '—' }} · {{ $j->kelas?->nama_kelas ?? '—' }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Pengajuan terbaru --}}
    <div class="col-12 col-xl-5">
        <div class="card-modern p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
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
</div>

<div class="row g-3">
    {{-- Status laboratorium --}}
    <div class="col-12 col-lg-6">
        <div class="card-modern p-4 h-100">
            <h3 class="font-display fw-semibold mb-3" style="font-size:1.05rem;">Status laboratorium hari ini</h3>
            <div class="d-flex align-items-start gap-3">
                <div style="width:44px;height:44px;border-radius:10px;background:{{ $wl[0] }};color:{{ $wl[1] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi {{ $wl[2] }} fs-5"></i>
                </div>
                <div class="min-w-0">
                    <div class="fw-bold" style="font-size:1.1rem;color:{{ $wl[1] }};">{{ $lab['label'] }}</div>
                    <div style="font-size:.82rem;color:var(--text-muted);">{{ $lab['keterangan'] }}</div>
                    <div style="font-size:.78rem;color:var(--text-muted);">
                        <i class="bi bi-clock me-1"></i>Jam operasional {{ $lab['jam'] }} · {{ $labelHari[$hariIni] ?? ucfirst($hariIni) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Aksi cepat --}}
    <div class="col-12 col-lg-6">
        <div class="card-modern p-4 h-100">
            <h3 class="font-display fw-semibold mb-3" style="font-size:1.05rem;">Aksi cepat</h3>
            <div class="row g-2">
                <div class="col-6">
                    <a href="{{ route('peminjaman.index') }}" class="btn btn-gold w-100 d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-plus-lg"></i> Ajukan Peminjaman
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ route('peminjaman.riwayat') }}" class="btn btn-outline-silab w-100 d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-clock-history"></i> Riwayat
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ route('katalog') }}" class="btn btn-outline-silab w-100 d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-collection"></i> Katalog
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ route('profil.index') }}" class="btn btn-outline-silab w-100 d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-person-circle"></i> Profil
                    </a>
                </div>
            </div>
            <div class="mt-3" style="font-size:.78rem;color:var(--text-muted);">
                <i class="bi bi-box-seam me-1"></i>{{ $katalogTersedia }} katalog tersedia disewakan/dipinjam hari ini.
            </div>
        </div>
    </div>
</div>
@endsection
