@extends('layouts.app')

@section('title', 'Ajukan Peminjaman')
@section('page-title', 'Peminjaman')
@section('page-subtitle', 'Ajukan peminjaman ruangan, alat, atau bahan laboratorium.')

@section('content')
@php
    $jamBuka = $aturanJamKerja['jam_buka'] ?? '08:00';
    $jamTutup = $aturanJamKerja['jam_tutup'] ?? '16:00';
    $hariKerja = $aturanJamKerja['hari'] ?? [];
    $fmt = fn ($v) => \Carbon\Carbon::parse($v)->format('Y-m-d\TH:i');

    if ($roleDosen) {
        $pinjamDefault = $awalIzin ? $awalIzin->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i');
        $mulai = \Carbon\Carbon::parse($pinjamDefault);
        [$jTutup, $mTutup] = array_pad(explode(':', $jamTutup), 2, 0);
        $batasTutup = $mulai->copy()->setTime((int) $jTutup, (int) $mTutup, 0);
        $selesai = $mulai->copy()->addHour();
        $kembaliDefault = ($selesai->greaterThan($batasTutup) ? $batasTutup : $selesai)->format('Y-m-d\TH:i');
    } else {
        $pinjamDefault = now()->addHour()->startOfHour()->format('Y-m-d\TH:i');
        $kembaliDefault = now()->addDay()->startOfHour()->format('Y-m-d\TH:i');
    }
    $nilaiPinjam = old('tanggal_peminjaman') ? $fmt(old('tanggal_peminjaman')) : $pinjamDefault;
    $nilaiKembali = old('tanggal_rencana_kembali') ? $fmt(old('tanggal_rencana_kembali')) : $kembaliDefault;

    $jsKatalog = $opsiKatalog->map(fn ($k) => ['id' => $k->id_katalog, 'nama' => $k->nama, 'jenis' => $k->jenis, 'stok' => $k->stok, 'satuan' => $k->nama_satuan])->values();
    $jsRuangan = $opsiRuangan->map(fn ($r) => ['id' => $r->id_ruangan, 'nama' => $r->nama_ruangan])->values();
@endphp

