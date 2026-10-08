@extends('layouts.app')

@section('title', 'Detail Jadwal')
@section('page-title', 'Detail Jadwal Perkuliahan')
@section('page-subtitle', 'Informasi lengkap satu jadwal perkuliahan.')

@php
    $jamTeks = fn (?string $t) => str_replace(':', '.', substr((string) $t, 0, 5));
@endphp

@section('content')

<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb mb-0" style="font-size:.8rem;color:var(--text-muted);">
        <li class="breadcrumb-item">
            <a href="{{ route('jadwal.index') }}" style="color:var(--text-muted);text-decoration:none;">
                <i class="bi bi-calendar-week me-1"></i>Jadwal Perkuliahan
            </a>
        </li>
        <li class="breadcrumb-item active" style="color:var(--text-dark);font-weight:600;">Detail Jadwal</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8 col-xl-7">
        <div class="card-modern overflow-hidden">
            <div class="px-4 py-3 d-flex flex-wrap align-items-center justify-content-between gap-2"
                 style="border-bottom:1px solid var(--border-color);background:var(--body-bg);">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:38px;height:38px;background:var(--gold-tint);border-radius:10px;display:flex;align-items:center;justify-content:center;color:var(--gold);flex-shrink:0;">
                        <i class="bi bi-calendar2-check-fill"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size:.95rem;">{{ $jadwal->mataKuliah->nama_mk ?? 'Tanpa Mata Kuliah' }}</div>
                        <div style="font-size:.75rem;color:var(--text-muted);">
                            {{ $jadwal->mataKuliah->kode_mk ?? '—' }} · {{ $jadwal->kelas->nama_kelas ?? '—' }}
                        </div>
                    </div>
                </div>
                <span class="badge-status {{ $jadwal->status === 'aktif' ? 'badge-status-ok' : 'badge-status-bad' }}">
                    {{ $jadwal->status === 'aktif' ? 'Aktif' : 'Dibatalkan' }}
                </span>
            </div>

            <div class="p-4">
                <div class="row g-3">
                    @php
                        $baris = [
                            ['icon' => 'bi-journal-bookmark', 'label' => 'Mata Kuliah', 'nilai' => ($jadwal->mataKuliah->nama_mk ?? '—') . ($jadwal->mataKuliah ? ' (' . $jadwal->mataKuliah->kode_mk . ' · ' . $jadwal->mataKuliah->sks . ' SKS)' : '')],
                            ['icon' => 'bi-people', 'label' => 'Kelas', 'nilai' => trim(($jadwal->kelas->nama_kelas ?? '—') . ($jadwal->kelas->prodi?->nama_prodi ? ' — ' . $jadwal->kelas->prodi->nama_prodi : ''))],
                            ['icon' => 'bi-person-workspace', 'label' => 'Dosen', 'nilai' => trim(($jadwal->dosen->nama ?? '—') . ($jadwal->dosen ? ' (' . $jadwal->nuptk_nidn . ')' : ''))],
                            ['icon' => 'bi-door-open', 'label' => 'Ruangan', 'nilai' => $jadwal->ruangan->nama_ruangan ?? '—'],
                            ['icon' => 'bi-calendar-day', 'label' => 'Hari', 'nilai' => ucfirst($jadwal->hari)],
                            ['icon' => 'bi-clock', 'label' => 'Jam', 'nilai' => $jamTeks($jadwal->jam_mulai) . ' - ' . $jamTeks($jadwal->jam_selesai)],
                            ['icon' => 'bi-mortarboard', 'label' => 'Tahun Akademik', 'nilai' => $jadwal->tahun_akademik],
                            ['icon' => 'bi-list-check', 'label' => 'Semester', 'nilai' => ucfirst($jadwal->semester)],
                        ];
                    @endphp
                    @foreach($baris as $b)
                    <div class="col-12 col-md-6">
                        <div class="p-3 h-100" style="background:var(--body-bg);border:1px solid var(--border-color);border-radius:10px;">
                            <div class="d-flex align-items-center gap-2 mb-1" style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                                <i class="bi {{ $b['icon'] }}"></i> {{ $b['label'] }}
                            </div>
                            <div class="fw-semibold" style="font-size:.9rem;">{{ $b['nilai'] }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="px-4 py-3 d-flex flex-wrap gap-2" style="border-top:1px solid var(--border-color);background:#FAFBFD;">
                <a href="{{ route('jadwal.edit', $jadwal) }}" class="btn btn-primary d-flex align-items-center gap-2">
                    <i class="bi bi-pencil"></i> Ubah Jadwal
                </a>
                <a href="{{ route('jadwal.index') }}" class="btn btn-outline-silab d-flex align-items-center gap-2">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                <form action="{{ route('jadwal.destroy', $jadwal) }}" method="POST" class="ms-auto"
                      onsubmit="return confirm('Hapus jadwal ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-del d-flex align-items-center gap-2"><i class="bi bi-trash"></i> Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
