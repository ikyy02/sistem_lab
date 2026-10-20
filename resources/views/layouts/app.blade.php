<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Beranda') — SIARKA</title>
    <meta name="application-name" content="SIARKA">
    <meta name="description" content="SIARKA — Sistem Informasi Administrasi, Reservasi, Katalog, dan Aktivitas Laboratorium">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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

<a href="#page-content" class="skip-link">Lewati ke konten utama</a>
<div id="sidebar-overlay" onclick="closeSidebar()" aria-hidden="true"></div>

<!-- ========== SIDEBAR ========== -->
<nav id="sidebar" aria-label="Menu utama">
    <a href="{{ url('/') }}" class="sidebar-brand">
        <div class="sidebar-brand-text">
            <div class="brand-name">SIARKA</div>
            <div class="brand-sub" style="font-size:.75rem;line-height:1.25;">Sistem Informasi Administrasi, Reservasi, Katalog, dan Aktivitas Laboratorium</div>
        </div>
    </a>

    @php
        $R = \App\Support\Role::class;
        // Satu sumber menu per role. r = nama route, is = pola URL.
        $beranda = ['route' => 'dashboard', 'label' => 'Beranda', 'icon' => 'bi-speedometer2', 'r' => ['dashboard']];
        $katalog = ['route' => 'katalog', 'label' => 'Katalog', 'icon' => 'bi-collection', 'r' => ['katalog']];
        $ajukan = ['route' => 'peminjaman.index', 'label' => 'Peminjaman', 'icon' => 'bi-arrow-up-right-square', 'r' => ['peminjaman.index', 'peminjaman.show']];
        $riwayat = ['route' => 'peminjaman.riwayat', 'label' => 'Riwayat Peminjaman', 'icon' => 'bi-clock-history', 'r' => ['peminjaman.riwayat']];
        $pengajuan = ['route' => 'pengajuan.index', 'label' => 'Pengajuan Peminjaman', 'icon' => 'bi-clipboard2-check', 'r' => ['pengajuan.*']];
        $pengembalian = ['route' => 'pengembalian.index', 'label' => 'Pengembalian', 'icon' => 'bi-arrow-down-left-square', 'r' => ['pengembalian.*']];
        $kelolaKatalog = ['route' => 'kelola-katalog.index', 'label' => 'Kelola Katalog', 'icon' => 'bi-boxes', 'is' => ['kelola-katalog*']];
        $dataAkademik = ['route' => 'data-akademik.index', 'label' => 'Data Akademik', 'icon' => 'bi-mortarboard', 'r' => ['data-akademik.index']];
        $jadwal = ['route' => 'jadwal.index', 'label' => 'Jadwal Perkuliahan', 'icon' => 'bi-calendar-week', 'r' => ['jadwal.*']];
        // Satu daftar datar per role, urut menurut alur kerja (tanpa kelompok).
        $menus = [
            $R::LABORAN => [
                $beranda, $pengajuan, $pengembalian, $katalog, $kelolaKatalog, $dataAkademik, $jadwal,
                ['route' => 'kelola-user.index', 'label' => 'Kelola Data User', 'icon' => 'bi-people', 'is' => ['kelola-user*']],
                ['route' => 'pengaturan-lab.index', 'label' => 'Pengaturan Laboratorium', 'icon' => 'bi-sliders', 'is' => ['pengaturan-lab*']],
            ],
            $R::DOSEN => [$beranda, $katalog, $ajukan, $riwayat, $pengajuan],
            $R::MAHASISWA => [$beranda, $katalog, $ajukan, $riwayat],
            $R::STAFF => [$beranda, $katalog, $dataAkademik, $jadwal],
        ][$role] ?? [$beranda];
    @endphp
    <div class="sidebar-nav">
        <ul class="nav flex-column">
            @foreach($menus as $m)
            @php $aktif = request()->routeIs(...($m['r'] ?? [])) || request()->is(...($m['is'] ?? ['-'])); @endphp
            <li class="nav-item">
                <a href="{{ route($m['route']) }}" class="nav-link {{ $aktif ? 'active' : '' }}" {{ $aktif ? 'aria-current="page"' : '' }}>
                    <i class="bi {{ $m['icon'] }}" aria-hidden="true"></i><span>{{ $m['label'] }}</span>
                </a>
            </li>
            @endforeach
        </ul>
    </div>
