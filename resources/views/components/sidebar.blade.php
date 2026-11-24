@php
    $me = \App\Services\AuthService::user();
    $role = $me['role'] ?? 'mahasiswa';
    $roleLabel = \App\Support\Role::LABELS[$role] ?? 'Pengguna';
    $initial = mb_strtoupper(mb_substr($me['nama'] ?? 'U', 0, 1));

    // Role subtext mapping for user footer card
    $roleSubtitles = [
        \App\Support\Role::LABORAN => 'Admin laboratorium',
        \App\Support\Role::DOSEN => 'Dosen pengampu',
        \App\Support\Role::MAHASISWA => 'Mahasiswa',
        \App\Support\Role::STAFF => 'Staf prodi',
    ];
    $roleSubtitle = $roleSubtitles[$role] ?? $roleLabel;

    // Badge counters
    $badgePersetujuan = 0;
    try {
        if ($role === \App\Support\Role::DOSEN || $role === \App\Support\Role::LABORAN) {
            $badgePersetujuan = \App\Models\Peminjaman::where('status', 'menunggu')->count();
        }
    } catch (\Throwable $e) {
        // Fallback gracefully if database table is unavailable
    }

    // Single menu configuration array filtered by role
    $menuConfig = [
        \App\Support\Role::MAHASISWA => [
            'Menu Utama' => [
                ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'dashboard'],
                ['label' => 'Katalog Lab', 'icon' => 'bi-collection', 'route' => 'katalog'],
                ['label' => 'Ajukan Peminjaman', 'icon' => 'bi-plus-circle', 'route' => 'peminjaman.index'],
                ['label' => 'Riwayat Peminjaman', 'icon' => 'bi-clock-history', 'route' => 'peminjaman.riwayat'],
                ['label' => 'SOP & Panduan', 'icon' => 'bi-file-earmark-text', 'route' => 'sop.index'],
                ['label' => 'Notifikasi', 'icon' => 'bi-bell', 'route' => 'notifikasi.index'],
            ],
        ],
        \App\Support\Role::DOSEN => [
            'Menu Utama' => [
                ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'dashboard'],
                ['label' => 'Peminjaman', 'icon' => 'bi-plus-circle', 'route' => 'peminjaman.index'],
                ['label' => 'Riwayat Peminjaman', 'icon' => 'bi-clock-history', 'route' => 'peminjaman.riwayat'],
                ['label' => 'Persetujuan', 'icon' => 'bi-clipboard-check', 'route' => 'pengajuan.index', 'badge' => $badgePersetujuan],
                ['label' => 'Jadwal Praktikum', 'icon' => 'bi-calendar3', 'route' => 'jadwal-praktikum.index'],
                ['label' => 'SOP & Panduan', 'icon' => 'bi-file-earmark-text', 'route' => 'sop.index'],
                ['label' => 'Katalog Lab', 'icon' => 'bi-collection', 'route' => 'katalog'],
            ],
        ],
        \App\Support\Role::LABORAN => [
            'Operasional' => [
                ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'dashboard'],
                ['label' => 'Persetujuan Peminjaman', 'icon' => 'bi-clipboard-check', 'route' => 'pengajuan.index', 'badge' => $badgePersetujuan],
                ['label' => 'Kelola Peminjaman', 'icon' => 'bi-box-seam', 'route' => 'pengembalian.index'],
                ['label' => 'Pengadaan TPK', 'icon' => 'bi-calculator', 'route' => 'pengadaan-tpk.index'],
                ['label' => 'Kelola Katalog', 'icon' => 'bi-boxes', 'route' => 'kelola-katalog.index'],
                ['label' => 'Kelola Data User', 'icon' => 'bi-people', 'route' => 'kelola-user.index'],
                ['label' => 'Pengaturan Laboratorium', 'icon' => 'bi-sliders', 'route' => 'pengaturan-lab.index'],
            ],
        ],
        \App\Support\Role::STAFF => [
            'Menu Utama' => [
                ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'dashboard'],
                ['label' => 'Data Akademik', 'icon' => 'bi-mortarboard', 'route' => 'data-akademik.index'],
                ['label' => 'Jadwal Perkuliahan', 'icon' => 'bi-calendar-week', 'route' => 'jadwal.index'],
                ['label' => 'Katalog', 'icon' => 'bi-collection', 'route' => 'katalog'],
            ],
        ],
    ];
    $currentRoleGroups = $menuConfig[$role] ?? ($menuConfig[\App\Support\Role::MAHASISWA] ?? []);
