<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SILAB') — Laboratorium Komputer Bisnis</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    @include('layouts._theme')
    @stack('styles')
</head>
<body>
@php
    $me = \App\Services\AuthService::user();
    $role = $me['role'] ?? null;
    $roleLabel = \App\Support\Role::LABELS[$role] ?? '';
    $isAdmin = $role === \App\Support\Role::LABORAN;
    $initial = mb_strtoupper(mb_substr($me['nama'] ?? 'U', 0, 1));
@endphp

<div id="sidebar-overlay" onclick="closeSidebar()"></div>

<!-- ========== SIDEBAR ========== -->
<nav id="sidebar">
    <a href="{{ url('/') }}" class="sidebar-brand">
        <div class="sidebar-brand-icon"><i class="bi bi-buildings"></i></div>
        <div class="sidebar-brand-text">
            <div class="brand-name">SILAB</div>
            <div class="brand-sub">Laboratorium Komputer &amp; Bisnis</div>
        </div>
    </a>

    <div class="sidebar-nav">
        <div class="sidebar-section-label">Menu Utama</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="{{ route('katalog') }}" class="nav-link {{ request()->routeIs('katalog') ? 'active' : '' }}">
                    <i class="bi bi-collection"></i><span>Katalog</span>
                </a>
            </li>
            @if($isAdmin)
            <li class="nav-item">
                <a href="{{ route('inventaris.index') }}" class="nav-link {{ request()->is('inventaris*') ? 'active' : '' }}">
                    <i class="bi bi-boxes"></i><span>Kelola Inventaris</span>
                </a>
            </li>
            @endif
        </ul>

        <div class="sidebar-section-label">Transaksi</div>
        <ul class="nav flex-column">
            <li class="nav-item"><a href="#" class="nav-link disabled-link"><i class="bi bi-arrow-up-right-square"></i><span>Peminjaman</span></a></li>
            <li class="nav-item"><a href="#" class="nav-link disabled-link"><i class="bi bi-arrow-down-left-square"></i><span>Pengembalian</span></a></li>
        </ul>

        @if($isAdmin)
        <div class="sidebar-section-label">Administrasi</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="{{ route('kelola-user.index') }}" class="nav-link {{ request()->is('kelola-user*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i><span>Kelola Data User</span>
                </a>
            </li>
            <li class="nav-item"><a href="#" class="nav-link disabled-link"><i class="bi bi-bar-chart-line"></i><span>Laporan</span></a></li>
        </ul>
        @endif
    </div>

    <div class="sidebar-footer">
        <div class="d-flex align-items-center gap-2">
            <div class="user-chip">{{ $initial }}</div>
            <div class="min-w-0 flex-grow-1">
                <div class="user-name text-truncate">{{ $me['nama'] ?? '' }}</div>
                <div class="user-role">{{ $roleLabel }}</div>
            </div>
        </div>
    </div>
</nav>

<!-- ========== MAIN ========== -->
<div id="main-wrapper">
    <header id="topbar">
        <button class="topbar-toggle" onclick="toggleSidebar()" aria-label="Toggle sidebar"><i class="bi bi-list"></i></button>

        <div class="topbar-title">
            <h1 class="font-display">@yield('page-title', 'SILAB')</h1>
            <p>@yield('page-subtitle', 'Sistem Informasi Laboratorium Jurusan Komputer dan Bisnis')</p>
        </div>

        <div class="topbar-right dropdown">
            <button class="topbar-user dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="topbar-avatar">{{ $initial }}</span>
                <span class="d-none d-sm-block text-start lh-sm">
                    <span class="d-block fw-semibold" style="font-size:.82rem">{{ $me['nama'] ?? '' }}</span>
                    <span class="d-block" style="font-size:.7rem;color:var(--text-muted)">{{ $roleLabel }}</span>
                </span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                <li class="px-3 py-2"><div style="font-size:.75rem;color:var(--text-muted)">{{ $me['email'] ?? '' }}</div></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-2"></i>Keluar</button>
                    </form>
                </li>
            </ul>
        </div>
    </header>

    <main id="page-content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-4" role="alert">
                <i class="bi bi-check-circle-fill fs-5"></i><span>{{ session('success') }}</span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2 mb-4" role="alert">
                <i class="bi bi-x-circle-fill fs-5"></i><span>{{ session('error') }}</span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @yield('content')
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('show');
        document.getElementById('sidebar-overlay').classList.toggle('show');
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('show');
        document.getElementById('sidebar-overlay').classList.remove('show');
    }
</script>
@stack('scripts')
</body>
</html>
