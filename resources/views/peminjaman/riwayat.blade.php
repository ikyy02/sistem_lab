@extends('layouts.app')

@section('title', 'Riwayat Peminjaman')
@section('page-title', 'Riwayat Peminjaman')
@section('page-subtitle', 'Daftar pengajuan peminjaman beserta status prosesnya.')

@section('content')

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="font-display fw-semibold mb-1" style="font-size:1.5rem;">Riwayat Peminjaman</h2>
        <p class="mb-0" style="font-size:.83rem;color:var(--text-muted);">Semua pengajuan peminjaman yang pernah Anda ajukan.</p>
    </div>
    <a href="{{ route('peminjaman.index') }}" class="btn btn-primary d-flex align-items-center gap-2"><i class="bi bi-plus-lg"></i> Ajukan Peminjaman</a>
</div>

<form method="GET" action="{{ route('peminjaman.riwayat') }}" class="card-modern p-3 mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-6 col-lg-3">
            <label class="form-label" for="status">Status</label>
            <select id="status" name="status" class="form-select">
                <option value="" @selected(! $status)>Semua Status</option>
                @foreach($labels as $key => $text)<option value="{{ $key }}" @selected($status === $key)>{{ $text }}</option>@endforeach
            </select>
        </div>
        <div class="col-6 col-lg-2">
            <label class="form-label" for="per_page">Tampil</label>
            <select id="per_page" name="per_page" class="form-select">
                @foreach($perPageOptions as $n)<option value="{{ $n }}" @selected($perPage === $n)>{{ $n }}</option>@endforeach
            </select>
        </div>
        <div class="col-6 col-lg-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1">Terapkan</button>
            <a href="{{ route('peminjaman.riwayat') }}" class="btn btn-outline-silab" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
        </div>
    </div>
</form>

<div class="card-modern overflow-hidden">
    <div class="table-responsive">
        <table class="table table-silab mb-0">
            <thead>
                <tr>
                    <th class="ps-4" style="width:56px;">No</th>
                    <th style="width:150px;">Pengajuan</th>
                    <th>Barang / Ruangan</th>
                    <th style="width:80px;">Jumlah</th>
                    <th style="width:150px;">Peminjaman</th>
                    <th style="width:150px;">Pengembalian</th>
                    <th style="width:180px;">Status</th>
                    <th class="text-end pe-4" style="width:100px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($data as $p)
                <tr>
                    <td class="ps-4 text-muted">{{ $data->firstItem() + $loop->index }}</td>
                    <td>
                        <div class="fw-semibold">{{ $p->tanggal_pengajuan?->format('d/m/Y H:i') }}</div>
                        <div style="font-size:.75rem;color:var(--text-muted);">{{ $p->jenis_peminjaman === 'atas_dosen' ? 'Atas Dosen' : 'Pribadi' }}</div>
                    </td>
                    <td>
                        <span class="d-inline-block text-truncate align-middle" style="max-width:320px;" title="{{ $p->ringkasan }}">{{ $p->ringkasan }}</span>
                    </td>
                    <td>{{ $p->jumlah_barang }}</td>
                    <td style="font-size:.82rem;">{{ $p->tanggal_peminjaman?->format('d/m/Y H:i') }}</td>
                    <td style="font-size:.82rem;">{{ $p->tanggal_rencana_kembali?->format('d/m/Y H:i') }}</td>
                    <td>@include('peminjaman._status', ['status' => $p->status, 'terlambat' => $p->terlambat])</td>
                    <td class="text-end pe-4">
                        <a href="{{ route('peminjaman.show', $p->id_peminjaman) }}" class="btn btn-outline-silab d-inline-flex align-items-center gap-1">
                            <i class="bi bi-eye"></i> Detail
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">
                        <div class="text-center py-5" style="color:var(--text-muted);">
                            <i class="bi bi-inbox fs-1 d-block mb-2" style="color:#c9bdb4;"></i>
                            <div class="fw-semibold mb-1" style="font-size:.95rem;color:var(--text-dark);">Belum ada riwayat peminjaman</div>
                            <div style="font-size:.83rem;">Ajukan peminjaman pertama Anda untuk memulai.</div>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($data->hasPages())
    <div class="table-footer">
        <div>Menampilkan {{ $data->firstItem() }}–{{ $data->lastItem() }} dari {{ $data->total() }} data</div>
        {{ $data->links('pagination.silab') }}
    </div>
    @endif
</div>
@endsection
