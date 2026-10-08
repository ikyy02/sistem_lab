@extends('layouts.app')

@section('title', 'Pengajuan Peminjaman')
@section('page-title', 'Pengajuan Peminjaman')
@section('page-subtitle', 'Periksa, setujui, atau tolak pengajuan peminjaman oleh mahasiswa dan dosen.')

@section('content')

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="font-display fw-semibold mb-1" style="font-size:1.5rem;">Pengajuan Peminjaman</h2>
        <p class="mb-0" style="font-size:.83rem;color:var(--text-muted);">
            {{ $jumlahMenunggu }} pengajuan menunggu persetujuan. Persetujuan memotong stok; penolakan tidak.
        </p>
    </div>
    <a href="{{ route('pengembalian.index') }}" class="btn btn-outline-silab d-flex align-items-center gap-2">
        <i class="bi bi-arrow-down-left-square"></i> Pengembalian
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger mb-3">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $pesan)<li>{{ $pesan }}</li>@endforeach
        </ul>
    </div>
@endif

<form method="GET" action="{{ route('pengajuan.index') }}" class="card-modern p-3 mb-3">
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
            <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-search me-1"></i> Terapkan</button>
            <a href="{{ route('pengajuan.index') }}" class="btn btn-outline-silab" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
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
                    <th style="width:230px;">Peminjam</th>
                    <th>Barang / Ruangan</th>
                    <th style="width:80px;">Jumlah</th>
                    <th style="width:160px;">Peminjaman</th>
                    <th style="width:170px;">Status</th>
                    <th class="text-end pe-4" style="width:230px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($data as $i => $p)
                <tr>
                    <td class="ps-4 text-muted">{{ $data->firstItem() + $i }}</td>
                    <td style="font-size:.82rem;">{{ $p->tanggal_pengajuan?->format('d/m/Y H:i') }}</td>
                    <td>
                        <div class="fw-semibold">{{ $p->nama_peminjam }}</div>
                        <div style="font-size:.75rem;color:var(--text-muted);">{{ $p->identitas_peminjam }} · {{ $p->jenis_peminjaman === 'atas_dosen' ? 'Atas Dosen' : 'Pribadi' }}</div>
                    </td>
                    <td>
                        <span class="d-inline-block text-truncate align-middle" style="max-width:300px;" title="{{ $p->ringkasan }}">{{ $p->ringkasan }}</span>
                        @if($p->keterangan)<div style="font-size:.75rem;color:var(--text-muted);text-truncate;" title="{{ $p->keterangan }}">{{ $p->keterangan }}</div>@endif
                    </td>
                    <td>{{ $p->jumlah_barang }}</td>
                    <td style="font-size:.8rem;">
                        {{ $p->tanggal_peminjaman?->format('d/m/Y') }}<br>
                        s/d {{ $p->tanggal_rencana_kembali?->format('d/m/Y H:i') }}
                    </td>
                    <td>
                        @include('peminjaman._status', ['status' => $p->status, 'terlambat' => $p->terlambat])
                        @if($p->status === 'ditolak' && $p->alasan_ditolak)
                            <div style="font-size:.72rem;color:var(--text-muted);" title="{{ $p->alasan_ditolak }}">Alasan: {{ \Illuminate\Support\Str::limit($p->alasan_ditolak, 40) }}</div>
                        @endif
                    </td>
                    <td class="text-end pe-4">
                        <div class="d-flex flex-wrap justify-content-end gap-2">
                            @if($p->status === 'menunggu')
                                <form action="{{ route('pengajuan.setujui', $p->id_peminjaman) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Setujui pengajuan peminjaman #{{ $p->id_peminjaman }} oleh {{ $p->nama_peminjam }}? Stok barang akan dipotong sesuai jumlah yang diajukan.')">
                                    @csrf
                                    <button type="submit" class="btn btn-edit d-flex align-items-center gap-1" style="padding:.3rem .65rem;font-size:.78rem;">
                                        <i class="bi bi-check-lg"></i> Setujui
                                    </button>
                                </form>
                                <button type="button" class="btn btn-del d-flex align-items-center gap-1" style="padding:.3rem .65rem;font-size:.78rem;"
                                        data-bs-toggle="modal" data-bs-target="#tolakModal"
                                        data-url="{{ route('pengajuan.tolak', $p->id_peminjaman) }}"
                                        data-info="#{{ $p->id_peminjaman }} — {{ $p->nama_peminjam }}">
                                    <i class="bi bi-x-lg"></i> Tolak
                                </button>
                            @endif
                            <a href="{{ route('peminjaman.show', $p->id_peminjaman) }}" class="btn btn-outline-silab d-flex align-items-center gap-1" style="padding:.3rem .65rem;font-size:.78rem;">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">
                        <div class="text-center py-5" style="color:var(--text-muted);">
                            <i class="bi bi-inbox fs-1 d-block mb-2" style="color:#c9bdb4;"></i>
                            <div class="fw-semibold mb-1" style="font-size:.95rem;color:var(--text-dark);">Tidak ada pengajuan</div>
                            <div style="font-size:.83rem;">Belum ada pengajuan peminjaman dengan status "{{ $labels[$status] ?? $status }}".</div>
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

{{-- Modal penolakan --}}
<div class="modal fade" id="tolakModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="tolakForm" action="" method="POST" class="modal-content"
              onsubmit="return confirm('Tolak pengajuan peminjaman ini? Stok barang tidak akan berubah.')">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" style="font-size:1rem;">Tolak Pengajuan Peminjaman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3" style="font-size:.85rem;color:var(--text-muted);">
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
                <button type="submit" class="btn btn-del d-flex align-items-center gap-2"><i class="bi bi-x-lg"></i> Tolak Pengajuan</button>
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
        modal.addEventListener('show.bs.modal', function (event) {
            var btn = event.relatedTarget;
            if (!btn) return;
            document.getElementById('tolakForm').action = btn.getAttribute('data-url');
            document.getElementById('tolakInfo').textContent = btn.getAttribute('data-info');
        });
    })();
</script>
@endpush
