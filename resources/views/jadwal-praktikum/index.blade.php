@extends('layouts.app')

@section('title', 'Jadwal Praktikum')
@section('page-title', 'Jadwal Praktikum')
@section('page-subtitle', 'Jadwal perkuliahan praktikum pada periode berjalan. Hanya dapat dilihat.')

@php
    $hariLabel = ['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat', 'sabtu' => 'Sabtu'];
    $jamTeks = fn (?string $t) => str_replace(':', '.', substr((string) $t, 0, 5));
@endphp

@section('content')

@if(! $berjalan && $periode === 'semua' && $data->isEmpty())
    <div class="alert alert-secondary mb-3">Belum ada jadwal praktikum yang dipublikasikan.</div>
@endif

<form method="GET" action="{{ route('jadwal-praktikum.index') }}" class="card-modern p-3 mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-lg-3">
            <label class="form-label" for="periode">Periode</label>
            <select id="periode" name="periode" class="form-select">
                @foreach($daftarPeriode as $pr)
                    <option value="{{ $pr->id_periode }}" @selected($periode === (string) $pr->id_periode)>{{ $pr->nama }}{{ $pr->terarsip() ? ' (Arsip)' : '' }}</option>
                @endforeach
                <option value="semua" @selected($periode === 'semua')>Semua periode</option>
            </select>
        </div>
        <div class="col-6 col-lg-2">
            <label class="form-label" for="hari">Hari</label>
            <select id="hari" name="hari" class="form-select">
                <option value="">Semua hari</option>
                @foreach($hariLabel as $nilai => $teks)
                    <option value="{{ $nilai }}" @selected($hari === $nilai)>{{ $teks }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-lg-2">
            <label class="form-label" for="per_page">Tampil</label>
            <select id="per_page" name="per_page" class="form-select">
                @foreach($perPageOptions as $n)<option value="{{ $n }}" @selected($perPage === $n)>{{ $n }}</option>@endforeach
            </select>
        </div>
        <div class="col-12 col-lg-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1">Terapkan</button>
            <a href="{{ route('jadwal-praktikum.index') }}" class="btn btn-outline-silab" title="Reset" aria-label="Reset filter"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i></a>
        </div>
    </div>
</form>

<div class="card-modern overflow-hidden">
    <div class="table-responsive">
        <table class="table table-silab mb-0">
            <thead>
                <tr>
                    <th class="ps-4" style="width:52px;">No</th>
                    <th>Hari &amp; Jam</th>
                    <th>Mata Kuliah</th>
                    <th>Kelas</th>
                    <th>Dosen</th>
                    <th>Ruangan</th>
                    <th class="pe-4">Status</th>
                </tr>
            </thead>
            <tbody>
            @forelse($data as $j)
                <tr>
                    <td class="ps-4 text-muted">{{ $data->firstItem() + $loop->index }}</td>
                    <td>
                        <div class="fw-semibold text-capitalize">{{ $j->hari }}</div>
                        <div style="font-size:.8rem;color:var(--text-muted);">{{ $jamTeks($j->jam_mulai) }} - {{ $jamTeks($j->jam_selesai) }}</div>
                        <div style="font-size:.8rem;color:var(--text-muted);">{{ $j->tahun_akademik }} · {{ ucfirst($j->semester) }}</div>
                        @if($j->periode)<div style="font-size:.8rem;color:var(--text-muted);">{{ $j->periode->nama }}</div>@endif
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $j->mataKuliah->nama_mk ?? '—' }}</div>
                        <div style="font-size:.8rem;color:var(--text-muted);">{{ $j->mataKuliah->kode_mk ?? 'Tanpa mata kuliah' }}@if($j->mataKuliah) · {{ $j->mataKuliah->sks }} SKS @endif</div>
                    </td>
                    <td>{{ $j->kelas->nama_kelas ?? '—' }}</td>
                    <td>
                        <div>{{ $j->dosen->nama ?? '—' }}</div>
                        <div style="font-size:.8rem;color:var(--text-muted);">{{ $j->nuptk_nidn }}</div>
                    </td>
                    <td>{{ $j->ruangan->nama_ruangan ?? '—' }}</td>
                    <td class="pe-4">
                        <span class="badge-status {{ $j->status === 'aktif' ? 'badge-status-ok' : 'badge-status-bad' }}">
                            {{ $j->status === 'aktif' ? 'Aktif' : 'Dibatalkan' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center py-5" style="color:var(--text-muted);">
                    <i class="bi bi-calendar-x fs-1 d-block mb-2" style="color:#c3cbdb;"></i>
                    Belum ada jadwal praktikum yang cocok dengan filter.
                </td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="table-footer">
        <div>
            {{ $data->total() > 0 ? 'Menampilkan ' . $data->firstItem() . '–' . $data->lastItem() . ' dari ' . $data->total() . ' jadwal' : '0 data' }}
        </div>
        {{ $data->links('pagination.silab') }}
    </div>
</div>

@endsection
