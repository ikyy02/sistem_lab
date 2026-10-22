<style>
    /* Warna dan ukuran utama (CSS variables). Ubah tema aplikasi dari sini. */
    :root {
        --sidebar-width: 272px;
        --header-height: 72px;

        /* Institusi, terang: charcoal hangat (bukan biru) untuk teks/tombol, emas sebagai aksen karakter */
        --ink: #2B2A28;
        --ink-dark: #1B1A18;
        --gold: #A9812F;
        --gold-bright: #C79A3F;
        --gold-tint: #FBF3E3;
        --gold-text: #7A5A14; /* emas gelap: teks di atas putih/tint, kontras >= 4.5:1 */
        --focus-ring: #7A5A14;

        --primary: #2B2A28;
        --primary-dark: #1B1A18;
        --primary-soft: #D9EAFD;
        --accent: #A9812F;
        --body-bg: #F8FAFC;
        --card-bg: #ffffff;
        --text-dark: #2B2A28;
        --text-muted: #6B7280;
        --border-color: #BCCCDC;
        --muted-blue: #9AA6B2;
        --bs-primary: #2B2A28;
        --bs-link-color: #2B2A28;
    }
    * { box-sizing: border-box; }
    /* Gutter scrollbar tetap: konten tidak bergeser ke samping saat halaman menjadi/berhenti dapat digulir (pesan validasi, modal). */
    html { scrollbar-gutter: stable; -webkit-text-size-adjust: 100%; scroll-padding-top: calc(var(--header-height) + 16px); }
    body { background: var(--body-bg); font-family: 'Inter', 'Segoe UI', system-ui, sans-serif; color: var(--text-dark); margin: 0; line-height: 1.55; -webkit-font-smoothing: antialiased; }
    /* Fokus keyboard terlihat jelas di semua kontrol */
    :focus-visible { outline: 3px solid var(--focus-ring); outline-offset: 2px; border-radius: 4px; }
    #sidebar :focus-visible { outline-offset: -3px; }
    .skip-link { position: fixed; top: 8px; left: 8px; z-index: 2000; background: var(--ink); color: #fff; padding: 10px 16px; border-radius: 8px; font-weight: 600; font-size: .88rem; transform: translateY(-200%); text-decoration: none; }
    .skip-link:focus { transform: none; color: #fff; }
    /* Kontrol interaktif: kursor tangan, tanpa jeda tap 300ms */
    a, button, summary, [role="button"], .btn, .nav-link, .page-link, .form-select, label[for], [data-bs-toggle], input[type="checkbox"], input[type="radio"], input[type="file"] { cursor: pointer; touch-action: manipulation; }
    :disabled, .disabled, [aria-disabled="true"] { cursor: not-allowed; }
    a { color: var(--ink); }
    @media (prefers-reduced-motion: reduce) { *, *::before, *::after { transition-duration: .01ms !important; animation-duration: .01ms !important; scroll-behavior: auto !important; } }
    h1, h2, .font-display { font-family: 'Fraunces', 'Inter', serif; letter-spacing: -.01em; }

    /* ===== Sidebar — terang, aksen emas sebagai identitas ===== */
    #sidebar { position: fixed; inset: 0 auto 0 0; width: var(--sidebar-width); background: #fff; z-index: 1040; display: flex; flex-direction: column; overflow-y: auto; border-right: 1px solid var(--border-color); transition: transform .3s ease; }
    .sidebar-brand { display: flex; align-items: center; gap: 13px; padding: 24px 22px 20px; border-bottom: 1px solid var(--border-color); text-decoration: none; }
    .brand-name { font-family: 'Fraunces', serif; color: var(--ink); font-size: 1.2rem; font-weight: 600; letter-spacing: .01em; line-height: 1.15; }
    .brand-sub { color: var(--text-muted); font-size: .75rem; font-weight: 500; }
    .sidebar-nav { padding: 18px 14px; flex: 1; }
    .sidebar-nav .nav-link { display: flex; align-items: center; gap: 12px; color: #3F4654; min-height: 44px; padding: 10px 14px; border-radius: 8px; font-size: .88rem; font-weight: 500; transition: background .15s, color .15s; border-left: 2px solid transparent; }
    .sidebar-nav .nav-link i { font-size: 1.02rem; width: 20px; text-align: center; color: #6B7380; transition: color .15s; }
    .sidebar-nav .nav-link:hover { background: var(--gold-tint); color: var(--ink); }
    .sidebar-nav .nav-link:hover i { color: var(--gold-text); }
    .sidebar-nav .nav-link.active { background: var(--gold-tint); color: var(--ink); font-weight: 600; border-left-color: var(--gold); }
    .sidebar-nav .nav-link.active i { color: var(--gold-text); }
    .sidebar-nav .nav-link.disabled-link { opacity: .4; pointer-events: none; }
    .user-name { font-size: .82rem; font-weight: 600; color: var(--ink); }
    .user-role { font-size:.75rem; color: var(--text-muted); }
    .min-w-0 { min-width: 0; }
    #sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(27,26,24,.42); z-index: 1039; }

    /* ===== Topbar & content ===== */
    #main-wrapper { margin-left: var(--sidebar-width); min-height: 100vh; min-width: 0; display: flex; flex-direction: column; }
    #topbar { height: var(--header-height); background: #fff; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; padding: 0 30px; position: sticky; top: 0; z-index: 100; gap: 16px; }
    .topbar-toggle { display: none; align-items: center; justify-content: center; min-width: 44px; min-height: 44px; background: none; border: 0; font-size: 1.4rem; padding: 4px 8px; border-radius: 6px; line-height: 1; color: var(--ink); }
    .topbar-toggle:hover { background: var(--body-bg); }
    .topbar-title { flex: 1; min-width: 0; }
    .topbar-title h1 { font-size: 1.2rem; font-weight: 600; margin: 0; }
    .topbar-title p { font-size: .8rem; color: var(--text-muted); margin: 0; }
    .topbar-user { display: flex; align-items: center; gap: 10px; min-height: 44px; background: none; border: 1px solid transparent; border-radius: 10px; padding: 5px 10px; }
    .topbar-user:hover { background: var(--body-bg); border-color: var(--border-color); }
    .topbar-user::after { margin-left: 4px; color: var(--text-muted); }
    .topbar-avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--gold-tint); color: var(--gold-text); display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: .85rem; flex-shrink: 0; border: 2px solid var(--gold); }
    #page-content { flex: 1; min-width: 0; overflow-x: clip; padding: 30px 32px 46px; }
    .topbar-user .d-block { max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .btn[disabled], .btn:disabled { cursor: not-allowed; }

    /* ===== Cards, buttons, forms, tables ===== */
    .card-modern { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 10px; box-shadow: 0 1px 3px rgba(43,42,40,.05); }
    .btn { border-radius: 8px; font-weight: 600; font-size: .85rem; min-height: 40px; transition: background-color .15s, border-color .15s, color .15s, transform .08s; }
    .btn:active:not(:disabled) { transform: translateY(1px); }
    .btn-primary, .btn-silab { background: var(--ink); border-color: var(--ink); color: #fff; }
    .btn-primary:hover, .btn-silab:hover, .btn-primary:focus, .btn-silab:focus { background: var(--ink-dark); border-color: var(--ink-dark); color: #fff; }
    .btn-outline-silab { background: #fff; color: var(--ink); border: 1px solid var(--border-color); }
    .btn-outline-silab:hover { background: var(--body-bg); border-color: var(--muted-blue); color: var(--ink); }
<<<<<<< HEAD
    /* Tab di kiri, tombol aksi di kanan, satu baris di desktop; membungkus rapi di layar sempit. */
    .tab-action-bar { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
    .tab-action-bar .nav { flex: 0 1 auto; min-width: 0; flex-wrap: wrap; }
    .tab-action-bar .nav-link, .tab-action-bar .btn { min-height: 42px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; }
    .tab-action-bar .btn i { font-size: 1rem; line-height: 1; }
    .tab-action-bar__actions { display: flex; flex: 0 0 auto; flex-wrap: nowrap; gap: 8px; margin-left: auto; }
    .tab-action-bar__actions .btn { white-space: nowrap; }
    @media (max-width: 575.98px) { .tab-action-bar { align-items: stretch; } .tab-action-bar__actions { flex-wrap: wrap; width: 100%; margin-left: 0; } .tab-action-bar__actions .btn { flex: 1 1 0; } }
=======
    .tab-action-bar { display: flex; flex-direction: column; align-items: flex-start; gap: 12px; }
    .tab-action-bar .nav-link, .tab-action-bar .btn { min-height: 42px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; }
    .tab-action-bar .btn i { font-size: 1rem; line-height: 1; }
    .tab-action-bar__actions { display: flex; flex-wrap: wrap; gap: 8px; }
    @media (max-width: 575.98px) { .tab-action-bar { align-items: stretch; } .tab-action-bar__actions .btn { flex: 1 1 0; } }
>>>>>>> c4ec5d9 (refactor: rapikan SIARKA dan perbaiki akses serta peminjaman)
    .btn-gold { background: #8A6A22; border-color: #8A6A22; color: #fff; }
    .btn-gold:hover { background: #6F551B; border-color: #6F551B; color: #fff; }
    .btn-edit { background: var(--body-bg); color: var(--ink); border: 1px solid var(--border-color); font-size: .8rem; padding: 5px 12px; min-height: 36px; }
    .btn-edit:hover { background: var(--ink); color: #fff; border-color: var(--ink); }
    .btn-del { background: #fff1f2; color: #9f1239; border: 1px solid #fecdd3; font-size: .8rem; padding: 5px 12px; min-height: 36px; }
    .btn-del:hover { background: #be123c; color: #fff; }
    .form-control, .form-select { border-color: #8A9AAD; border-radius: 8px; font-size: .9rem; min-height: 42px; padding: .55rem .8rem; }
    .form-control::placeholder { color: #6B7280; opacity: 1; }
    .form-control[readonly], .form-control:disabled, .form-select:disabled { background: #F1F4F8; color: #4B5563; }
    .invalid-feedback { font-size: .8rem; }
    .form-control:focus, .form-select:focus { border-color: var(--gold-text); box-shadow: 0 0 0 3px rgba(122,90,20,.25); outline: none; }
    .form-label { font-size: .85rem; font-weight: 600; color: #333D4A; margin-bottom: 4px; }

    /* ===== Tabel — satu gaya untuk seluruh aplikasi ===== */
    .table > :not(caption) > * > * { padding: .8rem .75rem; }
    .table-silab thead th { background: var(--body-bg); color: var(--text-dark); font-size:.75rem; text-transform: uppercase; letter-spacing: .05em; font-weight: 700; border-bottom: 2px solid var(--gold); white-space: nowrap; }
    .table-silab tbody tr { border-top: 1px solid #EDF1F6; transition: background .12s; }
    .table-silab tbody tr:hover { background: #FAFBFD; }
    .table-silab td { font-size: .88rem; vertical-align: middle; }
    .sort-link { color: inherit; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
    .sort-link { min-height: 24px; }
    .sort-link:hover, .sort-link.active { color: var(--gold-text); }
    .thumb { width: 56px; height: 56px; aspect-ratio: 1; object-fit: cover; border-radius: 8px; border: 1px solid var(--border-color); cursor: zoom-in; background: #fff; transition: transform .15s, box-shadow .15s; }
    .thumb:hover { transform: scale(1.06); box-shadow: 0 4px 12px rgba(43,42,40,.18); }
    .thumb-empty { width: 56px; height: 56px; border-radius: 8px; border: 1px dashed #d5cbc4; display: inline-flex; align-items: center; justify-content: center; color: #b9aca3; background: #faf8f6; }
    .badge-status { border-radius: 999px; font-weight: 600; padding: 5px 12px; font-size:.75rem; }
    .badge-status::before { content: ''; display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: currentColor; margin-right: 6px; vertical-align: middle; } /* penanda non-warna */
    .badge-status-ok { background: #ecfdf3; color: #146c43; }
    .badge-status-bad { background: #fef2f2; color: #b42318; }
    .table-footer { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 8px; padding: 12px 24px; border-top: 1px solid var(--border-color); background: #FAFBFD; font-size: .8rem; color: #5B6472; }

    .modal-content { border: 0; border-radius: 14px; box-shadow: 0 24px 60px rgba(43,42,40,.22); }
    .modal-header { background: var(--body-bg); border-bottom: 1px solid var(--border-color); border-radius: 14px 14px 0 0; }
    .modal-footer { border-top: 1px solid var(--border-color); }
    /* Notifikasi modal SIARKA (umpan balik & konfirmasi) */
    .modal-backdrop { --bs-backdrop-bg: #1B1A18; --bs-backdrop-opacity: .5; }
    .siarka-notif { --tone: var(--ink); --tint: #EEF2F7; }
    .siarka-notif[data-tipe="success"] { --tone: #146c43; --tint: #E7F4EC; }
    .siarka-notif[data-tipe="error"], .siarka-notif[data-tipe="bahaya"] { --tone: #b42318; --tint: #FCEBEA; }
    .siarka-notif[data-tipe="warning"], .siarka-notif[data-tipe="konfirmasi"] { --tone: var(--gold-text); --tint: var(--gold-tint); }
    .siarka-notif[data-tipe="info"] { --tone: var(--ink); --tint: var(--primary-soft); }
    .siarka-notif .modal-dialog { max-width: 400px; }
    .siarka-notif .modal-content { position: relative; padding: 34px 28px 26px; text-align: center; border-radius: 18px; background: #fff; }
    .siarka-notif .notif-tutup { position: absolute; top: 12px; right: 12px; padding: 10px; font-size:.75rem; }
    .siarka-notif .notif-ikon { width: 64px; height: 64px; margin: 0 auto 16px; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: var(--tint); color: var(--tone); font-size: 1.6rem; box-shadow: 0 0 0 8px color-mix(in srgb, var(--tint) 45%, transparent); }
    .siarka-notif .notif-judul { font-family: 'Fraunces', serif; font-size: 1.3rem; font-weight: 600; margin: 0 0 6px; color: var(--ink); }
    .siarka-notif .notif-pesan { font-size: .9rem; line-height: 1.5; color: var(--text-muted); margin: 0 0 22px; overflow-wrap: anywhere; }
    .siarka-notif .notif-aksi { display: flex; gap: 10px; justify-content: center; }
    .siarka-notif .notif-aksi .btn { min-width: 120px; min-height: 42px; }
    .btn-danger-silab { background: #b42318; border-color: #b42318; color: #fff; }
    .btn-danger-silab:hover, .btn-danger-silab:focus { background: #912018; border-color: #912018; color: #fff; }
    @media (max-width: 575.98px) { .siarka-notif .modal-dialog { margin: 16px; } .siarka-notif .notif-aksi { flex-direction: column-reverse; } .siarka-notif .notif-aksi .btn { width: 100%; } }
    .alert { border: 0; border-radius: 10px; border-left: 4px solid; }
    .alert-success { background: #ecfdf3; color: #146c43; border-left-color: #16a34a; }
    .alert-warning { background: #fffbeb; color: #7A4F00; border-left-color: #d99a00; }
    .alert-danger { background: #fef2f2; color: #b42318; border-left-color: #d92d20; }
    .badge-tab { background: var(--primary-soft); color: var(--ink); }
    .page-link { color: var(--ink); border-color: var(--border-color); font-size: .85rem; min-width: 40px; min-height: 40px; display: inline-flex; align-items: center; justify-content: center; }
    .page-item.active .page-link { background: var(--ink); border-color: var(--ink); color: #fff; }

    /* Tabs kategori */
    .nav-pills .nav-link.active-tab { background: var(--ink); color: #fff; border-color: var(--ink); }

    /* Kompatibilitas gaya inline lama -> tema baru */
    [style*="background:#9AA6B2"], [style*="background: #9AA6B2"], [style*="background:#1C232C"] { background: var(--ink) !important; }
    [style*="color:#9AA6B2"], [style*="color: #9AA6B2"], [style*="color:#7E8996"] { color: var(--ink) !important; }
    [style*="linear-gradient(135deg, #D9EAFD"] { background: var(--primary-soft) !important; border-color: var(--border-color) !important; }

    @media (max-width: 991.98px) {
        #sidebar { transform: translateX(-100%); }
        #sidebar.show { transform: translateX(0); }
        #sidebar-overlay.show { display: block; }
        #main-wrapper { margin-left: 0; }
        .topbar-toggle { display: flex; }
        #topbar { padding: 0 16px; }
        #page-content { padding: 20px 16px 32px; }
    }
    @media (max-width: 575.98px) {
        .topbar-title p { display: none; }
        /* 16px mencegah zoom otomatis iOS saat fokus input */
        .form-control, .form-select { font-size: 1rem; }
    }
    /* Layar sentuh: target 44px */
    @media (pointer: coarse) {
        .btn, .btn-edit, .btn-del, .page-link, .dropdown-item, .nav-pills .nav-link, .sort-link { min-height: 44px; }
        .btn-close { min-width: 44px; min-height: 44px; }
    }
    @media (min-width: 1500px) { #page-content { max-width: 1480px; width: 100%; margin-inline: auto; } }
</style>
