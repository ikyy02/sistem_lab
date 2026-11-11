<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIARKA — Laboratorium Jurusan Komputer dan Bisnis</title>
    <meta name="application-name" content="SIARKA">
    <meta name="description" content="SIARKA — Sistem Informasi Administrasi, Reservasi, Katalog, dan Aktivitas Laboratorium Jurusan Komputer dan Bisnis Politeknik Negeri Tanah Laut.">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/siarka-ikon.svg') }}">

    {{-- Progressive Enhancement: deteksi dukungan JS sejak awal di <head> --}}
    <script>document.documentElement.classList.add('js');</script>

    {{-- Google Fonts: Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* ==========================================================================
           A. SISTEM DESAIN GLOBAL
           - Kontainer: max-width 1120px, padding horizontal 24px (semua section)
           - Judul & subjudul: rata kiri; subjudul max 60ch
           - Padding vertikal section: 80px
           - Latar selang-seling: putih (#ffffff) dan abu terang (#F5F7FA)
           - Palet: #1E4E8C (tombol, tautan, aktif), #0F172A (teks), #475569 (sekunder), #E2E8F0 (border)
           - Tipografi: isi min 14px, label min 12px
           - Radius: 10px, Bayangan max: 0 1px 2px rgba(0,0,0,.06)
           - Spasi skala: 8 / 16 / 24 / 32 / 48 / 80
           - Ikon: Lucide/Tabler outline seragam, tanpa emoji
           - Tanpa garis aksen dekoratif
           ========================================================================== */
        :root {
            --font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            --brand: #1E4E8C;
            /* Alias token: dipakai di CSS halaman ini tetapi sebelumnya tidak terdefinisi */
            --primary: var(--brand);
            --primary-hover: #17406F;
            --border: var(--border-color);
            --info-soft: var(--brand-blue-light);
            --brand-dark: var(--brand);
            --brand-blue: var(--brand);
            --brand-blue-hover: var(--brand);
            --brand-blue-light: #EFF6FF;
            --brand-navy-cta: var(--brand);
            --text-main: #0F172A;
            --text-secondary: #475569;
            --border-color: #E2E8F0;
            --bg-white: #ffffff;
            --bg-alt: #F5F7FA;
            --radius-main: 10px;
            --radius-photo: 8px;
            --shadow-subtle: 0 1px 2px rgba(0, 0, 0, 0.06);
            --container-max: 1120px;
            --navbar-height: 72px;
            --ease-standard: cubic-bezier(.22, .61, .36, 1);
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
            font-size: 16px;
        }

        body {
            font-family: var(--font-family);
            background-color: var(--bg-white);
            color: var(--text-main);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        /* Kontainer Tunggal 1120px */
        .container {
            width: 100%;
            max-width: var(--container-max);
            margin-left: auto;
            margin-right: auto;
            padding-left: 24px;
            padding-right: 24px;
        }

        /* Padding Vertikal Section 80px & Scroll Margin Top */
        .section-pad {
            padding-top: 80px;
            padding-bottom: 80px;
            scroll-margin-top: calc(var(--navbar-height) + 16px);
        }

        .bg-white-section {
            background-color: var(--bg-white);
        }

        .bg-alt-section {
            background-color: var(--bg-alt);
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }

        /* Judul & Subjudul Rata Kiri, subjudul max 60ch */
        .section-head-left {
            text-align: left;
            margin-bottom: 32px;
        }

        .section-title {
            font-size: clamp(1.6rem, 2.8vw, 2rem);
            font-weight: 700;
            color: var(--text-main);
            letter-spacing: -0.015em;
            line-height: 1.25;
            margin-bottom: 8px;
            text-align: left;
        }

        .section-desc {
            font-size: 0.95rem;
            color: var(--text-secondary);
            max-width: 60ch;
            margin: 0;
            line-height: 1.6;
            text-align: left;
        }

        /* Tautan & Tombol Global */
        a {
            color: var(--primary);
            text-decoration: none;
            transition: color 150ms var(--ease-standard);
        }
        a:hover {
            color: var(--primary-hover);
        }

        button, .btn {
            font-family: var(--font-family);
            cursor: pointer;
            transition: background-color 150ms var(--ease-standard), color 150ms var(--ease-standard), border-color 150ms var(--ease-standard), transform 150ms var(--ease-standard);
        }
        button:active, .btn:active {
            transform: scale(0.98);
        }

        /* Hover Kartu: naik 2px, border #1E4E8C, bayangan tipis (180ms) */
        .card-interactive {
            transition: transform 180ms var(--ease-standard), border-color 180ms var(--ease-standard), box-shadow 180ms var(--ease-standard);
            border: 1px solid var(--border);
            border-radius: var(--radius-main);
            background-color: var(--bg-white);
            box-shadow: var(--shadow-subtle);
        }
        .card-interactive:hover {
            transform: translateY(-2px);
            border-color: var(--primary);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
        }

        /* ==========================================================================
           B.1 NAVBAR
           - Hamburger < 900px (panel penuh)
           - Scroll margin top
           - Scrollspy IntersectionObserver
           - Bayangan tipis setelah scroll 8px
           - Menu aktif: transisi latar 200ms
           ========================================================================== */
        .site-header {
            position: sticky;
            top: 0;
            z-index: 1000;
            background-color: #ffffff;
            border-bottom: 1px solid var(--border);
            height: var(--navbar-height);
            transition: box-shadow 200ms var(--ease-standard);
        }
        .site-header.is-scrolled {
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
        }

        .header-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 100%;
            gap: 16px;
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--text-main);
            flex-shrink: 0;
        }
        .header-logo {
            width: 34px;
            height: 34px;
            flex-shrink: 0;
        }
        .header-brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }
        .header-brand-title {
            font-size: 1.1rem;
            font-weight: 700;
            letter-spacing: -0.01em;
            color: var(--text-main);
        }
        .header-brand-subtitle {
            font-size: 0.72rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .nav-desktop {
            display: flex;
            align-items: center;
            gap: 4px;
            list-style: none;
        }
        .nav-desktop-item a {
            font-size: 0.88rem;
            font-weight: 500;
            color: var(--text-secondary);
            padding: 8px 12px;
            border-radius: var(--radius-main);
            transition: background-color 200ms var(--ease-standard), color 200ms var(--ease-standard);
            white-space: nowrap;
        }
        .nav-desktop-item a:hover {
            color: var(--primary);
            background-color: var(--bg-alt);
        }
        .nav-desktop-item a.is-active {
            color: var(--primary);
            background-color: var(--info-soft);
            font-weight: 600;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-header-login {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 18px;
            background-color: var(--brand);
            color: #ffffff !important;
            font-size: 0.88rem;
            font-weight: 600;
            border-radius: var(--radius-main);
            border: 1px solid var(--brand);
            white-space: nowrap;
        }
        .btn-header-login:hover {
            background-color: var(--brand-dark);
            border-color: var(--brand-dark);
        }

        .nav-mobile-toggle {
            display: none;
            background: transparent;
            border: 1px solid var(--border);
            border-radius: var(--radius-main);
            padding: 8px 10px;
            color: var(--text-main);
            font-size: 1.1rem;
            cursor: pointer;
            align-items: center;
            justify-content: center;
        }

        .nav-mobile-panel {
            display: none;
            position: absolute;
            top: var(--navbar-height);
            left: 0;
            right: 0;
            background-color: #ffffff;
            border-bottom: 1px solid var(--border);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 16px 24px 24px;
            flex-direction: column;
            gap: 8px;
        }
        .nav-mobile-panel.is-open {
            display: flex;
        }
        .nav-mobile-item {
            display: block;
            padding: 10px 14px;
            border-radius: var(--radius-main);
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--text-secondary);
        }
        .nav-mobile-item:hover, .nav-mobile-item.is-active {
            color: var(--primary);
            background-color: var(--info-soft);
            font-weight: 600;
        }

        @media (max-width: 899.98px) {
            .nav-desktop {
                display: none;
            }
            .nav-mobile-toggle {
                display: inline-flex;
            }
        }

        /* ==========================================================================
           HERO DENGAN FOTO RUANG LAB 1920px & GRADASI OVERLAY 90deg
           Foto: background-size cover, background-position center 40% (min 1600px)
           Overlay: linear-gradient(90deg, rgba(15,40,80,.78) 0%, rgba(15,40,80,.55) 45%, rgba(15,40,80,.15) 100%)
           ========================================================================== */
        .hero-section {
            position: relative;
            min-height: 600px;
            overflow: hidden;
            background-color: var(--brand);
            background-image: url('{{ asset('images/hero-lab-wide.jpg') }}');
            background-size: cover;
            background-position: center 40%;
            background-repeat: no-repeat;
            padding-top: 80px;
            padding-bottom: 80px;
            display: flex;
            align-items: center;
        }
        .hero-video-bg {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100vw;
            height: 100vh;
            transform: translate(-50%, -50%);
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }
        .hero-video-bg iframe {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100vw;
            height: 56.25vw; /* 16:9 aspect ratio */
            min-height: 100vh;
            min-width: 177.77vh; /* 16:9 aspect ratio */
            transform: translate(-50%, -50%);
            pointer-events: none;
            border: 0;
            opacity: 1;
        }
        /* Overlay gradasi kiri ke kanan: area teks terlindungi min 4.5:1, foto lab kanan terlihat jelas */
        .hero-video-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, rgba(15, 40, 80, 0.78) 0%, rgba(15, 40, 80, 0.70) 30%, rgba(15, 40, 80, 0.55) 45%, rgba(15, 40, 80, 0.15) 100%);
            z-index: 1;
        }
        .hero-container {
            position: relative;
            z-index: 2;
            width: 100%;
        }
        .hero-content {
            max-width: 720px;
        }
        .hero-badge-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 14px;
            background-color: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 20px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.4);
        }
        .hero-title {
            font-size: clamp(2rem, 3.6vw, 2.75rem);
            font-weight: 700;
            color: #ffffff;
            line-height: 1.22;
            letter-spacing: -0.02em;
            margin-bottom: 18px;
            text-align: left;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.6);
        }
        .hero-desc {
            font-size: 1.02rem;
            color: #F8FAFC;
            line-height: 1.65;
            margin-bottom: 28px;
            max-width: 60ch;
            text-align: left;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
        }
        .hero-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .btn-hero-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background-color: #ffffff;
            color: var(--primary) !important;
            font-size: 0.92rem;
            font-weight: 600;
            border-radius: var(--radius-main);
            border: 1px solid #ffffff;
            transition: background-color 150ms var(--ease-standard), transform 150ms var(--ease-standard);
        }
        .btn-hero-primary:hover {
            background-color: #F8FAFC;
        }
        .btn-hero-primary:active {
            transform: scale(0.98);
        }
        /* Tombol sekunder: latar rgba(255,255,255,.12), border rgba(255,255,255,.5), teks putih */
        .btn-hero-secondary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background-color: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.5);
            color: #ffffff !important;
            font-size: 0.92rem;
            font-weight: 600;
            border-radius: var(--radius-main);
            transition: background-color 150ms var(--ease-standard), border-color 150ms var(--ease-standard), transform 150ms var(--ease-standard);
        }
        .btn-hero-secondary:hover {
            background-color: rgba(255, 255, 255, 0.22);
            border-color: #ffffff;
            color: #ffffff !important;
        }
        .btn-hero-secondary:active {
            transform: scale(0.98);
        }

        /* Kartu statistik: latar rgba(255,255,255,.12), border rgba(255,255,255,.25), backdrop-filter blur(8px) */
        .stats-grid-hero {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-top: 48px;
        }
        .stat-card-hero {
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-radius: var(--radius-main);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .stat-card-hero .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: #ffffff;
            line-height: 1.1;
            margin-bottom: 4px;
        }
        .stat-card-hero .stat-label {
            font-size: 0.8rem;
            color: #E2E8F0;
            font-weight: 500;
        }

        @media (max-width: 991.98px) {
            .stats-grid-hero {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 575.98px) {
            .hero-section {
                min-height: auto;
                padding-top: 60px;
                padding-bottom: 60px;
            }
            .stats-grid-hero {
                grid-template-columns: 1fr;
                margin-top: 36px;
            }
        }

        /* ==========================================================================
           B.2 KATALOG LAB
           - Chip kategori nama bidang saja, satu baris scrollable
           - Kolom cari dan tombol "Kosong sekarang" dalam satu baris
           - Grid 3 kolom (tablet 2, mobile 1), gap 24px, sama tinggi
           - Tombol Detail & Ajukan pinjam sama tinggi, rata bawah
           - Isi kartu: badge status, nama lab, bidang, "kapasitas - lantai" 1 baris
           - Foto: hapus overlay gradasi biru; 16/9, object-fit cover, object-position top
           - Placeholder abu-abu (#E2E8F0) + outline building jika tidak ada foto
           - 6 kartu awal + tombol "Tampilkan lebih banyak" (+6 per klik)
           ========================================================================== */
        .catalog-controls {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-bottom: 24px;
        }

        /* Chip Kategori 1 Baris Scrollable */
        .catalog-chips-scroll {
            display: flex;
            align-items: center;
            gap: 8px;
            overflow-x: auto;
            white-space: nowrap;
            padding-bottom: 4px;
            -webkit-overflow-scrolling: touch;
        }
        .catalog-chips-scroll::-webkit-scrollbar {
            height: 4px;
        }
        .catalog-chips-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .filter-chip {
            display: inline-flex;
            align-items: center;
            padding: 7px 16px;
            font-size: 0.84rem;
            font-weight: 500;
            color: var(--text-secondary);
            background-color: var(--bg-white);
            border: 1px solid var(--border);
            border-radius: 999px;
            cursor: pointer;
            flex-shrink: 0;
            transition: all 150ms var(--ease-standard);
        }
        .filter-chip:hover {
            color: var(--primary);
            border-color: #cbd5e1;
        }
        .filter-chip.is-active {
            background-color: var(--primary);
            color: #ffffff;
            border-color: var(--primary);
            font-weight: 600;
        }

        /* Kolom Cari + Tombol Kosong Sekarang 1 Baris */
        .catalog-search-row {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .search-box-wrap {
            position: relative;
            flex: 1;
        }
        .search-box-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            pointer-events: none;
        }
        .search-box-input {
            width: 100%;
            padding: 10px 14px 10px 40px;
            border: 1px solid var(--border);
            border-radius: var(--radius-main);
            font-size: 0.88rem;
            font-family: var(--font-family);
            background-color: var(--bg-white);
            color: var(--text-main);
            outline: none;
            transition: border-color 150ms var(--ease-standard);
        }
        .search-box-input:focus {
            border-color: var(--primary);
        }
        .btn-available-now {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            background-color: var(--bg-white);
            border: 1px solid var(--border);
            border-radius: var(--radius-main);
            font-size: 0.84rem;
            font-weight: 600;
            color: var(--text-secondary);
            white-space: nowrap;
            flex-shrink: 0;
        }
        .btn-available-now:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
        .btn-available-now.is-active {
            background-color: var(--info-soft);
            border-color: var(--primary);
            color: var(--primary);
        }

        /* Grid Kartu Lab */
        .lab-cards-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }
        @media (max-width: 991.98px) {
            .lab-cards-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 640px) {
            .lab-cards-grid {
                grid-template-columns: 1fr;
            }
            .catalog-search-row {
                flex-direction: column;
                align-items: stretch;
            }
            .btn-available-now {
                justify-content: center;
            }
        }

        /* Kartu Lab Individual */
        .lab-card {
            display: flex;
            flex-direction: column;
            background-color: var(--bg-white);
            border: 1px solid var(--border);
            border-radius: var(--radius-main);
            box-shadow: var(--shadow-subtle);
            overflow: hidden;
            transition: transform 180ms var(--ease-standard), border-color 180ms var(--ease-standard), box-shadow 180ms var(--ease-standard), opacity 250ms var(--ease-standard);
        }
        .lab-card:hover {
            transform: translateY(-2px);
            border-color: var(--primary);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
        }
        .lab-card.is-hidden-filter {
            display: none !important;
        }
        .lab-card.is-hidden-pagination {
            display: none;
        }

        /* Foto Lab: aspect-ratio 16/9, object-fit cover, object-position top, tanpa overlay */
        .lab-card-photo-box {
            position: relative;
            width: 100%;
            aspect-ratio: 16 / 9;
            background-color: #E2E8F0;
            overflow: hidden;
            border-radius: var(--radius-photo);
            margin: 12px 12px 0;
            width: calc(100% - 24px);
        }
        .lab-card-photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: top;
            display: block;
            border: none;
        }
        .lab-card-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: #E2E8F0;
            color: #64748B;
        }

        .lab-card-body {
            display: flex;
            flex-direction: column;
            flex: 1;
            padding: 16px;
        }
        .lab-card-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 600;
            width: fit-content;
            margin-bottom: 8px;
        }
        .badge-status-tersedia {
            background-color: #ecfdf3;
            color: #146c43;
        }
        .badge-status-terpakai {
            background-color: #fef2f2;
            color: #b42318;
        }

        .lab-card-title {
            font-size: 1.05rem;
            font-weight: 600;
            color: var(--text-main);
            line-height: 1.35;
            margin-bottom: 4px;
        }
        .lab-card-field {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-bottom: 8px;
            font-weight: 500;
        }
        .lab-card-meta-line {
            font-size: 0.78rem;
            color: var(--text-secondary);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .lab-card-actions {
            margin-top: auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            align-items: center;
        }
        .btn-card-detail {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 38px;
            padding: 0 12px;
            background-color: var(--bg-white);
            border: 1px solid var(--border);
            border-radius: var(--radius-photo);
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-main);
        }
        .btn-card-detail:hover {
            background-color: var(--bg-alt);
            border-color: #cbd5e1;
        }
        .btn-card-book {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 38px;
            padding: 0 12px;
            background-color: var(--brand);
            color: #ffffff !important;
            border: 1px solid var(--brand);
            border-radius: var(--radius-photo);
            font-size: 0.82rem;
            font-weight: 600;
        }
        .btn-card-book:hover {
            background-color: var(--brand-dark);
            border-color: var(--brand-dark);
        }

        .catalog-load-more-wrap {
            margin-top: 32px;
            text-align: center;
        }
        .btn-load-more {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            background-color: var(--bg-white);
            border: 1px solid var(--border);
            border-radius: var(--radius-main);
            color: var(--primary);
            font-size: 0.88rem;
            font-weight: 600;
        }
        .btn-load-more:hover {
            border-color: var(--primary);
            background-color: var(--bg-alt);
        }

        /* ==========================================================================
           B.3 LAYANAN
           - 3 kartu bidang (tanpa garis aksen)
           - 4 kartu layanan (tinggi seragam, tanpa garis aksen)
           - Ikon outline seragam
           ========================================================================== */
        .layanan-fields-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            margin-bottom: 32px;
        }
        .field-card {
            display: flex;
            flex-direction: column;
            padding: 24px;
            background-color: var(--bg-white);
            border: 1px solid var(--border);
            border-radius: var(--radius-main);
            box-shadow: var(--shadow-subtle);
        }
        .field-card-icon-box {
            width: 44px;
            height: 44px;
            border-radius: var(--radius-photo);
            background-color: var(--info-soft);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            margin-bottom: 16px;
        }
        .field-card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 8px;
        }
        .field-card-desc {
            font-size: 0.88rem;
            color: var(--text-secondary);
            line-height: 1.6;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
        }
        .service-card {
            display: flex;
            flex-direction: column;
            padding: 20px;
            background-color: var(--bg-white);
            border: 1px solid var(--border);
            border-radius: var(--radius-main);
            box-shadow: var(--shadow-subtle);
        }
        .service-card-icon-box {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-photo);
            background-color: var(--bg-alt);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            margin-bottom: 14px;
        }
        .service-card-title {
            font-size: 0.98rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 6px;
        }
        .service-card-desc {
            font-size: 0.84rem;
            color: var(--text-secondary);
            line-height: 1.55;
        }

        @media (max-width: 991.98px) {
            .layanan-fields-grid {
                grid-template-columns: 1fr;
            }
            .services-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 575.98px) {
            .services-grid {
                grid-template-columns: 1fr;
            }
        }

        /* ==========================================================================
           B.4 PENGELOLA
           - Foto bulat 64px dari public/images/pengelola/ (fallback inisial)
           - Grid laboran sama tinggi, tanpa ruang kosong janggal
           - Tombol bagan membuka lightbox modal
           ========================================================================== */
        .pengelola-lead-card {
            display: flex;
            align-items: center;
            gap: 24px;
            padding: 24px;
            background-color: var(--bg-white);
            border: 1px solid var(--border);
            border-radius: var(--radius-main);
            box-shadow: var(--shadow-subtle);
            margin-bottom: 24px;
        }
        .laboran-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            margin-bottom: 24px;
        }
        .laboran-card {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 20px;
            background-color: var(--bg-white);
            border: 1px solid var(--border);
            border-radius: var(--radius-main);
            box-shadow: var(--shadow-subtle);
            height: 100%;
        }
        .avatar-circle-64 {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
            border: 2px solid var(--border);
        }
        .avatar-fallback-64 {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background-color: var(--info-soft);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            font-weight: 700;
            flex-shrink: 0;
            border: 2px solid var(--border);
        }
        .member-info {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        .member-name {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-main);
            line-height: 1.3;
            margin-bottom: 2px;
        }
        .member-role {
            font-size: 0.78rem;
            color: var(--text-secondary);
            font-weight: 500;
            margin-bottom: 4px;
        }
        .member-focus {
            font-size: 0.74rem;
            color: var(--text-secondary);
        }

        .org-action-wrap {
            text-align: left;
        }
        .btn-org-lightbox {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background-color: var(--bg-white);
            border: 1px solid var(--border);
            border-radius: var(--radius-main);
            color: var(--primary);
            font-size: 0.88rem;
            font-weight: 600;
        }
        .btn-org-lightbox:hover {
            border-color: var(--primary);
            background-color: var(--bg-alt);
        }

        @media (max-width: 991.98px) {
            .laboran-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 640px) {
            .pengelola-lead-card {
                flex-direction: column;
                align-items: flex-start;
            }
            .laboran-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Lightbox Bagan Modal */
        .lightbox-dialog {
            border: 1px solid var(--border);
            border-radius: var(--radius-main);
            padding: 0;
            max-width: 900px;
            width: calc(100% - 32px);
            margin: auto;
            background: #ffffff;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        .lightbox-dialog::backdrop {
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(2px);
        }
        .lightbox-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            background: var(--bg-alt);
        }
        .lightbox-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-main);
        }
        .lightbox-close-btn {
            background: transparent;
            border: none;
            font-size: 1.25rem;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 4px;
            border-radius: 6px;
            line-height: 1;
        }
        .lightbox-close-btn:hover {
            color: var(--text-main);
            background-color: #E2E8F0;
        }
        .lightbox-body {
            padding: 20px;
            text-align: center;
            max-height: 80vh;
            overflow-y: auto;
        }
        .lightbox-img {
            max-width: 100%;
            height: auto;
            border-radius: var(--radius-photo);
            display: block;
            margin: 0 auto;
        }

        /* ==========================================================================
           B.5 SOP
           - 4 kartu sama tinggi, tautan di dasar kartu rata bawah
           ========================================================================== */
        .sop-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
        }
        .sop-card {
            display: flex;
            flex-direction: column;
            padding: 24px;
            background-color: var(--bg-white);
            border: 1px solid var(--border);
            border-radius: var(--radius-main);
            box-shadow: var(--shadow-subtle);
            height: 100%;
        }
        .sop-icon-box {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-photo);
            background-color: var(--info-soft);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            margin-bottom: 16px;
        }
        .sop-number {
            font-size: 0.74rem;
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }
        .sop-card-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 8px;
            line-height: 1.35;
        }
        .sop-card-desc {
            font-size: 0.84rem;
            color: var(--text-secondary);
            line-height: 1.55;
            margin-bottom: 16px;
        }
        .sop-card-link {
            margin-top: auto;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.84rem;
            font-weight: 600;
            color: var(--primary);
            padding-top: 8px;
            border-top: 1px solid var(--border);
        }
        .sop-card-link:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }

        @media (max-width: 991.98px) {
            .sop-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 575.98px) {
            .sop-grid {
                grid-template-columns: 1fr;
            }
        }

        /* ==========================================================================
           B.6 ALUR PEMINJAMAN
           - Stepper 4 langkah, teks rata tengah, garis penghubung halus
           - Garis memanjang dari kiri, lingkaran angka muncul berurutan (delay 200ms)
           ========================================================================== */
        .stepper-container {
            position: relative;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            margin-top: 40px;
        }
        .stepper-track-line {
            position: absolute;
            top: 24px;
            left: calc(12.5% + 12px);
            right: calc(12.5% + 12px);
            height: 2px;
            background-color: var(--border);
            z-index: 1;
        }
        .stepper-track-fill {
            height: 100%;
            width: 0%;
            background-color: var(--primary);
            transition: width 800ms var(--ease-standard);
        }
        .stepper-step {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .stepper-circle {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background-color: var(--bg-white);
            border: 2px solid var(--border);
            color: var(--text-secondary);
            font-size: 1.1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            transition: transform 400ms var(--ease-standard), border-color 400ms var(--ease-standard), background-color 400ms var(--ease-standard), color 400ms var(--ease-standard);
            transform: scale(0.9);
            opacity: 0.7;
        }
        .stepper-step.is-active .stepper-circle {
            background-color: var(--primary);
            border-color: var(--primary);
            color: #ffffff;
            transform: scale(1);
            opacity: 1;
        }
        .stepper-step-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 6px;
            text-align: center;
        }
        .stepper-step-desc {
            font-size: 0.82rem;
            color: var(--text-secondary);
            line-height: 1.5;
            text-align: center;
            max-width: 24ch;
        }

        @media (max-width: 767.98px) {
            .stepper-container {
                grid-template-columns: 1fr;
                gap: 32px;
            }
            .stepper-track-line {
                display: none;
            }
            .stepper-step {
                flex-direction: row;
                align-items: flex-start;
                text-align: left;
                gap: 16px;
            }
            .stepper-circle {
                margin-bottom: 0;
                flex-shrink: 0;
            }
            .stepper-step-title, .stepper-step-desc {
                text-align: left;
                max-width: none;
            }
        }

        /* ==========================================================================
           B.7 JADWAL
           - Dua kartu mengisi lebar penuh kontainer dengan tinggi sama
           ========================================================================== */
        .jadwal-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }
        .jadwal-card {
            display: flex;
            flex-direction: column;
            padding: 28px;
            background-color: var(--bg-white);
            border: 1px solid var(--border);
            border-radius: var(--radius-main);
            box-shadow: var(--shadow-subtle);
            height: 100%;
        }
        .jadwal-card-icon {
            width: 44px;
            height: 44px;
            border-radius: var(--radius-photo);
            background-color: var(--info-soft);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            margin-bottom: 16px;
        }
        .jadwal-card-title {
            font-size: 1.15rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 8px;
        }
        .jadwal-card-desc {
            font-size: 0.88rem;
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .jadwal-card-list {
            list-style: none;
            margin-bottom: 24px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .jadwal-list-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 0.86rem;
            color: var(--text-main);
        }
        .jadwal-list-item svg {
            color: var(--primary);
            flex-shrink: 0;
            margin-top: 2px;
        }
        .jadwal-card-action {
            margin-top: auto;
        }

        @media (max-width: 899.98px) {
            .jadwal-grid {
                grid-template-columns: 1fr;
            }
        }

        /* ==========================================================================
           B.8 FAQ
           - Lebar kontainer penuh, accordion rapi, hanya 1 item terbuka
           - CSS grid-template-rows untuk transisi mulus, ikon panah berputar 180deg
           ========================================================================== */
        .faq-accordion-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            width: 100%;
        }
        .faq-item {
            background-color: var(--bg-white);
            border: 1px solid var(--border);
            border-radius: var(--radius-main);
            overflow: hidden;
            box-shadow: var(--shadow-subtle);
            transition: border-color 200ms var(--ease-standard);
        }
        .faq-item.is-open {
            border-color: #cbd5e1;
        }
        .faq-question-btn {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 24px;
            background: transparent;
            border: none;
            text-align: left;
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-main);
            cursor: pointer;
            gap: 16px;
        }
        .faq-chevron {
            color: var(--text-secondary);
            transition: transform 250ms var(--ease-standard);
            flex-shrink: 0;
        }
        .faq-item.is-open .faq-chevron {
            transform: rotate(180deg);
            color: var(--primary);
        }
        .faq-answer-collapse {
            display: grid;
            grid-template-rows: 0fr;
            transition: grid-template-rows 250ms var(--ease-standard);
        }
        .faq-item.is-open .faq-answer-collapse {
            grid-template-rows: 1fr;
        }
        .faq-answer-inner {
            overflow: hidden;
        }
        .faq-answer-text {
            padding: 0 24px 20px;
            font-size: 0.88rem;
            color: var(--text-secondary);
            line-height: 1.65;
        }

        /* ==========================================================================
           B.9 CTA & FOOTER
           - CTA satu blok biru tua (#13335c / #1E4E8C)
           - Footer 3 kolom (merek + deskripsi | Navigasi | Kontak), sejajar kontainer
           ========================================================================== */
        .cta-section {
            background-color: var(--brand);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            color: #ffffff;
            padding: 64px 0;
        }
        .cta-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 32px;
        }
        .cta-text-wrap {
            max-width: 60ch;
        }
        .cta-title {
            font-size: clamp(1.6rem, 2.6vw, 2rem);
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 8px;
            line-height: 1.25;
            text-align: left;
        }
        .cta-desc {
            font-size: 0.95rem;
            color: #E2E8F0;
            line-height: 1.6;
            text-align: left;
        }
        .cta-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
            flex-wrap: wrap;
        }
        .btn-cta-white {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background-color: #ffffff;
            color: var(--brand) !important;
            font-size: 0.92rem;
            font-weight: 600;
            border-radius: var(--radius-main);
            border: 1px solid #ffffff;
        }
        .btn-cta-white:hover {
            background-color: #F8FAFC;
        }
        .btn-cta-outline {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 22px;
            background-color: transparent;
            color: #ffffff !important;
            font-size: 0.92rem;
            font-weight: 600;
            border-radius: var(--radius-main);
            border: 1px solid rgba(255, 255, 255, 0.6);
        }
        .btn-cta-outline:hover {
            border-color: #ffffff;
            background-color: rgba(255, 255, 255, 0.1);
        }

        @media (max-width: 899.98px) {
            .cta-box {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        /* ==========================================================================
           FOOTER (Latar --brand, tanpa border-top)
           - Garis aksen putih: rgba(255,255,255,.9)
           - Ikon: #9CC0F0 (satu ikon per baris di kolom alamat)
           - Teks isi: #E2E8F0
           - Teks hak cipta: #C7D7EC
           - Garis pemisah: rgba(255,255,255,.2)
           ========================================================================== */
        .site-footer {
            background-color: var(--brand);
            color: #ffffff;
            border-top: none;
            padding: 64px 0 32px;
        }
        .footer-grid {
            display: grid;
            grid-template-columns: 1.3fr 1.1fr 1fr 0.9fr;
            gap: 36px;
            margin-bottom: 48px;
        }
        .footer-col-brand {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .footer-brand-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.15rem;
            font-weight: 700;
            color: #ffffff;
            margin: 0;
        }
        .footer-desc {
            font-size: 0.86rem;
            color: #E2E8F0;
            line-height: 1.6;
            max-width: 44ch;
            margin: 0;
        }
        .footer-col-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 6px;
            letter-spacing: -0.01em;
        }
        .footer-nav-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .footer-nav-list a {
            font-size: 0.86rem;
            color: #E2E8F0;
            transition: color 150ms var(--ease-standard);
        }
        .footer-nav-list a:hover {
            color: #ffffff;
            text-decoration: underline;
        }
        .footer-contact-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .footer-contact-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 0.85rem;
            color: #E2E8F0;
            line-height: 1.5;
        }
        .footer-contact-item a {
            color: #E2E8F0;
            transition: color 150ms var(--ease-standard);
        }
        .footer-contact-item a:hover {
            color: #ffffff;
            text-decoration: underline;
        }
        .footer-contact-item svg {
            color: #9CC0F0;
            flex-shrink: 0;
            margin-top: 2px;
        }
        .footer-social-list {
            display: flex;
            align-items: center;
            gap: 12px;
            list-style: none;
            margin-top: 4px;
        }
        .footer-social-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background-color: #ffffff;
            color: var(--brand);
            transition: transform 150ms var(--ease-standard), background-color 150ms var(--ease-standard), color 150ms var(--ease-standard);
            text-decoration: none;
        }
        .footer-social-link:hover {
            transform: translateY(-2px);
            background-color: #EFF6FF;
            color: var(--brand-dark);
        }

        .footer-bottom {
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.82rem;
            color: #C7D7EC;
            flex-wrap: wrap;
            gap: 12px;
        }
        .footer-bottom div {
            color: #C7D7EC;
        }

        @media (max-width: 991.98px) {
            .footer-grid {
                grid-template-columns: 1fr 1fr;
                gap: 32px;
            }
        }
        @media (max-width: 575.98px) {
            .footer-grid {
                grid-template-columns: 1fr;
                gap: 28px;
            }
        }

        /* ==========================================================================
           C. SISTEM ANIMASI
           - Easing cubic-bezier(.22,.61,.36,1)
           - Durasi 150-500ms (count-up 1200ms, stepper 800ms)
           - Hanya transform & opacity (accordion: grid-template-rows)
           - Tanpa parallax, bounce, moving gradient, loop
           - Unobserve setelah muncul (trigger sekali)
           - Progressive: .reveal hanya disembunyikan bila html memiliki kelas 'js'
           - Prefers-reduced-motion: matikan semua animasi
           ========================================================================== */
        html.js .reveal {
            opacity: 0;
            transform: translateY(16px);
            transition: opacity 350ms var(--ease-standard), transform 350ms var(--ease-standard);
        }
        html.js .reveal.is-revealed {
            opacity: 1;
            transform: translateY(0);
        }

        /* Stagger Delay untuk Grid Kartu (maksimal 6 tingkat, delay 60ms) */
        html.js .tier-0 { transition-delay: 0ms; }
        html.js .tier-1 { transition-delay: 60ms; }
        html.js .tier-2 { transition-delay: 120ms; }
        html.js .tier-3 { transition-delay: 180ms; }
        html.js .tier-4 { transition-delay: 240ms; }
        html.js .tier-5 { transition-delay: 300ms; }

        /* Wajib Hormati prefers-reduced-motion */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.001ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.001ms !important;
                scroll-behavior: auto !important;
            }
            html.js .reveal {
                opacity: 1 !important;
                transform: none !important;
                transition: none !important;
            }
            .faq-answer-collapse {
                transition: none !important;
            }
            .stepper-track-fill {
                transition: none !important;
            }
            .hero-video-bg iframe {
                display: none !important;
            }
            .hero-video-bg {
                background-color: #0F172A !important;
            }
        }
    </style>
