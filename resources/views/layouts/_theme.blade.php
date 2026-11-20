<style>
    :root {
        /* Layout */
        --sidebar-width: 260px;
        --header-height: 64px;

        /* Spasi Kelipatan 8px */
        --space-1: 8px;
        --space-2: 16px;
        --space-3: 24px;
        --space-4: 32px;

        /* Radius Standar */
        --radius-sm: 6px;
        --radius-md: 8px;   /* input, tombol, badge */
        --radius-lg: 12px;  /* card, modal */

        /* Tinggi Kontrol */
        --control-h: 40px;
        --control-h-sm: 32px;

        /* Warna Desain Sistem SIARKA */
        --primary:        #0F172A; /* Navy solid */
        --primary-hover:  #1E293B;
        --primary-focus:  rgba(15, 23, 42, 0.15);

        --font-sans:      'Inter', system-ui, -apple-system, sans-serif;

        --border:         #E2E8F0;
        --text-primary:   #0F172A;
        --text-secondary: #64748B;
        --text-muted:     #94A3B8;
        --bg-page:        #F8FAFC;
        --bg-surface:     #FFFFFF;
        --bg-subtle:      #F1F5F9;

        --danger:         #DC2626;
        --danger-soft:    #FEE2E2;
        --danger-hover:   #B91C1C;
        --info-soft:      #E0F2FE;
        --info:           #0284C7;
        --warning:        #D97706;
        --warning-soft:   #FEF3C7;
        --success:        #16A34A;
        --success-soft:   #DCFCE7;

        /* Backward Compatibility Mappings */
        --body-bg: var(--bg-page);
        --card-bg: var(--bg-surface);
        --text-dark: var(--text-primary);
        --border-color: var(--border);
        --brand-blue: var(--primary);
        --brand-blue-hover: var(--primary-hover);
        --brand-blue-light: var(--info-soft);
        --primary-dark: var(--primary-hover);
        --primary-soft: var(--bg-subtle);
        --accent: var(--primary);
        --accent-hover: var(--primary-hover);
        --accent-soft: var(--bg-subtle);
        --danger-bg: var(--danger-soft);
        --warning-bg: var(--warning-soft);
        --success-bg: var(--success-soft);
        --info-bg: var(--info-soft);
        --ink: var(--primary);
        --ink-dark: var(--primary-hover);
        --gold: var(--primary);
        --gold-tint: var(--bg-subtle);
        --gold-text: var(--text-primary);
        --muted-blue: var(--text-secondary);
        --bs-primary: var(--primary);
        --bs-link-color: var(--primary);
        --bs-body-color: var(--text-primary);
        --bs-body-bg: var(--bg-page);
        --bs-border-color: var(--border);
        --ease-standard: cubic-bezier(.22, .61, .36, 1);
    }

    * { box-sizing: border-box; }
    html { scrollbar-gutter: stable; scroll-padding-top: calc(var(--header-height) + 16px); }
    body {
        background: var(--bg-page);
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        font-size: 14px;
        font-weight: 400;
        color: var(--text-primary);
        margin: 0;
        -webkit-font-smoothing: antialiased;
        line-height: 1.5;
    }
    a { color: var(--primary); text-decoration: none; }
    a:hover { color: var(--primary-hover); }

    /* Tipografi Standar */
    h1, h2, h3, h4, h5, h6, .font-display {
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        letter-spacing: -0.015em;
        color: var(--text-primary);
    }
    .page-title, .topbar-title h1, h1.font-display {
        font-size: 20px;
        font-weight: 600;
        line-height: 1.3;
        color: var(--text-primary);
        margin: 0;
    }
    .card-title, .h-card, h5.fw-bold {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
    }
    .greet { font-size: 18px; font-weight: 600; color: var(--text-primary); margin: 0; }
    .greet em { font-style: normal; color: var(--primary); }
    .stat-num { font-size: 22px; font-weight: 600; color: var(--text-primary); line-height: 1.2; }

    /* ===== Toolbar Komponen (Langkah 2: Tab & Actions) ===== */
    .tab-action-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
    }
    .tab-action-bar__actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    /* Tab Bergaya Pill Abu Muda (Bukan Hitam Solid) */
    .nav-pills {
        gap: 8px;
    }
    .nav-pills .nav-link {
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--radius-md);
        font-size: 14px;
        font-weight: 500;
        color: var(--text-secondary);
        background: var(--bg-surface);
        border: 1px solid var(--border);
        padding: 0 16px;
        transition: all 150ms ease;
    }
    .nav-pills .nav-link:hover {
        background: var(--bg-subtle);
        color: var(--text-primary);
    }
    .nav-pills .nav-link.active-tab,
    .nav-pills .nav-link.active {
        background: var(--bg-subtle) !important;
        color: var(--primary) !important;
        border-color: #CBD5E1 !important;
        font-weight: 600 !important;
        box-shadow: inset 0 -2px 0 0 var(--primary) !important;
    }

    /* ===== Sidebar Bersih & Bebas Duplikasi ===== */
    body.sidebar-collapsed {
        --sidebar-width: 64px;
    }

    #sidebar.siarka-sidebar {
        position: fixed;
        inset: 0 auto 0 0;
        width: var(--sidebar-width);
        background: var(--bg-surface);
        z-index: 1040;
        display: flex;
        flex-direction: column;
        border-right: 1px solid var(--border);
        transition: width 200ms var(--ease-standard), transform 200ms var(--ease-standard);
        overflow: hidden;
    }

    .sidebar-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 16px;
        border-bottom: 1px solid var(--border);
        height: var(--header-height);
        flex-shrink: 0;
    }
    .sidebar-brand-box {
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        color: var(--text-primary);
        min-width: 0;
        overflow: hidden;
    }
    .sidebar-logo {
        flex-shrink: 0;
        width: 30px;
        height: 30px;
    }
    .sidebar-brand-info {
        display: flex;
        flex-direction: column;
        min-width: 0;
        line-height: 1.25;
        transition: opacity 200ms var(--ease-standard);
    }
    .sidebar-app-name {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--text-primary);
        letter-spacing: -0.01em;
    }
    .sidebar-app-sub {
        font-size: 0.7rem;
        color: var(--text-secondary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .sidebar-collapse-toggle {
        background: transparent;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-secondary);
        cursor: pointer;
        flex-shrink: 0;
        transition: color 200ms var(--ease-standard), background-color 200ms var(--ease-standard);
    }
    .sidebar-collapse-toggle:hover {
        background: var(--bg-subtle);
        color: var(--primary);
    }
    .collapse-icon {
        font-size: 0.8rem;
        transition: transform 200ms var(--ease-standard);
    }
    body.sidebar-collapsed .collapse-icon {
        transform: rotate(180deg);
    }
    body.sidebar-collapsed .sidebar-brand-info {
        display: none;
    }
    body.sidebar-collapsed .sidebar-collapse-toggle {
        margin: 0 auto;
    }

    .sidebar-nav-container {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 16px 10px;
    }
    .sidebar-group {
        margin-bottom: 20px;
    }
    .sidebar-group-title {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--text-muted);
        padding: 4px 10px 8px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        transition: opacity 200ms var(--ease-standard);
    }
    body.sidebar-collapsed .sidebar-group-title {
        opacity: 0;
        height: 0;
        padding: 0;
        margin: 0;
        overflow: hidden;
    }
    .sidebar-menu-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 3px;
    }
    .sidebar-menu-item {
        position: relative;
    }
    .sidebar-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 14px;
        min-height: 44px;
        border-radius: var(--radius-md);
        color: var(--text-secondary);
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        border-left: 3px solid transparent;
        transition: background-color 150ms var(--ease-standard), color 150ms var(--ease-standard), border-left-color 150ms var(--ease-standard);
        position: relative;
        white-space: nowrap;
    }
    .sidebar-link-icon {
        font-size: 1.05rem;
        width: 20px;
        text-align: center;
        color: var(--text-secondary);
        flex-shrink: 0;
        transition: color 150ms var(--ease-standard);
    }
    .sidebar-link-text {
        flex: 1;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        transition: opacity 150ms var(--ease-standard);
    }
    .sidebar-link:hover {
        background-color: var(--bg-subtle);
        color: var(--primary);
    }
    .sidebar-link:hover .sidebar-link-icon {
        color: var(--primary);
    }
    .sidebar-link.is-active {
        background-color: var(--bg-subtle);
        color: var(--primary);
        font-weight: 600;
        border-left: 3px solid var(--primary);
    }
    .sidebar-link.is-active .sidebar-link-icon {
        color: var(--primary);
    }
    .sidebar-item-badge {
        padding: 2px 6px;
        border-radius: 4px;
        background-color: var(--danger-soft);
        color: var(--danger);
        font-size: 11px;
        font-weight: 600;
        line-height: 1.2;
        flex-shrink: 0;
        transition: opacity 150ms var(--ease-standard);
    }
    body.sidebar-collapsed .sidebar-link-text {
        display: none;
    }
    body.sidebar-collapsed .sidebar-item-badge {
        position: absolute;
        top: 4px;
        right: 6px;
        padding: 2px 4px;
        font-size: 10px;
    }
    body.sidebar-collapsed .sidebar-link {
        justify-content: center;
        padding: 10px 0;
    }

    #sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(15,23,42,.4); z-index: 1039; }

    /* ===== Topbar & Layout ===== */
    #main-wrapper {
        margin-left: var(--sidebar-width);
        min-height: 100vh;
        min-width: 0;
        display: flex;
        flex-direction: column;
        transition: margin-left 200ms var(--ease-standard);
    }
    #topbar {
        height: var(--header-height);
        background: var(--bg-surface);
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        padding: 0 24px;
        position: sticky;
        top: 0;
        z-index: 100;
        gap: 16px;
    }
    .topbar-toggle {
        display: none;
        background: none;
        border: 1px solid var(--border);
        font-size: 1.25rem;
        padding: 4px 8px;
        border-radius: var(--radius-sm);
        line-height: 1;
        color: var(--text-primary);
    }
    .topbar-toggle:hover {
        background: var(--bg-subtle);
    }
    .topbar-title {
        flex: 1;
        min-width: 0;
    }
    .topbar-title h1 {
        font-size: 18px;
        font-weight: 600;
        margin: 0;
        color: var(--text-primary);
    }
    .topbar-title p {
        font-size: 12px;
        color: var(--text-secondary);
        margin: 0;
    }
    .topbar-user {
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 52px;
        background: transparent;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 6px 10px;
        cursor: pointer;
        transition: background-color 150ms ease, border-color 150ms ease;
    }
    .topbar-user:hover,
    .topbar-user[aria-expanded="true"] {
        background: var(--bg-subtle);
        border-color: var(--border);
    }
    .topbar-user::after {
        margin-left: 4px;
        color: var(--text-muted);
    }
    .topbar-avatar {
        width: 36px;
        height: 36px;
        border-radius: var(--radius-md);
        background: var(--bg-subtle);
        color: var(--primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
        flex-shrink: 0;
        border: 1px solid var(--border);
    }
    #page-content {
        flex: 1;
        min-width: 0;
        overflow-x: clip;
        padding: var(--space-3); /* 24px */
    }
    .topbar-user .d-block {
        max-width: 180px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Topbar Lonceng */
    .topbar-bell {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        background: none;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        color: var(--text-secondary);
        font-size: 1.05rem;
        flex-shrink: 0;
        cursor: pointer;
        transition: background-color 150ms ease, color 150ms ease;
    }
    .topbar-bell:hover, .topbar-bell[aria-expanded="true"] {
        background: var(--bg-subtle);
        color: var(--primary);
    }
    .notif-badge {
        position: absolute;
        top: -4px;
        right: -4px;
        min-width: 16px;
        height: 16px;
        padding: 0 4px;
        border-radius: 999px;
        background: var(--danger);
        color: #fff;
        font-size: 10px;
        font-weight: 600;
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .notif-menu {
        width: 340px;
        max-width: calc(100vw - 24px);
        padding: 0;
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(15,23,42,.08);
    }
    .notif-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 10px 16px;
        background: var(--bg-subtle);
        border-bottom: 1px solid var(--border);
    }
    .notif-mark-all {
        background: none;
        border: 0;
        padding: 0;
        font-size: 12px;
        font-weight: 500;
        color: var(--primary);
        cursor: pointer;
    }
    .notif-mark-all:hover {
        color: var(--primary-hover);
        text-decoration: underline;
    }
    .notif-list {
        max-height: 320px;
        overflow-y: auto;
    }
    .notif-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 10px 16px;
        white-space: normal;
        border-bottom: 1px solid var(--border);
        width: 100%;
        background: var(--bg-surface);
        transition: background-color 150ms ease;
    }
    .notif-item:hover, .notif-item:focus {
        background: var(--bg-subtle);
    }
    .notif-judul {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-primary);
    }
    .notif-pesan {
        font-size: 12px;
        color: var(--text-secondary);
        margin-top: 2px;
    }
    .notif-waktu {
        font-size: 11px;
        color: var(--text-muted);
        margin-top: 3px;
    }
    .notif-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--primary);
        flex-shrink: 0;
        margin-top: 5px;
    }
    .notif-dot-read {
        background: var(--border);
    }
    .notif-foot {
        border-top: 1px solid var(--border);
        background: var(--bg-surface);
    }
    .notif-kosong {
        padding: 24px 16px;
        text-align: center;
        font-size: 13px;
        color: var(--text-muted);
    }
    .notif-baris-baru {
        background: #F8FAFD;
    }

    /* ===== Kartu & Form ===== */
    .card-modern {
        background: var(--bg-surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        box-shadow: 0 1px 3px rgba(15,23,42,.03);
    }
    .btn {
        height: var(--control-h);
        border-radius: var(--radius-md);
        font-weight: 500;
        font-size: 14px;
        padding: 0 16px;
        transition: all 150ms var(--ease-standard);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    .btn-sm {
        height: var(--control-h-sm);
        font-size: 13px;
        padding: 0 12px;
    }
    .btn-primary, .btn-silab {
        background: var(--primary);
        border: 1px solid var(--primary);
        color: #ffffff;
    }
    .btn-primary:hover, .btn-silab:hover, .btn-primary:focus, .btn-silab:focus {
        background: var(--primary-hover);
        border-color: var(--primary-hover);
        color: #ffffff;
    }
    .btn-outline-silab {
        background: var(--bg-surface);
        color: var(--text-secondary);
        border: 1px solid var(--border);
    }
    .btn-outline-silab:hover {
        background: var(--bg-subtle);
        border-color: #CBD5E1;
        color: var(--text-primary);
    }
    .btn-gold {
        background: var(--primary);
        border: 1px solid var(--primary);
        color: #ffffff;
    }
    .btn-gold:hover {
        background: var(--primary-hover);
        border-color: var(--primary-hover);
        color: #ffffff;
    }
    .btn-edit {
        height: var(--control-h-sm);
        background: var(--bg-surface);
        color: var(--text-primary);
        border: 1px solid var(--border);
        font-size: 13px;
        padding: 0 10px;
        border-radius: var(--radius-md);
    }
    .btn-edit:hover {
        background: var(--bg-subtle);
        border-color: #CBD5E1;
        color: var(--primary);
    }
    .btn-del {
        height: var(--control-h-sm);
        background: var(--danger-soft);
        color: var(--danger);
        border: 1px solid rgba(220,38,38,.32);
        font-size: 13px;
        padding: 0 8px;
        border-radius: var(--radius-md);
    }
    .btn-del:hover {
        background: var(--danger-soft);
        border-color: rgba(220,38,38,.2);
        color: var(--danger-hover);
    }
    .form-control, .form-select {
        height: var(--control-h);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        font-size: 14px;
        padding: 0 12px;
        color: var(--text-primary);
        background-color: var(--bg-surface);
        transition: border-color 150ms ease, box-shadow 150ms ease;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 2px var(--primary-focus);
        color: var(--text-primary);
        outline: none;
    }
    .form-label {
        font-size: 13px;
        font-weight: 500;
        color: var(--text-secondary);
        margin-bottom: 6px;
    }
    .form-text {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 4px;
    }

    /* ===== Tabel Rapi & Terstandarisasi ===== */
    .table > :not(caption) > * > * {
        padding: 12px 16px;
    }
    .table-silab thead th {
        background: var(--bg-subtle);
        color: var(--text-secondary);
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 600;
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }
    .table-silab tbody tr {
        border-top: 1px solid var(--border);
        transition: background-color 120ms ease;
    }
    .table-silab tbody tr:hover {
        background-color: #F8FAFC;
    }
    .table-silab td {
        font-size: 14px;
        color: var(--text-primary);
        vertical-align: middle;
    }
    .col-center, td.col-center, th.col-center {
        text-align: center;
    }
    .sort-link {
        color: var(--text-secondary);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .sort-link i {
        color: var(--text-muted);
        font-size: 0.8rem;
    }
    .sort-link:hover, .sort-link.active {
        color: var(--primary);
        font-weight: 600;
    }
    .sort-link.active i {
        color: var(--primary);
    }
    .thumb {
        width: 48px;
        height: 48px;
        object-fit: cover;
        border-radius: var(--radius-sm);
        border: 1px solid var(--border);
        cursor: zoom-in;
        background: #ffffff;
        transition: transform 150ms ease;
    }
    .thumb:hover {
        transform: scale(1.05);
    }
    .thumb-empty {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-sm);
        border: 1px dashed var(--border);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);
        background: var(--bg-subtle);
    }
    .badge-status {
        border-radius: var(--radius-md);
        font-weight: 500;
        padding: 3px 8px;
        font-size: 12px;
        line-height: 1.3;
    }
    .badge-status-ok {
        background: var(--success-soft);
        color: var(--success);
        border: 1px solid rgba(22,163,74,.2);
    }
    .badge-status-bad {
        background: var(--danger-soft);
        color: var(--danger);
        border: 1px solid rgba(220,38,38,.2);
    }
    .badge-dipakai-zero {
        background: var(--bg-subtle);
        color: var(--text-secondary);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 3px 8px;
        font-size: 12px;
        font-weight: 500;
    }
    .badge-dipakai-active {
        background: var(--info-soft);
        color: #0369A1;
        border: 1px solid rgba(2,132,199,.2);
        border-radius: var(--radius-md);
        padding: 3px 8px;
        font-size: 12px;
        font-weight: 600;
    }
    .table-footer {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 16px;
        border-top: 1px solid var(--border);
        background: var(--bg-surface);
        font-size: 13px;
        color: var(--text-secondary);
    }

    /* Modal */
    .modal-content {
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        box-shadow: 0 10px 25px rgba(15,23,42,.1);
    }
    .modal-header {
        background: var(--bg-subtle);
        border-bottom: 1px solid var(--border);
        border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        padding: 14px 20px;
    }
    .modal-footer {
        border-top: 1px solid var(--border);
        padding: 14px 20px;
    }

    /* Alert */
    .alert {
        border: 1px solid transparent;
        border-radius: var(--radius-md);
        border-left: 3px solid;
        font-size: 14px;
        padding: 12px 16px;
    }
    .alert-success {
        background: var(--success-soft);
        color: var(--success);
        border-left-color: var(--success);
        border-color: rgba(22,163,74,.2);
    }
    .alert-warning {
        background: var(--warning-soft);
        color: var(--warning);
        border-left-color: var(--warning);
        border-color: rgba(217,119,6,.2);
    }
    .alert-danger {
        background: var(--danger-soft);
        color: var(--danger);
        border-left-color: var(--danger);
        border-color: rgba(220,38,38,.2);
    }

    .badge-tab {
        background: var(--bg-subtle);
        color: var(--text-secondary);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 3px 8px;
    }
    .page-link {
        color: var(--text-secondary);
        border-color: var(--border);
        font-size: 13px;
        border-radius: var(--radius-md) !important;
        margin: 0 2px;
        padding: 6px 12px;
    }
    .page-link:hover {
        background-color: var(--bg-subtle);
        color: var(--primary);
        border-color: var(--border);
    }
    .page-item.active .page-link {
        background: var(--primary);
        border-color: var(--primary);
        color: #ffffff;
    }

    /* Modal Notifikasi / Toast Dialog */
    .siarka-notif .modal-content {
        padding: 24px;
        text-align: center;
        border-radius: var(--radius-lg);
    }
    .siarka-notif .notif-tutup {
        position: absolute;
        top: 14px;
        right: 14px;
    }
    .siarka-notif .notif-ikon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        margin: 0 auto 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
    .siarka-notif[data-tipe="success"] .notif-ikon { background: var(--success-soft); color: var(--success); }
    .siarka-notif[data-tipe="error"] .notif-ikon,
    .siarka-notif[data-tipe="bahaya"] .notif-ikon { background: var(--danger-soft); color: var(--danger); }
    .siarka-notif[data-tipe="warning"] .notif-ikon { background: var(--warning-soft); color: var(--warning); }
    .siarka-notif[data-tipe="info"] .notif-ikon { background: var(--info-soft); color: var(--info); }
    .siarka-notif .notif-judul { font-size: 18px; font-weight: 600; margin-bottom: 6px; }
    .siarka-notif .notif-pesan { font-size: 14px; color: var(--text-secondary); margin-bottom: 18px; }
    .siarka-notif .notif-aksi { display: flex; justify-content: center; gap: 8px; }

    @media (max-width: 991.98px) {
        #sidebar { transform: translateX(-100%); }
        #sidebar.show { transform: translateX(0); }
        #sidebar-overlay.show { display: block; }
        #main-wrapper { margin-left: 0; }
        .topbar-toggle { display: flex; }
        #topbar { padding: 0 16px; }
        #page-content { padding: 16px; }
    }

    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
            animation-duration: 0.001ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.001ms !important;
            scroll-behavior: auto !important;
        }
    }
</style>