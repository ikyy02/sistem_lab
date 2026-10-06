<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — SIARKA</title>
    <meta name="application-name" content="SIARKA">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    @include('layouts._theme')
    <style>
        body { min-height: 100vh; display: flex; }
        .login-aside { flex: 1.1; background: var(--gold-tint); padding: 60px; display: flex; flex-direction: column; justify-content: space-between; position: relative; overflow: hidden; border-right: 1px solid #EFE1C2; }
        .login-aside::before { content: ''; position: absolute; right: -110px; top: -110px; width: 320px; height: 320px; border-radius: 50%; border: 1px solid #EFE1C2; }
        .login-aside::after { content: ''; position: absolute; left: -90px; bottom: -90px; width: 240px; height: 240px; border-radius: 50%; border: 1px solid #EFE1C2; }
        .login-aside { background-image: radial-gradient(#EFE1C2 1px, transparent 1px); background-size: 22px 22px; background-position: -6px -6px; }
        .gold-rule { width: 46px; height: 3px; background: var(--gold); border-radius: 2px; margin: 22px 0 18px; }
        .login-aside h2 { font-family: 'Fraunces', serif; font-weight: 600; font-size: 2.1rem; line-height: 1.28; max-width: 460px; color: var(--ink); position: relative; }
        .login-aside p { color: #7A6F52; max-width: 420px; font-size: .95rem; position: relative; }
        .login-feature { display: flex; align-items: flex-start; gap: 14px; position: relative; }
        .login-feature-icon { width: 38px; height: 38px; border-radius: 10px; background: #fff; border: 1px solid #EFE1C2; color: var(--gold); display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 6px rgba(169,129,47,.1); }
        .login-feature-text { font-size: .84rem; color: #5C5236; padding-top: 8px; }
        .login-feature-text strong { display: block; color: var(--ink); font-size: .88rem; margin-bottom: 1px; }
        .login-main { flex: 1; display: flex; align-items: center; justify-content: center; padding: 32px; background: #fff; }
        .login-card { width: 100%; max-width: 400px; }
        @media (max-width: 991.98px) { .login-aside { display: none; } }
    </style>
</head>
<body>
    <section class="login-aside">
        <div class="d-flex align-items-center gap-3">
            <span class="sidebar-brand-icon"><i class="bi bi-award"></i></span>
            <div><div style="font-family:'Fraunces',serif;font-weight:600;color:var(--ink);letter-spacing:.01em;">SIARKA</div><div style="font-size:.75rem;color:#8A7C55">Politeknik Negeri Tanah Laut</div></div>
        </div>
        <div>
            <div class="gold-rule"></div>
            <h2>SIARKA</h2>
            <p class="fw-semibold" style="color:var(--ink);">Sistem Informasi Administrasi, Reservasi, Katalog, dan Aktivitas Laboratorium</p>
            <p>Pengelolaan alat, bahan, dan ruangan laboratorium dalam satu sistem yang tertata dan dapat dipercaya.</p>

            <div class="d-flex flex-column gap-3 mt-4" style="position:relative;max-width:420px;">
                <div class="login-feature">
                    <span class="login-feature-icon"><i class="bi bi-grid-3x3-gap"></i></span>
                    <span class="login-feature-text"><strong>Katalog terpusat</strong>Ketersediaan alat, bahan, dan ruangan dapat dilihat kapan saja.</span>
                </div>
                <div class="login-feature">
                    <span class="login-feature-icon"><i class="bi bi-people"></i></span>
                    <span class="login-feature-text"><strong>Akses sesuai peran</strong>Mahasiswa, Dosen, Staff Prodi, dan Laboran punya halaman masing-masing.</span>
                </div>
                <div class="login-feature">
                    <span class="login-feature-icon"><i class="bi bi-shield-check"></i></span>
                    <span class="login-feature-text"><strong>Data tertata rapi</strong>Setiap kategori pengguna dan barang dikelola secara terpisah dan konsisten.</span>
                </div>
            </div>
        </div>
        <div style="font-size:.75rem;color:#9A8C63;position:relative;">&copy; {{ date('Y') }} SIARKA &middot; Jurusan Komputer dan Bisnis</div>
    </section>

    <section class="login-main">
        <form method="POST" action="{{ route('login.attempt') }}" class="login-card">
            @csrf
            <h1 class="mb-1" style="font-family:'Fraunces',serif;font-weight:600;font-size:1.7rem;">Masuk</h1>
            <p class="mb-4" style="color:var(--text-muted);font-size:.88rem">Gunakan email dan password akun Anda.</p>

            @error('email')
                <div class="alert alert-danger py-2 d-flex align-items-center gap-2"><i class="bi bi-exclamation-circle-fill"></i><span>{{ $message }}</span></div>
            @enderror

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="nama@domain.ac.id" required autofocus>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" id="password" name="password" class="form-control" required>
                    <button type="button" class="btn btn-outline-silab" onclick="var i=document.getElementById('password');i.type=i.type==='password'?'text':'password'" aria-label="Tampilkan password"><i class="bi bi-eye"></i></button>
                </div>
                @error('password')<div class="text-danger mt-1" style="font-size:.78rem">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2">Masuk</button>
            <p class="mt-4 mb-0 text-center" style="font-size:.76rem;color:var(--text-muted)">Akun dikelola oleh Laboran/Admin. Tidak tersedia pendaftaran mandiri.</p>
        </form>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
