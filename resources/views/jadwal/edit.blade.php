@extends('layouts.app')

@section('title', 'Ubah Jadwal')
@section('page-title', 'Ubah Jadwal Perkuliahan')
@section('page-subtitle', 'Perbarui data jadwal. Bentrok ruangan, hari, dan jam diperiksa otomatis.')

@section('content')

<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb mb-0" style="font-size:.8rem;color:var(--text-muted);">
        <li class="breadcrumb-item">
            <a href="{{ route('jadwal.index') }}" style="color:var(--text-muted);text-decoration:none;">
                <i class="bi bi-calendar-week me-1"></i>Jadwal Perkuliahan
            </a>
        </li>
        <li class="breadcrumb-item active" style="color:var(--text-dark);font-weight:600;">Ubah Jadwal</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-12 col-lg-9 col-xl-8">
        <div class="card-modern overflow-hidden">
            <div class="px-4 py-3 d-flex align-items-center gap-3" style="border-bottom:1px solid var(--border-color);background:var(--body-bg);">
                <div style="width:38px;height:38px;background:var(--primary-soft);border-radius:10px;display:flex;align-items:center;justify-content:center;color:var(--ink);flex-shrink:0;">
                    <i class="bi bi-pencil-fill"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size:.95rem;">Ubah Jadwal Perkuliahan</div>
                    <div style="font-size:.75rem;color:var(--text-muted);">
                        {{ $jadwal->mataKuliah->nama_mk ?? 'Tanpa mata kuliah' }} · {{ $jadwal->kelas->nama_kelas ?? '—' }}
                    </div>
                </div>
            </div>

            <form action="{{ route('jadwal.update', $jadwal) }}" method="POST" class="p-4">
                @csrf
                @method('PUT')
                @include('jadwal._form', ['jadwal' => $jadwal])

                <hr style="border-color:#EDF1F6;margin:1.25rem 0;">
                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                        <i class="bi bi-save"></i> Simpan Perubahan
                    </button>
                    <a href="{{ route('jadwal.index') }}" class="btn btn-outline-silab d-flex align-items-center gap-2">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
