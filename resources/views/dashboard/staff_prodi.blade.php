@extends('layouts.app')

@section('title', 'Dashboard Staf Prodi')
@section('page-title', 'Dashboard Staf Prodi')
@section('page-subtitle', 'Ringkasan data akademik Program Studi.')

@php
    $hora = (int) date('H');
    $sapaan = $hora < 11 ? 'Selamat pagi' : ($hora < 15 ? 'Selamat siang' : ($hora < 18 ? 'Selamat sore' : 'Selamat malam'));
    $jamTeks = fn (?string $t) => str_replace(':', '.', substr((string) $t, 0, 5));
    $kartu = [
        ['icon' => 'bi-person-workspace', 'nilai' => $totalDosen, 'label' => 'Total Dosen'],
        ['icon' => 'bi-journal-bookmark', 'nilai' => $totalMataKuliah, 'label' => 'Total Mata Kuliah'],
        ['icon' => 'bi-calendar-week', 'nilai' => $totalJadwal, 'label' => 'Total Jadwal Perkuliahan'],
    ];
@endphp

@section('content')

{{-- Header sapaan --}}
<div class="mb-4">
    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
        <h2 class="greet mb-0">Dashboard Staf Prodi</h2>
    </div>
    <p class="mb-1" style="color:var(--text-secondary); font-size:var(--fs-base);">
        {{ $sapaan }}, {{ $me['nama'] }}. Selamat datang kembali.
    </p>
</div>

