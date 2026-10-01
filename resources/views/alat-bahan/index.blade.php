@extends('layouts.app')

@section('title', 'Katalog')
@section('page-title', 'Katalog')
@section('page-subtitle', 'Alat, bahan, dan ruangan laboratorium Jurusan Komputer dan Bisnis.')

@section('content')
@php
    $isAdmin = (\App\Services\AuthService::user()['role'] ?? null) === \App\Support\Role::LABORAN;
    $jenisLabel = ['alat' => 'Alat', 'bahan' => 'Bahan', 'ruangan' => 'Ruangan'];
    $jenisIcon = ['alat' => 'bi-tools', 'bahan' => 'bi-droplet', 'ruangan' => 'bi-door-open'];
    $sortLabel = ['nama' => 'Nama', 'stok' => 'Stok/Kapasitas', 'kondisi' => 'Kondisi'];
@endphp

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="font-display fw-semibold mb-1" style="font-size:1.5rem;">Katalog</h2>
        <p class="mb-0" style="font-size:.83rem;color:var(--text-muted);">Daftar alat, bahan, dan ruangan yang tersedia.</p>
    </div>
    @if($isAdmin)
        <a href="{{ route('inventaris.index') }}" class="btn btn-primary d-flex align-items-center gap-2"><i class="bi bi-box-seam"></i> Kelola Katalog</a>
    @endif
</div>

<form method="GET" action="{{ route('katalog') }}" class="card-modern p-3 mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-lg-4">
            <label class="form-label" for="search">Pencarian</label>
            <div class="input-group">
                <span class="input-group-text bg-white" style="border-color:var(--border-color);"><i class="bi bi-search"></i></span>
                <input type="text" id="search" name="search" value="{{ $search }}" maxlength="100" class="form-control" placeholder="Cari nama, satuan, kondisi, keterangan...">
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <label class="form-label" for="jenisSelect">Jenis</label>
            <select id="jenisSelect" name="jenis" class="form-select">
                <option value="" @selected(! $jenis)>Semua Jenis</option>
                @foreach($jenisLabel as $key => $text)<option value="{{ $key }}" @selected($jenis === $key)>{{ $text }}</option>@endforeach
            </select>
        </div>
        <div class="col-6 col-lg-2">
            <label class="form-label" for="sort">Urutkan</label>
            <select id="sort" name="sort" class="form-select">
                @foreach($sortLabel as $key => $text)<option value="{{ $key }}" @selected($sort === $key)>{{ $text }}</option>@endforeach
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
            <a href="{{ route('katalog') }}" class="btn btn-outline-silab" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
        </div>
    </div>
</form>

@if($data->isEmpty())
    <div class="card-modern text-center py-5" style="color:var(--text-muted);">
        <i class="bi bi-inbox fs-1 d-block mb-2" style="color:#c9bdb4;"></i>
        {{ $search !== '' ? 'Tidak ada data yang cocok dengan “' . $search . '”.' : 'Belum ada data.' }}
    </div>
@else
    <div class="row g-3">
        @foreach($data as $item)
            @php $bad = in_array($item->kondisi, ['Rusak', 'Hilang', 'Kadaluarsa', 'Tidak Tersedia'], true); @endphp
            <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                <div class="card-modern h-100 overflow-hidden d-flex flex-column">
                    <div style="height:150px;background:var(--body-bg);display:flex;align-items:center;justify-content:center;border-bottom:1px solid var(--border-color);">
                        @if($item->gambar_url)
                            <img src="{{ $item->gambar_url }}" alt="{{ $item->nama }}" style="width:100%;height:100%;object-fit:cover;cursor:zoom-in;" loading="lazy" onclick="previewImage(this.src, this.alt)">
                        @else
                            <i class="bi {{ $jenisIcon[$item->jenis] ?? 'bi-box' }}" style="font-size:2.2rem;color:var(--muted-blue);"></i>
                        @endif
                    </div>
                    <div class="p-3 d-flex flex-column flex-grow-1">
                        <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                            <span class="fw-semibold" style="font-size:.95rem;">{{ $item->nama }}</span>
                            <span class="badge badge-tab text-nowrap" style="font-weight:600;padding:4px 10px;font-size:.7rem;">{{ $jenisLabel[$item->jenis] ?? $item->jenis }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-2" style="font-size:.82rem;color:var(--text-muted);">
                            <span>{{ $item->jenis === 'ruangan' ? 'Kapasitas: ' . $item->stok : $item->stok . ' ' . ($item->satuan ?: '') }}</span>
                        </div>
                        @if($item->kondisi)
                            <span class="badge-status {{ $bad ? 'badge-status-bad' : 'badge-status-ok' }} align-self-start mb-2">{{ $item->kondisi }}</span>
                        @endif
                        <p class="mb-0 mt-auto" style="font-size:.8rem;color:var(--text-muted);line-height:1.5;" title="{{ $item->keterangan }}">
                            {{ $item->keterangan ? \Illuminate\Support\Str::limit($item->keterangan, 90) : '—' }}
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="table-footer mt-3" style="border:1px solid var(--border-color);border-radius:10px;background:var(--card-bg);">
        <div>{{ $data->total() > 0 ? 'Menampilkan ' . $data->firstItem() . '–' . $data->lastItem() . ' dari ' . $data->total() . ' data' : '0 data' }}</div>
        {{ $data->links('pagination.silab') }}
    </div>
@endif

@include('inventaris._preview')
@endsection
