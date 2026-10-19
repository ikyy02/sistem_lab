{{--
    Sistem notifikasi tunggal SIARKA (modal tengah layar): umpan balik hasil tindakan (flash session
    success/error/warning/info) dan konfirmasi tindakan penting (atribut data-konfirmasi pada <form>).
    Validasi per kolom tetap inline di formulir. Dipasang setelah bootstrap.bundle.js.
--}}
@php
    $flash = collect([
        ['success', 'Berhasil', session('success')],
        ['error', 'Gagal', session('error')],
        ['warning', 'Perhatian', session('warning')],
        ['info', 'Informasi', session('info')],
    ])->filter(fn ($f) => filled($f[2]))->map(fn ($f) => ['tipe' => $f[0], 'judul' => $f[1], 'pesan' => $f[2]])->values();
@endphp

<div class="modal fade siarka-notif" id="notifModal" tabindex="-1" aria-hidden="true" aria-labelledby="notifJudul" aria-describedby="notifPesan">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <button type="button" class="btn-close notif-tutup" data-bs-dismiss="modal" aria-label="Tutup"></button>
            <div class="notif-ikon" id="notifIkon" aria-hidden="true"><i class="bi"></i></div>
            <h2 class="notif-judul" id="notifJudul"></h2>
            <p class="notif-pesan" id="notifPesan"></p>
            <div class="notif-aksi" id="notifAksi"></div>
        </div>
    </div>
</div>

@if($flash->isNotEmpty())
<script>window.SIARKA_FLASH = @json($flash);</script>
@endif
<script>
window.SiarkaNotif = (function () {
    const IKON = { success: 'bi-check-lg', error: 'bi-x-lg', warning: 'bi-exclamation-lg', info: 'bi-info-lg', bahaya: 'bi-trash3', konfirmasi: 'bi-question-lg' };
    // Judul & label tombol konfirmasi menurut kata pertama pesan (satu tempat, tanpa mengubah tiap form).
    const KONFIRMASI = {
        hapus: ['Hapus data?', 'Hapus', true], yakin: ['Hapus data?', 'Hapus', true],
        setujui: ['Setujui pengajuan?', 'Setujui', false], batalkan: ['Batalkan peminjaman?', 'Ya, batalkan', true],
        tandai: ['Selesaikan peminjaman?', 'Ya, selesai', false], catat: ['Catat pengembalian?', 'Catat', false],
        mulai: ['Mulai periode baru?', 'Mulai', false],
    };
    const el = document.getElementById('notifModal');
    let antrean = [], aktif = null, timer = null, fokusAwal = null;

    function tombol(label, kelas, onClick) {
        const b = document.createElement('button');
        b.type = 'button'; b.className = 'btn ' + kelas; b.textContent = label;
        b.addEventListener('click', onClick);
        return b;
    }

    function tampilkan(n) {
        aktif = n;
        const konfirm = n.tipe === 'konfirmasi';
        el.dataset.tipe = n.bahaya ? 'bahaya' : n.tipe;
        el.setAttribute('role', konfirm || n.tipe === 'error' ? 'alertdialog' : 'dialog');
        el.querySelector('#notifIkon i').className = 'bi ' + (n.bahaya ? IKON.bahaya : IKON[n.tipe]);
        el.querySelector('#notifJudul').textContent = n.judul;
        el.querySelector('#notifPesan').textContent = n.pesan;
        const aksi = el.querySelector('#notifAksi');
        aksi.replaceChildren();
        let utama;
        if (konfirm) {
            const batal = tombol('Batal', 'btn-outline-silab', function () { modal.hide(); });
            utama = tombol(n.ok, n.bahaya ? 'btn-danger-silab' : 'btn-primary', function () { aktif.dikonfirmasi = true; modal.hide(); });
            aksi.append(batal, utama);
            aktif.fokus = batal; // fokus awal pada pilihan aman
        } else {
            utama = tombol('Tutup', 'btn-primary', function () { modal.hide(); });
            aksi.append(utama);
            aktif.fokus = utama;
        }
        // Konfirmasi/gagal/peringatan tidak tertutup klik di luar; berhasil/info boleh.
        modal = new bootstrap.Modal(el, { backdrop: konfirm || n.tipe === 'error' || n.tipe === 'warning' ? 'static' : true, keyboard: true });
        modal.show();
    }
    let modal = null;

    el.addEventListener('shown.bs.modal', function () {
        if (aktif && aktif.fokus) aktif.fokus.focus();
        // Berhasil hilang sendiri (jeda saat disorot/difokuskan); lainnya menunggu pengguna.
        if (aktif && aktif.tipe === 'success') {
            const mulai = function () { clearTimeout(timer); timer = setTimeout(function () { modal.hide(); }, 5000); };
            const jeda = function () { clearTimeout(timer); };
            mulai();
            el.querySelector('.modal-content').onmouseenter = jeda; el.querySelector('.modal-content').onfocusin = jeda;
            el.querySelector('.modal-content').onmouseleave = mulai;
        }
    });
    el.addEventListener('hidden.bs.modal', function () {
        clearTimeout(timer);
        const selesai = aktif; aktif = null;
        modal.dispose(); modal = null;
        if (fokusAwal && document.contains(fokusAwal)) { try { fokusAwal.focus(); } catch (e) {} }
        if (selesai && selesai.dikonfirmasi && selesai.ok_fn) selesai.ok_fn();
        else if (antrean.length) setTimeout(function () { tampilkan(antrean.shift()); }, 120);
    });

    function tambah(n) {
        if (aktif || modal) { antrean.push(n); return; }
        fokusAwal = document.activeElement;
        tampilkan(n);
    }

    return {
        tampil: function (tipe, judul, pesan) { tambah({ tipe: tipe, judul: judul, pesan: pesan }); },
        konfirmasi: function (pesan, onOk, opsi) {
            opsi = opsi || {};
            const k = KONFIRMASI[(pesan.trim().split(/\s+/)[0] || '').toLowerCase()] || ['Konfirmasi tindakan', 'Lanjutkan', false];
            tambah({ tipe: 'konfirmasi', judul: opsi.judul || k[0], pesan: pesan, ok: opsi.ok || k[1], bahaya: opsi.bahaya ?? k[2], ok_fn: onOk });
        },
    };
})();

// Flash dari server: tampil sekali; tidak ada pesan, tidak ada popup.
(window.SIARKA_FLASH || []).forEach(function (f) { SiarkaNotif.tampil(f.tipe, f.judul, f.pesan); });

// Konfirmasi: form ber-atribut data-konfirmasi baru dikirim setelah pengguna menyetujui (fase capture agar mendahului penguncian kirim).
document.addEventListener('submit', function (e) {
    const f = e.target;
    if (!(f instanceof HTMLFormElement) || !f.hasAttribute('data-konfirmasi') || f.dataset.dikonfirmasi === '1') return;
    e.preventDefault();
    const pengirim = e.submitter || null;
    SiarkaNotif.konfirmasi(f.dataset.konfirmasi, function () {
        f.dataset.dikonfirmasi = '1';
        f.requestSubmit(pengirim && pengirim.form === f ? pengirim : undefined);
    });
}, true);
</script>
