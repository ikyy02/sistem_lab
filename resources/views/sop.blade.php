@extends('layouts.app')

@section('title', 'SOP & Panduan — SIARKA')
@section('page-title', 'SOP & Panduan')
@section('page-subtitle', 'Standar Operasional Prosedur penggunaan fasilitas laboratorium.')

@push('styles')
<style>
    /* ===== Halaman SOP: hanya memakai token desain yang sudah ada ===== */
    .sop-panel {
        background: var(--bg-surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 26px;
    }
    .sop-panel-head {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        padding-bottom: 20px;
        border-bottom: 1px solid var(--border);
    }
    .sop-monogram {
        flex: 0 0 auto;
        width: 46px;
        height: 46px;
        border-radius: 50%;
        background: var(--info-soft);
        color: var(--info);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        font-weight: 700;
        line-height: 1;
    }
    .sop-panel-title {
        font-family: inherit;
        font-size: 1.05rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0 0 4px;
        letter-spacing: -0.01em;
    }
    .sop-panel-desc {
        font-size: .82rem;
        color: var(--text-secondary);
        margin: 0;
        max-width: 62ch;
        line-height: 1.6;
    }
    .sop-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 16px;
        margin-top: 22px;
    }
    .sop-card {
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 18px;
        background: var(--bg-subtle);
        border: 1px solid var(--border);
        border-radius: 12px;
    }
    .sop-card-icon {
        color: var(--text-secondary);
        height: 24px;
    }
    .sop-card-title {
        font-family: inherit;
        font-size: .88rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
        line-height: 1.4;
    }
    .sop-card-kode {
        display: block;
        font-size: .68rem;
        font-weight: 600;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: 4px;
    }
    .sop-card-desc {
        font-size: .79rem;
        color: var(--text-secondary);
        margin: 0;
        line-height: 1.6;
        flex: 1 1 auto;
    }
    .sop-card .btn { margin-top: auto; align-self: flex-start; }

    .sop-sep { border: 0; border-top: 1px solid var(--border); margin: 22px 0; opacity: 1; }

    .sop-step-head {
        font-size: .85rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0 0 14px;
    }
    .sop-steps {
        list-style: none;
        counter-reset: sop-step;
        margin: 0;
        padding: 0;
        display: grid;
        gap: 10px;
    }
    .sop-steps li {
        counter-increment: sop-step;
        display: grid;
        grid-template-columns: 22px 1fr;
        gap: 10px;
        align-items: start;
        font-size: .81rem;
        color: var(--text-secondary);
        line-height: 1.55;
    }
    .sop-steps li::before {
        content: counter(sop-step);
        width: 22px;
        height: 22px;
        border-radius: 50%;
        border: 1px solid var(--border);
        background: var(--bg-surface);
        color: var(--text-primary);
        font-size: .7rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .sop-steps li b { color: var(--text-primary); font-weight: 600; }
    .sop-note {
        margin: 16px 0 0;
        padding: 12px 14px;
        border-left: 2px solid var(--border);
        background: var(--bg-subtle);
        border-radius: 0 8px 8px 0;
        font-size: .78rem;
        color: var(--text-secondary);
        line-height: 1.6;
    }

    .sop-ringkasan-btn {
        background: var(--bg-surface);
        border: 1px solid var(--border);
        color: var(--text-primary);
        border-radius: 8px;
        height: 30px;
        padding: 0 11px;
        font-size: .76rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        transition: background 140ms ease, border-color 140ms ease;
    }
    .sop-ringkasan-btn:hover { background: var(--bg-page); border-color: #CBD5E1; }

    .sop-dialog {
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 0;
        width: min(640px, calc(100vw - 32px));
        max-height: min(84vh, 720px);
        background: var(--bg-surface);
        color: var(--text-primary);
        box-shadow: 0 18px 48px rgba(15, 23, 42, .16);
    }
    .sop-dialog::backdrop { background: rgba(15, 23, 42, .38); }
    .sop-dialog-head {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 18px 20px 14px;
        border-bottom: 1px solid var(--border);
    }
    .sop-dialog-head h3 {
        font-family: inherit;
        font-size: .95rem;
        font-weight: 600;
        margin: 0 0 3px;
        line-height: 1.4;
    }
    .sop-dialog-head p { font-size: .78rem; color: var(--text-secondary); margin: 0; line-height: 1.55; }
    .sop-dialog-close {
        margin-left: auto;
        flex: 0 0 auto;
        width: 26px;
        height: 26px;
        border-radius: 6px;
        border: 1px solid transparent;
        background: transparent;
        color: var(--text-secondary);
        font-size: 1rem;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .sop-dialog-close:hover { background: var(--bg-subtle); border-color: var(--border); color: var(--text-primary); }
    .sop-dialog-body { padding: 16px 20px; overflow-y: auto; max-height: 52vh; }
    .sop-dialog-body ul { margin: 0; padding: 0; list-style: none; display: grid; gap: 9px; }
    .sop-dialog-body li {
        position: relative;
        padding-left: 16px;
        font-size: .82rem;
        color: var(--text-secondary);
        line-height: 1.6;
    }
    .sop-dialog-body li::before {
        content: "";
        position: absolute;
        left: 3px;
        top: .58em;
        width: 4px;
        height: 4px;
        border-radius: 50%;
        background: var(--text-muted);
    }
    .sop-dialog-foot {
        display: flex;
        justify-content: flex-end;
        padding: 13px 20px;
        border-top: 1px solid var(--border);
    }
    .sop-dialog-foot .btn { height: 32px; font-size: 13px; padding: 0 14px; }

    /* Fokus keyboard selalu terlihat */
    .sop-card:focus-visible,
    .sop-ringkasan-btn:focus-visible,
    .sop-dialog-close:focus-visible,
    .sop-dialog-foot .btn:focus-visible,
    .sop-cetak:focus-visible {
        outline: 2px solid var(--info);
        outline-offset: 2px;
    }
    .sop-card:focus-visible { outline-offset: 3px; }

    .sop-cetak { height: 34px; padding: 0 16px; font-size: 13px; flex: 0 0 auto; }

    @media (max-width: 575.98px) {
        .sop-panel { padding: 18px; border-radius: 14px; }
        .sop-panel-head { flex-wrap: wrap; }
        .sop-cetak { width: 100%; }
        .sop-grid { grid-template-columns: 1fr; gap: 12px; }
        .sop-card .btn { align-self: stretch; }
        .sop-dialog-body { max-height: 60vh; }
    }
    @media (max-width: 380px) {
        .sop-panel { padding: 14px; }
    }

    /* ===== Cetak: sembunyikan chrome aplikasi, tampilkan 4 SOP utuh ===== */
    @media print {
        #sidebar, #sidebar-overlay, #topbar, .sidebar-collapse-toggle,
        .sop-screen-only, .sop-cetak, .sop-ringkasan-btn, .sop-dialog { display: none !important; }
        #main-wrapper { margin-left: 0 !important; }
        #page-content { padding: 0 !important; overflow: visible !important; }
        body { background: #fff !important; }
        .sop-print-only { display: block !important; }
        .sop-print-item { break-inside: avoid; page-break-inside: avoid; margin-bottom: 16pt; }
        .sop-print-item h2 { font-size: 11pt; margin: 0 0 3pt; }
        .sop-print-item .sop-print-desc {
            font-size: 9pt; color: #444; margin: 0 0 6pt; line-height: 1.5;
        }
        .sop-print-item .sop-print-label {
            font-size: 8pt; text-transform: uppercase; letter-spacing: .05em;
            color: #666; margin: 0 0 3pt; font-weight: 600;
        }
        .sop-print-item ol { margin: 0; padding-left: 15pt; }
        .sop-print-item li { font-size: 9pt; line-height: 1.5; margin-bottom: 2pt; color: #222; }
        .sop-print-foot { font-size: 8pt; color: #555; margin-top: 10pt; border-top: 1px solid #ccc; padding-top: 6pt; }
        @page { margin: 15mm; }
    }
</style>
@endpush

@section('content')

<div class="sop-screen-only">
    <div class="sop-panel">
        <div class="sop-panel-head">
            <div class="sop-monogram" aria-hidden="true">S</div>
            <div style="min-width:0;flex:1 1 auto;">
                <h2 class="sop-panel-title">SOP &amp; Panduan SIARKA</h2>
                <p class="sop-panel-desc">Pedoman resmi tata tertib penggunaan fasilitas laboratorium demi keselamatan kerja dan pemeliharaan sarana prasarana.</p>
            </div>
            <button type="button" class="btn btn-primary sop-cetak" onclick="window.print()">Cetak</button>
        </div>

        <div class="sop-grid">
            @forelse($sops as $sop)
            <div class="sop-card"
                 data-kode="{{ $sop->kode }}"
                 data-judul="{{ $sop->judul }}"
                 data-deskripsi="{{ $sop->deskripsi }}"
                 data-tujuan="{{ $sop->tujuan }}"
                 data-poin='@json($sop->poin)'>
                <span class="sop-card-icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        @switch($sop->kode)
                            @case('SOP-01')
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <line x1="9" y1="13" x2="15" y2="13"/>
                                <line x1="9" y1="17" x2="13" y2="17"/>
                                @break
                            @case('SOP-02')
                                <rect x="2" y="3" width="20" height="14" rx="2"/>
                                <line x1="8" y1="21" x2="16" y2="21"/>
                                <line x1="12" y1="17" x2="12" y2="21"/>
                                @break
                            @case('SOP-03')
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                <path d="M9.5 12l1.8 1.8L15 10"/>
                                @break
                            @default
                                <polyline points="23 4 23 10 17 10"/>
                                <polyline points="1 20 1 14 7 14"/>
                                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
                        @endswitch
                    </svg>
                </span>
                <h3 class="sop-card-title">
                    <span class="sop-card-kode">{{ $sop->kode }}</span>
                    {{ $sop->judul }}
                </h3>
                <p class="sop-card-desc">{{ $sop->deskripsi }}</p>
                <button type="button" class="sop-ringkasan-btn"
                        onclick="bukaRingkasan(this)"
                        data-kode="{{ $sop->kode }}" data-judul="{{ $sop->judul }}"
                        data-tujuan="{{ $sop->tujuan }}" data-poin='@json($sop->poin)'>Lihat Ringkasan</button>
            </div>
            @empty
            <p class="sop-panel-desc">Data SOP belum tersedia. Jalankan <code>php artisan db:seed --class=SopSeeder</code>.</p>
            @endforelse
        </div>

        <hr class="sop-sep">

        <h3 class="sop-step-head">Panduan Peminjaman (Ringkasan)</h3>
        <ol class="sop-steps">
            <li><span><b>Ajukan.</b> Pilih ruangan, alat, dan bahan pada menu Ajukan Peminjaman, lalu kirim pengajuan.</span></li>
            <li><span><b>Tunggu verifikasi.</b> Dosen penanggung jawab memverifikasi, laboran memeriksa ketersediaan dan jadwal.</span></li>
            <li><span><b>Ambil kunci.</b> Tunjukkan bukti persetujuan dan identitas kepada laboran.</span></li>
            <li><span><b>Gunakan.</b> Pakai fasilitas sesuai keperluan yang diajukan dan jaga kondisi perangkat.</span></li>
            <li><span><b>Kembalikan.</b> Serahkan kunci, laboran memeriksa kondisi, peminjaman ditutup berstatus Selesai.</span></li>
        </ol>
        <p class="sop-note">Peminjaman baru bisa diajukan setelah peminjaman sebelumnya berstatus <b>Selesai</b>.</p>
    </div>
</div>

{{-- Hanya tampil saat dicetak: 4 SOP utuh dengan seluruh poin --}}
<div class="sop-print-only" style="display:none;">
    @foreach($sops as $sop)
    <div class="sop-print-item">
        <h2>{{ $sop->kode }} — {{ $sop->judul }}</h2>
        <p class="sop-print-desc">{{ $sop->deskripsi }}</p>
        <p class="sop-print-label">Tujuan</p>
        <p class="sop-print-desc">{{ $sop->tujuan }}</p>
        <p class="sop-print-label">Ketentuan</p>
        <ol>
            @foreach($sop->poin as $poin)
            <li>{{ $poin }}</li>
            @endforeach
        </ol>
    </div>
    @endforeach
    <p class="sop-print-foot">SIARKA — SOP &amp; Panduan penggunaan fasilitas laboratorium.</p>
</div>

<dialog id="sopDialog" class="sop-dialog" aria-labelledby="sopDialogJudul">
    <div class="sop-dialog-head">
        <div style="min-width:0;">
            <h3 id="sopDialogJudul">SOP</h3>
            <p id="sopDialogTujuan"></p>
        </div>
        <button type="button" class="sop-dialog-close" onclick="document.getElementById('sopDialog').close()" aria-label="Tutup ringkasan">&times;</button>
    </div>
    <div class="sop-dialog-body">
        <ul id="sopDialogPoin"></ul>
    </div>
    <div class="sop-dialog-foot">
        <button type="button" class="btn btn-outline-silab" onclick="document.getElementById('sopDialog').close()">Tutup</button>
    </div>
</dialog>

<script>
(function () {
    const dialog = document.getElementById('sopDialog');
    const judul = document.getElementById('sopDialogJudul');
    const tujuan = document.getElementById('sopDialogTujuan');
    const poin = document.getElementById('sopDialogPoin');

    window.bukaRingkasan = function (el) {
        // Tombol "Lihat Ringkasan" punya dataset sendiri; kartu juga punya.
        const src = el.dataset.poin ? el : el.closest('.sop-card');
        if (!src || !src.dataset.poin) return;

        judul.textContent = src.dataset.kode + ' — ' + src.dataset.judul;
        tujuan.textContent = src.dataset.tujuan;
        poin.innerHTML = '';

        let list = [];
        try { list = JSON.parse(src.dataset.poin); } catch (e) { list = []; }
        list.forEach(function (teks) {
            const li = document.createElement('li');
            li.textContent = teks;
            poin.appendChild(li);
        });

        if (typeof dialog.showModal === 'function') {
            dialog.showModal();
            dialog.querySelector('.sop-dialog-close').focus();
        } else {
            dialog.setAttribute('open', 'open');
        }
    };

    // Klik di area backdrop menutup dialog.
    dialog.addEventListener('click', function (e) {
        if (e.target === dialog) dialog.close();
    });
})();
</script>

@endsection
