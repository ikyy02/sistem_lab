@extends('layouts.app')

@section('title', 'Periode Jadwal')
@section('page-title', 'Periode Jadwal')
@section('page-subtitle', 'Masa berlaku jadwal perkuliahan. Periode lama otomatis menjadi arsip.')

@section('content')
@php $tgl = fn ($d) => $d?->format('d/m/Y'); @endphp

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="font-display fw-semibold mb-1" style="font-size:1.5rem;">Periode Jadwal Perkuliahan</h2>
        <p class="mb-0" style="font-size:.83rem;color:var(--text-muted);">Perbarui periode setiap pergantian periode atau setengah semester. Jadwal periode sebelumnya disimpan sebagai arsip dan tidak dapat diubah.</p>
    </div>
    <a href="{{ route('jadwal.index') }}" class="btn btn-outline-silab d-flex align-items-center gap-2"><i class="bi bi-arrow-left"></i> Jadwal Perkuliahan</a>
</div>

@if($errors->any())
    <div class="alert alert-danger mb-3"><ul class="mb-0 ps-3">@foreach($errors->all() as $pesan)<li>{{ $pesan }}</li>@endforeach</ul></div>
@endif

<div class="row g-3 mb-3">
    @if($berjalan)
    <div class="col-12 col-lg-6">
        <form method="POST" action="{{ route('jadwal.periode.update', $berjalan->id_periode) }}" class="card-modern p-4 h-100">
            @csrf @method('PUT')
            <h5 class="fw-bold mb-3" style="font-size:1rem;">Periode Berjalan</h5>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label" for="u_nama">Nama Periode <span class="text-danger">*</span></label>
                    <input type="text" id="u_nama" name="nama" value="{{ old('nama', $berjalan->nama) }}" maxlength="100" class="form-control">
                </div>
                <div class="col-6">
                    <label class="form-label" for="u_mulai">Tanggal Mulai <span class="text-danger">*</span></label>
                    <input type="date" id="u_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai', $berjalan->tanggal_mulai->format('Y-m-d')) }}" class="form-control">
                </div>
                <div class="col-6">
                    <label class="form-label" for="u_akhir">Tanggal Berakhir <span class="text-danger">*</span></label>
                    <input type="date" id="u_akhir" name="tanggal_berakhir" value="{{ old('tanggal_berakhir', $berjalan->tanggal_berakhir->format('Y-m-d')) }}" class="form-control">
                </div>
            </div>
            <div class="mt-4"><button type="submit" class="btn btn-primary">Simpan Perubahan</button></div>
        </form>
    </div>
    @endif

    <div class="col-12 {{ $berjalan ? 'col-lg-6' : '' }}">
        <form method="POST" action="{{ route('jadwal.periode.store') }}" class="card-modern p-4 h-100"
              onsubmit="return confirm('Mulai periode baru? Periode berjalan akan diarsipkan dan jadwalnya tidak dapat diubah lagi.')">
            @csrf
            <h5 class="fw-bold mb-1" style="font-size:1rem;">Mulai Periode Baru</h5>
            <p class="mb-3" style="font-size:.8rem;color:var(--text-muted);">{{ $berjalan ? 'Periode berjalan saat ini akan diarsipkan.' : 'Belum ada periode berjalan.' }} Jadwal baru akan tercatat pada periode ini.</p>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label" for="b_nama">Nama Periode <span class="text-danger">*</span></label>
                    <input type="text" id="b_nama" name="nama" maxlength="100" class="form-control" placeholder="Contoh: Genap 2026/2027 - Paruh 1">
                </div>
                <div class="col-6">
                    <label class="form-label" for="b_mulai">Tanggal Mulai <span class="text-danger">*</span></label>
                    <input type="date" id="b_mulai" name="tanggal_mulai" class="form-control">
                </div>
                <div class="col-6">
                    <label class="form-label" for="b_akhir">Tanggal Berakhir <span class="text-danger">*</span></label>
                    <input type="date" id="b_akhir" name="tanggal_berakhir" class="form-control">
                </div>
            </div>
            <div class="mt-4"><button type="submit" class="btn btn-primary">Mulai Periode Baru</button></div>
        </form>
    </div>
</div>

<div class="card-modern overflow-hidden">
    <div class="table-responsive">
        <table class="table table-silab mb-0">
            <thead><tr><th class="ps-4" style="width:56px;">No</th><th>Periode</th><th>Berlaku</th><th>Jumlah Jadwal</th><th class="pe-4">Status</th></tr></thead>
            <tbody>
            @forelse($periode as $p)
                <tr>
                    <td class="ps-4 text-muted">{{ $loop->iteration }}</td>
                    <td class="fw-semibold">{{ $p->nama }}</td>
                    <td>{{ $tgl($p->tanggal_mulai) }} – {{ $tgl($p->tanggal_berakhir) }}</td>
                    <td><a href="{{ route('jadwal.index', ['periode' => $p->id_periode]) }}">{{ $p->jadwals_count }} jadwal</a></td>
                    <td class="pe-4">
                        @if($p->terarsip())
                            <span class="badge-status" style="background:#f1f5f9;color:#475569;">Arsip</span>
                        @else
                            <span class="badge-status badge-status-ok">Berjalan</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center py-5" style="color:var(--text-muted);"><i class="bi bi-calendar-x fs-1 d-block mb-2" style="color:#c9bdb4;"></i>Belum ada periode jadwal.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