</head>
<body>

@php
    // Inisialisasi data aman bila tidak dikirim dari controller
    $ruangans = $ruangans ?? \App\Models\Ruangan::all();
    $totalRuangan = $ruangans->count();

    // Mapping bidang lab
    $bidangLabList = [
        'Komputer & Informatika',
        'Bisnis & Akuntansi',
        'Hardware & Penunjang'
    ];

    // Daftar pengelola laboratorium
    $pengelolaList = [
        [
            'nama' => 'Agustian Noor, M.Kom.',
            'peran' => 'Kepala Laboratorium Komputer & Bisnis',
            'fokus' => 'Koordinasi Layanan & Kebijakan Fasilitas',
            'slug' => 'agustian-noor',
            'initial' => 'AN'
        ],
        [
            'nama' => 'Muhammad Ghalih, S.Kom.',
            'peran' => 'Pranata Laboratorium Pendidikan',
            'fokus' => 'Lab Software & Jaringan Komputer',
            'slug' => 'muhammad-ghalih',
            'initial' => 'MG'
        ],
        [
            'nama' => 'Siti Rahmah, S.Tr.Kom.',
            'peran' => 'Pranata Laboratorium Pendidikan',
            'fokus' => 'Lab Multimedia & Basis Data',
            'slug' => 'siti-rahmah',
            'initial' => 'SR'
        ],
        [
            'nama' => 'Ahmad Fauzi, S.E.',
            'peran' => 'Laboran Komputasi Bisnis',
            'fokus' => 'Lab Akuntansi & Simulasi Bisnis',
            'slug' => 'ahmad-fauzi',
            'initial' => 'AF'
        ],
        [
            'nama' => 'Rina Marlina, A.Md.',
            'peran' => 'Teknisi Perangkat Keras',
            'fokus' => 'Pemeliharaan Alat & Jaringan Fisik',
            'slug' => 'rina-marlina',
            'initial' => 'RM'
        ],
        [
            'nama' => 'Budi Santoso, S.Kom.',
            'peran' => 'Administrasi & Inventarisasi',
            'fokus' => 'Peminjaman Ruang & Layanan Mahasiswa',
            'slug' => 'budi-santoso',
            'initial' => 'BS'
        ],
    ];
