<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - SIARKA Laboratorium Komputer dan Bisnis</title>
    <meta name="application-name" content="SIARKA">
    <meta name="description" content="Masuk ke Sistem Informasi Administrasi, Reservasi, Katalog, dan Aktivitas Laboratorium Jurusan Komputer dan Bisnis Politeknik Negeri Tanah Laut.">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/siarka-ikon.svg') }}">

    {{-- Google Fonts: Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* ==========================================================================
           SISTEM DESAIN SIARKA (Selaras dengan Landing Page)
           - Palet Warna: #1E4E8C (Brand), #163A69 (Hover), #EFF6FF (Light Blue)
           - Teks: #0F172A (Utama), #475569 (Sekunder)
           - Permukaan & Latar: #FFFFFF, #F5F7FA, Border: #E2E8F0
           - Radius: 10px (Komponen), 8px (Input/Tombol kecil)
           - Bayangan: Halus, tanpa efek berlebih
           - Tipografi: Inter
           ========================================================================== */
        :root {
            --font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            --brand: #1E4E8C;
            --brand-dark: #163A69;
            --brand-blue: #1E4E8C;
            --brand-blue-hover: #163A69;
            --brand-blue-light: #EFF6FF;
            --text-main: #0F172A;
            --text-secondary: #475569;
            --border-color: #E2E8F0;
            --bg-white: #ffffff;
            --bg-alt: #F5F7FA;
            --radius-main: 10px;
            --radius-input: 8px;
            --shadow-subtle: 0 1px 2px rgba(0, 0, 0, 0.06);
            --shadow-card: 0 4px 20px -2px rgba(15, 23, 42, 0.07), 0 1px 3px rgba(0, 0, 0, 0.04);
            --ease-standard: cubic-bezier(.22, .61, .36, 1);
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            font-size: 16px;
        }

        body {
            font-family: var(--font-family);
            background-color: var(--bg-alt);
            color: var(--text-main);
            min-height: 100vh;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        a {
            color: var(--brand-blue);
            text-decoration: none;
            transition: color 150ms var(--ease-standard);
        }
        a:hover {
            color: var(--brand-blue-hover);
        }

        /* Tata Letak Dua Kolom */
        .login-layout {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1.15fr 1fr;
        }

        /* --------------------------------------------------------------------------
           SISI KIRI: BRANDING HERO (Identik dengan Visual Hero Landing Page)
           -------------------------------------------------------------------------- */
        .login-side {
            position: relative;
            background-color: var(--brand);
            background-image: url('{{ asset('images/hero-lab-wide.jpg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            color: #ffffff;
            padding: 48px 48px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }

        /* Overlay Panel Kiri: Lapisan 1 (Warna) & Lapisan 2 (Gelap Kontras) */
        .login-side-overlay {
            position: absolute;
            inset: 0;
            background-color: #2563b8;
            mix-blend-mode: color;
            opacity: 0.75;
            z-index: 1;
            pointer-events: none;
        }

        .login-side-shade {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(10, 35, 90, 0.35) 0%, rgba(10, 35, 90, 0.30) 50%, rgba(10, 35, 90, 0.70) 100%);
            z-index: 2;
            pointer-events: none;
        }

        /* Fallback tanpa blend mode bila mix-blend-mode: color tidak didukung */
        @supports not (mix-blend-mode: color) {
            .login-side-overlay {
                background: linear-gradient(180deg, rgba(37, 99, 184, 0.65) 0%, rgba(37, 99, 184, 0.55) 50%, rgba(37, 99, 184, 0.85) 100%);
                mix-blend-mode: normal;
                opacity: 1;
            }
            .login-side-shade {
                display: none;
            }
        }

        .login-side-content {
            position: relative;
            z-index: 3;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Brand Header di Sisi Kiri */
        .side-brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #ffffff;
            width: fit-content;
        }
        .side-brand:hover {
            opacity: 0.95;
        }
        .side-brand-logo {
            width: 36px;
            height: 36px;
            flex-shrink: 0;
        }
        .side-brand-meta {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }
        .side-brand-title {
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: -0.01em;
            color: #ffffff;
        }
        .side-brand-subtitle {
            font-size: 0.74rem;
            color: #E2E8F0;
            font-weight: 500;
        }

        /* Konten Tengah Sisi Kiri */
        .side-center-body {
            max-width: 500px;
            padding: 32px 0;
        }

        .side-badge-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 5px 14px;
            background-color: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 20px;
        }
        .side-badge-tag svg {
            flex-shrink: 0;
        }

        .side-hero-title {
            font-size: clamp(1.8rem, 2.4vw, 2.3rem);
            font-weight: 700;
            color: #ffffff;
            line-height: 1.25;
            letter-spacing: -0.015em;
            margin-bottom: 14px;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
        }

        .side-hero-desc {
            font-size: 0.95rem;
            color: #E2E8F0;
            line-height: 1.65;
            margin-bottom: 28px;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
        }

        /* 3 Kartu Ringkas Layanan (Glassmorphism Elegan) */
        .side-highlights-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .side-highlight-item {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 14px 16px;
            background-color: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.22);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            border-radius: var(--radius-main);
        }
        .side-highlight-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background-color: rgba(255, 255, 255, 0.16);
            color: #ffffff;
            flex-shrink: 0;
            margin-top: 1px;
        }
        .side-highlight-text {
            display: flex;
            flex-direction: column;
        }
        .side-highlight-title {
            font-size: 0.88rem;
            font-weight: 600;
            color: #ffffff;
            line-height: 1.3;
        }
        .side-highlight-desc {
            font-size: 0.78rem;
            color: #E2E8F0;
            line-height: 1.45;
            margin-top: 2px;
        }

        .side-footer-meta {
            font-size: 0.8rem;
            color: rgba(226, 232, 240, 0.8);
            padding-top: 20px;
        }

        /* --------------------------------------------------------------------------
           SISI KANAN: FORMULIR AUTENTIKASI
           -------------------------------------------------------------------------- */
        .login-main {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 48px 24px;
            background-color: var(--bg-alt);
        }

        .login-box {
            width: 100%;
            max-width: 440px;
        }

        /* Tautan Kembali ke Beranda */
        .back-nav-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-secondary);
            font-size: 0.88rem;
            font-weight: 600;
            margin-bottom: 20px;
            padding: 6px 10px;
            border-radius: var(--radius-input);
            transition: all 150ms var(--ease-standard);
            width: fit-content;
        }
        .back-nav-link:hover {
            color: var(--brand-blue);
            background-color: #ffffff;
        }

        /* Kartu Form Login */
        .login-card {
            background-color: var(--bg-white);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-main);
            padding: 36px 32px;
            box-shadow: var(--shadow-card);
        }

        /* Brand Mobile (hanya tampil saat layar kecil) */
        .mobile-brand-box {
            display: none;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
            padding-bottom: 18px;
            border-bottom: 1px solid var(--border-color);
        }
        .mobile-brand-box img {
            width: 32px;
            height: 32px;
        }
        .mobile-brand-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--text-main);
            display: block;
            line-height: 1.2;
        }
        .mobile-brand-subtitle {
            font-size: 0.72rem;
            color: var(--text-secondary);
            display: block;
            font-weight: 500;
        }

        .login-card-head {
            margin-bottom: 24px;
        }

        .login-card-title {
            font-size: 1.45rem;
            font-weight: 700;
            color: var(--text-main);
            letter-spacing: -0.015em;
            line-height: 1.25;
            margin-bottom: 6px;
        }

        .login-card-subtitle {
            font-size: 0.88rem;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        /* Pesan Error Alert */
        .alert-error-box {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            background-color: #FEF2F2;
            border: 1px solid #FECACA;
            border-radius: var(--radius-input);
            color: #B91C1C;
            font-size: 0.85rem;
            line-height: 1.45;
            margin-bottom: 20px;
        }
        .alert-error-box svg {
            flex-shrink: 0;
            margin-top: 1px;
        }

        /* Kontrol Form */
        .form-item {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            font-size: 0.86rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 6px;
        }

        .form-input {
            width: 100%;
            height: 44px;
            padding: 10px 14px;
            font-family: var(--font-family);
            font-size: 0.92rem;
            color: var(--text-main);
            background-color: var(--bg-white);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-input);
            outline: none;
            transition: border-color 150ms var(--ease-standard), box-shadow 150ms var(--ease-standard);
        }

        .form-input::placeholder {
            color: #94A3B8;
        }

        .form-input:focus {
            border-color: var(--brand-blue);
            box-shadow: 0 0 0 3px rgba(30, 78, 140, 0.12);
        }

        .input-password-wrap {
            position: relative;
            display: flex;
        }

        .input-password-wrap .form-input {
            padding-right: 46px;
        }

        .btn-toggle-eye {
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            width: 44px;
            background: transparent;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0 var(--radius-input) var(--radius-input) 0;
            transition: color 150ms var(--ease-standard);
        }
        .btn-toggle-eye:hover {
            color: var(--text-main);
        }
        .btn-toggle-eye:focus-visible {
            outline: 2px solid var(--brand-blue);
            outline-offset: -2px;
        }

        .input-error-msg {
            color: #B91C1C;
            font-size: 0.78rem;
            font-weight: 500;
            margin-top: 5px;
        }

        /* Tombol Masuk */
        .btn-login-submit {
            width: 100%;
            height: 44px;
            padding: 0 20px;
            background-color: var(--brand);
            color: #ffffff;
            font-family: var(--font-family);
            font-size: 0.92rem;
            font-weight: 600;
            border: 1px solid var(--brand);
            border-radius: var(--radius-input);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background-color 150ms var(--ease-standard), border-color 150ms var(--ease-standard), transform 150ms var(--ease-standard);
            margin-top: 6px;
        }

        .btn-login-submit:hover {
            background-color: var(--brand-dark);
            border-color: var(--brand-dark);
        }

        .btn-login-submit:active {
            transform: scale(0.98);
        }

        .btn-login-submit:disabled {
            opacity: 0.75;
            cursor: not-allowed;
            transform: none;
        }

        /* Informasi Bantuan Akun */
        .login-info-card {
            margin-top: 22px;
            padding: 12px 14px;
            background-color: var(--brand-blue-light);
            border: 1px solid #DBEAFE;
            border-radius: var(--radius-input);
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .login-info-card svg {
            color: var(--brand-blue);
            flex-shrink: 0;
            margin-top: 2px;
        }
        .login-info-text {
            font-size: 0.78rem;
            color: var(--text-secondary);
            line-height: 1.5;
        }
        .login-info-text strong {
            color: var(--text-main);
        }

        /* --------------------------------------------------------------------------
           RESPONSIF
           -------------------------------------------------------------------------- */
        @media (max-width: 991.98px) {
            .login-layout {
                grid-template-columns: 1fr;
            }

            .login-side {
                display: none;
            }

            .mobile-brand-box {
                display: flex;
            }

            .login-main {
                min-height: 100vh;
                padding: 32px 16px;
            }
        }

        @media (max-width: 479.98px) {
            .login-card {
                padding: 26px 20px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                transition-duration: 0.001ms !important;
                animation-duration: 0.001ms !important;
            }
        }
    </style>
</head>
<body>

<div class="login-layout">
    {{-- ==========================================================================
         SISI KIRI: BRANDING HERO (Selaras dengan Landing Page)
         ========================================================================== --}}
    <aside class="login-side">
        <div class="login-side-overlay" aria-hidden="true"></div>
        <div class="login-side-shade" aria-hidden="true"></div>

        <div class="login-side-content">
            {{-- Header Merek --}}
            <a href="{{ route('landing') }}" class="side-brand" aria-label="Kembali ke Beranda SIARKA">
                <img src="{{ asset('images/siarka-ikon.svg') }}" alt="Logo SIARKA" class="side-brand-logo" width="36" height="36">
                <div class="side-brand-meta">
                    <span class="side-brand-title">SIARKA</span>
                    <span class="side-brand-subtitle">Laboratorium Komputer & Bisnis</span>
                </div>
            </a>

            {{-- Isi Tengah: Identitas & Layanan --}}
            <div class="side-center-body">
                <div class="side-badge-tag">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                        <path d="M2 17l10 5 10-5"></path>
                        <path d="M2 12l10 5 10-5"></path>
                    </svg>
                    <span>Politeknik Negeri Tanah Laut</span>
                </div>

                <h1 class="side-hero-title">Sistem Fasilitas & Layanan Laboratorium Terintegrasi</h1>
                <p class="side-hero-desc">
                    Pusat informasi inventaris, reservasi ruang praktikum, dan tata kelola kegiatan akademik Jurusan Komputer dan Bisnis secara transparan dan akuntabel.
                </p>

                {{-- 3 Kartu Sorotan Layanan --}}
                <div class="side-highlights-list">
                    <div class="side-highlight-item">
                        <div class="side-highlight-icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect>
                                <line x1="9" y1="22" x2="9" y2="22.01"></line>
                                <line x1="15" y1="22" x2="15" y2="22.01"></line>
                                <line x1="9" y1="6" x2="9" y2="6.01"></line>
                                <line x1="15" y1="6" x2="15" y2="6.01"></line>
                                <line x1="9" y1="10" x2="9" y2="10.01"></line>
                                <line x1="15" y1="10" x2="15" y2="10.01"></line>
                            </svg>
                        </div>
                        <div class="side-highlight-text">
                            <span class="side-highlight-title">Katalog & Reservasi Ruang Lab</span>
                            <span class="side-highlight-desc">Pengecekan ketersediaan jadwal serta pengajuan pinjam ruang praktikum.</span>
                        </div>
                    </div>

                    <div class="side-highlight-item">
                        <div class="side-highlight-icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                <line x1="12" y1="22.08" x2="12" y2="12"></line>
                            </svg>
                        </div>
                        <div class="side-highlight-text">
                            <span class="side-highlight-title">Peminjaman Alat & Bahan</span>
                            <span class="side-highlight-desc">Inventarisasi peralatan komputer dan bahan penunjang kegiatan praktikum.</span>
                        </div>
                    </div>

                    <div class="side-highlight-item">
                        <div class="side-highlight-icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                <path d="m9 12 2 2 4-4"></path>
                            </svg>
                        </div>
                        <div class="side-highlight-text">
                            <span class="side-highlight-title">Monitoring & Transparansi</span>
                            <span class="side-highlight-desc">Pelacakan status verifikasi izin dan pengembalian barang secara real-time.</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer Sisi Kiri --}}
            <div class="side-footer-meta">
                &copy; {{ date('Y') }} SIARKA &middot; Laboratorium Jurusan Komputer dan Bisnis Politala
            </div>
        </div>
    </aside>

    {{-- ==========================================================================
         SISI KANAN: FORMULIR AUTENTIKASI
         ========================================================================== --}}
    <main class="login-main">
        <div class="login-box">
            {{-- Tombol Navigasi Kembali ke Beranda --}}
            <a href="{{ route('landing') }}" class="back-nav-link" aria-label="Kembali ke Beranda SIARKA">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Kembali ke Beranda</span>
            </a>

            {{-- Kartu Login --}}
            <div class="login-card">
                {{-- Tampilan Merek Mobile --}}
                <div class="mobile-brand-box">
                    <img src="{{ asset('images/siarka-ikon.svg') }}" alt="Logo SIARKA" width="32" height="32">
                    <div>
                        <span class="mobile-brand-title">SIARKA</span>
                        <span class="mobile-brand-subtitle">Laboratorium Komputer & Bisnis</span>
                    </div>
                </div>

                <div class="login-card-head">
                    <h2 class="login-card-title">Masuk ke SIARKA</h2>
                    <p class="login-card-subtitle">Gunakan email institusi dan password akun terdaftar Anda.</p>
                </div>

                {{-- Alert Error jika Autentikasi Gagal --}}
                @error('email')
                    <div class="alert-error-box" role="alert">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <span>{{ $message }}</span>
                    </div>
                @enderror

                {{-- Formulir Autentikasi --}}
                <form method="POST" action="{{ route('login.attempt') }}" id="formLogin" novalidate>
                    @csrf

                    <div class="form-item">
                        <label for="email" class="form-label">Email Akun</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="form-input"
                            placeholder="nama@politala.ac.id"
                            required
                            autocomplete="username"
                            autofocus
                        >
                    </div>

                    <div class="form-item">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-password-wrap">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-input"
                                placeholder="Masukkan password akun Anda"
                                required
                                autocomplete="current-password"
                            >
                            <button type="button" class="btn-toggle-eye" id="togglePassword" aria-label="Tampilkan password" title="Tampilkan password">
                                <svg id="eyeIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <div class="input-error-msg">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn-login-submit" id="btnSubmit">
                        <span>Masuk ke SIARKA</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </button>
                </form>

                {{-- Catatan Pengelolaan Akun --}}
                <div class="login-info-card">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                    <div class="login-info-text">
                        <strong>Informasi Akun:</strong> Akun sivitas akademika didaftarkan oleh Laboran atau Admin jurusan. Jika mengalami kendala login, silakan hubungi pengelola laboratorium.
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

