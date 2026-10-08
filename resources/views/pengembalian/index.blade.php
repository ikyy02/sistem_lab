@extends('layouts.app')

@section('title', 'Pengembalian')
@section('page-title', 'Pengembalian')
@section('page-subtitle', 'Catat pengembalian barang pinjaman; stok otomatis diperbarui.')

@section('content')

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="font-display fw-semibold mb-1" style="font-size:1.5rem;">Pengembalian Barang</h2>
        <p class="mb-0" style="font-size:.83rem;color:var(--text-muted);">Peminjaman berstatus <strong>Disetujui</strong> dapat dicatat pengembaliannya. Pengembalian sebagian diperbolehkan.</p>
    </div>
    <a href="{{ route('pengajuan.index') }}" class="btn btn-outline-silab d-flex align-items-center gap-2">
        <i class="bi bi-arrow-up-right-square"></i> Pengajuan Peminjaman
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger mb-3">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $pesan)<li>{{ $pesan }}</li>@endforeach
        </ul>
    </div>
@endif

<form method="GET" action="{{ route('pengembalian.index') }}" class="card-modern p-3 mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-6 col-lg-3">
            <label class="form-label" for="status">Status</label>
            <select id="status" name="status" class="form-select">
                <option value="disetujui" @selected($status === 'disetujui')>Disetujui (Aktif)</option>
                <option value="selesai" @selected($status === 'selesai')>Selesai</option>
                <option value="semua" @selected($status === 'semua')>Semua Status</option>
            </select>
        </div>
        <div class="col-6 col-lg-2">
            <label class="form-label" for="per_page">Tampil</label>
            <select id="per_page" name="per_page" class="form-select">
                @foreach($perPageOptions as $n)<option value="{{ $n }}" @selected($perPage === $n)>{{ $n }}</option>@endforeach
            </select>
        </div>
        <div class="col-6 col-lg-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1">Terapkan</button>
            <a href="{{ route('pengembalian.index') }}" class="btn btn-outline-silab" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
        </div>
    </div>
</form>

