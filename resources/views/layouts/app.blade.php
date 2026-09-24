<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SILAB') — Laboratorium Komputer Bisnis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-bg: #ffffff;
            --sidebar-text: #64748b;
            --sidebar-active-bg: #f0fdf4;
            --sidebar-active-text: #16a34a;
            --sidebar-hover-bg: #f8fafc;
            --primary: #16a34a;
            --primary-dark: #15803d;
            --body-bg: #f8fafc;
            --card-bg: #ffffff;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --header-height: 64px;
        }

        * { box-sizing: border-box; }

        body {
            background-color: var(--body-bg);
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: var(--text-dark);
            margin: 0;
        }

        /* ========== SIDEBAR ========== */
        #sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            z-index: 1040;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
            overflow-y: auto;
            border-right: 1px solid var(--border-color);
            box-shadow: 2px 0 8px rgba(0,0,0,0.04);
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 20px 20px 18px;
            border-bottom: 1px solid #e2e8f0;
            text-decoration: none;
        }

        .sidebar-brand-icon {
            width: 40px;
            height: 40px;
            background: var(--primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .sidebar-brand-icon i {
            color: #fff;
            font-size: 1.2rem;
        }

        .sidebar-brand-text .brand-name {
            color: #0f172a;
            font-size: 1.1rem;
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: -0.02em;
        }

        .sidebar-brand-text .brand-sub {
            color: #64748b;
            font-size: 0.70rem;
            line-height: 1.1;
            font-weight: 500;
        }

        .sidebar-nav {
            padding: 16px 12px;
            flex: 1;
        }

        .sidebar-section-label {
            color: #94a3b8;
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 8px 10px 4px;
            margin-top: 8px;
        }

        .sidebar-nav .nav-item {
            margin-bottom: 2px;
        }

        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--sidebar-text);
            padding: 9px 12px;
            border-radius: 8px;
            font-size: 0.87rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .sidebar-nav .nav-link i {
            font-size: 1rem;
            width: 20px;
            text-align: center;
            flex-shrink: 0;
        }

        .sidebar-nav .nav-link:hover {
            background: var(--sidebar-hover-bg);
            color: var(--text-dark);
        }

        .sidebar-nav .nav-link.active {
            background: var(--primary);
            color: #fff;
            font-weight: 600;
            box-shadow: 0 1px 3px rgba(22, 163, 74, 0.2);
        }

        .sidebar-nav .nav-link.active i {
            color: #fff;
        }

        .sidebar-nav .nav-link.disabled-link {
            opacity: 0.45;
            cursor: default;
            pointer-events: none;
        }

        .sidebar-footer {
            padding: 14px 20px;
            border-top: 1px solid #e2e8f0;
        }

        .sidebar-footer small {
            color: #94a3b8;
            font-size: 0.7rem;
        }

        /* ========== OVERLAY (mobile) ========== */
        #sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1039;
        }

        /* ========== MAIN WRAPPER ========== */
        #main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s ease;
        }

        /* ========== TOPBAR ========== */
        #topbar {
            height: var(--header-height);
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
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
            border: none;
            color: var(--text-dark);
            font-size: 1.25rem;
            cursor: pointer;
            padding: 4px;
            border-radius: 6px;
            line-height: 1;
        }

        .topbar-toggle:hover { background: var(--body-bg); }

        .topbar-title {
            flex: 1;
        }

        .topbar-title h1 {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0;
            line-height: 1.2;
        }

        .topbar-title p {
            font-size: 0.78rem;
            color: var(--text-muted);
            margin: 0;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .topbar-avatar {
            width: 36px;
            height: 36px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
        }

        /* ========== CONTENT ========== */
        #page-content {
            flex: 1;
            padding: 28px 28px 40px;
        }

        /* ========== CARDS ========== */
        .card-modern {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 14px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 1px 2px rgba(0,0,0,0.03);
        }

        /* ========== STAT CARDS ========== */
        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 22px 22px 18px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            transition: box-shadow 0.2s;
        }

        .stat-card:hover {
            box-shadow: 0 4px 14px rgba(0,0,0,0.08);
        }

        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-bottom: 14px;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-dark);
            line-height: 1;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 0.82rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 991.98px) {
            #sidebar {
                transform: translateX(-100%);
            }

            #sidebar.show {
                transform: translateX(0);
            }

            #sidebar-overlay.show {
                display: block;
            }

            #main-wrapper {
                margin-left: 0;
            }

            .topbar-toggle {
                display: flex;
                align-items: center;
            }

            #page-content {
                padding: 20px 16px 32px;
            }
        }

        @media (max-width: 575.98px) {
            .topbar-title p { display: none; }
        }
    </style>
    @stack('styles')