{{-- Skrip Interaktivitas --}}
<script>
(function() {
    'use strict';

    // Toggle visibilitas password dengan ikon SVG
    const toggleBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const eyeIconSvg = document.getElementById('eyeIcon');

    const svgEyeOpen = '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle>';
    const svgEyeClosed = '<path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path><line x1="2" x2="22" y1="2" y2="22"></line>';

    if (toggleBtn && passwordInput && eyeIconSvg) {
        toggleBtn.addEventListener('click', function() {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            this.setAttribute('aria-label', isPassword ? 'Sembunyikan password' : 'Tampilkan password');
            this.setAttribute('title', isPassword ? 'Sembunyikan password' : 'Tampilkan password');
            eyeIconSvg.innerHTML = isPassword ? svgEyeClosed : svgEyeOpen;
        });
    }

    // Pencegahan submit ganda
    const formLogin = document.getElementById('formLogin');
    const submitBtn = document.getElementById('btnSubmit');

    if (formLogin && submitBtn) {
        formLogin.addEventListener('submit', function(e) {
            // Validasi input browser sederhana sebelum submit
            if (!formLogin.checkValidity()) {
                return;
            }

            if (this.dataset.submitting === '1') {
                e.preventDefault();
                return;
            }
            this.dataset.submitting = '1';

            setTimeout(function() {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span>Memproses...</span>';
            }, 0);
        });

        window.addEventListener('pageshow', function() {
            delete formLogin.dataset.submitting;
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<span>Masuk ke SIARKA</span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>';
        });
    }
})();
</script>
</body>
</html>
