@extends('layouts.app')

@section('title', 'Pengajuan Peminjaman')
@section('page-title', 'Pengajuan Peminjaman')
@section('page-subtitle', (\App\Services\AuthService::user()['role'] ?? null) === \App\Support\Role::DOSEN
    ? 'Setujui atau tolak pengajuan Kuliah/Praktikum yang ditujukan kepada Anda.'
    : 'Periksa, setujui, atau tolak pengajuan peminjaman oleh mahasiswa dan dosen.')

@push('styles')
<style>
    .pengajuan-summary { display:flex; align-items:center; gap:.75rem; border:1px solid var(--border); background:var(--bg-surface); }
    .pengajuan-summary-icon { display:inline-flex; width:2.5rem; height:2.5rem; flex:0 0 2.5rem; align-items:center; justify-content:center; border-radius:var(--radius-md); background:var(--bg-subtle); color:var(--primary); }
    .pengajuan-page {
        height: calc(100vh - var(--header-height) - 48px);
        height: calc(100dvh - var(--header-height) - 48px);
        min-height: 0;
        display: flex;
        flex-direction: column;
        gap: 12px;
        overflow: hidden;
    }
    .pengajuan-page > .pengajuan-summary,
    .pengajuan-page > .pengajuan-filters { flex: 0 0 auto; margin-bottom: 0 !important; }
    .pengajuan-page > .pengajuan-summary { padding: 10px 14px !important; }
    .pengajuan-page > .pengajuan-filters { padding: 12px !important; }
    .pengajuan-page > .pengajuan-card { flex: 1 1 auto; min-height: 0; display: flex; flex-direction: column; }
    .pengajuan-page .table-responsive { flex: 1 1 auto; min-height: 0; overflow-x: hidden; overflow-y: auto; }
    .pengajuan-table { width: 100%; table-layout: fixed; }
    .pengajuan-table th:nth-child(1), .pengajuan-table td:nth-child(1) { width: 4.5%; }
    .pengajuan-table th:nth-child(2), .pengajuan-table td:nth-child(2) { width: 12%; }
    .pengajuan-table th:nth-child(3), .pengajuan-table td:nth-child(3) { width: 15%; }
    .pengajuan-table th:nth-child(4), .pengajuan-table td:nth-child(4) { width: 19%; }
    .pengajuan-table th:nth-child(5), .pengajuan-table td:nth-child(5) { width: 7%; }
    .pengajuan-table th:nth-child(6), .pengajuan-table td:nth-child(6) { width: 14%; }
    .pengajuan-table th:nth-child(7), .pengajuan-table td:nth-child(7) { width: 13%; }
    .pengajuan-table th:nth-child(8), .pengajuan-table td:nth-child(8) { width: 15.5%; }
    .pengajuan-table td { overflow-wrap: anywhere; }
    .pengajuan-table td:nth-child(4) .text-truncate { max-width: 100% !important; }
    .pengajuan-table .jumlah-cell,
    .pengajuan-table .jumlah-heading {
        padding-left: 10px !important;
        padding-right: 10px !important;
        text-align: center;
        white-space: nowrap;
    }
    .pengajuan-table .jumlah-cell { vertical-align: middle; font-variant-numeric: tabular-nums; }
    .pengajuan-table .aksi-control {
        width: 100px !important;
        min-width: 100px;
        max-width: 100px;
        height: 44px;
        min-height: 44px;
        flex: 0 0 100px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
        white-space: nowrap;
    }
    .pengajuan-table .btn-tolak { border:1px solid rgba(220,38,38,.32); background:var(--danger-soft); color:var(--danger); white-space:nowrap; }
    .pengajuan-table .btn-tolak:hover, .pengajuan-table .btn-tolak:focus-visible { border-color:var(--danger); background:var(--danger); color:#fff; }
</style>
@endpush

@section('content')
<div class="pengajuan-page">
@php
    $me = \App\Services\AuthService::user();
    $sebagaiDosen = $me['role'] === \App\Support\Role::DOSEN;
@endphp

<div class="pengajuan-summary card-modern p-3 mb-3">
    <span class="pengajuan-summary-icon" aria-hidden="true"><i class="bi bi-info-circle"></i></span>
    <p class="mb-0" style="font-size:var(--fs-base);color:var(--text-muted);">
    @if($sebagaiDosen)
        {{ $jumlahMenunggu }} pengajuan menunggu keputusan Anda. Yang Anda setujui diteruskan ke Laboran untuk keputusan akhir.
    @else
        {{ $jumlahMenunggu }} pengajuan menunggu persetujuan. Persetujuan memotong stok; penolakan tidak.
    @endif
    </p>
</div>

@if($errors->any())
    <div class="alert alert-danger mb-3" role="alert">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $pesan)<li>{{ $pesan }}</li>@endforeach
        </ul>
    </div>
