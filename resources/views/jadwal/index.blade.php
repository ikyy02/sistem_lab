@extends('layouts.app')

@section('title', 'Jadwal Perkuliahan')
@section('page-title', 'Jadwal Perkuliahan')
@section('page-subtitle', 'Jadwal kuliah per kelas, dosen, dan ruangan. Bentrok ruangan/hari/jam diperiksa otomatis.')

@php
    $hariLabel = ['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat', 'sabtu' => 'Sabtu'];
    $jamTeks = fn (?string $t) => str_replace(':', '.', substr((string) $t, 0, 5));
@endphp

@section('content')

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="font-display fw-semibold mb-1" style="font-size:1.5rem;">Jadwal Perkuliahan</h2>
        <p class="mb-0" style="font-size:.83rem;color:var(--text-muted);">Kelola jadwal kuliah: tambah, lihat detail, ubah, dan hapus.</p>
    </div>
    <a href="{{ route('jadwal.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
        <i class="bi bi-plus-lg"></i> Tambah Jadwal
    </a>
</div>

{{-- Filter --}}
<form method="GET" action="{{ route('jadwal.index') }}" class="card-modern p-3 mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-lg-4">
            <label class="form-label" for="search">Pencarian</label>
            <div class="input-group">
                <span class="input-group-text bg-white" style="border-color:var(--border-color);"><i class="bi bi-search"></i></span>
                <input type="text" id="search" name="search" value="{{ $search }}" maxlength="100" class="form-control"
                       placeholder="Mata kuliah, dosen, ruangan, kelas...">
            </div>
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
            <label class="form-label" for="status">Status</label>
            <select id="status" name="status" class="form-select">
                <option value="">Semua status</option>
                <option value="aktif" @selected($status === 'aktif')>Aktif</option>
                <option value="dibatalkan" @selected($status === 'dibatalkan')>Dibatalkan</option>
            </select>
        </div>
        <div class="col-6 col-lg-1">
            <label class="form-label" for="per_page">Tampil</label>
            <select id="per_page" name="per_page" class="form-select">
                @foreach($perPageOptions as $n)<option value="{{ $n }}" @selected($perPage === $n)>{{ $n }}</option>@endforeach
            </select>
        </div>
        <div class="col-6 col-lg-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1">Terapkan</button>
            <a href="{{ route('jadwal.index') }}" class="btn btn-outline-silab" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
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
                    <th>Status</th>
                    <th class="text-end pe-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($data as $j)
                <tr>
                    <td class="ps-4 text-muted">{{ $data->firstItem() + $loop->index }}</td>
                    <td>
                        <div class="fw-semibold text-capitalize">{{ $j->hari }}</div>
                        <div style="font-size:.78rem;color:var(--text-muted);">{{ $jamTeks($j->jam_mulai) }} - {{ $jamTeks($j->jam_selesai) }}</div>
                        <div style="font-size:.72rem;color:var(--text-muted);">{{ $j->tahun_akademik }} · {{ ucfirst($j->semester) }}</div>
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $j->mataKuliah->nama_mk ?? '—' }}</div>
                        <div style="font-size:.78rem;color:var(--text-muted);">{{ $j->mataKuliah->kode_mk ?? 'Tanpa mata kuliah' }}@if($j->mataKuliah) · {{ $j->mataKuliah->sks }} SKS @endif</div>
                    </td>
                    <td>{{ $j->kelas->nama_kelas ?? '—' }}</td>
                    <td>
                        <div>{{ $j->dosen->nama ?? '—' }}</div>
                        <div style="font-size:.75rem;color:var(--text-muted);">{{ $j->nuptk_nidn }}</div>
                    </td>
                    <td>{{ $j->ruangan->nama_ruangan ?? '—' }}</td>
                    <td>
                        <span class="badge-status {{ $j->status === 'aktif' ? 'badge-status-ok' : 'badge-status-bad' }}">
                            {{ $j->status === 'aktif' ? 'Aktif' : 'Dibatalkan' }}
                        </span>
                    </td>
                    <td class="text-end pe-4">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('jadwal.show', $j) }}" class="btn btn-edit d-flex align-items-center gap-1" title="Lihat detail">
                                <i class="bi bi-eye"></i> Lihat
                            </a>
                            <a href="{{ route('jadwal.edit', $j) }}" class="btn btn-edit d-flex align-items-center gap-1" title="Ubah jadwal">
                                <i class="bi bi-pencil"></i> Ubah
                            </a>
                            <form action="{{ route('jadwal.destroy', $j) }}" method="POST"
                                  onsubmit="return confirm('Hapus jadwal {{ e($j->mataKuliah->nama_mk ?? '') }} pada {{ e($j->hari) }} {{ $jamTeks($j->jam_mulai) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-del d-flex align-items-center gap-1"><i class="bi bi-trash"></i> Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center py-5" style="color:var(--text-muted);">
                    <i class="bi bi-calendar-x fs-1 d-block mb-2" style="color:#c3cbdb;"></i>
                    {{ ($search !== '' || $hari !== '' || $status !== '') ? 'Tidak ada jadwal yang cocok dengan filter.' : 'Belum ada data jadwal perkuliahan.' }}
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
