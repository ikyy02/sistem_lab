<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — SILAB</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    @include('layouts._theme')
    <style>
        body { min-height: 100vh; display: flex; }
        .login-aside { flex: 1.1; background: var(--primary); color: #fff; padding: 56px; display: flex; flex-direction: column; justify-content: space-between; border-bottom: 6px solid var(--accent); }
        .login-aside h2 { font-weight: 800; font-size: 2rem; line-height: 1.25; max-width: 460px; }
        .login-aside p { color: #c7d2f0; max-width: 440px; font-size: .95rem; }
        .login-main { flex: 1; display: flex; align-items: center; justify-content: center; padding: 32px; }
        .login-card { width: 100%; max-width: 400px; }
        .brand-mark { width: 46px; height: 46px; background: #fff; color: var(--primary); border-radius: 11px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; }
        @media (max-width: 991.98px) { .login-aside { display: none; } }
    </style>
</head>
<body>
    <section class="login-aside">
        <div class="d-flex align-items-center gap-3">
            <span class="brand-mark"><i class="bi bi-buildings"></i></span>
            <div><div class="fw-bold" style="letter-spacing:.05em">SILAB</div><div style="font-size:.75rem;color:#c7d2f0">Politeknik Negeri Tanah Laut</div></div>
        </div>
        <div>
            <h2>Sistem Informasi Laboratorium Jurusan Komputer dan Bisnis</h2>
            <p>Pengelolaan alat, bahan, ruangan, serta peminjaman laboratorium dalam satu sistem terpadu.</p>
        </div>
        <div style="font-size:.75rem;color:#9fb0e0">&copy; {{ date('Y') }} Jurusan Komputer dan Bisnis</div>
    </section>

    <section class="login-main">
        <form method="POST" action="{{ route('login.attempt') }}" class="login-card">
            @csrf
            <h1 class="fw-bold mb-1" style="font-size:1.6rem">Masuk</h1>
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
