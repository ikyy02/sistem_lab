@extends('layouts.app')

@section('title', 'Ajukan Peminjaman')
@section('page-title', 'Peminjaman')
@section('page-subtitle', 'Ajukan peminjaman alat, bahan, atau ruangan laboratorium.')

@section('content')
@php
    $tgl = fn ($key, $default) => old($key) ? \Carbon\Carbon::parse(old($key))->format('Y-m-d\TH:i') : $default;
    $nilaiPinjam = $tgl('tanggal_peminjaman', now()->addHour()->startOfHour()->format('Y-m-d\TH:i'));
    $nilaiKembali = $tgl('tanggal_rencana_kembali', now()->addDay()->startOfHour()->format('Y-m-d\TH:i'));
    $jenis = old('jenis_peminjaman', 'pribadi');
    $jsKatalog = $opsiKatalog->map(fn ($k) => ['id' => $k->id_katalog, 'nama' => $k->nama, 'stok' => $k->stok, 'satuan' => $k->nama_satuan])->values();
    $jsRuangan = $opsiRuangan->map(fn ($r) => ['id' => $r->id_ruangan, 'nama' => $r->nama_ruangan])->values();
    $jsLamaBarang = collect(old('detail', []))->values();
    $jsLamaRuangan = collect(old('ruangan', []))->values();
@endphp