</head>
<body>

<!-- Sidebar Overlay (mobile) -->
<div id="sidebar-overlay" onclick="closeSidebar()"></div>

<!-- ========== SIDEBAR ========== -->
<nav id="sidebar">
    <a href="{{ url('/') }}" class="sidebar-brand">
        <div class="sidebar-brand-icon">
            <i class="bi bi-eyedropper"></i>
        </div>
        <div class="sidebar-brand-text">
            <div class="brand-name">SILAB</div>
            <div class="brand-sub">Laboratorium Komputer Bisnis</div>
        </div>
    </a>

    <div class="sidebar-nav">
        <div class="sidebar-section-label">Menu Utama</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="{{ url('/') }}" class="nav-link {{ request()->is('/') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('alat-bahan.index') }}" class="nav-link {{ request()->is('alat-bahan*') ? 'active' : '' }}">
                    <i class="bi bi-boxes"></i>
                    <span>Alat &amp; Bahan</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('katalog') }}" class="nav-link {{ request()->is('katalog') ? 'active' : '' }}">
                    <i class="bi bi-collection"></i>
                    <span>Katalog</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('mahasiswa.index') }}" class="nav-link {{ request()->is('mahasiswa*') ? 'active' : '' }}">
                    <i class="bi bi-person-lines-fill"></i>
                    <span>Data Mahasiswa</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-section-label mt-2">Transaksi</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="#" class="nav-link disabled-link">
                    <i class="bi bi-arrow-up-right-square"></i>
                    <span>Peminjaman</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link disabled-link">
                    <i class="bi bi-arrow-down-left-square"></i>
                    <span>Pengembalian</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-section-label mt-2">Administrasi</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <div class="nav-link disabled-link">
                    <i class="bi bi-people"></i>
                    <span>Pengguna</span>
                </div>
                <ul class="nav flex-column" style="padding-left:14px;">
                    <li class="nav-item">
                        <a href="{{ route('dosen.index') }}" class="nav-link {{ request()->is('dosen*') ? 'active' : '' }}">
                            <i class="bi bi-person-badge"></i>
                            <span>Dosen</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link disabled-link">
                    <i class="bi bi-bar-chart-line"></i>
                    <span>Laporan</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="sidebar-footer">
        <div class="d-flex align-items-center justify-content-between">
            <small><i class="bi bi-circle-fill text-success me-1" style="font-size:0.5rem"></i> Sistem aktif</small>
            <small class="text-muted">v1.0.0</small>
        </div>
    </div>
</nav>

<!-- ========== MAIN WRAPPER ========== -->
<div id="main-wrapper">

    <!-- TOPBAR -->
    <header id="topbar">
        <button class="topbar-toggle" onclick="toggleSidebar()" aria-label="Toggle sidebar">
            <i class="bi bi-list"></i>
        </button>

        <div class="topbar-title">
            <h1>@yield('page-title', 'Dashboard')</h1>
            <p>@yield('page-subtitle', 'Sistem Manajemen Laboratorium Kampus')</p>
        </div>

        <div class="topbar-right">
            <div class="topbar-avatar" title="Admin">
                <i class="bi bi-person-fill"></i>
            </div>
        </div>
    </header>

    <!-- CONTENT -->
    <main id="page-content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-4 rounded-3 border-0 shadow-sm" role="alert" style="background:#f0fdf4; color:#15803d; border-left:4px solid #16a34a !important; border-left-style:solid !important;">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <span>{{ session('success') }}</span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2 mb-4 rounded-3 border-0 shadow-sm" role="alert">
                <i class="bi bi-x-circle-fill fs-5"></i>
                <span>{{ session('error') }}</span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @yield('content')
    </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
    }

    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('show');
        document.getElementById('sidebar-overlay').classList.remove('show');
    }
</script>
@stack('scripts')
</body>
</html>