@endif

<form method="GET" action="{{ route('pengajuan.index') }}" class="card-modern p-3 mb-3 pengajuan-filters">
    <div class="row g-2 align-items-end">
        <div class="col-6 col-lg-3">
            <label class="form-label" for="status">Status</label>
            <select id="status" name="status" class="form-select">
                @foreach($labels as $key => $text)<option value="{{ $key }}" @selected($status === $key)>{{ $text }}</option>@endforeach
            </select>
        </div>
        <div class="col-12 col-lg-4">
            <label class="form-label" for="search">Pencarian</label>
            <input id="search" name="search" type="search" value="{{ $search }}" maxlength="100"
                   class="form-control" placeholder="Nama, NIM, email, atau keperluan...">
        </div>
        <div class="col-6 col-lg-2">
            <label class="form-label" for="per_page">Tampil</label>
            <select id="per_page" name="per_page" class="form-select">
                @foreach($perPageOptions as $n)<option value="{{ $n }}" @selected($perPage === $n)>{{ $n }}</option>@endforeach
            </select>
        </div>
        <div class="col-6 col-lg-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-search me-1" aria-hidden="true"></i> Terapkan</button>
            <a href="{{ route('pengajuan.index') }}" class="btn btn-outline-silab" title="Reset" aria-label="Reset filter"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i></a>
        </div>
    </div>
</form>

<div class="card-modern overflow-hidden pengajuan-card">
    <div class="table-responsive">
        <table class="table table-silab mb-0 pengajuan-table">
