<style>
    :root {
        --sidebar-width: 268px;
        --header-height: 68px;
        --primary: #1e3a8a;
        --primary-dark: #172c6b;
        --primary-soft: #e8edf9;
        --accent: #b7791f;
        --body-bg: #f3f5f9;
        --card-bg: #ffffff;
        --text-dark: #111827;
        --text-muted: #5b6577;
        --border-color: #dde3ee;
        --bs-primary: #1e3a8a;
        --bs-link-color: #1e3a8a;
    }
    * { box-sizing: border-box; }
    body { background: var(--body-bg); font-family: 'Inter', 'Segoe UI', system-ui, sans-serif; color: var(--text-dark); margin: 0; -webkit-font-smoothing: antialiased; }
    a { color: var(--primary); }

    /* Sidebar */
    #sidebar { position: fixed; inset: 0 auto 0 0; width: var(--sidebar-width); background: #fff; z-index: 1040; display: flex; flex-direction: column; overflow-y: auto; border-right: 1px solid var(--border-color); transition: transform .3s ease; }
    .sidebar-brand { display: flex; align-items: center; gap: 12px; padding: 20px 20px 18px; border-bottom: 1px solid var(--border-color); text-decoration: none; }
    .sidebar-brand-icon { width: 42px; height: 42px; background: var(--primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; border-bottom: 3px solid var(--accent); }
    .sidebar-brand-icon i { color: #fff; font-size: 1.2rem; }
    .brand-name { color: var(--primary); font-size: 1.15rem; font-weight: 800; letter-spacing: .04em; line-height: 1.15; }
    .brand-sub { color: var(--text-muted); font-size: .68rem; font-weight: 500; }
    .sidebar-nav { padding: 14px 12px; flex: 1; }
    .sidebar-section-label { color: #8b94a7; font-size: .66rem; font-weight: 700; text-transform: uppercase; letter-spacing: .09em; padding: 14px 12px 6px; }
    .sidebar-nav .nav-link { display: flex; align-items: center; gap: 11px; color: #3d475a; padding: 10px 12px; border-radius: 8px; font-size: .88rem; font-weight: 500; transition: background .15s, color .15s; }
    .sidebar-nav .nav-link i { font-size: 1.02rem; width: 20px; text-align: center; }
    .sidebar-nav .nav-link:hover { background: var(--primary-soft); color: var(--primary); }
    .sidebar-nav .nav-link.active { background: var(--primary); color: #fff; font-weight: 600; box-shadow: 0 2px 6px rgba(30,58,138,.28); }
    .sidebar-nav .nav-link.disabled-link { opacity: .4; pointer-events: none; }
    .sidebar-footer { padding: 14px 18px; border-top: 1px solid var(--border-color); background: #fafbfd; }
    .user-chip, .topbar-avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--primary-soft); color: var(--primary); display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: .85rem; flex-shrink: 0; }
    .user-name { font-size: .82rem; font-weight: 600; }
    .user-role { font-size: .7rem; color: var(--text-muted); }
    .min-w-0 { min-width: 0; }
    #sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(15,23,42,.45); z-index: 1039; }

    /* Topbar & content */
    #main-wrapper { margin-left: var(--sidebar-width); min-height: 100vh; display: flex; flex-direction: column; }
    #topbar { height: var(--header-height); background: #fff; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; padding: 0 28px; position: sticky; top: 0; z-index: 100; gap: 16px; }
    .topbar-toggle { display: none; background: none; border: 0; font-size: 1.4rem; padding: 4px 8px; border-radius: 6px; line-height: 1; }
    .topbar-toggle:hover { background: var(--body-bg); }
    .topbar-title { flex: 1; min-width: 0; }
    .topbar-title h1 { font-size: 1.1rem; font-weight: 700; margin: 0; }
    .topbar-title p { font-size: .78rem; color: var(--text-muted); margin: 0; }
    .topbar-user { display: flex; align-items: center; gap: 10px; background: none; border: 1px solid transparent; border-radius: 10px; padding: 5px 10px; }
    .topbar-user:hover { background: var(--body-bg); border-color: var(--border-color); }
    .topbar-user::after { margin-left: 4px; color: var(--text-muted); }
    #page-content { flex: 1; padding: 28px 30px 44px; }

    /* Cards, buttons, forms, tables */
    .card-modern { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; box-shadow: 0 1px 2px rgba(16,24,40,.05); }
    .btn { border-radius: 8px; font-weight: 600; font-size: .85rem; }
    .btn-primary, .btn-silab { background: var(--primary); border-color: var(--primary); color: #fff; }
    .btn-primary:hover, .btn-silab:hover, .btn-primary:focus, .btn-silab:focus { background: var(--primary-dark); border-color: var(--primary-dark); color: #fff; }
    .btn-outline-silab { background: #fff; color: var(--primary); border: 1px solid var(--border-color); }
    .btn-outline-silab:hover { background: var(--primary-soft); border-color: var(--primary); color: var(--primary); }
    .btn-edit { background: var(--primary-soft); color: var(--primary); border: 1px solid #cdd7f1; font-size: .78rem; padding: 5px 12px; }
    .btn-edit:hover { background: var(--primary); color: #fff; }
    .btn-del { background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; font-size: .78rem; padding: 5px 12px; }
    .btn-del:hover { background: #be123c; color: #fff; }
    .form-control, .form-select { border-color: var(--border-color); border-radius: 8px; font-size: .88rem; padding: .55rem .8rem; }
    .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(30,58,138,.14); }
    .form-label { font-size: .8rem; font-weight: 600; color: #334155; margin-bottom: 4px; }
    .table > :not(caption) > * > * { padding: .8rem .75rem; }
    .table-silab thead th { background: #f6f8fc; color: #4b5567; font-size: .72rem; text-transform: uppercase; letter-spacing: .05em; font-weight: 700; border-bottom: 1px solid var(--border-color); white-space: nowrap; }
    .table-silab tbody tr { border-top: 1px solid #edf0f6; transition: background .12s; }
    .table-silab tbody tr:hover { background: #f8faff; }
    .table-silab td { font-size: .86rem; vertical-align: middle; }
    .sort-link { color: inherit; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
    .sort-link:hover, .sort-link.active { color: var(--primary); }
    .modal-content { border: 0; border-radius: 14px; box-shadow: 0 20px 50px rgba(15,23,42,.2); }
    .modal-header { background: #f6f8fc; border-bottom: 1px solid var(--border-color); border-radius: 14px 14px 0 0; }
    .modal-footer { border-top: 1px solid var(--border-color); }
    .alert { border: 0; border-radius: 10px; border-left: 4px solid; }
    .alert-success { background: #ecfdf3; color: #146c43; border-left-color: #198754; }
    .alert-danger { background: #fef2f2; color: #b42318; border-left-color: #d92d20; }
    .badge-tab { background: var(--primary-soft); color: var(--primary); }
    .page-link { color: var(--primary); border-color: var(--border-color); font-size: .82rem; }
    .page-item.active .page-link { background: var(--primary); border-color: var(--primary); color: #fff; }

    /* Kompatibilitas halaman lama yang memakai warna hijau inline -> tema baru */
    [style*="background:#16a34a"], [style*="background: #16a34a"] { background: var(--primary) !important; }
    [style*="color:#16a34a"], [style*="color: #16a34a"], [style*="color:#15803d"] { color: var(--primary) !important; }
    [style*="linear-gradient(135deg, #f0fdf4"] { background: var(--primary-soft) !important; border-color: #cdd7f1 !important; }

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
