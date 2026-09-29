<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — SILAB</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    @include('layouts._theme')
    <style>
        body { min-height: 100vh; display: flex; background: #fff; }
        .login-aside { flex: 1.15; background: var(--ink); color: #fff; padding: 60px; display: flex; flex-direction: column; justify-content: space-between; position: relative; overflow: hidden; }
        .login-aside::before { content: ''; position: absolute; right: -120px; top: -120px; width: 340px; height: 340px; border-radius: 50%; border: 1px solid var(--ink-line); }
        .login-aside::after { content: ''; position: absolute; right: -60px; top: -60px; width: 220px; height: 220px; border-radius: 50%; border: 1px solid rgba(173,138,62,.35); }
        .gold-rule { width: 46px; height: 3px; background: var(--gold); border-radius: 2px; margin: 22px 0 18px; }
        .login-aside h2 { font-family: 'Fraunces', serif; font-weight: 600; font-size: 2.15rem; line-height: 1.28; max-width: 460px; position: relative; }
        .login-aside p { color: #9BA5B2; max-width: 420px; font-size: .95rem; position: relative; }
        .login-main { flex: 1; display: flex; align-items: center; justify-content: center; padding: 32px; }
        .login-card { width: 100%; max-width: 400px; }
        @media (max-width: 991.98px) { .login-aside { display: none; } }
    </style>
</head>
<body>
    <section class="login-aside">
        <div class="d-flex align-items-center gap-3">
            <span class="sidebar-brand-icon"><i class="bi bi-award"></i></span>
            <div><div class="fw-semibold text-white" style="font-family:'Fraunces',serif;letter-spacing:.02em;">SILAB</div><div style="font-size:.75rem;color:#8891A0">Politeknik Negeri Tanah Laut</div></div>
        </div>
        <div>
            <div class="gold-rule"></div>
            <h2>Laboratorium Jurusan Komputer &amp; Bisnis</h2>
            <p>Pengelolaan alat, bahan, dan ruangan laboratorium dalam satu sistem yang tertata dan dapat dipercaya.</p>
        </div>
        <div style="font-size:.75rem;color:#6B7684;position:relative;">&copy; {{ date('Y') }} Jurusan Komputer dan Bisnis</div>
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
