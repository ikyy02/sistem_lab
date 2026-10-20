<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIARKA — Laboratorium Komputer Politeknik Negeri Tanah Laut</title>
    <meta name="application-name" content="SIARKA">
    <meta name="description" content="SIARKA — Sistem Informasi Administrasi, Reservasi, Katalog, dan Aktivitas Laboratorium Komputer Politeknik Negeri Tanah Laut.">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    @include('layouts._theme')
    <style>
        html { scroll-behavior: smooth; scroll-padding-top: 88px; }
        body { background: #fff; }
        .container { max-width: 1160px; }

        /* Navbar: transparan menyatu dengan hero, putih setelah digulir */
        :root { --lp-nav-h: 76px; }
        html { scroll-padding-top: calc(var(--lp-nav-h) + 12px); }
        .lp-nav { position: fixed; top: 0; left: 0; right: 0; z-index: 1030; background: transparent; border-bottom: 1px solid transparent; transition: background-color .2s ease, border-color .2s ease; }
        .lp-nav .navbar { height: var(--lp-nav-h); padding: 0; }
        .lp-brand { font-family: 'Fraunces', serif; font-weight: 600; font-size: 1.5rem; letter-spacing: .04em; color: #fff; text-decoration: none; transition: color .2s ease; }
        .lp-link { position: relative; color: rgba(255,255,255,.82); text-decoration: none; font-size: .9rem; font-weight: 500; padding: 8px 4px !important; margin-inline: 14px; transition: color .2s ease; }
        .lp-link::after { content: ''; position: absolute; left: 0; right: 0; bottom: 0; height: 2px; background: var(--gold-bright); transform: scaleX(0); transition: transform .2s ease; }
        .lp-link:hover, .lp-link:focus-visible, .lp-link.active { color: #fff; }
        .lp-link.active::after { transform: scaleX(1); }
        .lp-masuk { margin-left: 20px; }
        .lp-nav .navbar-toggler { color: #fff; padding: 6px 8px; }
        .lp-nav .navbar-toggler:focus { box-shadow: 0 0 0 3px rgba(255,255,255,.3); }
        .lp-nav.is-scrolled { background: #fff; border-bottom-color: var(--border-color); }
        .lp-nav.is-scrolled .lp-brand { color: var(--ink); }
        .lp-nav.is-scrolled .lp-link { color: var(--text-muted); }
        .lp-nav.is-scrolled .lp-link:hover, .lp-nav.is-scrolled .lp-link.active { color: var(--ink); }
        .lp-nav.is-scrolled .lp-link::after { background: var(--gold); }
        .lp-nav.is-scrolled .navbar-toggler { color: var(--ink); }
        .lp-nav:not(.is-scrolled) .lp-masuk { background: #fff; border-color: #fff; color: var(--ink); }
        .lp-nav:not(.is-scrolled) .lp-masuk:hover { background: var(--gold-tint); border-color: var(--gold-tint); }

        /* Hero */
        .lp-hero { background: var(--ink); color: #fff; position: relative; overflow: hidden; }
        .lp-hero .container { padding-top: calc(var(--lp-nav-h) + 56px); padding-bottom: 84px; }
        .lp-eyebrow { display: inline-flex; align-items: center; gap: 10px; font-size: .75rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: var(--gold-bright); }
        .lp-eyebrow::before { content: ''; width: 30px; height: 2px; background: var(--gold-bright); }
        .lp-title { font-family: 'Fraunces', serif; font-weight: 600; font-size: clamp(2.1rem, 4.6vw, 3.4rem); line-height: 1.12; margin: 16px 0 18px; }
        .lp-title em { font-style: normal; color: var(--gold-bright); }
        .lp-lead { color: #D6D3CB; font-size: 1rem; line-height: 1.75; max-width: 540px; }
        .btn-lp { padding: 12px 24px; font-size: .92rem; border-radius: 10px; }
        .btn-lp-gold { background: var(--gold-bright); border-color: var(--gold-bright); color: #1B1A18; }
        .btn-lp-gold:hover { background: #D8AA50; border-color: #D8AA50; color: #1B1A18; }
        .btn-lp-ghost { background: transparent; border: 1px solid rgba(255,255,255,.38); color: #fff; }
        .btn-lp-ghost:hover { background: rgba(255,255,255,.1); color: #fff; border-color: #fff; }
        .lp-photos { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .lp-photo { position: relative; border-radius: 14px; overflow: hidden; background: #3a3936; aspect-ratio: 4 / 5; border: 1px solid rgba(255,255,255,.14); }
        .lp-photo:nth-child(2) { margin-top: 34px; }
        .lp-photo img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .lp-photo .lp-fallback { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,.35); font-size: 2.6rem; z-index: 0; }
        .lp-photo img { position: relative; z-index: 1; }
        .lp-photo-cap { position: absolute; left: 0; right: 0; bottom: 0; z-index: 2; padding: 22px 14px 12px; font-size:.75rem; color: #fff; background: linear-gradient(transparent, rgba(0,0,0,.65)); }


        /* Section */
        .lp-section { padding: 84px 0; }
        .lp-band { background: #FAF7F0; border-top: 1px solid #EFE1C2; border-bottom: 1px solid #EFE1C2; }
        .lp-kicker { font-size:.75rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--gold-text); margin-bottom: 8px; }
        .lp-h2 { font-family: 'Fraunces', serif; font-weight: 600; font-size: clamp(1.6rem, 3vw, 2.2rem); color: var(--ink); margin-bottom: 12px; }
        .lp-sub { color: var(--text-muted); max-width: 640px; line-height: 1.7; }
        .lp-card { height: 100%; background: #fff; border: 1px solid var(--border-color); border-radius: 14px; padding: 26px; }
        .lp-card:hover { border-color: var(--gold); }
        .lp-icon { width: 48px; height: 48px; border-radius: 12px; background: var(--gold-tint); border: 1px solid #EFE1C2; color: var(--gold-text); display: flex; align-items: center; justify-content: center; font-size: 1.3rem; margin-bottom: 16px; }
        .lp-card h3 { font-size: 1.02rem; font-weight: 700; margin-bottom: 8px; }
        .lp-card p { font-size: .88rem; color: var(--text-muted); margin: 0; line-height: 1.65; }
        .lp-lab { display: block; background: #fff; border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; height: 100%; }
        .lp-lab .foto { aspect-ratio: 16 / 10; background: var(--gold-tint); display: flex; align-items: center; justify-content: center; color: var(--gold-text); font-size: 2.2rem; overflow: hidden; }
        .lp-lab .foto img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .lp-lab .nama { padding: 14px 18px; font-weight: 600; font-size: .98rem; color: var(--ink); }
        .lp-all { display: inline-flex; align-items: center; gap: 8px; margin-top: 32px; padding: 11px 22px; border: 1px solid var(--ink); border-radius: 8px; color: var(--ink); text-decoration: none; font-weight: 600; font-size: .88rem; }
        .lp-all:hover { background: var(--ink); color: #fff; }
        .lp-index { list-style: none; margin: 0; padding: 0; columns: 3; column-gap: 32px; }
        .lp-index li { break-inside: avoid; padding: 12px 0; border-top: 1px solid var(--border-color); font-weight: 500; }
        .lp-index li span { color: var(--gold-text); font-weight: 600; font-size: .8rem; margin-right: 12px; }
        @media (max-width: 991.98px) { .lp-index { columns: 2; } }
        @media (max-width: 575.98px) { .lp-index { columns: 1; } }
        .lp-step { position: relative; padding-top: 4px; }
        .lp-step-no { width: 42px; height: 42px; border-radius: 50%; background: var(--ink); color: #fff; font-weight: 700; display: flex; align-items: center; justify-content: center; margin-bottom: 14px; }
        .lp-step h3 { font-size: 1rem; font-weight: 700; }
        .lp-step p { font-size: .87rem; color: var(--text-muted); line-height: 1.65; margin: 0; }
        .lp-jam { background: #fff; border: 1px solid var(--border-color); border-radius: 14px; padding: 26px; }
        .lp-jam .k { font-size:.75rem; text-transform: uppercase; letter-spacing: .08em; color: var(--text-muted); }
        .lp-cta { background: var(--ink); color: #fff; border-radius: 18px; padding: 44px; }
        .lp-cta h2 { font-family: 'Fraunces', serif; font-weight: 600; font-size: clamp(1.4rem, 2.6vw, 1.9rem); }

        /* Footer */
        .lp-footer { background: #1B1A18; color: #BDB9AE; padding: 56px 0 28px; font-size: .86rem; }
        .lp-footer h4 { color: #fff; font-size: .82rem; text-transform: uppercase; letter-spacing: .08em; margin-bottom: 14px; }
        .lp-footer a { color: #E7E4DB; text-decoration: none; }
        .lp-footer a:hover { color: var(--gold-bright); }
        .lp-footer li { margin-bottom: 8px; }
        .lp-copy { border-top: 1px solid rgba(255,255,255,.12); margin-top: 36px; padding-top: 20px; font-size:.8rem; }

        @media (max-width: 991.98px) {
            .lp-hero .container { padding-top: calc(var(--lp-nav-h) + 32px); padding-bottom: 56px; }
            :root { --lp-nav-h: 68px; }
            .lp-nav.menu-open:not(.is-scrolled) { background: var(--ink); }
            .lp-nav .navbar-collapse { padding: 8px 0 20px; }
            .lp-link { display: inline-block; margin-inline: 0; padding: 12px 2px !important; }
            .lp-masuk { margin: 12px 0 0; width: 100%; justify-content: center; }
            .lp-section { padding: 60px 0; }
            .lp-photos { max-width: 480px; }
        }
        @media (max-width: 575.98px) {
            .lp-cta { padding: 28px 22px; }
            .lp-photo:nth-child(2) { margin-top: 18px; }
        }
        @media (prefers-reduced-motion: reduce) { html { scroll-behavior: auto; } }
    </style>
</head>
<body>
@php
    $labelHari = ['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat', 'sabtu' => 'Sabtu', 'minggu' => 'Minggu'];
    $hariAktif = $setting ? array_map(fn ($h) => $labelHari[$h] ?? ucfirst($h), $setting->hariAktif()) : [];
    $jam = fn ($t) => str_replace(':', '.', substr((string) $t, 0, 5));
    // Ruangan/laboratorium berasal dari database (Kelola Katalog > Ruangan).
    $semuaRuangan = $ruangans;
    $pratinjau = $semuaRuangan->take(4);
    $fotoHero = $semuaRuangan->filter(fn ($r) => $r->gambar)->take(2)->values();
@endphp

<header class="lp-nav" id="lpNav">
    <div class="container">
        <nav class="navbar navbar-expand-lg">
            <a href="{{ route('landing') }}" class="lp-brand">SIARKA</a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navLanding" aria-controls="navLanding" aria-expanded="false" aria-label="Buka atau tutup menu">
                <i class="bi bi-list fs-2"></i>
            </button>
            <div class="collapse navbar-collapse justify-content-lg-end" id="navLanding">
                <ul class="navbar-nav align-items-lg-center">
                    <li class="nav-item"><a class="nav-link lp-link" href="#tentang" data-spy="tentang">Tentang</a></li>
                    <li class="nav-item"><a class="nav-link lp-link" href="#fasilitas" data-spy="fasilitas">Fasilitas</a></li>
                    <li class="nav-item"><a class="nav-link lp-link" href="#layanan" data-spy="layanan">Layanan</a></li>
                    @if($setting)<li class="nav-item"><a class="nav-link lp-link" href="#operasional" data-spy="operasional">Jam Operasional</a></li>@endif
                    <li class="nav-item"><a class="nav-link lp-link" href="#informasi" data-spy="informasi">Informasi</a></li>
                </ul>
                <a href="{{ route('login') }}" class="btn btn-primary lp-masuk d-inline-flex align-items-center gap-2"><i class="bi bi-box-arrow-in-right"></i> Masuk SIARKA</a>
            </div>
        </nav>
    </div>
</header>

<main>
    {{-- Hero --}}
    <section class="lp-hero">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-12 col-lg-6">
                    <span class="lp-eyebrow">Laboratorium Komputer Politala</span>
                    <h1 class="lp-title">Kelola dan manfaatkan laboratorium <em>dalam satu sistem</em>.</h1>
                    <p class="lp-lead">Di laboratorium Komputer Politeknik Negeri Tanah Laut, SIARKA membantu sivitas akademika melihat katalog, mengajukan peminjaman, dan memantau aktivitas laboratorium.</p>
                    <div class="d-flex flex-wrap gap-3 mt-4">
                        <a href="#fasilitas" class="btn btn-lp btn-lp-gold d-inline-flex align-items-center gap-2">Jelajahi Fasilitas <i class="bi bi-arrow-down"></i></a>
                    </div>
                </div>
                @if($fotoHero->isNotEmpty())
                <div class="col-12 col-lg-6">
                    <div class="lp-photos ms-lg-auto">
                        @foreach($fotoHero as $r)
                            <figure class="lp-photo mb-0">
                                <img src="{{ $r->gambar_url }}" alt="{{ $r->nama_ruangan }}" @if(! $loop->first) loading="lazy" @endif onerror="this.style.visibility='hidden'">
                                <figcaption class="lp-photo-cap">{{ $r->nama_ruangan }}</figcaption>
                            </figure>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </section>

    {{-- Tentang --}}
    <section class="lp-section" id="tentang">
        <div class="container">
            <div class="row g-5 align-items-start">
                <div class="col-12 col-lg-8">
                    <div class="lp-kicker">Tentang SIARKA</div>
                    <h2 class="lp-h2">Satu sistem untuk laboratorium.</h2>
                    <p class="lp-sub mb-0">SIARKA — <strong>Sistem Informasi Administrasi, Reservasi, Katalog, dan Aktivitas Laboratorium</strong> — adalah sistem untuk mengelola alat, bahan, ruangan, dan peminjaman laboratorium Jurusan Komputer dan Bisnis.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Fasilitas --}}
    <section class="lp-section lp-band" id="fasilitas">
        <div class="container">
            <div class="lp-kicker">Fasilitas dan Laboratorium</div>
            <h2 class="lp-h2">Laboratorium yang tersedia</h2>
            @if($semuaRuangan->isEmpty())
                <p class="lp-sub mb-0">Belum ada ruangan laboratorium yang terdaftar.</p>
            @else
                <p class="lp-sub mb-4">Ketersediaan ruangan untuk dipinjam dapat dilihat setelah masuk ke SIARKA.</p>
                <div class="row row-cols-1 row-cols-sm-2 g-4">
                    @foreach($pratinjau as $r)
                        <div class="col">
                            <figure class="lp-lab mb-0">
                                <div class="foto">
                                    @if($r->gambar_url)
                                        <img src="{{ $r->gambar_url }}" alt="{{ $r->nama_ruangan }}" loading="lazy" onerror="this.style.visibility='hidden'">
                                    @else
                                        <i class="bi bi-pc-display-horizontal"></i>
                                    @endif
                                </div>
                                <figcaption class="nama">{{ $r->nama_ruangan }}</figcaption>
                            </figure>
                        </div>
                    @endforeach
                </div>
                @if($semuaRuangan->count() > $pratinjau->count())
                    <a href="#semua-fasilitas" class="lp-all">Lihat Semua <i class="bi bi-arrow-down"></i></a>
                @endif
            @endif
        </div>
    </section>

    @if($semuaRuangan->count() > $pratinjau->count())
    <section class="lp-section" id="semua-fasilitas">
        <div class="container">
            <h2 class="lp-h2">Seluruh laboratorium</h2>
            <p class="lp-sub mb-4">{{ $semuaRuangan->count() }} ruangan terdaftar di SIARKA.</p>
            <ol class="lp-index">
                @foreach($semuaRuangan as $r)
                    <li><span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>{{ $r->nama_ruangan }}</li>
                @endforeach
            </ol>
        </div>
    </section>
    @endif

    {{-- Layanan --}}
    <section class="lp-section" id="layanan">
        <div class="container">
            <div class="lp-kicker">Layanan SIARKA</div>
            <h2 class="lp-h2">Fungsi yang tersedia di sistem</h2>
            <p class="lp-sub mb-5">Setiap layanan berikut tersedia di SIARKA setelah pengguna masuk dengan akunnya.</p>
            <div class="row g-4">
                <div class="col-12 col-md-6 col-lg-3"><div class="lp-card"><div class="lp-icon"><i class="bi bi-collection"></i></div><h3>Katalog</h3><p>Daftar alat, bahan, dan ruangan laboratorium beserta stok yang tersedia.</p></div></div>
                <div class="col-12 col-md-6 col-lg-3"><div class="lp-card"><div class="lp-icon"><i class="bi bi-calendar-check"></i></div><h3>Peminjaman</h3><p>Mahasiswa dan Dosen mengajukan peminjaman ruangan, alat, atau bahan, lalu memantau statusnya.</p></div></div>
                <div class="col-12 col-md-6 col-lg-3"><div class="lp-card"><div class="lp-icon"><i class="bi bi-clipboard2-check"></i></div><h3>Persetujuan dan Pengembalian</h3><p>Dosen dan Laboran memproses pengajuan; Laboran mencatat pengembalian alat dan bahan.</p></div></div>
                <div class="col-12 col-md-6 col-lg-3"><div class="lp-card"><div class="lp-icon"><i class="bi bi-calendar3"></i></div><h3>Jadwal dan Administrasi</h3><p>Jadwal perkuliahan, data akademik, pengguna, dan pengaturan operasional dikelola pihak berwenang.</p></div></div>
            </div>
        </div>
    </section>

    {{-- Alur --}}
    <section class="lp-section lp-band" id="alur">
        <div class="container">
            <div class="lp-kicker">Alur Peminjaman</div>
            <h2 class="lp-h2">Empat langkah sederhana</h2>
            <div class="row g-4 mt-2">
                <div class="col-12 col-sm-6 col-lg-3"><div class="lp-step"><div class="lp-step-no">1</div><h3>Masuk</h3><p>Gunakan akun yang dikelola Laboran/Admin.</p></div></div>
                <div class="col-12 col-sm-6 col-lg-3"><div class="lp-step"><div class="lp-step-no">2</div><h3>Ajukan</h3><p>Tentukan jadwal, lalu pilih ruangan, alat, atau bahan.</p></div></div>
                <div class="col-12 col-sm-6 col-lg-3"><div class="lp-step"><div class="lp-step-no">3</div><h3>Diproses</h3><p>Dosen atau Laboran menyetujui atau menolak pengajuan.</p></div></div>
                <div class="col-12 col-sm-6 col-lg-3"><div class="lp-step"><div class="lp-step-no">4</div><h3>Selesai</h3><p>Pengembalian dicatat dan status peminjaman diperbarui.</p></div></div>
            </div>
        </div>
    </section>

    {{-- Jam operasional (dari Pengaturan Operasional) --}}
    @if($setting)
    <section class="lp-section" id="operasional">
        <div class="container">
            <div class="row g-4 align-items-center">
                <div class="col-12 col-lg-6">
                    <div class="lp-kicker">Jam Operasional</div>
                    <h2 class="lp-h2">Kapan laboratorium dapat digunakan</h2>
                    <p class="lp-sub mb-0">Jam dan hari berikut diatur oleh Laboran/Admin dan menjadi dasar penjadwalan peminjaman.</p>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="lp-jam d-flex flex-wrap gap-4">
                        <div><div class="k">Jam</div><div class="fw-bold" style="font-size:1.4rem;">{{ $jam($setting->jam_buka) }} – {{ $jam($setting->jam_tutup) }}</div></div>
                        <div><div class="k">Hari</div><div class="fw-semibold">{{ $hariAktif ? implode(', ', $hariAktif) : '—' }}</div></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- Ajakan --}}
    <section class="pb-5 pt-2">
        <div class="container">
            <div class="lp-cta d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h2 class="mb-1">Siap menggunakan laboratorium?</h2>
                    <p class="mb-0" style="color:#D6D3CB;">Masuk ke SIARKA untuk melihat katalog dan mengajukan peminjaman.</p>
                </div>
                <a href="{{ route('login') }}" class="btn btn-lp btn-lp-gold d-inline-flex align-items-center gap-2"><i class="bi bi-box-arrow-in-right"></i> Masuk SIARKA</a>
            </div>
        </div>
    </section>
</main>

<footer class="lp-footer" id="informasi">
    <div class="container">
        <div class="row g-4">
            <div class="col-12 col-lg-5">
                <div class="lp-brand-name mb-2" style="color:#fff;">SIARKA</div>
                <p class="mb-0" style="max-width:420px;line-height:1.7;">Sistem Informasi Administrasi, Reservasi, Katalog, dan Aktivitas Laboratorium — Jurusan Komputer dan Bisnis, Politeknik Negeri Tanah Laut.</p>
            </div>
            <div class="col-12 col-lg-7">
                <h4>Halaman</h4>
                <ul class="list-unstyled mb-0">
                    <li><a href="#tentang">Tentang</a></li>
                    <li><a href="#fasilitas">Fasilitas</a></li>
                    <li><a href="#layanan">Layanan</a></li>
                    <li><a href="{{ route('login') }}">Masuk SIARKA</a></li>
                </ul>
            </div>
        </div>
        <div class="lp-copy">&copy; {{ date('Y') }} SIARKA &middot; Jurusan Komputer dan Bisnis, Politeknik Negeri Tanah Laut</div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (function () {
        const nav = document.getElementById('lpNav');
        const menu = document.getElementById('navLanding');
        const links = [...document.querySelectorAll('.lp-link[data-spy]')];
        const peta = { 'tentang': 'tentang', 'fasilitas': 'fasilitas', 'semua-fasilitas': 'fasilitas', 'layanan': 'layanan', 'alur': 'layanan', 'operasional': 'operasional', 'informasi': 'informasi' };

        function perbarui() {
            nav.classList.toggle('is-scrolled', window.scrollY > 8);
            const batas = nav.offsetHeight + 40;
            let aktif = null;
            Object.keys(peta).forEach(function (id) {
                const el = document.getElementById(id);
                if (el && el.getBoundingClientRect().top <= batas) aktif = peta[id];
            });
            if (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 4) aktif = 'informasi';
            links.forEach(a => a.classList.toggle('active', a.dataset.spy === aktif));
        }
        window.addEventListener('scroll', perbarui, { passive: true });
        window.addEventListener('resize', perbarui);
        perbarui();

        menu.addEventListener('show.bs.collapse', () => nav.classList.add('menu-open'));
        menu.addEventListener('hidden.bs.collapse', () => nav.classList.remove('menu-open'));
        menu.querySelectorAll('a').forEach(function (a) {
            a.addEventListener('click', function () {
                if (menu.classList.contains('show')) bootstrap.Collapse.getOrCreateInstance(menu).hide();
            });
        });
    })();
</script>
</body>
</html>
