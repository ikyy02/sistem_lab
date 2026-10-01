<style>
    :root {
        --sidebar-width: 272px;
        --header-height: 72px;

        /* Institusi, terang: charcoal hangat (bukan biru) untuk teks/tombol, emas sebagai aksen karakter */
        --ink: #2B2A28;
        --ink-dark: #1B1A18;
        --gold: #A9812F;
        --gold-bright: #C79A3F;
        --gold-tint: #FBF3E3;

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
    body { background: var(--body-bg); font-family: 'Inter', 'Segoe UI', system-ui, sans-serif; color: var(--text-dark); margin: 0; -webkit-font-smoothing: antialiased; }
    a { color: var(--ink); }
    h1, h2, .font-display { font-family: 'Fraunces', 'Inter', serif; letter-spacing: -.01em; }

    /* ===== Sidebar — terang, aksen emas sebagai identitas ===== */
    #sidebar { position: fixed; inset: 0 auto 0 0; width: var(--sidebar-width); background: #fff; z-index: 1040; display: flex; flex-direction: column; overflow-y: auto; border-right: 1px solid var(--border-color); transition: transform .3s ease; }
    .sidebar-brand { display: flex; align-items: center; gap: 13px; padding: 24px 22px 20px; border-bottom: 1px solid var(--border-color); text-decoration: none; }
    .sidebar-brand-icon { width: 44px; height: 44px; background: linear-gradient(155deg, var(--gold-bright), var(--gold)); border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(169,129,47,.28); }
    .sidebar-brand-icon i { color: #fff; font-size: 1.2rem; }
    .brand-name { font-family: 'Fraunces', serif; color: var(--ink); font-size: 1.2rem; font-weight: 600; letter-spacing: .01em; line-height: 1.15; }
    .brand-sub { color: var(--text-muted); font-size: .68rem; font-weight: 500; }
    .sidebar-nav { padding: 18px 14px; flex: 1; }
    .sidebar-section-label { display: flex; align-items: center; gap: 8px; color: #9AA1AB; font-size: .72rem; font-weight: 600; padding: 18px 10px 8px; }
    .sidebar-section-label::before { content: ''; width: 12px; height: 1px; background: var(--gold); flex-shrink: 0; }
    .sidebar-nav .nav-link { display: flex; align-items: center; gap: 12px; color: #4B5361; padding: 10.5px 14px; border-radius: 8px; font-size: .88rem; font-weight: 500; transition: background .15s, color .15s; border-left: 2px solid transparent; }
    .sidebar-nav .nav-link i { font-size: 1.02rem; width: 20px; text-align: center; color: #8A93A0; transition: color .15s; }
    .sidebar-nav .nav-link:hover { background: var(--gold-tint); color: var(--ink); }
    .sidebar-nav .nav-link:hover i { color: var(--gold); }
    .sidebar-nav .nav-link.active { background: var(--gold-tint); color: var(--ink); font-weight: 600; border-left-color: var(--gold); }
    .sidebar-nav .nav-link.active i { color: var(--gold); }
    .sidebar-nav .nav-link.disabled-link { opacity: .4; pointer-events: none; }
    .sidebar-footer { padding: 16px 18px; border-top: 1px solid var(--border-color); background: #FAFAF8; }
    .user-chip { width: 38px; height: 38px; border-radius: 50%; background: var(--gold-tint); color: var(--gold); border: 1px solid #EFE1C2; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: .85rem; flex-shrink: 0; }
    .user-name { font-size: .82rem; font-weight: 600; color: var(--ink); }
    .user-role { font-size: .7rem; color: var(--text-muted); }
    .min-w-0 { min-width: 0; }
    #sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(27,26,24,.42); z-index: 1039; }

    /* ===== Topbar & content ===== */
    #main-wrapper { margin-left: var(--sidebar-width); min-height: 100vh; display: flex; flex-direction: column; }
    #topbar { height: var(--header-height); background: #fff; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; padding: 0 30px; position: sticky; top: 0; z-index: 100; gap: 16px; }
    .topbar-toggle { display: none; background: none; border: 0; font-size: 1.4rem; padding: 4px 8px; border-radius: 6px; line-height: 1; color: var(--ink); }
    .topbar-toggle:hover { background: var(--body-bg); }
    .topbar-title { flex: 1; min-width: 0; }
    .topbar-title h1 { font-size: 1.2rem; font-weight: 600; margin: 0; }
    .topbar-title p { font-size: .78rem; color: var(--text-muted); margin: 0; }
    .topbar-user { display: flex; align-items: center; gap: 10px; background: none; border: 1px solid transparent; border-radius: 10px; padding: 5px 10px; }
    .topbar-user:hover { background: var(--body-bg); border-color: var(--border-color); }
    .topbar-user::after { margin-left: 4px; color: var(--text-muted); }
    .topbar-avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--gold-tint); color: var(--gold); display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: .85rem; flex-shrink: 0; border: 2px solid var(--gold); }
    #page-content { flex: 1; padding: 30px 32px 46px; }

    /* ===== Cards, buttons, forms, tables ===== */
    .card-modern { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 10px; box-shadow: 0 1px 3px rgba(43,42,40,.05); }
    .btn { border-radius: 8px; font-weight: 600; font-size: .85rem; }
    .btn-primary, .btn-silab { background: var(--ink); border-color: var(--ink); color: #fff; }
    .btn-primary:hover, .btn-silab:hover, .btn-primary:focus, .btn-silab:focus { background: var(--ink-dark); border-color: var(--ink-dark); color: #fff; }
    .btn-outline-silab { background: #fff; color: var(--ink); border: 1px solid var(--border-color); }
    .btn-outline-silab:hover { background: var(--body-bg); border-color: var(--muted-blue); color: var(--ink); }
    .btn-gold { background: var(--gold); border-color: var(--gold); color: #fff; }
    .btn-gold:hover { background: #8E6B26; border-color: #8E6B26; color: #fff; }
    .btn-edit { background: var(--body-bg); color: var(--ink); border: 1px solid var(--border-color); font-size: .78rem; padding: 5px 12px; }
    .btn-edit:hover { background: var(--ink); color: #fff; border-color: var(--ink); }
    .btn-del { background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; font-size: .78rem; padding: 5px 12px; }
    .btn-del:hover { background: #be123c; color: #fff; }
    .form-control, .form-select { border-color: var(--border-color); border-radius: 8px; font-size: .88rem; padding: .55rem .8rem; }
    .form-control:focus, .form-select:focus { border-color: var(--gold); box-shadow: 0 0 0 3px rgba(169,129,47,.14); }
    .form-label { font-size: .8rem; font-weight: 600; color: #3A4451; margin-bottom: 4px; }

    /* ===== Tabel — satu gaya untuk seluruh aplikasi ===== */
    .table > :not(caption) > * > * { padding: .8rem .75rem; }
    .table-silab thead th { background: var(--body-bg); color: var(--text-dark); font-size: .72rem; text-transform: uppercase; letter-spacing: .06em; font-weight: 700; border-bottom: 2px solid var(--gold); white-space: nowrap; }
    .table-silab tbody tr { border-top: 1px solid #EDF1F6; transition: background .12s; }
    .table-silab tbody tr:hover { background: #FAFBFD; }
    .table-silab td { font-size: .86rem; vertical-align: middle; }
    .sort-link { color: inherit; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
    .sort-link:hover, .sort-link.active { color: var(--gold); }
    .thumb { width: 56px; height: 56px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border-color); cursor: zoom-in; background: #fff; transition: transform .15s, box-shadow .15s; }
    .thumb:hover { transform: scale(1.06); box-shadow: 0 4px 12px rgba(43,42,40,.18); }
    .thumb-empty { width: 56px; height: 56px; border-radius: 8px; border: 1px dashed #d5cbc4; display: inline-flex; align-items: center; justify-content: center; color: #b9aca3; background: #faf8f6; }
    .badge-status { border-radius: 999px; font-weight: 600; padding: 5px 12px; font-size: .73rem; }
    .badge-status-ok { background: #ecfdf3; color: #146c43; }
    .badge-status-bad { background: #fef2f2; color: #b42318; }
    .table-footer { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 8px; padding: 12px 24px; border-top: 1px solid var(--border-color); background: #FAFBFD; font-size: .8rem; color: var(--text-muted); }

    .modal-content { border: 0; border-radius: 14px; box-shadow: 0 24px 60px rgba(43,42,40,.22); }
    .modal-header { background: var(--body-bg); border-bottom: 1px solid var(--border-color); border-radius: 14px 14px 0 0; }
    .modal-footer { border-top: 1px solid var(--border-color); }
    .alert { border: 0; border-radius: 10px; border-left: 4px solid; }
    .alert-success { background: #ecfdf3; color: #15803d; border-left-color: #16a34a; }
    .alert-danger { background: #fef2f2; color: #b42318; border-left-color: #d92d20; }
    .badge-tab { background: var(--primary-soft); color: var(--ink); }
    .page-link { color: var(--ink); border-color: var(--border-color); font-size: .82rem; }
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
    @media (max-width: 575.98px) { .topbar-title p { display: none; } }
</style>