</nav>

<!-- ========== MAIN ========== -->
<div id="main-wrapper">
    <header id="topbar">
        <button type="button" class="topbar-toggle" onclick="toggleSidebar()" aria-label="Buka menu" aria-controls="sidebar" aria-expanded="false"><i class="bi bi-list"></i></button>

        <div class="topbar-title">
            <h1 class="font-display">@yield('page-title', 'SIARKA')</h1>
            <p>@yield('page-subtitle', 'Sistem Informasi Administrasi, Reservasi, Katalog, dan Aktivitas Laboratorium')</p>
        </div>

        <div class="topbar-right dropdown">
            <button type="button" class="topbar-user dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menu akun {{ $me['nama'] ?? '' }}">
                <span class="topbar-avatar">{{ $initial }}</span>
                <span class="d-none d-sm-block text-start lh-sm">
                    <span class="d-block fw-semibold" style="font-size:.82rem">{{ $me['nama'] ?? '' }}</span>
                    <span class="d-block" style="font-size:.75rem;color:var(--text-muted)">{{ $roleLabel }}</span>
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

    <main id="page-content" tabindex="-1">
        @yield('content')
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@include('layouts._notifikasi')
<script>
    function setSidebar(buka) {
        document.getElementById('sidebar').classList.toggle('show', buka);
        document.getElementById('sidebar-overlay').classList.toggle('show', buka);
        const t = document.querySelector('.topbar-toggle');
        if (t) { t.setAttribute('aria-expanded', buka ? 'true' : 'false'); t.setAttribute('aria-label', buka ? 'Tutup menu' : 'Buka menu'); if (!buka) t.focus({ preventScroll: true }); }
    }
    function toggleSidebar() { setSidebar(!document.getElementById('sidebar').classList.contains('show')); }
    function closeSidebar() { setSidebar(false); }
    // Setiap modal diberi nama aksesibel dari judulnya (aria-labelledby) dan role dialog
    document.querySelectorAll('.modal:not([aria-labelledby])').forEach(function (m, i) {
        const j = m.querySelector('.modal-title');
        if (!j) return;
        if (!j.id) j.id = 'modalJudul' + i;
        m.setAttribute('aria-labelledby', j.id);
    });
    // Escape menutup sidebar mobile; fokus kembali ke tombol menu
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && document.getElementById('sidebar').classList.contains('show')) closeSidebar();
    });

    // Satu mekanisme untuk semua form POST: cegah kirim ganda dan beri umpan balik "memproses"
    // tanpa mengubah ukuran tombol (lebar dikunci). Form dapat memilih keluar dengan atribut data-no-lock.
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (e.defaultPrevented || !(form instanceof HTMLFormElement) || form.hasAttribute('data-no-lock')) return;
        if ((form.method || '').toLowerCase() !== 'post') return;
        if (form.dataset.terkirim === '1') { e.preventDefault(); return; }
        form.dataset.terkirim = '1';
        const tombol = e.submitter || form.querySelector('button[type=submit]');
        setTimeout(function () {
            if (!tombol) return;
            tombol.dataset.semula = tombol.innerHTML;
            tombol.style.minWidth = tombol.offsetWidth + 'px';
            tombol.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Memproses...';
            form.querySelectorAll('button[type=submit]').forEach(b => { b.disabled = true; });
        }, 0);
    });
    // Kembali lewat cache browser (Back): pulihkan form yang sempat terkunci.
    window.addEventListener('pageshow', function () {
        document.querySelectorAll('form[data-terkirim="1"]').forEach(function (f) {
            delete f.dataset.terkirim;
            f.querySelectorAll('button[type=submit]').forEach(function (b) {
                b.disabled = false;
                if (b.dataset.semula) { b.innerHTML = b.dataset.semula; b.style.minWidth = ''; }
            });
        });
    });
</script>
@stack('scripts')
</body>
</html>