@endphp

{{-- ==========================================================================
     NAVBAR
     ========================================================================== --}}
<header id="siteHeader" class="site-header">
    <div class="container header-container">
        <a href="{{ url('/') }}" class="header-brand" aria-label="Beranda SIARKA">
            <img src="{{ asset('images/siarka-ikon.svg') }}" alt="Logo SIARKA" class="header-logo" width="34" height="34">
            <div class="header-brand-text">
                <span class="header-brand-title">SIARKA</span>
                <span class="header-brand-subtitle">Laboratorium Komputer & Bisnis</span>
            </div>
        </a>

        <nav aria-label="Navigasi Utama">
            <ul class="nav-desktop">
                <li class="nav-desktop-item"><a href="#katalog" class="nav-link-anchor">Katalog Lab</a></li>
                <li class="nav-desktop-item"><a href="#layanan" class="nav-link-anchor">Layanan</a></li>
                <li class="nav-desktop-item"><a href="#pengelola" class="nav-link-anchor">Pengelola</a></li>
                <li class="nav-desktop-item"><a href="#sop" class="nav-link-anchor">SOP</a></li>
                <li class="nav-desktop-item"><a href="#alur" class="nav-link-anchor">Alur Pinjam</a></li>
                <li class="nav-desktop-item"><a href="#jadwal" class="nav-link-anchor">Jadwal</a></li>
                <li class="nav-desktop-item"><a href="#faq" class="nav-link-anchor">FAQ</a></li>
            </ul>
        </nav>

        <div class="header-actions">
            <a href="{{ route('login') }}" class="btn-header-login">Masuk SIARKA</a>
            <button type="button" class="nav-mobile-toggle" id="mobileMenuBtn" aria-label="Buka Menu Navigasi" aria-expanded="false">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobile Panel (di bawah 900px) --}}
    <div id="mobileMenuPanel" class="nav-mobile-panel" aria-hidden="true">
        <a href="#katalog" class="nav-mobile-item">Katalog Lab</a>
        <a href="#layanan" class="nav-mobile-item">Layanan</a>
        <a href="#pengelola" class="nav-mobile-item">Pengelola</a>
        <a href="#sop" class="nav-mobile-item">SOP</a>
        <a href="#alur" class="nav-mobile-item">Alur Pinjam</a>
        <a href="#jadwal" class="nav-mobile-item">Jadwal</a>
        <a href="#faq" class="nav-mobile-item">FAQ</a>
    </div>
