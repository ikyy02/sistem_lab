@extends('layouts.app')

@section('title', 'Dashboard Staf Prodi')
@section('page-title', 'Dashboard Staf Prodi')
@section('page-subtitle', 'Ringkasan data akademik Program Studi.')

@php
    $hora = (int) date('H');
    $sapaan = $hora < 11 ? 'Selamat pagi' : ($hora < 15 ? 'Selamat siang' : ($hora < 18 ? 'Selamat sore' : 'Selamat malam'));
    $jamTeks = fn (?string $t) => str_replace(':', '.', substr((string) $t, 0, 5));
    $kartu = [
        ['icon' => 'bi-person-workspace', 'nilai' => $totalDosen, 'label' => 'Total Dosen', 'teks' => 'var(--gold)', 'latar' => 'var(--gold-tint)'],
        ['icon' => 'bi-journal-bookmark', 'nilai' => $totalMataKuliah, 'label' => 'Total Mata Kuliah', 'teks' => 'var(--ink)', 'latar' => 'var(--primary-soft)'],
        ['icon' => 'bi-calendar-week', 'nilai' => $totalJadwal, 'label' => 'Total Jadwal Perkuliahan', 'teks' => 'var(--gold)', 'latar' => 'var(--gold-tint)'],
    ];
@endphp

@section('content')

{{-- Header sapaan --}}
<div class="mb-4">
    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
        <h2 class="font-display fw-semibold mb-0" style="font-size:1.7rem;">Dashboard Staf Prodi</h2>
    </div>
    <p class="mb-1" style="color:var(--text-muted); font-size:.9rem;">
        {{ $sapaan }}, {{ $me['nama'] }}. Selamat datang kembali.
    </p>
</div>

{{-- Kartu statistik --}}
<div class="row g-3 mb-4">
    @foreach($kartu as $k)
    <div class="col-12 col-sm-6 col-lg-4">
        <div class="card-modern p-4 h-100">
            <div class="d-flex align-items-center gap-3">
                <div style="width:44px;height:44px;border-radius:10px;background:{{ $k['latar'] }};color:{{ $k['teks'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi {{ $k['icon'] }} fs-5"></i>
                </div>
                <div class="min-w-0">
                    <div class="fw-bold" style="font-size:1.5rem;line-height:1.2;">{{ number_format($k['nilai']) }}</div>
                    <div style="font-size:.78rem;color:var(--text-muted);">{{ $k['label'] }}</div>
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
                    <i class="bi bi-calendar-week me-2" style="color:var(--gold);"></i>Jadwal Perkuliahan
                </h3>
                <a href="{{ route('jadwal.index') }}" class="btn btn-outline-silab btn-sm d-inline-flex align-items-center gap-1">
                    Lihat Semua Jadwal <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            @forelse($jadwal as $j)
            <div class="d-flex gap-3 py-3" style="{{ $loop->first ? '' : 'border-top:1px solid #EDF1F6;' }}">
                <div class="text-center flex-shrink-0" style="width:92px;">
                    <div class="fw-bold" style="font-size:.92rem;">{{ $jamTeks($j->jam_mulai) }} - {{ $jamTeks($j->jam_selesai) }}</div>
                    <div style="font-size:.72rem;color:var(--text-muted);text-transform:capitalize;">{{ $j->hari }}{{ $j->hari === $hariIni ? ' (hari ini)' : '' }}</div>
                </div>
                <div class="min-w-0 flex-grow-1">
                    <div class="fw-semibold" style="font-size:.95rem;">
                        {{ $j->mataKuliah->nama_mk ?? 'Tanpa mata kuliah' }}
                        @if($j->status === 'dibatalkan')
                            <span class="badge-status badge-status-bad ms-1" style="font-size:.65rem;">Dibatalkan</span>
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
                <h3 class="font-display fw-semibold mb-0" style="font-size:1.05rem;">
                    <i class="bi bi-journal-bookmark me-2" style="color:var(--gold);"></i>Mata Kuliah
                </h3>
                <a href="{{ route('data-akademik.index', ['tab' => 'mata-kuliah']) }}" class="btn btn-outline-silab btn-sm d-inline-flex align-items-center gap-1">
                    Lihat Semua <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            @forelse($mataKuliah as $mk)
            <div class="d-flex align-items-center gap-3 py-2" style="{{ $loop->first ? '' : 'border-top:1px solid #EDF1F6;' }}">
                <span class="badge-status badge-tab flex-shrink-0" style="font-size:.7rem;">{{ $mk->kode_mk }}</span>
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
            <div class="d-flex align-items-center gap-3 mb-3">
                <div style="width:40px;height:40px;border-radius:10px;background:var(--gold-tint);color:var(--gold);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-person-workspace"></i>
                </div>
                <div>
                    <h3 class="font-display fw-semibold mb-0" style="font-size:1.05rem;">Data Dosen</h3>
                    <div style="font-size:.76rem;color:var(--text-muted);">Total {{ number_format($totalDosen) }} dosen terdaftar</div>
                </div>
            </div>

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