@if($errors->any())
    <div class="alert alert-danger mb-3">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $pesan)<li>{{ $pesan }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="font-display fw-semibold mb-1" style="font-size:1.5rem;">Ajukan Peminjaman</h2>
        <p class="mb-0" style="font-size:.83rem;color:var(--text-muted);">Pilih alat, bahan, atau ruangan beserta jumlah dan waktu peminjamannya. Pengajuan akan berstatus <strong>Menunggu Persetujuan</strong>.</p>
    </div>
    <a href="{{ route('peminjaman.riwayat') }}" class="btn btn-outline-silab d-flex align-items-center gap-2"><i class="bi bi-clock-history"></i> Riwayat Peminjaman</a>
</div>

<form method="POST" action="{{ route('peminjaman.store') }}" id="formPinjam"
      onsubmit="return confirm('Kirim pengajuan peminjaman ini? Pengajuan akan menunggu persetujuan Dosen/Laboran.')">
    @csrf
    <div class="card-modern p-4">
        <h3 class="font-display fw-semibold mb-1" style="font-size:1.05rem;">Data Peminjaman</h3>
        <p class="mb-3" style="font-size:.8rem;color:var(--text-muted);">Semua data wajib diisi kecuali bagian ruangan (opsional).</p>

        <div class="row g-3">
            <div class="col-12 col-md-3">
                <label class="form-label" for="jenis_peminjaman">Jenis Peminjaman</label>
                <select id="jenis_peminjaman" name="jenis_peminjaman" class="form-select @error('jenis_peminjaman') is-invalid @enderror" onchange="toggleDosen(this.value)">
                    <option value="pribadi" @selected($jenis === 'pribadi')>Pribadi</option>
                    <option value="atas_dosen" @selected($jenis === 'atas_dosen')>Atas Dosen</option>
                </select>
                @error('jenis_peminjaman')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="col-12 col-md-3" id="boxDosen" style="display:none;">
                <label class="form-label" for="email_dosen">Dosen Penanggung Jawab</label>
                <select id="email_dosen" name="email_dosen" class="form-select @error('email_dosen') is-invalid @enderror">
                    <option value="">Pilih dosen...</option>
                    @foreach($opsiDosen as $d)
                        <option value="{{ $d->email }}" @selected(old('email_dosen') === $d->email)>{{ $d->nama }}</option>
                    @endforeach
                </select>
                @error('email_dosen')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label" for="tanggal_peminjaman">Tanggal & Waktu Peminjaman</label>
                <input type="datetime-local" id="tanggal_peminjaman" name="tanggal_peminjaman" value="{{ $nilaiPinjam }}"
                       class="form-control @error('tanggal_peminjaman') is-invalid @enderror">
                @error('tanggal_peminjaman')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label" for="tanggal_rencana_kembali">Tanggal & Waktu Pengembalian</label>
                <input type="datetime-local" id="tanggal_rencana_kembali" name="tanggal_rencana_kembali" value="{{ $nilaiKembali }}"
                       class="form-control @error('tanggal_rencana_kembali') is-invalid @enderror">
                @error('tanggal_rencana_kembali')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
                <label class="form-label" for="keterangan">Keperluan Peminjaman</label>
                <textarea id="keterangan" name="keterangan" rows="2" maxlength="500"
                          class="form-control @error('keterangan') is-invalid @enderror"
                          placeholder="Contoh: Praktikum jaringan komputer kelas A">{{ old('keterangan') }}</textarea>
                @error('keterangan')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    <div class="card-modern p-4 mt-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div>
                <h3 class="font-display fw-semibold mb-1" style="font-size:1.05rem;">Barang (Alat / Bahan)</h3>
                <p class="mb-0" style="font-size:.8rem;color:var(--text-muted);">Pilih alat atau bahan beserta jumlah yang dipinjam.</p>
            </div>
            <button type="button" class="btn btn-outline-silab d-flex align-items-center gap-2" onclick="tambahBarang()"><i class="bi bi-plus-lg"></i> Tambah Barang</button>
        </div>
        @error('detail')<div class="alert alert-danger mb-3">{{ $message }}</div>@enderror
        <div class="table-responsive">
            <table class="table table-silab mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Alat / Bahan</th>
                        <th style="width:200px;">Jumlah</th>
                        <th class="text-end pe-4" style="width:90px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="barangRows"></tbody>
            </table>
        </div>
    </div>

    <div class="card-modern p-4 mt-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div>
                <h3 class="font-display fw-semibold mb-1" style="font-size:1.05rem;">Ruangan (Opsional)</h3>
                <p class="mb-0" style="font-size:.8rem;color:var(--text-muted);">Pilih ruangan bila peminjaman memerlukan ruangan beserta periode pemakaiannya.</p>
            </div>
            <button type="button" class="btn btn-outline-silab d-flex align-items-center gap-2" onclick="tambahRuangan()"><i class="bi bi-plus-lg"></i> Tambah Ruangan</button>
        </div>
        @error('ruangan')<div class="alert alert-danger mb-3">{{ $message }}</div>@enderror
        <div class="table-responsive">
            <table class="table table-silab mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" style="width:22%;">Ruangan</th>
                        <th style="width:22%;">Mulai</th>
                        <th style="width:22%;">Selesai</th>
                        <th>Keterangan</th>
                        <th class="text-end pe-4" style="width:90px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="ruanganRows"></tbody>
            </table>
        </div>
    </div>

    <div class="d-flex flex-wrap justify-content-end gap-2 mt-3">
        <a href="{{ route('katalog') }}" class="btn btn-outline-silab"><i class="bi bi-collection me-1"></i> Lihat Katalog</a>
        <button type="submit" class="btn btn-primary d-flex align-items-center gap-2"><i class="bi bi-send"></i> Kirim Pengajuan</button>
    </div>
</form>

<div class="card-modern overflow-hidden mt-4">
    <div class="p-4 pb-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h3 class="font-display fw-semibold mb-1" style="font-size:1.05rem;">Katalog Tersedia</h3>
            <p class="mb-0" style="font-size:.8rem;color:var(--text-muted);">Alat dan bahan dengan stok tersedia untuk dipinjam.</p>
        </div>
        <form method="GET" action="{{ route('peminjaman.index') }}" class="d-flex gap-2">
            <input type="text" name="search" value="{{ $search }}" maxlength="100" class="form-control" placeholder="Cari nama, satuan, ruangan..." style="min-width:220px;">
            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
            @if($search !== '')<a href="{{ route('peminjaman.index') }}" class="btn btn-outline-silab" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>@endif
        </form>
    </div>

    @if($katalog->isEmpty())
        <div class="text-center py-5" style="color:var(--text-muted);font-size:.85rem;border-top:1px solid var(--border-color);">
            <i class="bi bi-inbox fs-1 d-block mb-2" style="color:#c9bdb4;"></i>
            {{ $search !== '' ? 'Tidak ada data yang cocok dengan "' . $search . '".' : 'Belum ada katalog dengan stok tersedia.' }}
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-silab mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Nama</th>
                        <th style="width:100px;">Jenis</th>
                        <th style="width:120px;">Stok</th>
                        <th style="width:120px;">Satuan</th>
                        <th class="pe-4">Ruangan</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($katalog as $k)
                    <tr>
                        <td class="ps-4 fw-semibold">{{ $k->nama }}</td>
                        <td><span class="badge-status" style="background:var(--primary-soft);color:var(--ink);">{{ $k->jenis === 'alat' ? 'Alat' : 'Bahan' }}</span></td>
                        <td>{{ $k->stok }}</td>
                        <td>{{ $k->nama_satuan }}</td>
                        <td class="pe-4">{{ $k->nama_ruangan }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="table-footer">
            <div>Menampilkan {{ $katalog->firstItem() }}–{{ $katalog->lastItem() }} dari {{ $katalog->total() }} data</div>
            <div class="d-flex align-items-center gap-3">
                <form method="GET" action="{{ route('peminjaman.index') }}" class="d-flex align-items-center gap-2">
                    <input type="hidden" name="search" value="{{ $search }}">
                    <label class="mb-0" style="font-size:.78rem;">Tampil</label>
                    <select name="per_page" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                        @foreach($perPageOptions as $n)<option value="{{ $n }}" @selected($perPage === $n)>{{ $n }}</option>@endforeach
                    </select>
                </form>
                {{ $katalog->links('pagination.silab') }}
            </div>
        </div>
    @endif
</div>

<div class="card-modern p-4 mt-3">
    <h3 class="font-display fw-semibold mb-1" style="font-size:1.05rem;">Ruangan Tersedia</h3>
    <p class="mb-3" style="font-size:.8rem;color:var(--text-muted);">Daftar ruangan laboratorium yang dapat diajukan.</p>
    <div class="d-flex flex-wrap gap-2">
        @forelse($opsiRuangan->take(12) as $r)
            <span class="badge-status" style="background:var(--gold-tint);color:#6E6142;"><i class="bi bi-door-open me-1"></i>{{ $r->nama_ruangan }}</span>
        @empty
            <span style="font-size:.83rem;color:var(--text-muted);">Belum ada ruangan.</span>
        @endforelse
        @if($opsiRuangan->count() > 12)
            <a href="{{ route('katalog', ['jenis' => 'ruangan']) }}" class="badge-status text-decoration-none" style="background:var(--primary-soft);color:var(--ink);">+{{ $opsiRuangan->count() - 12 }} ruangan lainnya</a>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    const OPSI_KATALOG = @json($jsKatalog);
    const OPSI_RUANGAN = @json($jsRuangan);
    const LAMA_BARANG = @json($jsLamaBarang);
    const LAMA_RUANGAN = @json($jsLamaRuangan);
    let idxBarang = 0;
    let idxRuangan = 0;

    function opsiNilai(nilai, teks) {
        const o = document.createElement('option');
        o.value = nilai;
        o.textContent = teks;
        return o;
    }

    function toggleDosen(jenis) {
        document.getElementById('boxDosen').style.display = jenis === 'atas_dosen' ? '' : 'none';
    }

    function tambahBarang(data = {}) {
        const i = idxBarang++;
        const tr = document.createElement('tr');

        const tdPilih = document.createElement('td');
        tdPilih.className = 'ps-4';
        const sel = document.createElement('select');
        sel.name = 'detail[' + i + '][id_katalog]';
        sel.className = 'form-select';
        sel.required = true;
        sel.appendChild(opsiNilai('', 'Pilih alat / bahan...'));
        OPSI_KATALOG.forEach(k => {
            const o = opsiNilai(k.id, k.nama + ' — stok ' + k.stok);
            o.dataset.stok = k.stok;
            if (String(data.id_katalog) === String(k.id)) o.selected = true;
            sel.appendChild(o);
        });
        const ket = document.createElement('div');
        ket.className = 'form-text';
        tdPilih.appendChild(sel);
        tdPilih.appendChild(ket);

        const tdJumlah = document.createElement('td');
        const input = document.createElement('input');
        input.type = 'number';
        input.name = 'detail[' + i + '][jumlah]';
        input.className = 'form-control';
        input.min = 1;
        input.step = 1;
        input.required = true;
        input.value = data.jumlah || 1;
        tdJumlah.appendChild(input);

        const sinkron = () => {
            const stok = parseInt(sel.selectedOptions[0]?.dataset.stok || '0', 10);
            input.max = stok > 0 ? stok : '';
            ket.textContent = sel.value ? 'Tersedia: ' + stok + ' unit. Jumlah peminjaman tidak boleh melebihi stok.' : '';
        };
        sel.addEventListener('change', sinkron);
        sinkron();

        const tdAksi = document.createElement('td');
        tdAksi.className = 'pe-4 text-end';
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-del';
        btn.title = 'Hapus';
        btn.innerHTML = '<i class="bi bi-trash"></i>';
        btn.onclick = () => tr.remove();
        tdAksi.appendChild(btn);

        tr.append(tdPilih, tdJumlah, tdAksi);
        document.getElementById('barangRows').appendChild(tr);
    }

    function tambahRuangan(data = {}) {
        const i = idxRuangan++;
        const tr = document.createElement('tr');

        const tdPilih = document.createElement('td');
        tdPilih.className = 'ps-4';
        const sel = document.createElement('select');
        sel.name = 'ruangan[' + i + '][id_ruangan]';
        sel.className = 'form-select';
        sel.required = true;
        sel.appendChild(opsiNilai('', 'Pilih ruangan...'));
        OPSI_RUANGAN.forEach(r => {
            const o = opsiNilai(r.id, r.nama);
            if (String(data.id_ruangan) === String(r.id)) o.selected = true;
            sel.appendChild(o);
        });
        tdPilih.appendChild(sel);

        const tdMulai = document.createElement('td');
        const mulai = document.createElement('input');
        mulai.type = 'datetime-local';
        mulai.name = 'ruangan[' + i + '][tanggal_mulai]';
        mulai.className = 'form-control';
        mulai.required = true;
        mulai.value = data.tanggal_mulai ? String(data.tanggal_mulai).replace(' ', 'T') : '';
        tdMulai.appendChild(mulai);

        const tdSelesai = document.createElement('td');
        const selesai = document.createElement('input');
        selesai.type = 'datetime-local';
        selesai.name = 'ruangan[' + i + '][tanggal_selesai]';
        selesai.className = 'form-control';
        selesai.required = true;
        selesai.value = data.tanggal_selesai ? String(data.tanggal_selesai).replace(' ', 'T') : '';
        tdSelesai.appendChild(selesai);

        const tdKet = document.createElement('td');
        const ket = document.createElement('input');
        ket.type = 'text';
        ket.name = 'ruangan[' + i + '][keterangan]';
        ket.className = 'form-control';
        ket.maxLength = 500;
        ket.placeholder = 'Keperluan pemakaian ruangan';
        ket.value = data.keterangan || '';
        tdKet.appendChild(ket);

        const tdAksi = document.createElement('td');
        tdAksi.className = 'pe-4 text-end';
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-del';
        btn.title = 'Hapus';
        btn.innerHTML = '<i class="bi bi-trash"></i>';
        btn.onclick = () => tr.remove();
        tdAksi.appendChild(btn);

        tr.append(tdPilih, tdMulai, tdSelesai, tdKet, tdAksi);
        document.getElementById('ruanganRows').appendChild(tr);
    }

    document.addEventListener('DOMContentLoaded', function () {
        toggleDosen(document.getElementById('jenis_peminjaman').value);
        const lamaBarang = LAMA_BARANG.length ? LAMA_BARANG : [{}];
        lamaBarang.forEach(tambahBarang);
        LAMA_RUANGAN.forEach(tambahRuangan);
    });
</script>
@endpush