</header>

<main id="main-content">
    {{-- ==========================================================================
         HERO SECTION DENGAN VIDEO BACKGROUND YOUTUBE (Referensi: sman3palu.sch.id)
         ========================================================================== --}}
    <section class="section-pad hero-section" id="hero">
        {{-- Video Background YouTube --}}
        <div class="hero-video-bg" aria-hidden="true">
            <iframe
                src="https://www.youtube.com/embed/FthMSjd1KkI?autoplay=1&mute=1&loop=1&playlist=FthMSjd1KkI&controls=0&rel=0&modestbranding=1&playsinline=1&enablejsapi=1"
                title="Video Profil Laboratorium"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                tabindex="-1">
            </iframe>
        </div>
        <div class="hero-video-overlay" aria-hidden="true"></div>

        <div class="container hero-container">
            <div class="hero-content reveal">
                <div class="hero-badge-tag">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5"></path><path d="M2 12l10 5 10-5"></path></svg>
                    <span>Politeknik Negeri Tanah Laut</span>
                </div>
                <h1 class="hero-title">Sistem Fasilitas & Layanan Laboratorium Terintegrasi</h1>
                <p class="hero-desc">Pusat informasi inventaris, reservasi ruang praktikum, dan tata kelola kegiatan akademik Jurusan Komputer dan Bisnis secara transparan dan akuntabel.</p>
                <div class="hero-actions">
                    <a href="#katalog" class="btn-hero-primary">
                        Lihat Katalog Lab
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </a>
                    <a href="{{ route('login') }}" class="btn-hero-secondary">
                        Ajukan Peminjaman
                    </a>
                </div>
            </div>

            {{-- Statistik Angka (Count-up 1200ms saat 60% terlihat) dengan kartu glassmorphism elegan --}}
            <div class="stats-grid-hero">
                <div class="stat-card-hero reveal tier-0">
                    <div class="stat-number" data-stat-target="{{ max($totalRuangan, 6) }}">0</div>
                    <div class="stat-label">Ruang Laboratorium</div>
                </div>
                <div class="stat-card-hero reveal tier-1">
                    <div class="stat-number" data-stat-target="180">0</div>
                    <div class="stat-label">Unit Komputer & PC</div>
                </div>
                <div class="stat-card-hero reveal tier-2">
                    <div class="stat-number" data-stat-target="45">0</div>
                    <div class="stat-label">Sesi Praktikum Mingguan</div>
                </div>
                <div class="stat-card-hero reveal tier-3">
                    <div class="stat-number" data-stat-target="1200">0</div>
                    <div class="stat-label">Mahasiswa Terlayani</div>
                </div>
            </div>
        </div>
    </section>

    {{-- ==========================================================================
         B.2 KATALOG LAB (Abu Terang #F5F7FA)
         - Chip nama bidang saja 1 baris scrollable
         - Kolom cari dan tombol "Kosong sekarang" 1 baris
         - Grid 3 kolom, gap 24px, sama tinggi, tombol rata bawah
         - Foto 16/9, object-fit cover, object-position top
         - Placeholder #E2E8F0 jika tidak ada foto
         - 6 kartu awal + "Tampilkan lebih banyak" (+6 per klik)
         ========================================================================== --}}
    <section id="katalog" class="section-pad bg-alt-section">
        <div class="container">
            <div class="section-head-left reveal">
                <h2 class="section-title">Katalog Ruang Laboratorium</h2>
                <p class="section-desc">Pilih dan temukan ruang laboratorium yang sesuai dengan kebutuhan perkuliahan, praktikum mandiri, maupun kegiatan riset akademik.</p>
            </div>

            {{-- Kontrol Filter & Pencarian --}}
            <div class="catalog-controls reveal">
                {{-- Chip Kategori: Nama Bidang Saja, 1 Baris Scrollable --}}
                <div class="catalog-chips-scroll" role="tablist" aria-label="Filter Bidang Laboratorium">
                    <button type="button" class="filter-chip is-active" data-filter="all" onclick="setCatalogFilter('all', this)">Semua</button>
                    @foreach($bidangLabList as $bidang)
                        <button type="button" class="filter-chip" data-filter="{{ Str::slug($bidang) }}" onclick="setCatalogFilter('{{ Str::slug($bidang) }}', this)">{{ $bidang }}</button>
                    @endforeach
                </div>

                {{-- Kolom Cari & Tombol Kosong Sekarang 1 Baris --}}
                <div class="catalog-search-row">
                    <div class="search-box-wrap">
                        <svg class="search-box-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input type="text" id="labSearchInput" class="search-box-input" placeholder="Cari nama laboratorium atau spesifikasi..." oninput="filterLabCards()">
                    </div>
                    <button type="button" id="btnAvailableFilter" class="btn-available-now" onclick="toggleAvailableOnly()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        Kosong sekarang
                    </button>
                </div>
            </div>

            {{-- Grid Kartu Lab --}}
            @if($ruangans->isEmpty())
                <div class="card-interactive" style="padding: 48px 24px; text-align: center;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto 12px; display:block;">
                        <rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect>
                        <path d="M9 22v-4h6v4"></path>
                    </svg>
                    <p style="font-size: 0.95rem; color: var(--text-secondary); font-weight: 500;">Belum ada ruangan laboratorium yang terdaftar.</p>
                </div>
            @else
                <div class="lab-cards-grid" id="labCardsGrid">
                    @foreach($ruangans as $index => $r)
                        @php
                            // Penentuan bidang deterministik
                            $bidangRuangan = $r->bidang ?? $bidangLabList[$index % count($bidangLabList)];
                            $slugBidang = Str::slug($bidangRuangan);
                            // Status ketersediaan real-time dari database
                            $isAvailable = true;
                            try {
                                if (!empty($r->id_ruangan)) {
                                    $now = now();
                                    $isBooked = \App\Models\PeminjamanRuangan::where('id_ruangan', $r->id_ruangan)
                                        ->where('tanggal_mulai', '<=', $now)
                                        ->where('tanggal_selesai', '>=', $now)
                                        ->whereHas('peminjaman', function($q) {
                                            $q->where('status', 'disetujui');
                                        })->exists();
                                    $isAvailable = !$isBooked;
                                }
                            } catch (\Throwable $e) {
                                $isAvailable = ($index % 4 !== 3);
                            }
                            $statusSlug = $isAvailable ? 'tersedia' : 'terpakai';
                            $kapasitas = $r->kapasitas ?: 30;
                            $lantai = $r->lantai ?: (($index % 3) + 1);

                            // Jalur gambar lab sesuai nama lab dari https://labkom.politala.ac.id/
                            // Hapus foto keluarga dan foto yang tidak relevan
                            $namaLower = strtolower($r->nama_ruangan);
                            $labImageFile = null;

                            if (str_contains($namaLower, 'linus') || str_contains($namaLower, 'torvalds')) {
                                $labImageFile = str_contains($namaLower, '2') ? 'lt2.jpg' : 'lt.jpg';
                            } elseif (str_contains($namaLower, 'steve') || str_contains($namaLower, 'jobs')) {
                                $labImageFile = str_contains($namaLower, '2') ? 'sj2.jpg' : 'sj.jpg';
                            } elseif (str_contains($namaLower, 'bill') || str_contains($namaLower, 'gates')) {
                                $labImageFile = str_contains($namaLower, '2') ? 'bg2.jpg' : 'bg.jpg';
                            } elseif (str_contains($namaLower, 'kenneth') || str_contains($namaLower, 'thompson')) {
                                $labImageFile = str_contains($namaLower, '2') ? 'kt2.jpg' : 'kt.jpg';
                            } elseif (!empty($r->gambar) && !str_contains($r->gambar, '2108') && !str_contains(strtolower($r->gambar), 'keluarga')) {
                                $labImageFile = $r->gambar;
                            }

                            $hasCustomImage = !empty($labImageFile);
                            $imageUrl = $hasCustomImage ? \App\Support\GambarKatalog::url($labImageFile, \App\Models\Ruangan::GAMBAR_DIR) : null;
                            $tierClass = 'tier-' . ($index % 6);
                        @endphp
                        <div class="lab-card card-interactive reveal {{ $tierClass }} {{ $index >= 6 ? 'is-hidden-pagination' : '' }}"
                             data-lab-index="{{ $index }}"
                             data-lab-name="{{ strtolower($r->nama_ruangan) }}"
                             data-lab-field="{{ $slugBidang }}"
                             data-lab-status="{{ $statusSlug }}">
                            <div class="lab-card-photo-box">
                                @if($hasCustomImage)
                                    <img src="{{ $imageUrl }}" alt="{{ $r->nama_ruangan }}" class="lab-card-photo" loading="lazy">
                                @else
                                    <div class="lab-card-placeholder">
                                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect>
                                            <path d="M9 22v-4h6v4"></path>
                                            <line x1="8" y1="6" x2="8.01" y2="6"></line>
                                            <line x1="16" y1="6" x2="16.01" y2="6"></line>
                                            <line x1="12" y1="6" x2="12.01" y2="6"></line>
                                            <line x1="12" y1="10" x2="12.01" y2="10"></line>
                                            <line x1="12" y1="14" x2="12.01" y2="14"></line>
                                            <line x1="16" y1="10" x2="16.01" y2="10"></line>
                                            <line x1="16" y1="14" x2="16.01" y2="14"></line>
                                            <line x1="8" y1="10" x2="8.01" y2="10"></line>
                                            <line x1="8" y1="14" x2="8.01" y2="14"></line>
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            <div class="lab-card-body">
                                <div>
                                    <span class="lab-card-status-badge {{ $isAvailable ? 'badge-status-tersedia' : 'badge-status-terpakai' }}">
                                        {{ $isAvailable ? 'Tersedia' : 'Sedang Dipakai' }}
                                    </span>
                                </div>
                                <h3 class="lab-card-title">{{ $r->nama_ruangan }}</h3>
                                <p class="lab-card-field">{{ $bidangRuangan }}</p>
                                <div class="lab-card-meta-line">
                                    <span>{{ $kapasitas }} Kursi</span>
                                    <span>—</span>
                                    <span>Lantai {{ $lantai }}</span>
                                </div>

                                <div class="lab-card-actions">
                                    <button type="button" class="btn-card-detail"
                                            onclick="openLabModal('{{ addslashes($r->nama_ruangan) }}', '{{ addslashes($bidangRuangan) }}', '{{ $kapasitas }} Kursi', 'Lantai {{ $lantai }}', '{{ $isAvailable ? 'Tersedia' : 'Sedang Dipakai' }}', '{{ $imageUrl ?? '' }}')">
                                        Detail
                                    </button>
                                    <a href="{{ route('login') }}" class="btn-card-book">Ajukan Pinjam</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Tombol Tampilkan Lebih Banyak (Paginasi Stagger +6) --}}
                @if($ruangans->count() > 4)
                    <div class="catalog-load-more-wrap">
                        <button type="button" id="semua-fasilitas" class="btn-load-more" onclick="loadMoreCards()">
                            <span>Tampilkan lebih banyak</span>
                            <span style="display:none;" aria-hidden="true">Lihat Semua</span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                    </div>
                @endif
            @endif
        </div>
    </section>

    {{-- ==========================================================================
         B.3 LAYANAN (Putih)
         - 3 kartu bidang (tanpa garis aksen)
         - 4 kartu layanan (tinggi seragam, tanpa garis aksen)
         - Ikon outline seragam
         ========================================================================== --}}
    <section id="layanan" class="section-pad bg-white-section">
        <div class="container">
            <div class="section-head-left reveal">
                <h2 class="section-title">Bidang Keahlian & Layanan Laboratorium</h2>
                <p class="section-desc">Fasilitas laboratorium terbagi ke dalam kluster keahlian vokasional dengan dukungan sarana perangkat keras dan perangkat lunak standar industri.</p>
            </div>

            {{-- 3 Kartu Bidang --}}
            <div class="layanan-fields-grid">
                <div class="field-card card-interactive reveal tier-0">
                    <div class="field-card-icon-box">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                        </svg>
                    </div>
                    <h3 class="field-card-title">Komputer & Informatika</h3>
                    <p class="field-card-desc">Fasilitas praktikum pemrograman, rekayasa perangkat lunak, arsitektur jaringan komputer, komputasi awan, dan kecerdasan buatan.</p>
                </div>

                <div class="field-card card-interactive reveal tier-1">
                    <div class="field-card-icon-box">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="20" x2="18" y2="10"></line>
                            <line x1="12" y1="20" x2="12" y2="4"></line>
                            <line x1="6" y1="20" x2="6" y2="14"></line>
                        </svg>
                    </div>
                    <h3 class="field-card-title">Bisnis & Akuntansi</h3>
                    <p class="field-card-desc">Laboratorium simulasi akuntansi digital, tata kelola perpajakan terapan, enterprise resource planning (ERP), dan analisis data keuangan bisnis.</p>
                </div>

                <div class="field-card card-interactive reveal tier-2">
                    <div class="field-card-icon-box">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect>
                            <rect x="9" y="9" width="6" height="6"></rect>
                            <line x1="9" y1="1" x2="9" y2="4"></line>
                            <line x1="15" y1="1" x2="15" y2="4"></line>
                            <line x1="9" y1="20" x2="9" y2="23"></line>
                            <line x1="15" y1="20" x2="15" y2="23"></line>
                            <line x1="20" y1="9" x2="23" y2="9"></line>
                            <line x1="20" y1="14" x2="23" y2="14"></line>
                            <line x1="1" y1="9" x2="4" y2="9"></line>
                            <line x1="1" y1="14" x2="4" y2="14"></line>
                        </svg>
                    </div>
                    <h3 class="field-card-title">Hardware & Penunjang</h3>
                    <p class="field-card-desc">Sarana perakitan komputer, uji kelayakan mikrokontroler & Internet of Things (IoT), pemeliharaan berkala, serta perbaikan perangkat institusi.</p>
                </div>
            </div>

            {{-- 4 Kartu Layanan (Tinggi seragam, tanpa garis aksen) --}}
            <div class="services-grid">
                <div class="service-card card-interactive reveal tier-0">
                    <div class="service-card-icon-box">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                        </svg>
                    </div>
                    <h3 class="service-card-title">Praktikum Terjadwal</h3>
                    <p class="service-card-desc">Layanan pelaksanaan modul perkuliahan terstruktur semester reguler bagi seluruh program studi jurusan.</p>
                </div>

                <div class="service-card card-interactive reveal tier-1">
                    <div class="service-card-icon-box">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                    <h3 class="service-card-title">Peminjaman Mandiri</h3>
                    <p class="service-card-desc">Pemanfaatan ruang dan perangkat di luar jadwal kuliah untuk tugas akhir, penelitian skripsi, atau kompetisi.</p>
                </div>

                <div class="service-card card-interactive reveal tier-2">
                    <div class="service-card-icon-box">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="8" r="7"></circle>
                            <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline>
                        </svg>
                    </div>
                    <h3 class="service-card-title">Uji Kompetensi</h3>
                    <p class="service-card-desc">Penyelenggaraan sertifikasi keahlian IT & Bisnis bekerjasama dengan Lembaga Sertifikasi Profesi (LSP).</p>
                </div>

                <div class="service-card card-interactive reveal tier-3">
                    <div class="service-card-icon-box">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                        </svg>
                    </div>
                    <h3 class="service-card-title">Bantuan Teknis</h3>
                    <p class="service-card-desc">Dukungan laboran dalam penyiapan perangkat lunak praktikum, konfigurasi server, serta instalasi alat.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ==========================================================================
         B.4 PENGELOLA (Abu Terang #F5F7FA)
         - Foto bulat 64px dari public/images/pengelola/ (fallback inisial)
         - Kartu laboran dalam grid sama tinggi, tanpa ruang kosong janggal
         - Tombol "Lihat bagan struktur organisasi resmi" membuka lightbox
         ========================================================================== --}}
    <section id="pengelola" class="section-pad bg-alt-section">
        <div class="container">
            <div class="section-head-left reveal">
                <h2 class="section-title">Struktur Pengelola Laboratorium</h2>
                <p class="section-desc">Tim tenaga kependidikan dan pranata laboratorium yang berdedikasi menjaga ketersediaan, ketertiban, dan kelancaran operasional setiap ruang.</p>
            </div>

            {{-- Kartu Kepala Laboratorium --}}
            <div class="pengelola-lead-card card-interactive reveal">
                @php
                    $kepala = $pengelolaList[0];
                    $fotoKepala = 'images/pengelola/' . $kepala['slug'] . '.jpg';
                    $hasFotoKepala = file_exists(public_path($fotoKepala));
                @endphp
                @if($hasFotoKepala)
                    <img src="{{ asset($fotoKepala) }}" alt="{{ $kepala['nama'] }}" class="avatar-circle-64">
                @else
                    <div class="avatar-fallback-64">{{ $kepala['initial'] }}</div>
                @endif
                <div class="member-info">
                    <h3 class="member-name">{{ $kepala['nama'] }}</h3>
                    <div class="member-role">{{ $kepala['peran'] }}</div>
                    <div class="member-focus">{{ $kepala['fokus'] }}</div>
                </div>
            </div>

            {{-- Grid Laboran Sama Tinggi --}}
            <div class="laboran-grid">
                @for($i = 1; $i < count($pengelolaList); $i++)
                    @php
                        $m = $pengelolaList[$i];
                        $fotoLaboran = 'images/pengelola/' . $m['slug'] . '.jpg';
                        $hasFotoLaboran = file_exists(public_path($fotoLaboran));
                        $tierClass = 'tier-' . ($i % 6);
                    @endphp
                    <div class="laboran-card card-interactive reveal {{ $tierClass }}">
                        @if($hasFotoLaboran)
                            <img src="{{ asset($fotoLaboran) }}" alt="{{ $m['nama'] }}" class="avatar-circle-64">
                        @else
                            <div class="avatar-fallback-64">{{ $m['initial'] }}</div>
                        @endif
                        <div class="member-info">
                            <h4 class="member-name">{{ $m['nama'] }}</h4>
                            <div class="member-role">{{ $m['peran'] }}</div>
                            <div class="member-focus">{{ $m['fokus'] }}</div>
                        </div>
                    </div>
                @endfor
            </div>

            {{-- Tombol Buka Lightbox Bagan --}}
            <div class="org-action-wrap reveal">
                <button type="button" class="btn-org-lightbox" onclick="openOrgChartModal()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                        <polyline points="21 15 16 10 5 21"></polyline>
                    </svg>
                    Lihat bagan struktur organisasi resmi
                </button>
            </div>
        </div>
    </section>

    {{-- Lightbox Dialog Bagan Struktur Organisasi --}}
    <dialog id="orgChartDialog" class="lightbox-dialog" aria-labelledby="orgChartDialogTitle">
        <div class="lightbox-header">
            <h3 id="orgChartDialogTitle" class="lightbox-title">Bagan Struktur Organisasi Laboratorium</h3>
            <button type="button" class="lightbox-close-btn" onclick="closeOrgChartModal()" aria-label="Tutup Dialog">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="lightbox-body">
            <img src="{{ asset('images/struktur_organisasi.jpg') }}" alt="Bagan Struktur Organisasi Resmi Laboratorium Jurusan Komputer dan Bisnis" class="lightbox-img">
        </div>
    </dialog>

    {{-- ==========================================================================
         B.5 SOP (Putih)
         - 4 kartu sama tinggi, tautan di dasar kartu rata bawah
         ========================================================================== --}}
    <section id="sop" class="section-pad bg-white-section">
        <div class="container">
            <div class="section-head-left reveal">
                <h2 class="section-title">Standar Operasional Prosedur (SOP)</h2>
                <p class="section-desc">Pedoman resmi tata tertib penggunaan fasilitas laboratorium demi keselamatan kerja dan pemeliharaan sarana prasarana institusi.</p>
            </div>

            <div class="sop-grid">
                <div class="sop-card card-interactive reveal tier-0">
                    <div class="sop-icon-box">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </div>
                    <div class="sop-number">SOP-01</div>
                    <h3 class="sop-card-title">Tata Tertib Ruang Laboratorium</h3>
                    <p class="sop-card-desc">Ketentuan berpakaian rapi, larangan membawa makanan/minuman, dan kewajiban menjaga kebersihan ruangan praktikum.</p>
                    <a href="{{ route('login') }}" class="sop-card-link">
                        Unduh Dokumen
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </a>
                </div>

                <div class="sop-card card-interactive reveal tier-1">
                    <div class="sop-icon-box">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                        </svg>
                    </div>
                    <div class="sop-number">SOP-02</div>
                    <h3 class="sop-card-title">Peminjaman Ruang & Alat</h3>
                    <p class="sop-card-desc">Mekanisme reservasi online SIARKA, verifikasi dosen penanggung jawab, serta penyerahan kunci ruangan.</p>
                    <a href="{{ route('login') }}" class="sop-card-link">
                        Unduh Dokumen
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </a>
                </div>

                <div class="sop-card card-interactive reveal tier-2">
                    <div class="sop-icon-box">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                    </div>
                    <div class="sop-number">SOP-03</div>
                    <h3 class="sop-card-title">Keselamatan & Kesehatan Kerja</h3>
                    <p class="sop-card-desc">Prosedur penanganan darurat kelistrikan, evakuasi kebakaran, dan pertolongan pertama di area laboratorium.</p>
                    <a href="{{ route('login') }}" class="sop-card-link">
                        Unduh Dokumen
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </a>
                </div>

                <div class="sop-card card-interactive reveal tier-3">
                    <div class="sop-icon-box">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="23 4 23 10 17 10"></polyline>
                            <polyline points="1 20 1 14 7 14"></polyline>
                            <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                        </svg>
                    </div>
                    <div class="sop-number">SOP-04</div>
                    <h3 class="sop-card-title">Pengembalian & Pelaporan</h3>
                    <p class="sop-card-desc">Pemeriksaan fisik kondisi perangkat pasca-penggunaan, pencatatan insiden log, dan verifikasi akhir laboran.</p>
                    <a href="{{ route('login') }}" class="sop-card-link">
                        Unduh Dokumen
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ==========================================================================
         B.6 ALUR PEMINJAMAN (Abu Terang #F5F7FA)
         - Stepper 4 langkah, teks rata tengah, garis penghubung halus
         - Garis memanjang dari kiri, nomor muncul berurutan (delay 200ms)
         ========================================================================== --}}
    <section id="alur" class="section-pad bg-alt-section">
        <div class="container">
            <div class="section-head-left reveal">
                <h2 class="section-title">Alur Peminjaman Laboratorium</h2>
                <p class="section-desc">Empat langkah mudah pengajuan peminjaman laboratorium untuk keperluan akademik secara tertib dan termonitor.</p>
            </div>

            <div class="stepper-container" id="stepperContainer">
                <div class="stepper-track-line">
                    <div class="stepper-track-fill" id="stepperFill"></div>
                </div>

                <div class="stepper-step" id="stepNode-1">
                    <div class="stepper-circle">1</div>
                    <h3 class="stepper-step-title">Pengajuan Online</h3>
                    <p class="stepper-step-desc">Masuk ke SIARKA dan isi formulir permohonan tanggal, jam, dan tujuan kegiatan.</p>
                </div>

                <div class="stepper-step" id="stepNode-2">
                    <div class="stepper-circle">2</div>
                    <h3 class="stepper-step-title">Verifikasi & Persetujuan</h3>
                    <p class="stepper-step-desc">Dosen pembimbing atau laboran memeriksa kelayakan dan menyetujui reservasi.</p>
                </div>

                <div class="stepper-step" id="stepNode-3">
                    <div class="stepper-circle">3</div>
                    <h3 class="stepper-step-title">Pengambilan Kunci</h3>
                    <p class="stepper-step-desc">Konfirmasi kedatangan ke meja piket laboran dan gunakan ruang sesuai SOP.</p>
                </div>

                <div class="stepper-step" id="stepNode-4">
                    <div class="stepper-circle">4</div>
                    <h3 class="stepper-step-title">Pengembalian Ruang</h3>
                    <p class="stepper-step-desc">Pemeriksaan kondisi komputer, inventaris alat, dan penandatanganan log akhir.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ==========================================================================
         B.7 JADWAL (Putih)
         - Dua kartu mengisi lebar penuh kontainer dengan tinggi sama
         ========================================================================== --}}
    <section id="jadwal" class="section-pad bg-white-section">
        <div class="container">
            <div class="section-head-left reveal">
                <h2 class="section-title">Jadwal Praktikum & Waktu Operasional</h2>
                <p class="section-desc">Periksa jadwal pemakaian ruang secara berkala guna memastikan laboratorium bebas dari bentrok sesi perkuliahan.</p>
            </div>

            <div class="jadwal-grid">
                <div class="jadwal-card card-interactive reveal tier-0">
                    <div class="jadwal-card-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </div>
                    <h3 class="jadwal-card-title">Jadwal Praktikum Terjadwal</h3>
                    <p class="jadwal-card-desc">Seluruh jadwal mata kuliah praktikum semester berjalan disinkronisasikan langsung melalui sistem data akademik.</p>
                    <ul class="jadwal-card-list">
                        <li class="jadwal-list-item">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span>Sesi Pagi: 08.00 – 11.40 WITA (Senin – Jumat)</span>
                        </li>
                        <li class="jadwal-list-item">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span>Sesi Siang: 13.00 – 16.40 WITA (Senin – Jumat)</span>
                        </li>
                        <li class="jadwal-list-item">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span>Pengecekan bentrok ruangan secara otomatis</span>
                        </li>
                    </ul>
                    <div class="jadwal-card-action">
                        <a href="{{ route('login') }}" class="btn-primary-action">Buka Kalender Jadwal</a>
                    </div>
                </div>

                <div class="jadwal-card card-interactive reveal tier-1">
                    <div class="jadwal-card-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                    <h3 class="jadwal-card-title">Jam Layanan & Meja Piket</h3>
                    <p class="jadwal-card-desc">Waktu pelayanan permohonan peminjaman, pengambilan kunci, konsultasi modul, dan pengaduan sarana laboratorium.</p>
                    <ul class="jadwal-card-list">
                        <li class="jadwal-list-item">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span>Senin – Kamis: 08.00 – 16.00 WITA</span>
                        </li>
                        <li class="jadwal-list-item">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span>Jumat: 08.00 – 11.30 & 13.30 – 16.30 WITA</span>
                        </li>
                        <li class="jadwal-list-item">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span>Sabtu, Minggu & Hari Libur Nasional: Tutup</span>
                        </li>
                    </ul>
                    <div class="jadwal-card-action">
                        <a href="#pengelola" class="btn-secondary-action">Hubungi Laboran</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ==========================================================================
         B.8 FAQ (Abu Terang #F5F7FA)
         - Lebar kontainer penuh, accordion rapi, satu item terbuka
         - Buka-tutup halus CSS grid-template-rows, rotasi panah 180deg
         ========================================================================== --}}
    <section id="faq" class="section-pad bg-alt-section">
        <div class="container">
            <div class="section-head-left reveal">
                <h2 class="section-title">Pertanyaan yang Sering Diajukan (FAQ)</h2>
                <p class="section-desc">Jawaban ringkas seputar akun pengguna, mekanisme peminjaman ruang, serta prosedur pemanfaatan alat laboratorium.</p>
            </div>

            <div class="faq-accordion-list reveal" id="faqAccordion">
                <div class="faq-item is-open">
                    <button type="button" class="faq-question-btn" onclick="toggleFaq(this)">
                        <span>Bagaimana cara mahasiswa memperoleh akun SIARKA?</span>
                        <svg class="faq-chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                    <div class="faq-answer-collapse">
                        <div class="faq-answer-inner">
                            <p class="faq-answer-text">Akun mahasiswa dibuatkan secara terpusat oleh staf prodi atau laboran berdasarkan data registrasi akademik awal semester. Gunakan email institusi mahasiswa Anda untuk masuk ke sistem SIARKA.</p>
                        </div>
                    </div>
                </div>

                <div class="faq-item">
                    <button type="button" class="faq-question-btn" onclick="toggleFaq(this)">
                        <span>Berapa hari batas waktu pengajuan peminjaman laboratorium?</span>
                        <svg class="faq-chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                    <div class="faq-answer-collapse">
                        <div class="faq-answer-inner">
                            <p class="faq-answer-text">Permohonan peminjaman mandiri dianjurkan diajukan paling lambat 2 (dua) hari kerja sebelum tanggal pelaksanaan kegiatan agar laboran dan dosen penanggung jawab dapat memverifikasi ketersediaan ruang.</p>
                        </div>
                    </div>
                </div>

                <div class="faq-item">
                    <button type="button" class="faq-question-btn" onclick="toggleFaq(this)">
                        <span>Apakah mahasiswa boleh menggunakan laboratorium di akhir pekan?</span>
                        <svg class="faq-chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                    <div class="faq-answer-collapse">
                        <div class="faq-answer-inner">
                            <p class="faq-answer-text">Peminjaman di akhir pekan hanya diperkenankan untuk kegiatan resmi institusi, perlombaan akademik berskala nasional, atau penelitian tugas akhir khusus dengan surat rekomendasi resmi dosen pembimbing dan persetujuan Ketua Jurusan.</p>
                        </div>
                    </div>
                </div>

                <div class="faq-item">
                    <button type="button" class="faq-question-btn" onclick="toggleFaq(this)">
                        <span>Apa yang harus dilakukan jika terjadi kerusakan perangkat saat praktikum?</span>
                        <svg class="faq-chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                    <div class="faq-answer-collapse">
                        <div class="faq-answer-inner">
                            <p class="faq-answer-text">Segera laporkan kepada laboran atau asisten piket di meja layanan. Jangan mencoba memperbaiki perangkat keras kelistrikan sendiri tanpa pendampingan teknisi resmi laboratorium.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ==========================================================================
         B.9 CTA SATU BLOK BIRU TUA
         ========================================================================== --}}
    <section class="cta-section">
        <div class="container">
            <div class="cta-box reveal">
                <div class="cta-text-wrap">
                    <h2 class="cta-title">Siap Memanfaatkan Fasilitas Laboratorium?</h2>
                    <p class="cta-desc">Masuk ke SIARKA untuk memeriksa jadwal praktikum yang sedang berjalan, inventaris katalog alat, atau ajukan permohonan ruangan secara mandiri.</p>
                </div>
                <div class="cta-actions">
                    <a href="{{ route('login') }}" class="btn-cta-white">
                        Masuk SIARKA
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </a>
                    <a href="#katalog" class="btn-cta-outline">Jelajahi Lab</a>
                </div>
            </div>
        </div>
    </section>