@endphp

<aside id="sidebar" class="siarka-sidebar">
    {{-- Header Sidebar: Logo Kotak Putih + Nama + Tombol Collapse --}}
    <div class="sidebar-top">
        <a href="{{ url('/') }}" class="sidebar-brand-box" title="SIARKA — Kembali ke Beranda">
            <div class="sidebar-logo-wrap">
                <img src="{{ asset('images/siarka-ikon.svg') }}" alt="Logo SIARKA" class="sidebar-logo" width="22" height="22">
            </div>
            <div class="sidebar-brand-info">
                <span class="sidebar-app-name">SIARKA</span>
                <span class="sidebar-app-sub">Lab Komputer & Bisnis</span>
            </div>
        </a>
        <button type="button" class="sidebar-collapse-toggle d-none d-lg-flex" onclick="toggleSidebarCollapse()" title="Perkecil / Perluas Sidebar" aria-label="Toggle Collapse Sidebar">
            <i class="bi bi-chevron-left collapse-icon"></i>
        </button>
    </div>

    {{-- Navigasi Menu Berdasarkan Konfigurasi Array --}}
    <nav class="sidebar-nav-container" aria-label="Navigasi Menu Utama">
        @foreach($currentRoleGroups as $groupLabel => $items)
            <div class="sidebar-group">
                @if(!empty($groupLabel))
                    <div class="sidebar-group-title" title="{{ $groupLabel }}">{{ $groupLabel }}</div>
                @endif
                <ul class="sidebar-menu-list">
                    @foreach($items as $item)
                        @php
                            $href = '#';
                            $isActive = false;
                            if (!empty($item['route']) && \Illuminate\Support\Facades\Route::has($item['route'])) {
                                $href = route($item['route']);
                                $isActive = request()->routeIs($item['route']) || request()->routeIs($item['route'] . '.*');
                            } elseif (!empty($item['url'])) {
                                $href = $item['url'];
                            }
                        @endphp
                        <li class="sidebar-menu-item">
                            <a href="{{ $href }}" class="sidebar-link {{ $isActive ? 'is-active' : '' }}" title="{{ $item['label'] }}">
                                <i class="bi {{ $item['icon'] }} sidebar-link-icon"></i>
                                <span class="sidebar-link-text">{{ $item['label'] }}</span>
                                @if(!empty($item['badge']) && $item['badge'] > 0)
                                    <span class="sidebar-item-badge">{{ $item['badge'] > 99 ? '99+' : $item['badge'] }}</span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </nav>

    {{-- Footer Sidebar: Card Pengguna Aktif & Tombol Logout --}}
    <div class="sidebar-user-footer">
        <div class="sidebar-user-card">
            <div class="sidebar-user-avatar">
                {{ $initial }}
            </div>
            <div class="sidebar-user-info">
                <span class="sidebar-user-name" title="{{ $me['nama'] ?? 'Pengguna' }}">{{ $me['nama'] ?? 'Pengguna' }}</span>
                <span class="sidebar-user-role">{{ $roleSubtitle }}</span>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="sidebar-user-logout-form m-0 p-0">
                @csrf
                <button type="submit" class="sidebar-user-logout-btn" title="Keluar dari Akun" aria-label="Keluar">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>
</aside>