{{-- Kartu statistik --}}
<div class="row g-3 mb-4">
    @foreach($kartu as $k)
    <div class="col-12 col-sm-6 col-lg-4">
        <div class="card-modern p-4 h-100">
            <div class="d-flex align-items-center gap-3">
                <div style="width:40px;height:40px;border-radius:6px;background:var(--bg-subtle);color:var(--primary);border:1px solid rgba(47,93,138,.18);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi {{ $k['icon'] }} fs-5" aria-hidden="true"></i>
                </div>
                <div class="min-w-0">
                    <div class="stat-num">{{ number_format($k['nilai']) }}</div>
                    <div style="font-size:var(--fs-xs);color:var(--text-secondary);">{{ $k['label'] }}</div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Jadwal Perkuliahan + Mata Kuliah --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-lg-7">
        <div class="card-modern p-4 h-100">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <h3 class="font-display fw-semibold mb-0" style="font-size:1.05rem;">
                    <i class="bi bi-calendar-week me-2" aria-hidden="true" style="color:var(--primary);"></i>Jadwal Perkuliahan
                </h3>
                <a href="{{ route('jadwal.index') }}" class="btn btn-outline-silab btn-sm d-inline-flex align-items-center gap-1">
                    Lihat Semua Jadwal <i class="bi bi-arrow-right" aria-hidden="true"></i>
                </a>
            </div>

            @forelse($jadwal as $j)
            <div class="d-flex gap-3 py-3" style="{{ $loop->first ? '' : 'border-top:1px solid #EDF1F6;' }}">
                <div class="text-center flex-shrink-0" style="width:92px;">
                    <div class="fw-bold" style="font-size:var(--fs-base);">{{ $jamTeks($j->jam_mulai) }} - {{ $jamTeks($j->jam_selesai) }}</div>
                    <div style="font-size:var(--fs-xs);color:var(--text-muted);text-transform:capitalize;">{{ $j->hari }}{{ $j->hari === $hariIni ? ' (hari ini)' : '' }}</div>
                </div>
                <div class="min-w-0 flex-grow-1">
                    <div class="fw-semibold" style="font-size:var(--fs-base);">
                        {{ $j->mataKuliah->nama_mk ?? 'Tanpa mata kuliah' }}
                        @if($j->status === 'dibatalkan')
                            <span class="badge-status badge-status-bad ms-1" style="font-size:var(--fs-xs);">Dibatalkan</span>
                        @endif
                    </div>
                    <div style="font-size:var(--fs-base);color:var(--text-muted);">
                        Dosen: {{ $j->dosen->nama ?? '—' }} · Ruang: {{ $j->ruangan->nama_ruangan ?? '—' }}
                    </div>
                    <div style="font-size:var(--fs-base);color:var(--text-muted);">
                        Kelas: {{ $j->kelas->nama_kelas ?? '—' }} · Semester {{ ucfirst($j->semester) }} {{ $j->tahun_akademik }}
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-4" style="color:var(--text-muted);font-size:var(--fs-base);">
                <i class="bi bi-calendar-x fs-2 d-block mb-2" aria-hidden="true" style="color:#c9c3bd;"></i>
                Belum ada data jadwal perkuliahan.
            </div>
            @endforelse
        </div>
    </div>

    <div class="col-12 col-lg-5">
        <div class="card-modern p-4 h-100">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <h3 class="font-display fw-semibold mb-0" style="font-size:1.05rem;">
                    <i class="bi bi-journal-bookmark me-2" aria-hidden="true" style="color:var(--primary);"></i>Mata Kuliah
                </h3>
                <a href="{{ route('data-akademik.index', ['tab' => 'mata-kuliah']) }}" class="btn btn-outline-silab btn-sm d-inline-flex align-items-center gap-1">
                    Lihat Semua <i class="bi bi-arrow-right" aria-hidden="true"></i>
                </a>
            </div>

            @forelse($mataKuliah as $mk)
            <div class="d-flex align-items-center gap-3 py-2" style="{{ $loop->first ? '' : 'border-top:1px solid #EDF1F6;' }}">
                <span class="badge-status badge-tab flex-shrink-0" style="font-size:var(--fs-xs);">{{ $mk->kode_mk }}</span>
                <div class="min-w-0 flex-grow-1">
                    <div class="fw-semibold text-truncate" style="font-size:var(--fs-base);">{{ $mk->nama_mk }}</div>
                    <div style="font-size:var(--fs-xs);color:var(--text-muted);">{{ $mk->sks }} SKS · Semester {{ ucfirst($mk->semester) }}</div>
                </div>
            </div>
            @empty
            <div class="text-center py-4" style="color:var(--text-muted);font-size:var(--fs-base);">
                <i class="bi bi-journal-x fs-2 d-block mb-2" aria-hidden="true" style="color:#c9c3bd;"></i>
                Belum ada data mata kuliah.
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Ringkasan Dosen --}}
<div class="row g-3">
    <div class="col-12">
        <div class="card-modern p-4">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div style="width:40px;height:40px;border-radius:6px;background:var(--bg-subtle);color:var(--primary);border:1px solid rgba(47,93,138,.18);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-person-workspace" aria-hidden="true"></i>
                </div>
                <div>
                    <h3 class="font-display fw-semibold mb-0" style="font-size:1.05rem;">Data Dosen</h3>
                    <div style="font-size:var(--fs-xs);color:var(--text-muted);">Total {{ number_format($totalDosen) }} dosen terdaftar</div>
                </div>
            </div>

            @if($dosenPerProdi->isEmpty())
                <div class="text-center py-3" style="color:var(--text-muted);font-size:var(--fs-base);">
                    <i class="bi bi-inbox fs-2 d-block mb-2" aria-hidden="true" style="color:#c9c3bd;"></i>
                    Belum ada data dosen.
                </div>
            @else
                <div class="row g-2">
                    @foreach($dosenPerProdi as $prodi => $jumlah)
                    <div class="col-12 col-md-4 d-flex align-items-center justify-content-between gap-3 py-2" style="border-top:1px solid #EDF1F6;">
                        <span style="font-size:var(--fs-base);">{{ $prodi }}</span>
                        <span class="badge-status badge-tab" style="font-size:var(--fs-xs);">{{ number_format($jumlah) }}</span>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