</main>

{{-- ==========================================================================
     FOOTER SEJAJAR KONTAINER (Warna sama persis dengan tombol "Masuk")
     ========================================================================== --}}
<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            {{-- Kolom 1: Merek SIARKA + Navigasi --}}
            <div class="footer-col-brand">
                <div class="footer-brand-title">
                    <img src="{{ asset('images/siarka-ikon.svg') }}" alt="Logo SIARKA" width="28" height="28" style="filter: brightness(0) invert(1);">
                    <span>SIARKA</span>
                </div>
                <p class="footer-desc">Sistem Informasi Administrasi, Reservasi, Katalog, dan Aktivitas Laboratorium Jurusan Komputer dan Bisnis Politeknik Negeri Tanah Laut.</p>
                <ul class="footer-nav-list" style="margin-top: 6px;">
                    <li><a href="#katalog">&rarr; Katalog Laboratorium</a></li>
                    <li><a href="#layanan">&rarr; Bidang Keahlian &amp; Layanan</a></li>
                    <li><a href="#sop">&rarr; Standar Operasional (SOP)</a></li>
                    <li><a href="#alur">&rarr; Alur Peminjaman Lab</a></li>
                </ul>
            </div>

            {{-- Kolom 2: Alamat Kami (sesuai referensi resmi Politala) --}}
            <div>
                <h4 class="footer-col-title">Alamat Kami</h4>
                <ul class="footer-contact-list">
                    <li class="footer-contact-item">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        <span>Jl. A.Yani KM.6, Desa Panggung, Kec. Pelaihari, Kabupaten Tanah Laut, Kalimantan Selatan 70815</span>
                    </li>
                    <li class="footer-contact-item">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><path d="M9 22v-4h6v4"></path></svg>
                        <span>Gedung Laboratorium Terpadu — Jurusan Komputer &amp; Bisnis</span>
                    </li>
                </ul>
            </div>

            {{-- Kolom 3: Kontak Kami (sesuai referensi resmi Politala) --}}
            <div>
                <h4 class="footer-col-title">Kontak Kami</h4>
                <ul class="footer-contact-list">
                    <li class="footer-contact-item">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                        <a href="mailto:mail@politala.ac.id">mail@politala.ac.id</a>
                    </li>
                    <li class="footer-contact-item">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                        <span>(0512) 2021065</span>
                    </li>
                    <li class="footer-contact-item">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <span>Senin – Jumat, 08.00 – 16.00 WITA</span>
                    </li>
                </ul>
            </div>

            {{-- Kolom 4: Ikuti Kami (sesuai referensi resmi Politala) --}}
            <div>
                <h4 class="footer-col-title">Ikuti Kami</h4>
                <div class="footer-social-list">
                    {{-- Facebook --}}
                    <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" class="footer-social-link" aria-label="Facebook Politeknik Negeri Tanah Laut">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
                        </svg>
                    </a>
                    {{-- Instagram --}}
                    <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" class="footer-social-link" aria-label="Instagram Politeknik Negeri Tanah Laut">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                        </svg>
                    </a>
                    {{-- YouTube --}}
                    <a href="https://youtube.com" target="_blank" rel="noopener noreferrer" class="footer-social-link" aria-label="YouTube Politeknik Negeri Tanah Laut">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19.1c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.43zM9.75 15.02V8.58l5.85 3.22-5.85 3.22z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div>&copy; 2009 Politeknik Negeri Tanah Laut</div>
            <div>SIARKA — Laboratorium Jurusan Komputer dan Bisnis.</div>
        </div>
    </div>
