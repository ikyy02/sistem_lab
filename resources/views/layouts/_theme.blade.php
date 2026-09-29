<style>
    :root {
        --sidebar-width: 272px;
        --header-height: 72px;

        /* Institusi: sidebar gelap "ink" + aksen perunggu/emas, permukaan terang dari palet klien */
        --ink: #1C232C;
        --ink-soft: #2A333F;
        --ink-line: rgba(255,255,255,.09);
        --gold: #AD8A3E;
        --gold-bright: #C9A75B;

        --primary: #1C232C;
        --primary-dark: #10151C;
        --primary-soft: #D9EAFD;
        --accent: #AD8A3E;
        --body-bg: #F8FAFC;
        --card-bg: #ffffff;
        --text-dark: #1C232C;
        --text-muted: #5D6875;
        --border-color: #BCCCDC;
        --muted-blue: #9AA6B2;
        --bs-primary: #1C232C;
        --bs-link-color: #1C232C;
    }
    * { box-sizing: border-box; }
    body { background: var(--body-bg); font-family: 'Inter', 'Segoe UI', system-ui, sans-serif; color: var(--text-dark); margin: 0; -webkit-font-smoothing: antialiased; }
    a { color: var(--primary); }
    h1, h2, .font-display { font-family: 'Fraunces', 'Inter', serif; letter-spacing: -.01em; }

    /* ===== Sidebar — institusi, gelap, aksen emas ===== */
    #sidebar { position: fixed; inset: 0 auto 0 0; width: var(--sidebar-width); background: var(--ink); z-index: 1040; display: flex; flex-direction: column; overflow-y: auto; transition: transform .3s ease; box-shadow: 2px 0 24px rgba(16,21,28,.18); }
    .sidebar-brand { display: flex; align-items: center; gap: 13px; padding: 24px 22px 20px; border-bottom: 1px solid var(--ink-line); text-decoration: none; }
    .sidebar-brand-icon { width: 44px; height: 44px; background: linear-gradient(155deg, var(--gold-bright), var(--gold)); border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(173,138,62,.35); }
    .sidebar-brand-icon i { color: var(--ink); font-size: 1.2rem; }
    .brand-name { font-family: 'Fraunces', serif; color: #fff; font-size: 1.2rem; font-weight: 600; letter-spacing: .02em; line-height: 1.15; }
    .brand-sub { color: #8891A0; font-size: .68rem; font-weight: 500; }
    .sidebar-nav { padding: 18px 14px; flex: 1; }
    .sidebar-section-label { display: flex; align-items: center; gap: 8px; color: #6B7684; font-size: .72rem; font-weight: 600; padding: 18px 10px 8px; }
    .sidebar-section-label::before { content: ''; width: 12px; height: 1px; background: var(--gold); flex-shrink: 0; }
    .sidebar-nav .nav-link { display: flex; align-items: center; gap: 12px; color: #B7BFC9; padding: 10.5px 14px; border-radius: 8px; font-size: .88rem; font-weight: 500; transition: background .15s, color .15s; border-left: 2px solid transparent; }
    .sidebar-nav .nav-link i { font-size: 1.02rem; width: 20px; text-align: center; color: #7C8794; transition: color .15s; }
    .sidebar-nav .nav-link:hover { background: var(--ink-soft); color: #fff; }
    .sidebar-nav .nav-link:hover i { color: var(--gold-bright); }
    .sidebar-nav .nav-link.active { background: var(--ink-soft); color: #fff; font-weight: 600; border-left-color: var(--gold); }
    .sidebar-nav .nav-link.active i { color: var(--gold-bright); }
    .sidebar-nav .nav-link.disabled-link { opacity: .38; pointer-events: none; }
    .sidebar-footer { padding: 16px 18px; border-top: 1px solid var(--ink-line); }
    .user-chip { width: 38px; height: 38px; border-radius: 50%; background: var(--ink-soft); color: var(--gold-bright); border: 1px solid var(--ink-line); display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: .85rem; flex-shrink: 0; }
    .user-name { font-size: .82rem; font-weight: 600; color: #fff; }
    .user-role { font-size: .7rem; color: #8891A0; }
    .min-w-0 { min-width: 0; }
    #sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(16,21,28,.5); z-index: 1039; }

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
    .topbar-avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--ink); color: var(--gold-bright); display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: .85rem; flex-shrink: 0; border: 2px solid var(--gold); }
    #page-content { flex: 1; padding: 30px 32px 46px; }

    /* ===== Cards, buttons, forms, tables ===== */
    .card-modern { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 10px; box-shadow: 0 1px 3px rgba(28,35,44,.06); }
    .btn { border-radius: 8px; font-weight: 600; font-size: .85rem; }
    .btn-primary, .btn-silab { background: var(--ink); border-color: var(--ink); color: #fff; }
    .btn-primary:hover, .btn-silab:hover, .btn-primary:focus, .btn-silab:focus { background: var(--primary-dark); border-color: var(--primary-dark); color: #fff; }
    .btn-outline-silab { background: #fff; color: var(--ink); border: 1px solid var(--border-color); }
    .btn-outline-silab:hover { background: var(--body-bg); border-color: var(--muted-blue); color: var(--ink); }
    .btn-gold { background: var(--gold); border-color: var(--gold); color: #fff; }
    .btn-gold:hover { background: #96742F; border-color: #96742F; color: #fff; }
    .btn-edit { background: var(--body-bg); color: var(--ink); border: 1px solid var(--border-color); font-size: .78rem; padding: 5px 12px; }
    .btn-edit:hover { background: var(--ink); color: #fff; border-color: var(--ink); }
    .btn-del { background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; font-size: .78rem; padding: 5px 12px; }
    .btn-del:hover { background: #be123c; color: #fff; }
    .form-control, .form-select { border-color: var(--border-color); border-radius: 8px; font-size: .88rem; padding: .55rem .8rem; }
    .form-control:focus, .form-select:focus { border-color: var(--gold); box-shadow: 0 0 0 3px rgba(173,138,62,.14); }
    .form-label { font-size: .8rem; font-weight: 600; color: #3A4451; margin-bottom: 4px; }
    .table > :not(caption) > * > * { padding: .8rem .75rem; }
    .table-silab thead th { background: var(--body-bg); color: var(--text-dark); font-size: .72rem; text-transform: uppercase; letter-spacing: .06em; font-weight: 700; border-bottom: 2px solid var(--gold); white-space: nowrap; }
    .table-silab tbody tr { border-top: 1px solid #EDF1F6; transition: background .12s; }
    .table-silab tbody tr:hover { background: #FAFBFD; }
    .table-silab td { font-size: .86rem; vertical-align: middle; }
    .sort-link { color: inherit; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
    .sort-link:hover, .sort-link.active { color: var(--gold); }
    .modal-content { border: 0; border-radius: 14px; box-shadow: 0 24px 60px rgba(16,21,28,.28); }
    .modal-header { background: var(--body-bg); border-bottom: 1px solid var(--border-color); border-radius: 14px 14px 0 0; }
    .modal-footer { border-top: 1px solid var(--border-color); }
    .alert { border: 0; border-radius: 10px; border-left: 4px solid; }
    .alert-success { background: #ecfdf3; color: #15803d; border-left-color: #16a34a; }
    .alert-danger { background: #fef2f2; color: #b42318; border-left-color: #d92d20; }
    .badge-tab { background: var(--primary-soft); color: var(--ink); }
    .page-link { color: var(--ink); border-color: var(--border-color); font-size: .82rem; }
    .page-item.active .page-link { background: var(--ink); border-color: var(--ink); color: #fff; }

    /* Tabs kategori: aktif memakai ink + garis emas */
    .nav-pills .nav-link.active-tab { background: var(--ink); color: #fff; border-color: var(--ink); }

    /* Kompatibilitas gaya inline lama -> tema baru */
    [style*="background:#9AA6B2"], [style*="background: #9AA6B2"] { background: var(--ink) !important; }
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