@push('styles')
<style>
    .pj-wrap { max-width: 1100px; margin-inline: auto; }
    .pj-card .pj-judul { font-size: 1.05rem; font-weight: 600; margin: 0 0 .25rem; font-family: var(--font-display, inherit); }
    .pj-card .pj-ket { font-size: .8rem; color: var(--text-muted); margin: 0 0 1rem; }
    .pj-kategori + .pj-kategori { margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid var(--border-color); }
    .pj-kategori-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .5rem; margin-bottom: .6rem; }
    .pj-kategori-head h4 { font-size: .95rem; font-weight: 600; margin: 0; }
    .pj-tabel { border: 1px solid var(--border-color); border-radius: 10px; }
    .pj-tabel .table { --bs-table-bg: transparent; }
    .pj-tabel td { vertical-align: top; }
    .pj-aksi { width: 72px; }
    .pj-jumlah { width: 150px; }
    .pj-kosong { text-align: center; padding: .9rem; font-size: .82rem; color: var(--text-muted); }
    .pj-bar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .75rem; margin-top: 1rem; }
    .pj-hint { font-size: .8rem; color: var(--text-muted); flex: 1 1 260px; }
    .pj-hint.siap { color: #15803d; }
    #btnAjukan:disabled { cursor: not-allowed; opacity: .55; filter: grayscale(.25); }
    @media (max-width: 575.98px) {
        .pj-jumlah { width: 110px; }
        .pj-bar .btn, .pj-bar > div:last-child { width: 100%; }
        .pj-bar > div:last-child { display: flex; flex-direction: column; }
    }
</style>
@endpush

<div class="pj-wrap">
@if($errors->any())
    <div class="alert alert-danger mb-3" id="ringkasanError" role="alert">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $pesan)<li>{{ $pesan }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="mb-3">
    <h2 class="font-display fw-semibold mb-1" style="font-size:1.5rem;">Ajukan Peminjaman</h2>
    <p class="mb-0" style="font-size:.83rem;color:var(--text-muted);">
        @if($roleDosen)
            Peminjaman pembelajaran berlaku 1 hari dan hanya pada jam operasional laboratorium ({{ $jamBuka }} – {{ $jamTutup }}). Pengajuan akan berstatus <strong>Menunggu Persetujuan</strong>.
        @else
            Tentukan jadwal sekali, lalu pilih ruangan, alat, atau bahan yang dibutuhkan. Pengajuan akan berstatus <strong>Menunggu Persetujuan</strong>.
        @endif
    </p>
</div>

<form method="POST" action="{{ route('peminjaman.store') }}" id="formPinjam" data-no-lock novalidate>
    @csrf
    <input type="hidden" name="token_pengajuan" value="{{ old('token_pengajuan') ?: \Illuminate\Support\Str::uuid() }}">
    @include($roleDosen ? 'peminjaman._form_dosen' : 'peminjaman._form_mahasiswa')

    @include('peminjaman._sumber_daya')

    <div id="pesanForm" class="alert alert-danger py-2 mt-3 mb-0" role="alert" style="display:none;"></div>

    <div class="pj-bar">
        <div class="pj-hint" id="hintAjukan" aria-live="polite">Tambahkan minimal satu ruangan, alat, atau bahan untuk mengaktifkan tombol Ajukan Peminjaman.</div>
        <div class="d-flex flex-wrap gap-2 justify-content-end">
            <a href="{{ route('katalog') }}" class="btn btn-outline-silab"><i class="bi bi-collection me-1"></i> Lihat Katalog</a>
            <button type="submit" id="btnAjukan" class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-2" disabled>
                <i class="bi bi-send" id="ikonAjukan"></i><span id="teksAjukan">Ajukan Peminjaman</span>
            </button>
        </div>
    </div>
</form>
</div>
@endsection

@push('scripts')
<script>
    const OPSI_KATALOG = @json($jsKatalog);
    const OPSI_RUANGAN = @json($jsRuangan);
    const LAMA_BARANG = @json(collect(old('detail', []))->values());
    const LAMA_RUANGAN = @json(collect(old('ruangan', []))->values());
    const ROLE_DOSEN = @json($roleDosen);
    const JAM_KERJA = @json(['buka' => $jamBuka, 'tutup' => $jamTutup, 'hari' => $hariKerja]);
    const NAMA_HARI = ['minggu', 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
    const PESAN_ITEM = 'Pilih minimal satu ruangan, alat, atau bahan yang ingin dipinjam sebelum mengajukan peminjaman.';
    let idxBarang = 0;
    let idxRuangan = 0;
    let sedangKirim = false;

    const el = (id) => document.getElementById(id);

    function opsiNilai(nilai, teks) {
        const o = document.createElement('option');
        o.value = nilai;
        o.textContent = teks;
        return o;
    }

    function tombolHapus(tr) {
        const td = document.createElement('td');
        td.className = 'pe-3 text-end';
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-del';
        btn.title = 'Hapus';
        btn.setAttribute('aria-label', 'Hapus baris');
        btn.innerHTML = '<i class="bi bi-trash"></i>';
        btn.onclick = () => { tr.remove(); perbarui(); };
        td.appendChild(btn);
        return td;
    }

    function toggleDosen(jenis) {
        const box = el('boxDosen');
        const sel = el('email_dosen');
        if (box) box.style.display = jenis === 'atas_dosen' ? '' : 'none';
        if (sel) sel.disabled = jenis !== 'atas_dosen';
    }

    function barisValid(tr) {
        const pilih = tr.querySelector('select');
        if (!pilih || pilih.value === '') return false;
        const jumlah = tr.querySelector('input[type=number]');
        if (!jumlah) return true; // ruangan
        const n = Number(jumlah.value);
        const maks = jumlah.max ? Number(jumlah.max) : Infinity;
        return Number.isInteger(n) && n >= 1 && n <= maks;
    }

    // Jumlah item yang benar-benar valid (placeholder dan baris belum lengkap tidak dihitung).
    function jumlahItemValid() {
        return [...document.querySelectorAll('#ruanganRows tr, #alatRows tr, #bahanRows tr')].filter(barisValid).length;
    }

    // Satu-satunya tempat status form diperbarui: pesan kosong, pesan petunjuk, dan tombol.
    function perbarui() {
        document.querySelectorAll('[data-kosong-untuk]').forEach(e => {
            e.style.display = el(e.dataset.kosongUntuk).children.length ? 'none' : '';
        });
        const ada = jumlahItemValid() > 0;
        el('pesanItem').style.display = ada ? 'none' : '';
        const hint = el('hintAjukan');
        hint.classList.toggle('siap', ada);
        hint.textContent = ada
            ? 'Item sudah dipilih. Periksa kembali jadwal lalu ajukan peminjaman.'
            : 'Tambahkan minimal satu ruangan, alat, atau bahan untuk mengaktifkan tombol Ajukan Peminjaman.';
        el('btnAjukan').disabled = !ada || sedangKirim;
        el('btnAjukan').title = ada ? '' : PESAN_ITEM;
    }

    function tambahBarang(kategori, data = {}) {
        const i = idxBarang++;
        const tr = document.createElement('tr');

        const tdPilih = document.createElement('td');
        tdPilih.className = 'ps-3';
        const sel = document.createElement('select');
        sel.name = 'detail[' + i + '][id_katalog]';
        sel.className = 'form-select';
        sel.setAttribute('aria-label', 'Pilih ' + kategori);
        sel.appendChild(opsiNilai('', 'Pilih ' + kategori + '...'));
        OPSI_KATALOG.filter(k => k.jenis === kategori).forEach(k => {
            const o = opsiNilai(k.id, k.nama + ' — stok ' + k.stok);
            o.dataset.stok = k.stok;
            if (String(data.id_katalog) === String(k.id)) o.selected = true;
            sel.appendChild(o);
        });
        const ket = document.createElement('div');
        ket.className = 'form-text';
        tdPilih.append(sel, ket);

        const tdJumlah = document.createElement('td');
        const input = document.createElement('input');
        input.type = 'number';
        input.name = 'detail[' + i + '][jumlah]';
        input.className = 'form-control';
        input.min = 1;
        input.step = 1;
        input.setAttribute('aria-label', 'Jumlah');
        input.value = data.jumlah || 1;
        tdJumlah.appendChild(input);

        const sinkron = () => {
            const stok = parseInt(sel.selectedOptions[0]?.dataset.stok || '0', 10);
            input.max = stok > 0 ? stok : '';
            const n = Number(input.value);
            const salah = sel.value && (!Number.isInteger(n) || n < 1 || (stok > 0 && n > stok));
            input.classList.toggle('is-invalid', !!salah);
            ket.textContent = sel.value ? 'Tersedia: ' + stok + '. Jumlah 1 – ' + stok + '.' : '';
            perbarui();
        };
        sel.addEventListener('change', sinkron);
        input.addEventListener('input', sinkron);

        tr.append(tdPilih, tdJumlah, tombolHapus(tr));
        el(kategori === 'alat' ? 'alatRows' : 'bahanRows').appendChild(tr);
        sinkron();
    }

    function tambahRuangan(data = {}) {
        const i = idxRuangan++;
        const tr = document.createElement('tr');

        const tdPilih = document.createElement('td');
        tdPilih.className = 'ps-3';
        const sel = document.createElement('select');
        sel.name = 'ruangan[' + i + '][id_ruangan]';
        sel.className = 'form-select';
        sel.setAttribute('aria-label', 'Pilih ruangan');
        sel.appendChild(opsiNilai('', 'Pilih ruangan...'));
        OPSI_RUANGAN.forEach(r => {
            const o = opsiNilai(r.id, r.nama);
            if (String(data.id_ruangan) === String(r.id)) o.selected = true;
            sel.appendChild(o);
        });
        sel.addEventListener('change', perbarui);
        tdPilih.appendChild(sel);

        tr.append(tdPilih, tombolHapus(tr));
        el('ruanganRows').appendChild(tr);
        perbarui();
    }

    function waktuKerja(nilai) {
        const d = new Date(nilai);
        if (isNaN(d.getTime())) return false;
        if (!JAM_KERJA.hari.includes(NAMA_HARI[d.getDay()])) return false;
        const menit = d.getHours() * 60 + d.getMinutes();
        const [jBuka, mBuka] = JAM_KERJA.buka.split(':').map(Number);
        const [jTutup, mTutup] = JAM_KERJA.tutup.split(':').map(Number);
        return menit >= (jBuka * 60 + mBuka) && menit <= (jTutup * 60 + mTutup);
    }

    // Mengembalikan daftar pesan kesalahan sisi klien (backend tetap memvalidasi ulang).
    function validasiForm() {
        const form = el('formPinjam');
        const galat = [];
        if (jumlahItemValid() === 0) galat.push(PESAN_ITEM);

        const pinjam = form.tanggal_peminjaman.value;
        const kembali = form.tanggal_rencana_kembali.value;
        if (!pinjam || !kembali) {
            galat.push('Waktu mulai dan waktu selesai wajib diisi.');
        } else {
            const dPinjam = new Date(pinjam), dKembali = new Date(kembali);
            if (dKembali < dPinjam) galat.push('Waktu selesai tidak boleh sebelum waktu mulai.');
            else if (document.querySelector('#ruanganRows tr') && dKembali <= dPinjam) galat.push('Waktu selesai harus setelah waktu mulai bila meminjam ruangan.');
            if (ROLE_DOSEN) {
                if (dPinjam.toDateString() !== dKembali.toDateString()) galat.push('Peminjaman Dosen hanya berlaku 1 hari: tanggal selesai harus sama dengan tanggal mulai.');
                if (!waktuKerja(pinjam) || !waktuKerja(kembali)) galat.push('Jadwal harus berada dalam jam kerja laboratorium (' + JAM_KERJA.buka + ' – ' + JAM_KERJA.tutup + ') pada hari operasional.');
            }
        }
        const jenis = form.jenis_peminjaman;
        if (jenis && jenis.value === 'atas_dosen' && !form.email_dosen.value) galat.push('Dosen penanggung jawab wajib dipilih.');
        if (form.keterangan && !form.keterangan.value.trim()) galat.push('Keterangan wajib diisi.');
        if (document.querySelector('#alatRows .is-invalid, #bahanRows .is-invalid')) galat.push('Jumlah alat/bahan tidak valid atau melebihi stok.');
        return galat;
    }

    function tampilkanGalat(galat) {
        const box = el('pesanForm');
        box.innerHTML = '<ul class="mb-0 ps-3"></ul>';
        galat.forEach(g => { const li = document.createElement('li'); li.textContent = g; box.firstChild.appendChild(li); });
        box.style.display = '';
        box.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function aturKirim(proses) {
        sedangKirim = proses;
        const btn = el('btnAjukan');
        el('teksAjukan').textContent = proses ? 'Mengajukan...' : 'Ajukan Peminjaman';
        el('ikonAjukan').className = proses ? 'spinner-border spinner-border-sm' : 'bi bi-send';
        btn.setAttribute('aria-busy', proses ? 'true' : 'false');
        perbarui();
    }

    el('formPinjam').addEventListener('submit', function (e) {
        if (sedangKirim) { e.preventDefault(); return; }       // cegah klik/enter berulang
        const galat = validasiForm();
        if (galat.length) { e.preventDefault(); tampilkanGalat(galat); return; }
        el('pesanForm').style.display = 'none';
        // Tunda penonaktifan tombol sampai pengiriman dimulai agar data form tetap terkirim.
        setTimeout(() => aturKirim(true), 0);
    });

    // Kembali ke halaman ini lewat cache browser (tombol Back): pulihkan tombol.
    window.addEventListener('pageshow', () => aturKirim(false));

    document.addEventListener('DOMContentLoaded', function () {
        const selectJenis = el('jenis_peminjaman');
        if (selectJenis) toggleDosen(selectJenis.value);
        const kategoriDari = (id) => OPSI_KATALOG.find(k => String(k.id) === String(id))?.jenis;
        LAMA_BARANG.forEach(b => { const k = kategoriDari(b.id_katalog) || 'alat'; tambahBarang(k, b); });
        LAMA_RUANGAN.forEach(tambahRuangan);
        perbarui();
        const galat = el('ringkasanError');
        if (galat) galat.scrollIntoView({ block: 'center' });
    });
</script>
@endpush
