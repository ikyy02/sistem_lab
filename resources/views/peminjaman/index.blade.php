@extends('layouts.app')

@section('title', 'Ajukan Peminjaman')
@section('page-title', 'Peminjaman')
@section('page-subtitle', 'Ajukan peminjaman alat, bahan, atau ruangan laboratorium.')

@section('content')
@php
    $fmt = fn ($v) => \Carbon\Carbon::parse($v)->format('Y-m-d\TH:i');
    $jamBuka = $aturanJamKerja['jam_buka'] ?? '08:00';
    $jamTutup = $aturanJamKerja['jam_tutup'] ?? '16:00';
    $hariKerja = $aturanJamKerja['hari'] ?? [];

    // Dosen: pengajuan otomatis "Pembelajaran"; tanggal mengikuti jam operasional pertama yang belum lewat.
    $jenis = $roleDosen ? 'pembelajaran' : old('jenis_peminjaman', 'pribadi');
    $pinjamDefault = $roleDosen
        ? ($awalIzin ? $awalIzin->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i'))
        : now()->addHour()->startOfHour()->format('Y-m-d\TH:i');
    if ($roleDosen) {
        $mulai = \Carbon\Carbon::parse($pinjamDefault);
        [$jTutup, $mTutup] = array_pad(explode(':', $jamTutup), 2, 0);
        $batasTutup = $mulai->copy()->setTime((int) $jTutup, (int) $mTutup, 0);
        $selesai = $mulai->copy()->addHour();
        if ($selesai->greaterThan($batasTutup)) {
            $selesai = $batasTutup;
        }
        $kembaliDefault = $selesai->format('Y-m-d\TH:i');
    } else {
        $kembaliDefault = now()->addDay()->startOfHour()->format('Y-m-d\TH:i');
    }
    $nilaiPinjam = old('tanggal_peminjaman') ? $fmt(old('tanggal_peminjaman')) : $pinjamDefault;
    $nilaiKembali = old('tanggal_rencana_kembali') ? $fmt(old('tanggal_rencana_kembali')) : $kembaliDefault;

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
        <p class="mb-0" style="font-size:.83rem;color:var(--text-muted);">
            @if($roleDosen)
                Peminjaman pembelajaran berlaku 1 hari dan hanya dapat dijadwalkan pada jam operasional laboratorium ({{ $jamBuka }} – {{ $jamTutup }}). Pengajuan akan berstatus <strong>Menunggu Persetujuan</strong>.
            @else
                Pilih alat, bahan, atau ruangan beserta jumlah dan waktu peminjamannya. Pengajuan akan berstatus <strong>Menunggu Persetujuan</strong>.
            @endif
        </p>
    </div>
    <a href="{{ route('peminjaman.riwayat') }}" class="btn btn-outline-silab d-flex align-items-center gap-2"><i class="bi bi-clock-history"></i> Riwayat Peminjaman</a>
</div>

<form method="POST" action="{{ route('peminjaman.store') }}" id="formPinjam"
      onsubmit="return prosesKirim(event)">
    @csrf
    <div class="card-modern p-4">
        <h3 class="font-display fw-semibold mb-1" style="font-size:1.05rem;">Data Peminjaman</h3>
        <p class="mb-3" style="font-size:.8rem;color:var(--text-muted);">
            @if($roleDosen)
                Lengkapi data peminjaman, barang/alat/bahan, dan ruangan (opsional) pada formulir berikut.
            @else
                Semua data wajib diisi kecuali bagian ruangan (opsional).
            @endif
        </p>

        <div class="row g-3">
            @if($roleDosen)
                <div class="col-12 col-md-3">
                    <label class="form-label" for="jenis_peminjaman_tampil">Jenis Peminjaman</label>
                    <input type="text" id="jenis_peminjaman_tampil" class="form-control" value="Pembelajaran" disabled>
                    <input type="hidden" name="jenis_peminjaman" id="jenis_peminjaman" value="pembelajaran">
                </div>
            @else
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
            @endif

            <div class="col-6 col-md-3">
                <label class="form-label" for="tanggal_peminjaman">Tanggal & Waktu Peminjaman</label>
                <input type="datetime-local" id="tanggal_peminjaman" name="tanggal_peminjaman" value="{{ $nilaiPinjam }}"
                       @if($roleDosen && $awalIzin) min="{{ $awalIzin->format('Y-m-d\TH:i') }}" @endif
                       class="form-control @error('tanggal_peminjaman') is-invalid @enderror">
                @if($roleDosen)
                    <div class="form-text">Hanya {{ $jamBuka }} – {{ $jamTutup }} pada hari operasional ({{ implode(', ', $hariKerja) }}).</div>
                @endif
                @error('tanggal_peminjaman')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label" for="tanggal_rencana_kembali">Tanggal & Waktu Pengembalian</label>
                <input type="datetime-local" id="tanggal_rencana_kembali" name="tanggal_rencana_kembali" value="{{ $nilaiKembali }}"
                       class="form-control @error('tanggal_rencana_kembali') is-invalid @enderror">
                @if($roleDosen)
                    <div class="form-text">Harus pada tanggal yang sama dengan peminjaman (maksimal 1 hari).</div>
                @endif
                @error('tanggal_rencana_kembali')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            @unless($roleDosen)
                <div class="col-12">
                    <label class="form-label" for="keterangan">Keperluan Peminjaman</label>
                    <textarea id="keterangan" name="keterangan" rows="2" maxlength="500"
                              class="form-control @error('keterangan') is-invalid @enderror"
                              placeholder="Contoh: Praktikum jaringan komputer kelas A">{{ old('keterangan') }}</textarea>
                    @error('keterangan')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            @endunless
        </div>

        <hr class="my-4">

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

        <hr class="my-4">

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div>
                <h3 class="font-display fw-semibold mb-1" style="font-size:1.05rem;">Ruangan (Opsional)</h3>
                <p class="mb-0" style="font-size:.8rem;color:var(--text-muted);">
                    @if($roleDosen)
                        Periode ruangan harus berada pada tanggal yang sama dengan peminjaman.
                    @else
                        Pilih ruangan bila peminjaman memerlukan ruangan beserta periode pemakaiannya.
                    @endif
                </p>
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
@endsection

@push('scripts')
<script>
    const OPSI_KATALOG = @json($jsKatalog);
    const OPSI_RUANGAN = @json($jsRuangan);
    const LAMA_BARANG = @json($jsLamaBarang);
    const LAMA_RUANGAN = @json($jsLamaRuangan);
    const ROLE_DOSEN = @json($roleDosen);
    const JAM_KERJA = @json(['buka' => $jamBuka, 'tutup' => $jamTutup, 'hari' => $hariKerja]);
    const NAMA_HARI = ['minggu', 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
    let idxBarang = 0;
    let idxRuangan = 0;

    function opsiNilai(nilai, teks) {
        const o = document.createElement('option');
        o.value = nilai;
        o.textContent = teks;
        return o;
    }

    function toggleDosen(jenis) {
        const box = document.getElementById('boxDosen');
        if (box) box.style.display = jenis === 'atas_dosen' ? '' : 'none';
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
        if (data.tanggal_mulai) {
            mulai.value = String(data.tanggal_mulai).replace(' ', 'T');
        } else if (ROLE_DOSEN) {
            mulai.value = document.getElementById('tanggal_peminjaman').value;
        }
        tdMulai.appendChild(mulai);

        const tdSelesai = document.createElement('td');
        const selesai = document.createElement('input');
        selesai.type = 'datetime-local';
        selesai.name = 'ruangan[' + i + '][tanggal_selesai]';
        selesai.className = 'form-control';
        selesai.required = true;
        if (data.tanggal_selesai) {
            selesai.value = String(data.tanggal_selesai).replace(' ', 'T');
        } else if (ROLE_DOSEN) {
            selesai.value = document.getElementById('tanggal_rencana_kembali').value;
        }
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

    function waktuKerja(stringNilai) {
        const d = new Date(stringNilai);
        if (isNaN(d.getTime())) return false;
        const hari = NAMA_HARI[d.getDay()];
        if (!JAM_KERJA.hari.includes(hari)) return false;
        const menit = d.getHours() * 60 + d.getMinutes();
        const [jBuka, mBuka] = JAM_KERJA.buka.split(':').map(Number);
        const [jTutup, mTutup] = JAM_KERJA.tutup.split(':').map(Number);
        return menit >= (jBuka * 60 + mBuka) && menit <= (jTutup * 60 + mTutup);
    }

    // Validasi sisi klien khusus Dosen (dilakukan ulang di backend).
    function validasiDosen() {
        const form = document.getElementById('formPinjam');
        const pinjam = form.tanggal_peminjaman.value;
        const kembali = form.tanggal_rencana_kembali.value;
        if (!pinjam || !kembali) {
            return 'Tanggal peminjaman dan pengembalian wajib diisi.';
        }
        const dPinjam = new Date(pinjam);
        const dKembali = new Date(kembali);
        if (dKembali < dPinjam) {
            return 'Tanggal pengembalian tidak boleh sebelum tanggal peminjaman.';
        }
        if (dPinjam.toDateString() !== dKembali.toDateString()) {
            return 'Peminjaman Dosen hanya berlaku 1 hari: tanggal pengembalian harus sama dengan tanggal peminjaman.';
        }
        if (!waktuKerja(pinjam)) {
            return 'Waktu peminjaman harus berada dalam jam kerja laboratorium (' + JAM_KERJA.buka + ' – ' + JAM_KERJA.tutup + ') pada hari operasional.';
        }
        if (!waktuKerja(kembali)) {
            return 'Waktu pengembalian harus berada dalam jam kerja laboratorium (' + JAM_KERJA.buka + ' – ' + JAM_KERJA.tutup + ') pada hari operasional.';
        }

        const rows = document.getElementById('ruanganRows').querySelectorAll('tr');
        for (const tr of rows) {
            const mulai = tr.querySelector('[name$="[tanggal_mulai]"]');
            const selesai = tr.querySelector('[name$="[tanggal_selesai]"]');
            if (!mulai || !selesai || !mulai.value || !selesai.value) continue;
            const dMulai = new Date(mulai.value);
            const dSelesai = new Date(selesai.value);
            if (dSelesai <= dMulai) {
                return 'Tanggal selesai ruangan harus setelah tanggal mulai.';
            }
            if (dMulai.toDateString() !== dPinjam.toDateString() || dSelesai.toDateString() !== dPinjam.toDateString()) {
                return 'Periode ruangan peminjaman Dosen harus berada pada tanggal yang sama dengan peminjaman.';
            }
            if (!waktuKerja(mulai.value) || !waktuKerja(selesai.value)) {
                return 'Waktu ruangan harus berada dalam jam kerja laboratorium (' + JAM_KERJA.buka + ' – ' + JAM_KERJA.tutup + ').';
            }
        }

        return null;
    }

    function prosesKirim(e) {
        if (ROLE_DOSEN) {
            const pesan = validasiDosen();
            if (pesan) {
                e.preventDefault();
                alert(pesan);
                return false;
            }
        }
        return confirm('Kirim pengajuan peminjaman ini? Pengajuan akan menunggu persetujuan Dosen/Laboran.');
    }

    document.addEventListener('DOMContentLoaded', function () {
        const selectJenis = document.getElementById('jenis_peminjaman');
        if (selectJenis && selectJenis.tagName === 'SELECT') {
            toggleDosen(selectJenis.value);
        }
        const lamaBarang = LAMA_BARANG.length ? LAMA_BARANG : [{}];
        lamaBarang.forEach(tambahBarang);
        LAMA_RUANGAN.forEach(tambahRuangan);
    });
</script>
@endpush
