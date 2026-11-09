<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Beranda') — SIARKA</title>
    <meta name="application-name" content="SIARKA">
    <meta name="description" content="SIARKA — Sistem Informasi Administrasi, Reservasi, Katalog, dan Aktivitas Laboratorium">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/siarka-ikon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('css/siarka.css') }}?v={{ filemtime(public_path('css/siarka.css')) }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
<a href="#page-content" class="skip-link">Lewati ke konten</a>
@php
    $me = \App\Services\AuthService::user();
    $role = $me['role'] ?? null;
    $roleLabel = \App\Support\Role::LABELS[$role] ?? '';
    $isAdmin = $role === \App\Support\Role::LABORAN;
    $isStaff = $role === \App\Support\Role::STAFF;
    $initial = mb_strtoupper(mb_substr($me['nama'] ?? 'U', 0, 1));

    // Lonceng notifikasi memakai akun dari database (bukan Auth::user()).
    $notifAkun = \App\Models\Akun::find($me['email'] ?? '');
    $notifTerbaru = $notifAkun ? $notifAkun->notifications()->latest()->take(10)->get() : collect();
    // Daftar belum penuh (<10) berarti semua notifikasi sudah terambil: hitung dari daftar, tanpa query kedua.
    $notifJumlah = ! $notifAkun ? 0 : ($notifTerbaru->count() < 10 ? $notifTerbaru->whereNull('read_at')->count() : $notifAkun->unreadNotifications()->count());
@endphp

<div id="sidebar-overlay" data-sidebar="tutup"></div>

<!-- ========== SIDEBAR KOMPONEN TUNGGAL BERBASIS ROLE ========== -->
@include('components.sidebar')

<!-- ========== MAIN ========== -->
<div id="main-wrapper">
    <header id="topbar">
        <button class="topbar-toggle" data-sidebar="toggle" aria-label="Toggle sidebar"><i class="bi bi-list" aria-hidden="true"></i></button>

        <div class="topbar-title">
            <h1 class="font-display">@yield('page-title', 'SIARKA')</h1>
            <p>@yield('page-subtitle', 'Sistem Informasi Administrasi, Reservasi, Katalog, dan Aktivitas Laboratorium')</p>
        </div>

        <div class="dropdown">
            <button class="topbar-bell" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifikasi">
                <i class="bi bi-bell" aria-hidden="true"></i>
                @if($notifJumlah > 0)
                    <span class="notif-badge">{{ $notifJumlah > 99 ? '99+' : $notifJumlah }}</span>
                @endif
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow-sm notif-menu">
                <div class="notif-head">
                    <span class="fw-semibold" style="font-size:.875rem;">Notifikasi</span>
                    @if($notifJumlah > 0)
                    <form action="{{ route('notifikasi.baca-semua') }}" method="POST" data-no-lock>
                        @csrf
                        <button type="submit" class="notif-mark-all">Tandai semua dibaca</button>
                    </form>
                    @endif
                </div>
                <div class="notif-list">
                    @forelse($notifTerbaru as $notif)
                    <form action="{{ route('notifikasi.baca', $notif->id) }}" method="POST" data-no-lock>
                        @csrf
                        <button type="submit" class="dropdown-item notif-item" title="{{ $notif->read_at ? 'Sudah dibaca' : 'Klik untuk tandai sudah dibaca' }}">
                            <span class="notif-dot {{ $notif->read_at ? 'notif-dot-read' : '' }}"></span>
                            <span class="flex-grow-1 min-w-0 text-start">
                                <span class="d-block notif-judul text-truncate">{{ $notif->data['judul'] ?? 'Notifikasi' }}</span>
                                <span class="d-block notif-pesan">{{ $notif->data['pesan'] ?? '' }}</span>
                                <span class="d-block notif-waktu">{{ $notif->created_at?->format('d/m/Y H:i') }}</span>
                            </span>
                        </button>
                    </form>
                    @empty
                    <div class="notif-kosong"><i class="bi bi-bell-slash d-block mb-1" aria-hidden="true" style="font-size:1.1rem;"></i>Belum ada notifikasi.</div>
                    @endforelse
                </div>
                <div class="notif-foot">
                    <a class="dropdown-item text-center fw-semibold py-2" href="{{ route('notifikasi.index') }}" style="font-size:.875rem;"><i class="bi bi-inbox me-1" aria-hidden="true"></i>Lihat semua notifikasi</a>
                </div>
            </div>
        </div>

        <div class="topbar-right dropdown">
            <button class="topbar-user dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="topbar-avatar">{{ $initial }}</span>
                <span class="d-none d-sm-block text-start lh-sm">
                    <span class="d-block fw-semibold" style="font-size:.875rem">{{ $me['nama'] ?? '' }}</span>
                    <span class="d-block" style="font-size:.75rem;color:var(--text-muted)">{{ $roleLabel }}</span>
                </span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                <li class="px-3 py-2"><div style="font-size:.75rem;color:var(--text-muted)">{{ $me['email'] ?? '' }}</div></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="{{ route('profil.index') }}"><i class="bi bi-person me-2" aria-hidden="true"></i>Profil Saya</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i>Keluar</button>
                    </form>
                </li>
            </ul>
        </div>
    </header>

    <main id="page-content" tabindex="-1">
        @yield('content')
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('show');
        document.getElementById('sidebar-overlay').classList.toggle('show');
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('show');
        document.getElementById('sidebar-overlay').classList.remove('show');
    }
    function toggleSidebarCollapse() {
        document.body.classList.toggle('sidebar-collapsed');
        try {
            localStorage.setItem('siarka_sidebar_collapsed', document.body.classList.contains('sidebar-collapsed') ? '1' : '0');
        } catch(e) {}
    }
    (function() {
        try {
            if (localStorage.getItem('siarka_sidebar_collapsed') === '1' && window.innerWidth >= 992) {
                document.body.classList.add('sidebar-collapsed');
            }
        } catch(e) {}
    })();

    document.addEventListener('click', function (e) {
        const el = e.target.closest('[data-sidebar]');
        if (!el) return;
        if (el.dataset.sidebar === 'toggle') toggleSidebar();
        else if (el.dataset.sidebar === 'collapse') toggleSidebarCollapse();
        else closeSidebar();
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
@include('layouts._notifikasi')
@stack('scripts')
</body>
</html>