</footer>

{{-- Modal Detail Laboratorium --}}
<dialog id="labDetailDialog" class="lightbox-dialog" aria-labelledby="labDetailTitle">
    <div class="lightbox-header">
        <h3 id="labDetailTitle" class="lightbox-title">Detail Laboratorium</h3>
        <button type="button" class="lightbox-close-btn" onclick="closeLabModal()" aria-label="Tutup Dialog">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>
    <div class="lightbox-body" style="text-align: left; padding: 24px;">
        <div id="modalPhotoContainer" style="margin-bottom: 16px; border-radius: 8px; overflow: hidden; display: none;">
            <img id="modalLabPhoto" src="" alt="" style="width: 100%; max-height: 320px; object-fit: cover; display: block;">
        </div>
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 8px;">
            <h4 id="modalLabName" style="font-size: 1.25rem; font-weight: 700; color: var(--text-main);">Nama Lab</h4>
            <span id="modalLabStatus" class="lab-card-status-badge badge-status-tersedia">Tersedia</span>
        </div>
        <p id="modalLabField" style="font-size: 0.88rem; color: var(--text-secondary); margin-bottom: 16px;">Bidang</p>
        <div style="display: flex; gap: 16px; font-size: 0.84rem; color: var(--text-secondary); padding: 12px 16px; background: var(--bg-alt); border-radius: 8px; margin-bottom: 24px;">
            <div><strong>Kapasitas:</strong> <span id="modalLabCapacity">-</span></div>
            <div><strong>Lokasi:</strong> <span id="modalLabFloor">-</span></div>
        </div>
        <div style="display: flex; justify-content: flex-end; gap: 10px;">
            <button type="button" class="btn-card-detail" onclick="closeLabModal()">Tutup</button>
            <a href="{{ route('login') }}" class="btn-card-book" style="padding: 0 18px;">Ajukan Pinjam</a>
        </div>
    </div>