@forelse($data as $p)
    @php
        $aktif = $p->status === 'disetujui';
        $riwayatP = $riwayat->get($p->id_peminjaman);
        $adaInput = false;
        foreach ($p->details as $d) {
            $sisaD = max(0, (int) $d->jumlah - (int) ($sudah[$d->id_detail] ?? 0));
            if ($aktif && $sisaD > 0) { $adaInput = true; break; }
        }
    @endphp
    <div class="card-modern overflow-hidden mb-3" id="peminjaman-{{ $p->id_peminjaman }}">
        <div class="p-4 d-flex flex-wrap align-items-center justify-content-between gap-3" style="border-bottom:1px solid var(--border-color);">
            <div class="min-w-0">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <span class="fw-semibold" style="font-size:1.02rem;">Peminjaman #{{ $p->id_peminjaman }}</span>
                    @include('peminjaman._status', ['status' => $p->status, 'terlambat' => $p->terlambat])
                </div>
                <div style="font-size:.8rem;color:var(--text-muted);">
                    {{ $p->nama_peminjam }} · {{ $p->identitas_peminjam }} ·
                    Batas kembali <strong>{{ $p->tanggal_rencana_kembali?->format('d/m/Y H:i') }}</strong>
                </div>
                @if($p->ruangans->isNotEmpty())
                    <div style="font-size:.75rem;color:var(--text-muted);">
                        Ruangan: {{ $p->ruangans->map(fn ($r) => $r->ruangan?->nama_ruangan ?? '—')->implode(', ') }}
                    </div>
                @endif
            </div>
            <a href="{{ route('peminjaman.show', $p->id_peminjaman) }}" class="btn btn-outline-silab d-flex align-items-center gap-2">
                <i class="bi bi-eye"></i> Detail
            </a>
        </div>

        <div class="p-4 pb-0">
            @if($adaInput)
            <form action="{{ route('pengembalian.store', $p->id_peminjaman) }}" method="POST"
                  onsubmit="return confirm('Catat pengembalian peminjaman #{{ $p->id_peminjaman }}? Stok barang akan diperbarui sesuai jumlah yang diisi.');">
                @csrf
            @endif
                <div class="table-responsive">
                    <table class="table table-silab mb-0">
                        <thead>
                            <tr>
                                <th class="ps-0">Barang</th>
                                <th style="width:80px;">Jenis</th>
                                <th style="width:90px;">Dipinjam</th>
                                <th style="width:110px;">Dikembalikan</th>
                                <th style="width:80px;">Sisa</th>
                                @if($adaInput)
                                    <th style="width:120px;">Jumlah</th>
                                    <th style="width:130px;">Kondisi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($p->details as $d)
                            @php
                                $kembaliD = (int) ($sudah[$d->id_detail] ?? 0);
                                $sisaD = max(0, (int) $d->jumlah - $kembaliD);
                                $isAlat = $d->katalog?->jenis === 'alat';
                                $bisaInput = $aktif && $sisaD > 0;
                            @endphp
                            <tr>
                                <td class="ps-0 fw-semibold">{{ $d->katalog?->nama ?? '—' }}</td>
                                <td>{{ $isAlat ? 'Alat' : 'Bahan' }}</td>
                                <td>{{ $d->jumlah }}</td>
                                <td>{{ $kembaliD }}</td>
                                <td>
                                    @if($sisaD === 0)
                                        <span class="badge-status" style="background:#ecfdf3;color:#146c43;">Lengkap</span>
                                    @else
                                        {{ $sisaD }}
                                    @endif
                                </td>
                                @if($adaInput)
                                    <td>
                                        @if($bisaInput)
                                            <input type="number" name="jumlah[{{ $d->id_detail }}]" min="1" max="{{ $sisaD }}"
                                                   value="{{ old('jumlah.' . $d->id_detail, $sisaD) }}"
                                                   class="form-control form-control-sm @error('jumlah.'.$d->id_detail) is-invalid @enderror"
                                                   style="max-width:90px;" aria-label="Jumlah dikembalikan">
                                            @error('jumlah.'.$d->id_detail)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        @else
                                            <span style="color:var(--text-muted);">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($bisaInput && $isAlat)
                                            <select name="kondisi[{{ $d->id_detail }}]"
                                                    class="form-select form-select-sm @error('kondisi.'.$d->id_detail) is-invalid @enderror" style="max-width:110px;">
                                                <option value="">Pilih...</option>
                                                <option value="baik" @selected(old('kondisi.'.$d->id_detail, 'baik') === 'baik')>Baik</option>
                                                <option value="rusak" @selected(old('kondisi.'.$d->id_detail) === 'rusak')>Rusak</option>
                                            </select>
                                            @error('kondisi.'.$d->id_detail)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        @elseif($bisaInput)
                                            <span style="color:var(--text-muted);font-size:.75rem;">Tanpa kondisi</span>
                                        @else
                                            <span style="color:var(--text-muted);">—</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                @if($adaInput)
                    <div class="row g-3 mt-3 align-items-end">
                        <div class="col-12 col-lg-8">
                            <label class="form-label" for="keterangan-{{ $p->id_peminjaman }}">Keterangan (opsional)</label>
                            <input id="keterangan-{{ $p->id_peminjaman }}" name="keterangan" type="text" maxlength="500"
                                   value="{{ old('keterangan') }}" class="form-control" placeholder="Contoh: kondisi lensa berdebu, dst.">
                        </div>
                        <div class="col-12 col-lg-4 d-flex justify-content-lg-end">
                            <button type="submit" class="btn btn-gold d-flex align-items-center gap-2">
                                <i class="bi bi-box-arrow-in-down"></i> Catat Pengembalian
                            </button>
                        </div>
                    </div>
                    <p class="mb-0 mt-2" style="font-size:.73rem;color:var(--text-muted);">
                        Kosongkan kolom jumlah agar barang tidak dikembalikan. Stok hanya bertambah untuk barang <strong>baik</strong> dan bahan.
                    </p>
                @endif
            @if($adaInput)
            </form>
            @endif
        </div>

        @if($riwayatP && $riwayatP->isNotEmpty())
            <div class="px-4 pb-4 pt-3" style="border-top:1px solid var(--border-color);margin-top:1rem;">
                <div class="fw-semibold mb-2" style="font-size:.85rem;"><i class="bi bi-clock-history me-1"></i> Riwayat Pengembalian</div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0" style="font-size:.8rem;">
                        <thead>
                            <tr>
                                <th style="width:150px;">Tanggal</th>
                                <th style="width:220px;">Penerima</th>
                                <th>Barang</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($riwayatP as $pg)
                            <tr>
                                <td>{{ $pg->tanggal_pengembalian?->format('d/m/Y H:i') }}</td>
                                <td>{{ $pg->email_penerima }}</td>
                                <td>
                                    @foreach($pg->details as $det)
                                        <div>
                                            {{ $det->detailPeminjaman?->katalog?->nama ?? 'Barang' }}
                                            <strong>{{ $det->jumlah_dikembalikan }}</strong>
                                            @if($det->kondisi)
                                                <span style="color:{{ $det->kondisi === 'baik' ? '#146c43' : '#b42318' }};">({{ $det->kondisi === 'baik' ? 'baik' : 'rusak' }})</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </td>
                                <td>{{ $pg->keterangan ?? '—' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@empty
    <div class="card-modern">
        <div class="text-center py-5" style="color:var(--text-muted);">
            <i class="bi bi-inbox fs-1 d-block mb-2" style="color:#c9bdb4;"></i>
            <div class="fw-semibold mb-1" style="font-size:.95rem;color:var(--text-dark);">Tidak ada peminjaman</div>
            <div style="font-size:.83rem;">Belum ada peminjaman berstatus "{{ $status }}".</div>
        </div>
    </div>
@endforelse

@if($data->hasPages())
<div class="card-modern mt-1">
    <div class="table-footer mb-0" style="border-radius:inherit;">
        <div>Menampilkan {{ $data->firstItem() }}–{{ $data->lastItem() }} dari {{ $data->total() }} data</div>
        {{ $data->links('pagination.silab') }}
    </div>
</div>
@endif
@endsection
