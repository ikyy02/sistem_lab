<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Beranda') — SIARKA</title>
    <meta name="application-name" content="SIARKA">
    <meta name="description" content="SIARKA — Sistem Informasi Administrasi, Reservasi, Katalog, dan Aktivitas Laboratorium">
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
    $isStaff = $role === \App\Support\Role::STAFF;
    $initial = mb_strtoupper(mb_substr($me['nama'] ?? 'U', 0, 1));
@endphp

<div id="sidebar-overlay" onclick="closeSidebar()"></div>

<!-- ========== SIDEBAR ========== -->
<nav id="sidebar">
    <a href="{{ url('/') }}" class="sidebar-brand">
        <div class="sidebar-brand-icon"><i class="bi bi-buildings"></i></div>
        <div class="sidebar-brand-text">
            <div class="brand-name">SIARKA</div>
            <div class="brand-sub" style="font-size:.6rem;line-height:1.25;">Sistem Informasi Administrasi, Reservasi, Katalog, dan Aktivitas Laboratorium</div>
        </div>
    </a>

    <div class="sidebar-nav">
        <div class="sidebar-section-label">Menu Utama</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i><span>Beranda</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('katalog') }}" class="nav-link {{ request()->routeIs('katalog') ? 'active' : '' }}">
                    <i class="bi bi-collection"></i><span>Katalog</span>
                </a>
            </li>
            @if($isStaff)
            <li class="nav-item">
                <a href="{{ route('data-akademik.index') }}" class="nav-link {{ request()->routeIs('data-akademik.index') ? 'active' : '' }}">
                    <i class="bi bi-mortarboard"></i><span>Data Akademik</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('jadwal.index') }}" class="nav-link {{ request()->routeIs('jadwal.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-week"></i><span>Jadwal Perkuliahan</span>
                </a>
            </li>
            @endif
            @if($isAdmin)
            <li class="nav-item">
                <a href="{{ route('kelola-katalog.index') }}" class="nav-link {{ request()->is('kelola-katalog*') ? 'active' : '' }}">
                    <i class="bi bi-boxes"></i><span>Kelola Katalog</span>
                </a>
            </li>
            @endif
        </ul>

        @unless($isStaff)
        <div class="sidebar-section-label">Transaksi</div>
        <ul class="nav flex-column">
            @if($role === \App\Support\Role::MAHASISWA || $role === \App\Support\Role::DOSEN)
            <li class="nav-item">
                <a href="{{ route('peminjaman.index') }}" class="nav-link {{ request()->routeIs('peminjaman.index', 'peminjaman.show') ? 'active' : '' }}">
                    <i class="bi bi-arrow-up-right-square"></i><span>Peminjaman</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('peminjaman.riwayat') }}" class="nav-link {{ request()->routeIs('peminjaman.riwayat') ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i><span>Riwayat Peminjaman</span>
                </a>
            </li>
            @endif
            @if($role === \App\Support\Role::DOSEN || $isAdmin)
            <li class="nav-item">
                <a href="{{ route('pengajuan.index') }}" class="nav-link {{ request()->routeIs('pengajuan.*') ? 'active' : '' }}">
                    <i class="bi bi-clipboard2-check"></i><span>Pengajuan Peminjaman</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('pengembalian.index') }}" class="nav-link {{ request()->routeIs('pengembalian.*') ? 'active' : '' }}">
                    <i class="bi bi-arrow-down-left-square"></i><span>Pengembalian</span>
                </a>
            </li>
            @endif
        </ul>
        @endunless

        @if($isAdmin)
        <div class="sidebar-section-label">Administrasi</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="{{ route('kelola-user.index') }}" class="nav-link {{ request()->is('kelola-user*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i><span>Kelola Data User</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('master-data.index') }}" class="nav-link {{ request()->is('master-data*') ? 'active' : '' }}">
                    <i class="bi bi-diagram-3"></i><span>Kelola Data Master</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('pengaturan-operasional.index') }}" class="nav-link {{ request()->is('pengaturan-operasional*') ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i><span>Pengaturan Operasional</span>
                </a>
            </li>
        </ul>
        @endif
    </div>

    <div class="sidebar-footer">
        <a href="{{ route('profil.index') }}" class="d-flex align-items-center gap-2 text-decoration-none" title="Profil Saya">
            <div class="user-chip">{{ $initial }}</div>
            <div class="min-w-0 flex-grow-1">
                <div class="user-name text-truncate">{{ $me['nama'] ?? '' }}</div>
                <div class="user-role">{{ $roleLabel }}</div>
            </div>
        </a>
    </div>
</nav>

<!-- ========== MAIN ========== -->
<div id="main-wrapper">
    <header id="topbar">
        <button class="topbar-toggle" onclick="toggleSidebar()" aria-label="Toggle sidebar"><i class="bi bi-list"></i></button>

        <div class="topbar-title">
            <h1 class="font-display">@yield('page-title', 'SIARKA')</h1>
            <p>@yield('page-subtitle', 'Sistem Informasi Administrasi, Reservasi, Katalog, dan Aktivitas Laboratorium')</p>
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
                <li><a href="{{ route('profil.index') }}" class="dropdown-item"><i class="bi bi-person-circle me-2"></i>Profil Saya</a></li>
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