</dialog>

{{-- ==========================================================================
     VANILLA JAVASCRIPT
     - Tanpa library eksternal
     - Easing cubic-bezier(.22,.61,.36,1)
     - Prefers-reduced-motion check
     - Scrollspy IntersectionObserver rootMargin "-40% 0px -55% 0px"
     - Scroll reveal staggered 60ms delay
     - Stat count-up 1200ms ease-out cubic
     - Stepper animation
     - FAQ Accordion 1 item
     ========================================================================== --}}
<script>
(function() {
    'use strict';

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // 1. Navbar Shadow on Scroll > 8px
    const siteHeader = document.getElementById('siteHeader');
    function updateHeaderShadow() {
        if (!siteHeader) return;
        if (window.scrollY > 8) {
            siteHeader.classList.add('is-scrolled');
        } else {
            siteHeader.classList.remove('is-scrolled');
        }
    }
    window.addEventListener('scroll', updateHeaderShadow, { passive: true });
    updateHeaderShadow();

    // 2. Mobile Menu Toggle
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenuPanel = document.getElementById('mobileMenuPanel');
    if (mobileMenuBtn && mobileMenuPanel) {
        mobileMenuBtn.addEventListener('click', function() {
            const isOpen = mobileMenuPanel.classList.toggle('is-open');
            mobileMenuBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            mobileMenuPanel.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        });

        // Tutup saat item diklik
        mobileMenuPanel.querySelectorAll('.nav-mobile-item').forEach(function(item) {
            item.addEventListener('click', function() {
                mobileMenuPanel.classList.remove('is-open');
                mobileMenuBtn.setAttribute('aria-expanded', 'false');
                mobileMenuPanel.setAttribute('aria-hidden', 'true');
            });
        });
    }

    // 3. Scrollspy dengan IntersectionObserver rootMargin "-40% 0px -55% 0px"
    const navAnchors = document.querySelectorAll('.nav-desktop-item a, .nav-mobile-item');
    const sectionsToObserve = document.querySelectorAll('section[id]');
    if ('IntersectionObserver' in window && sectionsToObserve.length > 0) {
        const spyObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const id = entry.target.getAttribute('id');
                    navAnchors.forEach(function(anchor) {
                        const href = anchor.getAttribute('href');
                        if (href === '#' + id) {
                            anchor.classList.add('is-active');
                        } else {
                            anchor.classList.remove('is-active');
                        }
                    });
                }
            });
        }, {
            rootMargin: '-40% 0px -55% 0px',
            threshold: 0
        });

        sectionsToObserve.forEach(function(sec) {
            spyObserver.observe(sec);
        });
    }

    // 4. Scroll Reveal (Fade + naik 16px, trigger sekali lalu unobserve)
    const revealElements = document.querySelectorAll('.reveal');
    if (!prefersReducedMotion && 'IntersectionObserver' in window && revealElements.length > 0) {
        const revealObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-revealed');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1
        });

        revealElements.forEach(function(el) {
            revealObserver.observe(el);
        });
    } else {
        // Reduced motion aktif atau browser lama: langsung tampilkan
        revealElements.forEach(function(el) {
            el.classList.add('is-revealed');
        });
    }

    // 5. Stat Counter: ease-out cubic, 1200ms saat 60% terlihat, format id-ID
    const statCards = document.querySelectorAll('[data-stat-target]');
    if (statCards.length > 0) {
        function animateStatCounter(el) {
            const target = parseInt(el.getAttribute('data-stat-target'), 10) || 0;
            if (prefersReducedMotion) {
                el.textContent = new Intl.NumberFormat('id-ID').format(target);
                return;
            }

            const duration = 1200;
            const startTime = performance.now();

            function updateCounter(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                // Ease-out cubic: 1 - Math.pow(1 - progress, 3)
                const easeOut = 1 - Math.pow(1 - progress, 3);
                const currentVal = Math.floor(easeOut * target);

                el.textContent = new Intl.NumberFormat('id-ID').format(currentVal);

                if (progress < 1) {
                    requestAnimationFrame(updateCounter);
                } else {
                    el.textContent = new Intl.NumberFormat('id-ID').format(target);
                }
            }

            requestAnimationFrame(updateCounter);
        }

        if ('IntersectionObserver' in window) {
            const statObserver = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        const targetEl = entry.target;
                        animateStatCounter(targetEl);
                        statObserver.unobserve(targetEl);
                    }
                });
            }, {
                threshold: 0.6
            });

            statCards.forEach(function(card) {
                statObserver.observe(card);
            });
        } else {
            statCards.forEach(function(card) {
                animateStatCounter(card);
            });
        }
    }

    // 6. Stepper Alur: garis memanjang dari kiri (800ms) lalu angka berurutan (delay 200ms)
    const stepperContainer = document.getElementById('stepperContainer');
    const stepperFill = document.getElementById('stepperFill');
    const stepNodes = [
        document.getElementById('stepNode-1'),
        document.getElementById('stepNode-2'),
        document.getElementById('stepNode-3'),
        document.getElementById('stepNode-4')
    ];

    function activateStepper() {
        if (prefersReducedMotion) {
            if (stepperFill) stepperFill.style.width = '100%';
            stepNodes.forEach(function(node) {
                if (node) node.classList.add('is-active');
            });
            return;
        }

        if (stepperFill) stepperFill.style.width = '100%';
        stepNodes.forEach(function(node, idx) {
            if (node) {
                setTimeout(function() {
                    node.classList.add('is-active');
                }, idx * 200);
            }
        });
    }

    if (stepperContainer && 'IntersectionObserver' in window) {
        const stepObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    activateStepper();
                    stepObserver.unobserve(stepperContainer);
                }
            });
        }, {
            threshold: 0.3
        });
        stepObserver.observe(stepperContainer);
    } else {
        activateStepper();
    }

    // 7. Lightbox Bagan Struktur Organisasi
    const orgDialog = document.getElementById('orgChartDialog');
    window.openOrgChartModal = function() {
        if (!orgDialog) return;
        if (typeof orgDialog.showModal === 'function') {
            orgDialog.showModal();
        } else {
            orgDialog.setAttribute('open', '');
        }
    };
    window.closeOrgChartModal = function() {
        if (!orgDialog) return;
        if (typeof orgDialog.close === 'function') {
            orgDialog.close();
        } else {
            orgDialog.removeAttribute('open');
        }
    };
    if (orgDialog) {
        orgDialog.addEventListener('click', function(e) {
            const rect = orgDialog.getBoundingClientRect();
            const isInDialog = (rect.top <= e.clientY && e.clientY <= rect.top + rect.height
                && rect.left <= e.clientX && e.clientX <= rect.left + rect.width);
            if (!isInDialog) {
                window.closeOrgChartModal();
            }
        });
    }

    // 8. Modal Detail Lab
    const labDialog = document.getElementById('labDetailDialog');
    window.openLabModal = function(name, field, capacity, floor, status, imageUrl) {
        if (!labDialog) return;
        document.getElementById('modalLabName').textContent = name;
        document.getElementById('modalLabField').textContent = field;
        document.getElementById('modalLabCapacity').textContent = capacity;
        document.getElementById('modalLabFloor').textContent = floor;

        const statusEl = document.getElementById('modalLabStatus');
        statusEl.textContent = status;
        if (status === 'Tersedia') {
            statusEl.className = 'lab-card-status-badge badge-status-tersedia';
        } else {
            statusEl.className = 'lab-card-status-badge badge-status-terpakai';
        }

        const photoContainer = document.getElementById('modalPhotoContainer');
        const photoEl = document.getElementById('modalLabPhoto');
        if (imageUrl && imageUrl.trim() !== '' && !imageUrl.includes('2108') && !imageUrl.toLowerCase().includes('keluarga')) {
            photoEl.src = imageUrl;
            photoEl.alt = name;
            photoContainer.style.display = 'block';
        } else {
            photoEl.src = '';
            photoContainer.style.display = 'none';
        }

        if (typeof labDialog.showModal === 'function') {
            labDialog.showModal();
        } else {
            labDialog.setAttribute('open', '');
        }
    };
    window.closeLabModal = function() {
        if (!labDialog) return;
        if (typeof labDialog.close === 'function') {
            labDialog.close();
        } else {
            labDialog.removeAttribute('open');
        }
    };
    if (labDialog) {
        labDialog.addEventListener('click', function(e) {
            const rect = labDialog.getBoundingClientRect();
            const isInDialog = (rect.top <= e.clientY && e.clientY <= rect.top + rect.height
                && rect.left <= e.clientX && e.clientX <= rect.left + rect.width);
            if (!isInDialog) {
                window.closeLabModal();
            }
        });
    }

    // 9. FAQ Accordion (Satu item terbuka saja)
    window.toggleFaq = function(button) {
        const parentItem = button.closest('.faq-item');
        if (!parentItem) return;

        const isOpen = parentItem.classList.contains('is-open');
        const allItems = document.querySelectorAll('#faqAccordion .faq-item');

        allItems.forEach(function(item) {
            item.classList.remove('is-open');
        });

        if (!isOpen) {
            parentItem.classList.add('is-open');
        }
    };

    // 10. Katalog Filtering & Pagination
    let activeCategory = 'all';
    let availableOnly = false;
    let visibleCount = 6;

    window.setCatalogFilter = function(categorySlug, button) {
        activeCategory = categorySlug;
        document.querySelectorAll('.filter-chip').forEach(function(chip) {
            chip.classList.remove('is-active');
        });
        button.classList.add('is-active');
        window.filterLabCards();
    };

    window.toggleAvailableOnly = function() {
        availableOnly = !availableOnly;
        const btn = document.getElementById('btnAvailableFilter');
        if (btn) {
            btn.classList.toggle('is-active', availableOnly);
        }
        window.filterLabCards();
    };

    window.filterLabCards = function() {
        const searchVal = (document.getElementById('labSearchInput')?.value || '').toLowerCase().trim();
        const cards = document.querySelectorAll('#labCardsGrid .lab-card');
        let matchedCount = 0;

        cards.forEach(function(card) {
            const name = card.getAttribute('data-lab-name') || '';
            const field = card.getAttribute('data-lab-field') || '';
            const status = card.getAttribute('data-lab-status') || '';

            const matchSearch = !searchVal || name.includes(searchVal);
            const matchCategory = (activeCategory === 'all') || (field === activeCategory);
            const matchAvailable = !availableOnly || (status === 'tersedia');

            const isMatch = matchSearch && matchCategory && matchAvailable;

            if (isMatch) {
                card.classList.remove('is-hidden-filter');
                matchedCount++;
                if (matchedCount <= visibleCount) {
                    card.classList.remove('is-hidden-pagination');
                } else {
                    card.classList.add('is-hidden-pagination');
                }
            } else {
                card.classList.add('is-hidden-filter');
            }
        });

        // Tampilkan/sembunyikan tombol muat lebih banyak
        const loadMoreBtn = document.getElementById('semua-fasilitas');
        if (loadMoreBtn) {
            if (matchedCount > visibleCount) {
                loadMoreBtn.style.display = 'inline-flex';
            } else {
                loadMoreBtn.style.display = 'none';
            }
        }
    };

    window.loadMoreCards = function() {
        visibleCount += 6;
        window.filterLabCards();

        // Stagger animasi untuk kartu baru yang muncul
        const newlyShown = document.querySelectorAll('#labCardsGrid .lab-card:not(.is-hidden-filter):not(.is-hidden-pagination)');
        newlyShown.forEach(function(card, idx) {
            card.classList.add('is-revealed');
        });
    };

    // Jalankan filter awal
    window.filterLabCards();

})();
</script>
</body>
</html>
