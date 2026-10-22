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
    <h2 class="font-display fw-semibold mb-0" style="font-size:1.7rem;">{{ $sapaan }}, {{ $me['nama'] }}.</h2>
</div>

{{-- Ringkasan --}}
<div class="row g-3 mb-4">
    @foreach($kartu as $k)
    <div class="col-12 col-sm-4">
        <div class="card-modern p-3 h-100">
            <div class="fw-bold" style="font-size:1.3rem;">{{ number_format($k['nilai']) }}</div>
            <div style="font-size:.8rem;color:var(--text-muted);">{{ $k['label'] }}</div>
        </div>
    </div>
    @endforeach
</div>

{{-- Jadwal Perkuliahan + Mata Kuliah --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-lg-7">
        <div class="card-modern p-4 h-100">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <h3 class="font-display fw-semibold mb-0" style="font-size:1.05rem;">Jadwal Kuliah
                </h3>
                <a href="{{ route('jadwal.index') }}" style="font-size:.8rem;">
                    Lihat semua
                </a>
            </div>

            @forelse($jadwal as $j)
            <div class="d-flex gap-3 py-3" style="{{ $loop->first ? '' : 'border-top:1px solid #EDF1F6;' }}">
                <div class="text-center flex-shrink-0" style="width:92px;">
                    <div class="fw-bold" style="font-size:.92rem;">{{ $jamTeks($j->jam_mulai) }} - {{ $jamTeks($j->jam_selesai) }}</div>
                    <div style="font-size:.75rem;color:var(--text-muted);text-transform:capitalize;">{{ $j->hari }}{{ $j->hari === $hariIni ? ' (hari ini)' : '' }}</div>
                </div>
                <div class="min-w-0 flex-grow-1">
                    <div class="fw-semibold" style="font-size:.95rem;">
                        {{ $j->mataKuliah->nama_mk ?? 'Tanpa mata kuliah' }}
                        @if($j->status === 'dibatalkan')
                            <span class="badge-status badge-status-bad ms-1" style="font-size:.75rem;">Dibatalkan</span>
                        @endif
                    </div>
                    <div style="font-size:.8rem;color:var(--text-muted);">
                        Dosen: {{ $j->dosen->nama ?? '—' }} · Ruang: {{ $j->ruangan->nama_ruangan ?? '—' }}
                    </div>
                    <div style="font-size:.8rem;color:var(--text-muted);">
                        Kelas: {{ $j->kelas->nama_kelas ?? '—' }} · Semester {{ ucfirst($j->semester) }} {{ $j->tahun_akademik }}
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-4" style="color:var(--text-muted);font-size:.85rem;">
                <i class="bi bi-calendar-x fs-2 d-block mb-2" style="color:#c9c3bd;"></i>
                Belum ada data jadwal perkuliahan.
            </div>
            @endforelse
        </div>
    </div>

    <div class="col-12 col-lg-5">
        <div class="card-modern p-4 h-100">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <h3 class="font-display fw-semibold mb-0" style="font-size:1.05rem;">Mata Kuliah
                </h3>
                <a href="{{ route('data-akademik.index', ['tab' => 'mata-kuliah']) }}" style="font-size:.8rem;">
                    Lihat semua
                </a>
            </div>

            @forelse($mataKuliah as $mk)
            <div class="d-flex align-items-center gap-3 py-2" style="{{ $loop->first ? '' : 'border-top:1px solid #EDF1F6;' }}">
                <span class="badge-status badge-tab flex-shrink-0" style="font-size:.75rem;">{{ $mk->kode_mk }}</span>
                <div class="min-w-0 flex-grow-1">
                    <div class="fw-semibold text-truncate" style="font-size:.88rem;">{{ $mk->nama_mk }}</div>
                    <div style="font-size:.75rem;color:var(--text-muted);">{{ $mk->sks }} SKS · Semester {{ ucfirst($mk->semester) }}</div>
                </div>
            </div>
            @empty
            <div class="text-center py-4" style="color:var(--text-muted);font-size:.85rem;">
                <i class="bi bi-journal-x fs-2 d-block mb-2" style="color:#c9c3bd;"></i>
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
            <h3 class="font-display fw-semibold mb-3" style="font-size:1.05rem;">Dosen per program studi</h3>

            @if($dosenPerProdi->isEmpty())
                <div class="text-center py-3" style="color:var(--text-muted);font-size:.85rem;">
                    <i class="bi bi-inbox fs-2 d-block mb-2" style="color:#c9c3bd;"></i>
                    Belum ada data dosen.
                </div>
            @else
                <div class="row g-2">
                    @foreach($dosenPerProdi as $prodi => $jumlah)
                    <div class="col-12 col-md-4 d-flex align-items-center justify-content-between gap-3 py-2" style="border-top:1px solid #EDF1F6;">
                        <span style="font-size:.85rem;">{{ $prodi }}</span>
                        <span class="badge-status badge-tab" style="font-size:.75rem;">{{ number_format($jumlah) }}</span>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
