<!DOCTYPE html>
<html lang="id" style="scroll-behavior:smooth;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIARKA — Sistem Informasi Administrasi, Reservasi, Katalog, dan Aktivitas Laboratorium</title>
    <meta name="application-name" content="SIARKA">
    <meta name="description" content="SIARKA — Sistem Informasi Administrasi, Reservasi, Katalog, dan Aktivitas Laboratorium Jurusan Komputer dan Bisnis.">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    @include('layouts._theme')
    <style>
        body { background: #fff; }
        /* Semua tautan anchor (termasuk #masuk pada kartu login) berhenti di bawah navbar sticky, bukan menempel ke tepi atas. */
        html { scroll-padding-top: 96px; }
        .lp-nav { position: sticky; top: 0; z-index: 1030; background: rgba(255,255,255,.92); backdrop-filter: blur(8px); border-bottom: 1px solid var(--border-color); }
        .lp-brand { display: flex; align-items: center; gap: 12px; text-decoration: none; }
        .lp-brand-name { font-family: 'Fraunces', serif; font-weight: 600; font-size: 1.25rem; color: var(--ink); letter-spacing: .02em; line-height: 1.1; }
        .lp-brand-sub { font-size: .68rem; color: var(--text-muted); }
        .lp-link { color: #4B5563; text-decoration: none; font-size: .88rem; font-weight: 500; padding: 6px 4px; }
        .lp-link:hover { color: var(--gold); }
        .lp-hero { position: relative; overflow: hidden; background: var(--gold-tint); border-bottom: 1px solid #EFE1C2;
            background-image: radial-gradient(#EFE1C2 1px, transparent 1px); background-size: 22px 22px; }
        .lp-hero::after { content: ''; position: absolute; right: -120px; bottom: -120px; width: 340px; height: 340px; border-radius: 50%; border: 1px solid #EFE1C2; }
        .lp-eyebrow { display: inline-flex; align-items: center; gap: 8px; font-size: .74rem; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: var(--gold); }
        .lp-eyebrow::before { content: ''; width: 28px; height: 2px; background: var(--gold); border-radius: 2px; }
        .lp-title { font-family: 'Fraunces', serif; font-weight: 700; font-size: clamp(2.6rem, 6vw, 4rem); line-height: 1.05; color: var(--ink); margin: 14px 0 10px; }
        .lp-fullname { font-size: 1.05rem; font-weight: 600; color: var(--ink); max-width: 520px; }
        .lp-lead { color: #6B6249; max-width: 520px; font-size: .98rem; line-height: 1.7; }
        .lp-card { background: #fff; border: 1px solid var(--border-color); border-radius: 16px; padding: 30px; box-shadow: 0 18px 40px rgba(43,42,40,.08); position: relative; z-index: 1; }
        .lp-section { padding: 72px 0; }
        .lp-section-title { font-family: 'Fraunces', serif; font-weight: 600; font-size: 1.8rem; color: var(--ink); }
        .lp-section-sub { color: var(--text-muted); max-width: 620px; }
        .lp-feature { height: 100%; border: 1px solid var(--border-color); border-radius: 14px; padding: 24px; background: #fff; transition: box-shadow .15s, transform .15s; }
        .lp-feature:hover { box-shadow: 0 12px 28px rgba(43,42,40,.08); transform: translateY(-2px); }
        .lp-icon { width: 46px; height: 46px; border-radius: 12px; background: var(--gold-tint); border: 1px solid #EFE1C2; color: var(--gold); display: flex; align-items: center; justify-content: center; font-size: 1.25rem; margin-bottom: 16px; }
        .lp-letter { font-family: 'Fraunces', serif; font-weight: 700; color: var(--gold); font-size: 1.05rem; }
        .lp-step { position: relative; padding-left: 56px; }
        .lp-step-no { position: absolute; left: 0; top: 0; width: 40px; height: 40px; border-radius: 50%; background: var(--ink); color: #fff; font-weight: 700; display: flex; align-items: center; justify-content: center; }
        .lp-band { background: #FAFBFD; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); }
        .lp-role { border-left: 3px solid var(--gold); padding: 4px 0 4px 16px; }
        .lp-footer { background: var(--ink); color: #D6D3CB; padding: 36px 0; font-size: .84rem; }
        .lp-footer a { color: #fff; }
        @media (max-width: 575.98px) { .lp-section { padding: 52px 0; } .lp-card { padding: 22px; } }
    </style>
</head>
<body>
@php
    $labelHari = ['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat', 'sabtu' => 'Sabtu', 'minggu' => 'Minggu'];
    $hariAktif = $setting ? array_map(fn ($h) => $labelHari[$h] ?? ucfirst($h), $setting->hariAktif()) : [];
    $jam = fn ($t) => str_replace(':', '.', substr((string) $t, 0, 5));
@endphp

<header class="lp-nav">
    <div class="container d-flex align-items-center justify-content-between py-3">
        <a href="{{ route('landing') }}" class="lp-brand">
            <span class="sidebar-brand-icon"><i class="bi bi-buildings"></i></span>
            <span><span class="lp-brand-name d-block">SIARKA</span><span class="lp-brand-sub d-none d-sm-block">Politeknik Negeri Tanah Laut</span></span>
        </a>
        <nav class="d-none d-md-flex align-items-center gap-4">
            <a href="#tentang" class="lp-link">Tentang</a>
            <a href="#fitur" class="lp-link">Layanan</a>
            <a href="#alur" class="lp-link">Alur</a>
            <a href="#peran" class="lp-link">Pengguna</a>
            <a href="#operasional" class="lp-link">Jam Operasional</a>
        </nav>
        <a href="#masuk" class="btn btn-primary d-flex align-items-center gap-2"><i class="bi bi-box-arrow-in-right"></i> Masuk</a>
    </div>
</header>

<main>
    {{-- Hero + Login --}}
    <section class="lp-hero" id="tentang">
        <div class="container py-5">
            <div class="row align-items-center g-5 py-lg-4">
                <div class="col-12 col-lg-7">
                    <span class="lp-eyebrow">Jurusan Komputer dan Bisnis</span>
                    <h1 class="lp-title">SIARKA</h1>
                    <p class="lp-fullname">Sistem Informasi Administrasi, Reservasi, Katalog, dan Aktivitas Laboratorium</p>
                    <p class="lp-lead">Satu sistem untuk mengelola katalog alat, bahan, dan ruangan laboratorium, mengajukan dan memproses peminjaman, serta memantau jadwal dan aktivitas laboratorium secara tertata.</p>
                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <a href="#masuk" class="btn btn-primary px-4 py-2 d-flex align-items-center gap-2"><i class="bi bi-box-arrow-in-right"></i> Masuk ke SIARKA</a>
                        <a href="#fitur" class="btn btn-outline-silab px-4 py-2">Lihat Layanan</a>
                    </div>
                </div>

                <div class="col-12 col-lg-5">
                    <form method="POST" action="{{ route('login.attempt') }}" class="lp-card" id="masuk">
                        @csrf
                        <h2 class="mb-1" style="font-family:'Fraunces',serif;font-weight:600;font-size:1.6rem;">Masuk</h2>
                        <p class="mb-4" style="color:var(--text-muted);font-size:.88rem;">Gunakan email dan password akun Anda.</p>

                        @error('email')
                            <div class="alert alert-danger login-error py-2 d-flex align-items-center gap-2"><i class="bi bi-exclamation-circle-fill"></i><span>{{ $message }}</span></div>
                        @enderror

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="nama@domain.ac.id" required autocomplete="username">
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password">
                                <button type="button" class="btn btn-outline-silab" onclick="var i=document.getElementById('password');i.type=i.type==='password'?'text':'password'" aria-label="Tampilkan password"><i class="bi bi-eye"></i></button>
                            </div>
                            @error('password')<div class="text-danger mt-1" style="font-size:.78rem">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2">Masuk</button>
                        <p class="mt-4 mb-0 text-center" style="font-size:.76rem;color:var(--text-muted)">Akun dikelola oleh Laboran/Admin. Tidak tersedia pendaftaran mandiri.</p>
                    </form>
                </div>
            </div>
        </div>
    </section>

    {{-- Layanan: makna SIARKA --}}
    <section class="lp-section" id="fitur">
        <div class="container">
            <h2 class="lp-section-title mb-2">Layanan SIARKA</h2>
            <p class="lp-section-sub mb-5">Setiap huruf pada SIARKA mewakili satu kebutuhan utama pengelolaan laboratorium Jurusan Komputer dan Bisnis.</p>
            <div class="row g-4">
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="lp-feature">
                        <div class="lp-icon"><i class="bi bi-clipboard-data"></i></div>
                        <div class="lp-letter">A · Administrasi</div>
                        <p class="mb-0 mt-2" style="font-size:.88rem;color:var(--text-muted);">Pengelolaan data pengguna, data master, pengaturan operasional, dan jadwal perkuliahan oleh pihak berwenang.</p>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="lp-feature">
                        <div class="lp-icon"><i class="bi bi-calendar-check"></i></div>
                        <div class="lp-letter">R · Reservasi</div>
                        <p class="mb-0 mt-2" style="font-size:.88rem;color:var(--text-muted);">Pengajuan peminjaman alat, bahan, dan ruangan secara daring, lengkap dengan persetujuan dan status prosesnya.</p>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="lp-feature">
                        <div class="lp-icon"><i class="bi bi-collection"></i></div>
                        <div class="lp-letter">K · Katalog</div>
                        <p class="mb-0 mt-2" style="font-size:.88rem;color:var(--text-muted);">Daftar alat, bahan, dan ruangan laboratorium beserta ketersediaan stok yang dapat dilihat setiap pengguna.</p>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="lp-feature">
                        <div class="lp-icon"><i class="bi bi-activity"></i></div>
                        <div class="lp-letter">A · Aktivitas</div>
                        <p class="mb-0 mt-2" style="font-size:.88rem;color:var(--text-muted);">Riwayat peminjaman, pengembalian, dan jadwal kegiatan laboratorium dalam satu tempat.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Alur --}}
    <section class="lp-section lp-band" id="alur">
        <div class="container">
            <h2 class="lp-section-title mb-2">Alur Peminjaman</h2>
            <p class="lp-section-sub mb-5">Dari pengajuan hingga selesai, setiap tahap tercatat dan dapat dipantau.</p>
            <div class="row g-4">
                @foreach([
                    ['Ajukan', 'Pilih alat, bahan, atau ruangan, tentukan jumlah dan waktu, lalu kirim pengajuan.'],
                    ['Persetujuan', 'Dosen atau Laboran memeriksa pengajuan dan menyetujui atau menolaknya.'],
                    ['Digunakan', 'Alat, bahan, atau ruangan dipakai sesuai jadwal peminjaman yang disetujui.'],
                    ['Selesai', 'Pengembalian dicatat dan peminjaman ditutup oleh pihak yang bertanggung jawab.'],
                ] as $i => [$judul, $teks])
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="lp-step">
                            <span class="lp-step-no">{{ $i + 1 }}</span>
                            <div class="fw-bold mb-1">{{ $judul }}</div>
                            <div style="font-size:.86rem;color:var(--text-muted);">{{ $teks }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Pengguna --}}
    <section class="lp-section" id="peran">
        <div class="container">
            <h2 class="lp-section-title mb-2">Untuk Siapa SIARKA</h2>
            <p class="lp-section-sub mb-5">Akses disesuaikan dengan peran sehingga setiap pengguna hanya melihat yang dibutuhkannya.</p>
            <div class="row g-4">
                @foreach([
                    ['Mahasiswa', 'Melihat katalog, mengajukan peminjaman mandiri atau dengan penanggung jawab dosen, dan memantau riwayat.'],
                    ['Dosen', 'Mengajukan peminjaman untuk pembelajaran, serta memproses pengajuan yang menjadi tanggung jawabnya.'],
                    ['Staf Prodi', 'Mengelola data akademik dan jadwal perkuliahan beserta periode berlakunya.'],
                    ['Laboran/Admin', 'Mengelola katalog, stok, pengguna, data master, pengaturan operasional, serta memproses peminjaman dan pengembalian.'],
                ] as [$judul, $teks])
                    <div class="col-12 col-md-6">
                        <div class="lp-role">
                            <div class="fw-bold">{{ $judul }}</div>
                            <div style="font-size:.88rem;color:var(--text-muted);">{{ $teks }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Jam operasional (dari Pengaturan Operasional) --}}
    @if($setting)
    <section class="lp-section lp-band" id="operasional">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-12 col-lg-6">
                    <h2 class="lp-section-title mb-2">Jam Operasional Laboratorium</h2>
                    <p class="lp-section-sub mb-0">Layanan laboratorium tersedia pada hari dan jam berikut.</p>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="lp-feature d-flex flex-wrap align-items-center gap-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="lp-icon mb-0"><i class="bi bi-clock"></i></div>
                            <div><div style="font-size:.74rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;">Jam</div><div class="fw-bold" style="font-size:1.2rem;">{{ $jam($setting->jam_buka) }} – {{ $jam($setting->jam_tutup) }}</div></div>
                        </div>
                        <div>
                            <div style="font-size:.74rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;">Hari</div>
                            <div class="fw-semibold">{{ $hariAktif ? implode(', ', $hariAktif) : '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif
</main>

<footer class="lp-footer">
    <div class="container d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="fw-bold" style="color:#fff;">SIARKA</div>
            <div>Sistem Informasi Administrasi, Reservasi, Katalog, dan Aktivitas Laboratorium</div>
        </div>
        <div>&copy; {{ date('Y') }} SIARKA &middot; Jurusan Komputer dan Bisnis &middot; <a href="#masuk">Masuk</a></div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (function () {
        const kartu = document.getElementById('masuk');
        const email = kartu.querySelector('input[name="email"]');
        const tinggiNav = () => document.querySelector('.lp-nav').offsetHeight;

        function kartuTerlihat() {
            const r = kartu.getBoundingClientRect();
            return r.top >= tinggiNav() && r.bottom <= window.innerHeight;
        }

        // Tombol/tautan "Masuk": bila kartu sudah terlihat, halaman TIDAK digulir (hanya fokus ke email);
        // bila belum terlihat, kartu digulir ke tengah layar di bawah navbar, bukan menempel ke tepi atas.
        function keKartu(e) {
            e.preventDefault();
            if (!kartuTerlihat()) {
                const r = kartu.getBoundingClientRect();
                const target = window.scrollY + r.top - Math.max(tinggiNav() + 16, (window.innerHeight - r.height) / 2);
                window.scrollTo({ top: Math.max(0, target), behavior: 'smooth' });
            }
            if (email) email.focus({ preventScroll: true });
        }
        document.querySelectorAll('a[href="#masuk"]').forEach(a => a.addEventListener('click', keKartu));

        // Setelah gagal masuk, pastikan kartu terlihat tanpa menempel ke navbar.
        if (document.querySelector('.login-error') && !kartuTerlihat()) {
            const r = kartu.getBoundingClientRect();
            window.scrollTo({ top: Math.max(0, window.scrollY + r.top - Math.max(tinggiNav() + 16, (window.innerHeight - r.height) / 2)) });
        }
    })();
</script>
</body>
</html>
