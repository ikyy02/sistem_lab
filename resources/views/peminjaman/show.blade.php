@extends('layouts.app')

@section('title', 'Detail Peminjaman #' . $p->id_peminjaman)
@section('page-title', 'Detail Peminjaman')
@section('page-subtitle', 'Transaksi peminjaman nomor #' . $p->id_peminjaman . '.')

@section('content')
@php $saya = \App\Services\AuthService::user(); @endphp

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="font-display fw-semibold mb-2" style="font-size:1.5rem;">Peminjaman #{{ $p->id_peminjaman }}</h2>
        @include('peminjaman._status', ['status' => $p->status, 'terlambat' => $p->terlambat])
    </div>
    <div class="d-flex flex-wrap gap-2">
        @if($p->email_peminjam === $saya['email'])
            <a href="{{ route('peminjaman.riwayat') }}" class="btn btn-outline-silab"><i class="bi bi-arrow-left me-1"></i> Riwayat Saya</a>
        @else
            <a href="{{ url()->previous() }}" class="btn btn-outline-silab"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
        @endif
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger mb-3">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $pesan)<li>{{ $pesan }}</li>@endforeach
        </ul>
    </div>
@endif

@if($dapatMemproses)
    <div class="card-modern p-4 mb-3" style="background:var(--gold-tint);border-color:#EFE1C2;">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div style="font-size:.85rem;color:#6E6142;">
                <i class="bi bi-exclamation-circle me-1"></i>
                Pengajuan ini <strong>menunggu persetujuan</strong>. Menyetujui akan memotong stok sesuai jumlah peminjaman; menolak tidak mengubah stok.
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-del d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#tolakModal">
                    <i class="bi bi-x-lg"></i> Tolak
                </button>
                <form action="{{ route('pengajuan.setujui', $p->id_peminjaman) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Setujui pengajuan peminjaman #{{ $p->id_peminjaman }}? Stok barang akan dipotong sesuai jumlah yang diajukan.')">
                    @csrf
                    <button type="submit" class="btn btn-primary d-flex align-items-center gap-2"><i class="bi bi-check-lg"></i> Setujui</button>
                </form>
            </div>
        </div>
    </div>
@endif

@if($dapatKembalikan)
    <div class="card-modern p-4 mb-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div style="font-size:.85rem;color:var(--text-muted);">
                <i class="bi bi-arrow-down-left-square me-1"></i>
                Peminjaman aktif. Catat pengembalian barang untuk mengembalikan stok.
            </div>
            <a href="{{ route('pengembalian.index') }}" class="btn btn-gold d-flex align-items-center gap-2"><i class="bi bi-box-arrow-in-down"></i> Catat Pengembalian</a>
        </div>
    </div>
@endif

