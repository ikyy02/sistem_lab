<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — SIARKA</title>
    <meta name="application-name" content="SIARKA">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    @include('layouts._theme')
    <style>
        html { scrollbar-gutter: auto; }
        body { min-height: 100vh; background: var(--gold-tint); }
        .lg-wrap { min-height: 100vh; display: grid; grid-template-columns: minmax(0, 5fr) minmax(0, 6fr); }
        .lg-side { background: var(--ink); color: #fff; padding: 48px; display: flex; flex-direction: column; justify-content: space-between;
            background-image: linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px); background-size: 44px 44px; }
        .lg-brand { display: flex; align-items: center; gap: 12px; text-decoration: none; color: #fff; }
        .lg-brand-name { font-family: 'Fraunces', serif; font-weight: 600; font-size: 1.3rem; letter-spacing: .02em; line-height: 1.1; }
        .lg-side h1 { font-family: 'Fraunces', serif; font-weight: 600; font-size: clamp(1.7rem, 2.6vw, 2.3rem); line-height: 1.2; margin: 0 0 14px; }
        .lg-side h1 em { font-style: normal; color: var(--gold-bright); }
        .lg-side p { color: #D6D3CB; line-height: 1.7; max-width: 400px; margin: 0; }
        .lg-main { display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 40px 24px; }
        .lg-top { width: 100%; max-width: 440px; margin-bottom: 16px; }
        .lg-back { display: inline-flex; align-items: center; gap: 8px; color: var(--ink); text-decoration: none; font-size: .86rem; font-weight: 600; padding: 6px 2px; }
        .lg-back:hover { color: var(--gold); }
        .lg-card { width: 100%; max-width: 440px; background: #fff; border: 1px solid var(--border-color); border-radius: 16px; padding: 34px; box-shadow: 0 18px 40px rgba(43,42,40,.08); }
        .lg-card h2 { font-family: 'Fraunces', serif; font-weight: 600; font-size: 1.7rem; margin-bottom: 4px; }
        .lg-error { min-height: 0; }
        @media (max-width: 991.98px) {
            .lg-wrap { display: block; }
            .lg-side { display: none; }
            .lg-main { min-height: 100vh; padding: 28px 16px; }
            .lg-mobile-brand { display: flex !important; }
        }
        @media (max-width: 575.98px) { .lg-card { padding: 24px 20px; } }
        .lg-mobile-brand { display: none; align-items: center; gap: 12px; margin-bottom: 18px; text-decoration: none; color: var(--ink); width: 100%; max-width: 440px; }
    </style>
</head>
<body>
<div class="lg-wrap">
    <aside class="lg-side">
        <a href="{{ route('landing') }}" class="lg-brand">
            <span class="lg-brand-name">SIARKA</span>
        </a>
        <div>
            <h1>Laboratorium Komputer Politala <em>dalam satu sistem</em>.</h1>
            <p>Sistem Informasi Administrasi, Reservasi, Katalog, dan Aktivitas Laboratorium — Jurusan Komputer dan Bisnis.</p>
        </div>
        <div style="font-size:.78rem;color:#9C988C;">&copy; {{ date('Y') }} SIARKA &middot; Politeknik Negeri Tanah Laut</div>
    </aside>

    <main class="lg-main">
        <a href="{{ route('landing') }}" class="lg-mobile-brand">
            <span class="lg-brand-name" style="color:var(--ink);">SIARKA</span>
        </a>
        <div class="lg-top">
            <a href="{{ route('landing') }}" class="lg-back"><i class="bi bi-arrow-left"></i> Kembali ke Beranda</a>
        </div>

        <form method="POST" action="{{ route('login.attempt') }}" class="lg-card" id="formLogin">
            @csrf
            <h2>Masuk</h2>
            <p class="mb-4" style="color:var(--text-muted);font-size:.88rem;">Gunakan email dan password akun Anda.</p>

            @error('email')
                <div class="alert alert-danger login-error py-2 d-flex align-items-center gap-2" role="alert"><i class="bi bi-exclamation-circle-fill"></i><span>{{ $message }}</span></div>
            @enderror

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="nama@domain.ac.id" required autocomplete="username" autofocus>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password">
                    <button type="button" class="btn btn-outline-silab" id="togglePassword" aria-label="Tampilkan password"><i class="bi bi-eye"></i></button>
                </div>
                @error('password')<div class="text-danger mt-1" style="font-size:.78rem">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2">Masuk</button>
            <p class="mt-4 mb-0 text-center" style="font-size:.76rem;color:var(--text-muted)">Akun dikelola oleh Laboran/Admin. Tidak tersedia pendaftaran mandiri.</p>
        </form>
    </main>
</div>

<script>
    document.getElementById('togglePassword').addEventListener('click', function () {
        const i = document.getElementById('password');
        const tampil = i.type === 'password';
        i.type = tampil ? 'text' : 'password';
        this.setAttribute('aria-label', tampil ? 'Sembunyikan password' : 'Tampilkan password');
        this.firstElementChild.className = tampil ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
    // Kunci tombol Masuk saat dikirim (cegah kirim ganda) tanpa mengubah ukuran tombol.
    document.getElementById('formLogin').addEventListener('submit', function (e) {
        if (this.dataset.terkirim === '1') { e.preventDefault(); return; }
        this.dataset.terkirim = '1';
        const b = this.querySelector('button[type=submit]');
        setTimeout(function () { b.disabled = true; b.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Memproses...'; }, 0);
    });
    window.addEventListener('pageshow', function () {
        const f = document.getElementById('formLogin'); const b = f.querySelector('button[type=submit]');
        delete f.dataset.terkirim; b.disabled = false; b.textContent = 'Masuk';
    });
</script>
</body>
</html>