<caption class="visually-hidden">{{ trim($__env->yieldContent('page-title')) }}</caption>
            <thead>
                <tr>
                    <th scope="col" class="ps-4">No</th>
                    <th scope="col">Tanggal</th>
                    <th scope="col" >Peminjam</th>
                    <th scope="col">Barang / Ruangan</th>
                    <th scope="col" class="jumlah-heading">Jumlah</th>
                    <th scope="col" >Status</th>
                    <th scope="col" class="text-end pe-4" >Aksi</th>
                </tr>
            </thead>
            <tbody>
            {{-- Tombol setujui/tolak ditampilkan sesuai kewenangan role dan tahap persetujuan. --}}
            @forelse($data as $i => $p)
                <tr>
                    <td class="ps-4 text-muted">{{ $data->firstItem() + $i }}</td>
                    <td style="font-size:var(--fs-base);">
                        <div>Ajukan: {{ $p->tanggal_pengajuan?->format('d/m/Y H:i') }}</div>
                        <div style="color:var(--text-muted);">Pinjam: {{ $p->tanggal_peminjaman?->format('d/m/Y') }} s/d {{ $p->tanggal_rencana_kembali?->format('d/m/Y H:i') }}</div>
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $p->nama_peminjam }}</div>
                        <div style="font-size:var(--fs-base);color:var(--text-muted);">{{ $p->identitas_peminjam }} · {{ $p->label_jenis }}</div>
                    </td>
                    <td>
                        <span class="d-inline-block text-truncate align-middle" style="max-width:300px;" title="{{ $p->ringkasan }}">{{ $p->ringkasan }}</span>
                        @if($p->keterangan)<div style="font-size:var(--fs-base);color:var(--text-muted);text-truncate;" title="{{ $p->keterangan }}">{{ $p->keterangan }}</div>@endif
                    </td>
                    <td class="jumlah-cell">{{ $p->jumlah_barang ?: '—' }}</td>
                    <td>
                        @include('peminjaman._status', ['status' => $p->status, 'terlambat' => $p->terlambat])
                        @if($p->menungguDosen())
                            <div style="font-size:var(--fs-base);color:var(--text-muted);">Menunggu keputusan Dosen</div>
                        @elseif($p->diteruskanKeLaboran())
                            <div style="font-size:var(--fs-base);color:var(--text-muted);">Disetujui Dosen, menunggu Laboran</div>
                        @endif
                        @if($p->status === 'ditolak' && $p->alasan_ditolak)
                            <div style="font-size:var(--fs-base);color:var(--text-muted);" title="{{ $p->alasan_ditolak }}">Alasan: {{ \Illuminate\Support\Str::limit($p->alasan_ditolak, 40) }}</div>
                        @endif
                    </td>
                    <td class="text-end pe-4">
                        <div class="d-flex flex-wrap justify-content-end gap-2">
                            @if($p->status === 'menunggu' && $p->bisaDiprosesOleh($me['email'], $me['role']))
                                <form action="{{ route('pengajuan.setujui', $p->id_peminjaman) }}" method="POST" class="d-inline"
                                      data-konfirmasi="Setujui pengajuan peminjaman #{{ $p->id_peminjaman }} oleh {{ $p->nama_peminjam }}? {{ $sebagaiDosen ? 'Pengajuan akan diteruskan ke Laboran untuk keputusan akhir.' : 'Stok barang akan dipotong sesuai jumlah yang diajukan.' }}">
                                    @csrf
                                    <button type="submit" class="btn btn-edit aksi-control d-flex align-items-center gap-1" style="padding:.3rem .65rem;font-size:var(--fs-base);">
                                        <i class="bi bi-check-lg" aria-hidden="true"></i><span class="visually-hidden">Setujui</span>
                                    </button>
                                </form>
                                <button type="button" class="btn btn-del btn-tolak aksi-control d-flex align-items-center gap-1" style="padding:.3rem .65rem;font-size:var(--fs-base);"
                                        data-bs-toggle="modal" data-bs-target="#tolakModal"
                                        data-url="{{ route('pengajuan.tolak', $p->id_peminjaman) }}"
                                        data-info="#{{ $p->id_peminjaman }} — {{ $p->nama_peminjam }}">
                                    <i class="bi bi-x-lg" aria-hidden="true"></i><span class="visually-hidden">Tolak</span>
                                </button>
                            @endif
                            <a href="{{ route('peminjaman.show', $p->id_peminjaman) }}" class="btn btn-outline-silab aksi-control d-flex align-items-center gap-1" style="padding:.3rem .65rem;font-size:var(--fs-base);">
                                <i class="bi bi-eye" aria-hidden="true"></i><span class="visually-hidden">Detail</span>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <div class="text-center py-5" style="color:var(--text-muted);">
                            <i class="bi bi-inbox fs-1 d-block mb-2" aria-hidden="true" style="color:#c9bdb4;"></i>
                            <div class="fw-semibold mb-1" style="font-size:var(--fs-base);color:var(--text-primary);">Tidak ada pengajuan</div>
                            <div style="font-size:var(--fs-base);">Belum ada pengajuan peminjaman dengan status "{{ $labels[$status] ?? $status }}".</div>
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

</div>
{{-- Modal penolakan --}}
<div class="modal fade" id="tolakModal" aria-labelledby="tolakModal-judul" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="tolakForm" action="" method="POST" class="modal-content"
              >
            @csrf
            <div class="modal-header">
                <h5 id="tolakModal-judul" class="modal-title fw-semibold" style="font-size:var(--fs-md);">Tolak Pengajuan Peminjaman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3" style="font-size:var(--fs-base);color:var(--text-muted);">
                    Pengajuan <strong id="tolakInfo"></strong> akan berstatus <strong>Ditolak</strong>. Stok barang tidak berubah.
                </p>
                <label class="form-label" for="alasan">Alasan Penolakan</label>
                <textarea id="alasan" name="alasan" rows="3" maxlength="500" required
                          class="form-control @error('alasan') is-invalid @enderror"
                          placeholder="Tuliskan alasan penolakan...">{{ old('alasan') }}</textarea>
                @error('alasan')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-silab" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-del d-flex align-items-center gap-2"><i class="bi bi-x-lg" aria-hidden="true"></i><span class="visually-hidden">Tolak</span> Pengajuan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var modal = document.getElementById('tolakModal');
        if (!modal) return;
        // Isi form tolak (action dan nama peminjam) dari tombol yang diklik sebelum modal tampil.
        modal.addEventListener('show.bs.modal', function (event) {
            var btn = event.relatedTarget;
            if (!btn) return;
            document.getElementById('tolakForm').action = btn.getAttribute('data-url');
            document.getElementById('tolakInfo').textContent = btn.getAttribute('data-info');
        });
    })();
</script>
@endpush