<div class="row g-3">
    <div class="col-12 col-lg-6">
        <div class="card-modern p-4 h-100">
            <h3 class="font-display fw-semibold mb-3" style="font-size:1.05rem;">Data Peminjam</h3>
            <table class="table table-sm mb-0" style="font-size:.85rem;">
                <tbody>
                    <tr><td style="width:170px;color:var(--text-muted);">Nama</td><td class="fw-semibold">{{ $p->nama_peminjam }}</td></tr>
                    <tr><td style="color:var(--text-muted);">{{ $p->mahasiswaPeminjam ? 'NIM' : 'NIDN/ID' }}</td><td>{{ $p->identitas_peminjam }}</td></tr>
                    <tr><td style="color:var(--text-muted);">Email</td><td>{{ $p->email_peminjam }}</td></tr>
                    <tr><td style="color:var(--text-muted);">Jenis Peminjaman</td><td>{{ $p->jenis_peminjaman === 'atas_dosen' ? 'Atas Dosen' : 'Pribadi' }}</td></tr>
                    @if($p->email_dosen)
                        <tr><td style="color:var(--text-muted);">Dosen Penanggung Jawab</td><td>{{ $p->dosenPenanggung?->nama ?? $p->email_dosen }}</td></tr>
                    @endif
                    <tr><td style="color:var(--text-muted);">Keperluan</td><td>{{ $p->keterangan ?? '—' }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="card-modern p-4 h-100">
            <h3 class="font-display fw-semibold mb-3" style="font-size:1.05rem;">Waktu & Proses</h3>
            <table class="table table-sm mb-0" style="font-size:.85rem;">
                <tbody>
                    <tr><td style="width:190px;color:var(--text-muted);">Waktu Pengajuan</td><td>{{ $p->tanggal_pengajuan?->format('d/m/Y H:i') }}</td></tr>
                    <tr><td style="color:var(--text-muted);">Tanggal Peminjaman</td><td>{{ $p->tanggal_peminjaman?->format('d/m/Y H:i') }}</td></tr>
                    <tr><td style="color:var(--text-muted);">Batas Pengembalian</td><td>{{ $p->tanggal_rencana_kembali?->format('d/m/Y H:i') }}</td></tr>
                    <tr>
                        <td style="color:var(--text-muted);">Diproses Oleh</td>
                        <td>{{ $p->email_pemroses ? ($p->pemroses?->role === 'dosen' ? 'Dosen' : 'Laboran/Admin') . ' · ' . $p->email_pemroses : '—' }}</td>
                    </tr>
                    <tr><td style="color:var(--text-muted);">Waktu Persetujuan</td><td>{{ $p->tanggal_persetujuan?->format('d/m/Y H:i') ?? '—' }}</td></tr>
                    @if($p->status === 'ditolak')
                        <tr>
                            <td style="color:var(--text-muted);">Alasan Ditolak</td>
                            <td style="color:#b42318;">{{ $p->alasan_ditolak ?? '—' }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card-modern overflow-hidden mt-3">
    <div class="p-4 pb-2">
        <h3 class="font-display fw-semibold mb-1" style="font-size:1.05rem;">Barang yang Dipinjam</h3>
        <p class="mb-0" style="font-size:.8rem;color:var(--text-muted);">Jumlah yang sudah dikembalikan dicatat melalui menu Pengembalian.</p>
    </div>
    @if($p->details->isEmpty())
        <div class="text-center py-4" style="color:var(--text-muted);font-size:.85rem;border-top:1px solid var(--border-color);">Tidak ada barang pada peminjaman ini.</div>
    @else
        <div class="table-responsive">
            <table class="table table-silab mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Barang</th>
                        <th style="width:90px;">Jenis</th>
                        <th style="width:90px;">Stok Saat Ini</th>
                        <th style="width:110px;">Dipinjam</th>
                        <th style="width:130px;">Dikembalikan</th>
                        <th class="pe-4" style="width:90px;">Sisa</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($p->details as $d)
                    @php
                        $kembali = (int) ($sudah[$d->id_detail] ?? 0);
                        $sisa = max(0, (int) $d->jumlah - $kembali);
                    @endphp
                    <tr>
                        <td class="ps-4 fw-semibold">{{ $d->katalog?->nama ?? '—' }}</td>
                        <td>{{ $d->katalog?->jenis === 'alat' ? 'Alat' : 'Bahan' }}</td>
                        <td>{{ $d->katalog?->stok }}</td>
                        <td>{{ $d->jumlah }}</td>
                        <td>{{ $kembali }}</td>
                        <td class="pe-4">{{ $sisa }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@if($p->ruangans->isNotEmpty())
<div class="card-modern overflow-hidden mt-3">
    <div class="p-4 pb-2">
        <h3 class="font-display fw-semibold mb-0" style="font-size:1.05rem;">Ruangan yang Dipinjam</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-silab mb-0">
            <thead>
                <tr>
                    <th class="ps-4">Ruangan</th>
                    <th style="width:160px;">Mulai</th>
                    <th style="width:160px;">Selesai</th>
                    <th class="pe-4">Keterangan</th>
                </tr>
            </thead>
            <tbody>
            @foreach($p->ruangans as $r)
                <tr>
                    <td class="ps-4 fw-semibold">{{ $r->ruangan?->nama_ruangan ?? '—' }}</td>
                    <td>{{ $r->tanggal_mulai?->format('d/m/Y H:i') }}</td>
                    <td>{{ $r->tanggal_selesai?->format('d/m/Y H:i') }}</td>
                    <td class="pe-4">{{ $r->keterangan ?? '—' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="card-modern overflow-hidden mt-3">
    <div class="p-4 pb-2">
        <h3 class="font-display fw-semibold mb-1" style="font-size:1.05rem;">Riwayat Pengembalian</h3>
        <p class="mb-0" style="font-size:.8rem;color:var(--text-muted);">Setiap pencatatan pengembalian beserta penerimanya.</p>
    </div>
    @if($p->pengembalians->isEmpty())
        <div class="text-center py-4" style="color:var(--text-muted);font-size:.85rem;border-top:1px solid var(--border-color);">Belum ada pengembalian yang dicatat.</div>
    @else
        <div class="table-responsive">
            <table class="table table-silab mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" style="width:160px;">Tanggal Aktual</th>
                        <th style="width:220px;">Penerima</th>
                        <th>Barang</th>
                        <th class="pe-4">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($p->pengembalians as $pg)
                    <tr>
                        <td class="ps-4">{{ $pg->tanggal_pengembalian?->format('d/m/Y H:i') }}</td>
                        <td>{{ $pg->email_penerima }}</td>
                        <td>
                            @foreach($pg->details as $det)
                                <div style="font-size:.82rem;">
                                    {{ $det->detailPeminjaman?->katalog?->nama ?? 'Barang' }}
                                    <strong>{{ $det->jumlah_dikembalikan }}</strong>
                                    @if($det->kondisi)
                                        <span class="badge-status" style="background:{{ $det->kondisi === 'baik' ? '#ecfdf3;color:#146c43' : '#fef2f2;color:#b42318' }};">{{ $det->kondisi === 'baik' ? 'Baik' : 'Rusak' }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </td>
                        <td class="pe-4" style="font-size:.82rem;">{{ $pg->keterangan ?? '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- Modal penolakan --}}
@if($dapatMemproses)
<div class="modal fade" id="tolakModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('pengajuan.tolak', $p->id_peminjaman) }}" method="POST" class="modal-content"
              onsubmit="return confirm('Tolak pengajuan peminjaman #{{ $p->id_peminjaman }}? Stok barang tidak akan berubah.')">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" style="font-size:1rem;">Tolak Pengajuan Peminjaman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3" style="font-size:.85rem;color:var(--text-muted);">Pengajuan oleh <strong>{{ $p->nama_peminjam }}</strong> akan berstatus <strong>Ditolak</strong>. Stok barang tidak berubah.</p>
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
@endif
@endsection
