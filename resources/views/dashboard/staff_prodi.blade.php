@extends('layouts.app')

@section('title', 'Dashboard Staf Prodi')
@section('page-title', 'Dashboard Staf Prodi')
@section('page-subtitle', 'Ringkasan data akademik Program Studi.')

@php
    $hora = (int) date('H');
    $sapaan = $hora < 11 ? 'Selamat pagi' : ($hora < 15 ? 'Selamat siang' : ($hora < 18 ? 'Selamat sore' : 'Selamat malam'));
    $jamTeks = fn (?string $t) => str_replace(':', '.', substr((string) $t, 0, 5));
    $kartu = [
        ['icon' => 'bi-person-workspace', 'nilai' => $totalDosen, 'label' => 'Total Dosen', 'route' => route('data-akademik.index', ['tab' => 'dosen'])],
        ['icon' => 'bi-journal-bookmark', 'nilai' => $totalMataKuliah, 'label' => 'Total Mata Kuliah', 'route' => route('data-akademik.index', ['tab' => 'mata-kuliah'])],
        ['icon' => 'bi-calendar-week', 'nilai' => $totalJadwal, 'label' => 'Total Jadwal Perkuliahan', 'route' => route('jadwal.index')],
    ];
@endphp

@section('content')

{{-- Header sapaan ringkas --}}
<div class="mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
        <h2 class="greet mb-1">Dashboard Staf Prodi</h2>
        <p class="mb-0 text-muted" style="font-size:.875rem;">
            {{ $sapaan }}, <strong class="text-dark">{{ $me['nama'] }}</strong>. Berikut ringkasan aktivitas akademik program studi Anda.
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('jadwal.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2 shadow-sm">
            <i class="bi bi-plus-lg"></i> Tambah Jadwal
        </a>
    </div>
</div>

{{-- Kartu statistik interaktif --}}
<div class="row g-3 mb-4">
    @foreach($kartu as $k)
    <div class="col-12 col-sm-6 col-lg-4">
        <a href="{{ $k['route'] }}" class="text-decoration-none">
            <div class="card-modern p-4 h-100 transition-hover">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:44px;height:44px;border-radius:8px;background:var(--accent-soft);color:var(--accent);border:1px solid rgba(47,93,138,.18);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="bi {{ $k['icon'] }} fs-5"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="stat-num">{{ number_format($k['nilai']) }}</div>
                            <div style="font-size:.8rem;color:var(--text-primary);">{{ $k['label'] }}</div>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right text-muted" style="font-size:.85rem;"></i>
                </div>
            </div>
        </a>
    </div>
    @endforeach
</div>

{{-- Konten Utama: Jadwal Perkuliahan & Mata Kuliah --}}
<div class="row g-3">
    {{-- Kolom Kiri: Jadwal Perkuliahan --}}
    <div class="col-12 col-lg-7">
        <div class="card-modern p-4 h-100 d-flex flex-column">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <h3 class="font-display fw-semibold mb-0" style="font-size:1.05rem;">
                    <i class="bi bi-calendar-week me-2" style="color:var(--accent);"></i>Jadwal Perkuliahan
                </h3>
                <a href="{{ route('jadwal.index') }}" class="btn btn-outline-silab btn-sm d-inline-flex align-items-center gap-1">
                    Lihat Semua <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="flex-grow-1">
                @forelse($jadwal as $j)
                <div class="d-flex gap-3 py-3" style="{{ $loop->first ? '' : 'border-top:1px solid #EDF1F6;' }}">
                    <div class="text-center flex-shrink-0" style="width:96px;">
                        <div class="fw-bold" style="font-size:.9rem;color:var(--text-primary);">{{ $jamTeks($j->jam_mulai) }} - {{ $jamTeks($j->jam_selesai) }}</div>
                        <span class="badge rounded-pill text-uppercase px-2 py-0.5 {{ $j->hari === $hariIni ? 'bg-primary-subtle text-primary border' : 'bg-light text-muted border' }}" style="font-size:.7rem;">
                            {{ $j->hari }}{{ $j->hari === $hariIni ? ' (hari ini)' : '' }}
                        </span>
                    </div>
                    <div class="min-w-0 flex-grow-1">
                        <div class="fw-semibold" style="font-size:.92rem;color:var(--text-primary);">
                            {{ $j->mataKuliah->nama_mk ?? 'Tanpa mata kuliah' }}
                            @if($j->status === 'dibatalkan')
                                <span class="badge-status badge-status-bad ms-1" style="font-size:.65rem;">Dibatalkan</span>
                            @endif
                        </div>
                        <div style="font-size:.8rem;color:var(--text-muted);" class="mt-0.5">
                            <i class="bi bi-person me-1"></i>{{ $j->dosen->nama ?? '—' }} &nbsp;·&nbsp; <i class="bi bi-door-open me-1"></i>{{ $j->ruangan->nama_ruangan ?? '—' }}
                        </div>
                        <div style="font-size:.78rem;color:var(--text-secondary);" class="mt-0.5">
                            Kelas {{ $j->kelas->nama_kelas ?? '—' }} · Semester {{ ucfirst($j->semester) }} {{ $j->tahun_akademik }}
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5" style="color:var(--text-muted);font-size:.85rem;">
                    <i class="bi bi-calendar-x fs-1 d-block mb-2" style="color:#cbd5e1;"></i>
                    Belum ada data jadwal perkuliahan.
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Kolom Kanan: Mata Kuliah --}}
    <div class="col-12 col-lg-5">
        <div class="card-modern p-4 h-100 d-flex flex-column">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <h3 class="font-display fw-semibold mb-0" style="font-size:1.05rem;">
                    <i class="bi bi-journal-bookmark me-2" style="color:var(--accent);"></i>Mata Kuliah
                </h3>
                <a href="{{ route('data-akademik.index', ['tab' => 'mata-kuliah']) }}" class="btn btn-outline-silab btn-sm d-inline-flex align-items-center gap-1">
                    Lihat Semua <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="flex-grow-1">
                @forelse($mataKuliah as $mk)
                <div class="d-flex align-items-center gap-3 py-2.5" style="{{ $loop->first ? '' : 'border-top:1px solid #EDF1F6;' }}">
                    <span class="badge bg-light text-dark border flex-shrink-0 font-monospace" style="font-size:.75rem;">{{ $mk->kode_mk }}</span>
                    <div class="min-w-0 flex-grow-1">
                        <div class="fw-semibold text-truncate" style="font-size:.88rem;color:var(--text-primary);">{{ $mk->nama_mk }}</div>
                        <div style="font-size:.76rem;color:var(--text-muted);">{{ $mk->sks }} SKS · Semester {{ ucfirst($mk->semester) }}</div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5" style="color:var(--text-muted);font-size:.85rem;">
                    <i class="bi bi-journal-x fs-1 d-block mb-2" style="color:#cbd5e1;"></i>
                    Belum ada data mata kuliah.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection
