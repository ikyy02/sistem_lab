@php
    $me = \App\Services\AuthService::user();
    $role = $me['role'] ?? 'mahasiswa';
    $roleLabel = \App\Support\Role::LABELS[$role] ?? 'Pengguna';
    $initial = mb_strtoupper(mb_substr($me['nama'] ?? 'U', 0, 1));

    // Badge counters
    $badgePersetujuan = 0;
    $badgePeminjamanSaya = 0;
    try {
        if ($role === \App\Support\Role::DOSEN || $role === \App\Support\Role::LABORAN) {
            $badgePersetujuan = \App\Models\Peminjaman::where('status', 'menunggu')->count();
        }
        if ($role === \App\Support\Role::MAHASISWA) {
            $badgePeminjamanSaya = \App\Models\Peminjaman::where('email_peminjam', $me['email'] ?? '')
                ->whereIn('status', ['menunggu', 'disetujui'])->count();
        }
    } catch (\Throwable $e) {
        // Fallback gracefully if database table is unavailable
    }

    // Single menu configuration array filtered by role
    $menuConfig = [
        \App\Support\Role::MAHASISWA => [
            'Utama' => [
                ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'dashboard'],
                ['label' => 'Katalog Lab', 'icon' => 'bi-collection', 'route' => 'katalog'],
            ],
            'Peminjaman' => [
                ['label' => 'Peminjaman', 'icon' => 'bi-card-checklist', 'route' => 'peminjaman.index', 'badge' => $badgePeminjamanSaya],
                ['label' => 'Riwayat', 'icon' => 'bi-clock-history', 'route' => 'peminjaman.riwayat'],
            ],
            'Informasi' => [
                ['label' => 'Jadwal Praktikum', 'icon' => 'bi-calendar3', 'url' => url('/#jadwal'), 'external' => true],
                ['label' => 'SOP & Panduan', 'icon' => 'bi-file-earmark-text', 'url' => url('/#sop'), 'external' => true],
                ['label' => 'Notifikasi', 'icon' => 'bi-bell', 'route' => 'notifikasi.index'],
            ],
            'Akun' => [
                ['label' => 'Profil', 'icon' => 'bi-person', 'route' => 'profil.index'],
                ['label' => 'Bantuan', 'icon' => 'bi-question-circle', 'url' => url('/#faq'), 'external' => true],
            ],
        ],
        \App\Support\Role::DOSEN => [
            'Utama' => [
                ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'dashboard'],
                ['label' => 'Katalog Lab', 'icon' => 'bi-collection', 'route' => 'katalog'],
            ],
            'Peminjaman' => [
                ['label' => 'Peminjaman', 'icon' => 'bi-card-checklist', 'route' => 'peminjaman.index'],
                ['label' => 'Persetujuan', 'icon' => 'bi-clipboard-check', 'route' => 'pengajuan.index', 'badge' => $badgePersetujuan],
                ['label' => 'Riwayat', 'icon' => 'bi-clock-history', 'route' => 'peminjaman.riwayat'],
            ],
            'Informasi' => [
                ['label' => 'Jadwal Praktikum', 'icon' => 'bi-calendar3', 'url' => url('/#jadwal'), 'external' => true],
            ],
            'Akun' => [
                ['label' => 'Profil', 'icon' => 'bi-person', 'route' => 'profil.index'],
            ],
        ],
        \App\Support\Role::LABORAN => [
            'Operasional' => [
                ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'dashboard'],
                ['label' => 'Persetujuan', 'icon' => 'bi-clipboard-check', 'route' => 'pengajuan.index', 'badge' => $badgePersetujuan],
                ['label' => 'Pengembalian', 'icon' => 'bi-arrow-down-left-square', 'route' => 'pengembalian.index'],
                ['label' => 'Pengadaan TPK', 'icon' => 'bi-calculator', 'route' => 'pengadaan-tpk.index'],
            ],
            'Data Master' => [
                ['label' => 'Kelola Katalog', 'icon' => 'bi-boxes', 'route' => 'kelola-katalog.index'],
                ['label' => 'Data Akademik', 'icon' => 'bi-mortarboard', 'route' => 'data-akademik.index'],
                ['label' => 'Jadwal Perkuliahan', 'icon' => 'bi-calendar-week', 'route' => 'jadwal.index'],
            ],
            'Pengguna' => [
                ['label' => 'Kelola Data User', 'icon' => 'bi-people', 'route' => 'kelola-user.index'],
            ],
            'Lainnya' => [
                ['label' => 'Pengaturan Laboratorium', 'icon' => 'bi-sliders', 'route' => 'pengaturan-lab.index'],
                ['label' => 'Profil', 'icon' => 'bi-person', 'route' => 'profil.index'],
            ],
        ],
        \App\Support\Role::STAFF => [
            'Utama' => [
                ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'dashboard'],
                ['label' => 'Katalog Lab', 'icon' => 'bi-collection', 'route' => 'katalog'],
            ],
            'Akademik' => [
                ['label' => 'Data Akademik', 'icon' => 'bi-mortarboard', 'route' => 'data-akademik.index'],
                ['label' => 'Jadwal Perkuliahan', 'icon' => 'bi-calendar-week', 'route' => 'jadwal.index'],
            ],
            'Akun' => [
                ['label' => 'Profil', 'icon' => 'bi-person', 'route' => 'profil.index'],
            ],
        ],
    ];

    $currentRoleGroups = $menuConfig[$role] ?? ($menuConfig[\App\Support\Role::MAHASISWA] ?? []);
@endphp

<aside id="sidebar" class="siarka-sidebar">
    {{-- Header Sidebar: Logo + Subjudul --}}
    <div class="sidebar-top">
        <a href="{{ url('/') }}" class="sidebar-brand-box" title="SIARKA — Kembali ke Beranda">
            <img src="{{ asset('images/siarka-ikon.svg') }}" alt="Logo SIARKA" class="sidebar-logo" width="32" height="32">
            <div class="sidebar-brand-info">
                <span class="sidebar-app-name">SIARKA</span>
                <span class="sidebar-app-sub">Lab Komputer & Bisnis</span>
            </div>
        </a>
    </div>

    {{-- Navigasi Menu Berdasarkan Konfigurasi Array --}}
    <nav class="sidebar-nav-container" aria-label="Navigasi Menu Utama">
        @foreach($currentRoleGroups as $groupLabel => $items)
            <div class="sidebar-group">
                <div class="sidebar-group-title">{{ $groupLabel }}</div>
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
                            <a href="{{ $href }}" class="sidebar-link {{ $isActive ? 'is-active' : '' }}" data-label="{{ $item['label'] }}" @if($isActive) aria-current="page" @endif>
                                <i class="bi {{ $item['icon'] }} sidebar-link-icon" aria-hidden="true"></i>
                                <span class="sidebar-link-text">{{ $item['label'] }}@if(!empty($item['external']))<span class="visually-hidden-sr"> (membuka halaman beranda)</span>@endif</span>
                                @if(!empty($item['external']))<i class="bi bi-box-arrow-up-right sidebar-link-ext" aria-hidden="true"></i>@endif
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

    <div class="sidebar-foot">
        <button type="button" class="sidebar-collapse-toggle d-none d-lg-flex" data-sidebar="collapse" data-label="Perkecil sidebar" aria-label="Perkecil sidebar" aria-controls="sidebar" aria-expanded="true">
            <i class="bi bi-chevron-left collapse-icon" aria-hidden="true"></i>
            <span class="sidebar-collapse-label">Perkecil</span>
        </button>
    </div>
</aside>
