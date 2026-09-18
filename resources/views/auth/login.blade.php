<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SILAB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            border-radius: 1rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            overflow: hidden;
            background: #fff;
        }
        .login-header {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            padding: 2rem;
            text-align: center;
            color: #fff;
        }
        .login-header .icon {
            width: 64px; height: 64px;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.8rem;
        }
        .login-body { padding: 2rem; }
        .form-control:focus { border-color: #16a34a; box-shadow: 0 0 0 0.2rem rgba(22,163,74,0.15); }
        .btn-login {
            background: linear-gradient(135deg, #16a34a, #15803d);
            border: 0; color: #fff; font-weight: 600;
            border-radius: 10px;
            padding: 0.65rem;
            transition: opacity 0.2s;
        }
        .btn-login:hover { opacity: 0.9; color: #fff; }
        .form-control { border-radius: 10px; }
    </style>
</head>
<body>
<div class="container px-3">
    <div class="login-card card mx-auto border-0">
        <div class="login-header">
            <div class="icon"><i class="bi bi-eyedropper"></i></div>
            <h4 class="fw-bold mb-1">SILAB</h4>
            <p class="mb-0 opacity-75" style="font-size:0.85rem;">Laboratorium Komputer Bisnis</p>
        </div>

        <div class="login-body">
            @if($errors->any())
                <div class="alert alert-danger py-2 rounded-3" style="font-size:0.85rem;">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success py-2 rounded-3" style="font-size:0.85rem;">
                    <i class="bi bi-check-circle-fill me-1"></i>{{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ url('/login') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold mb-1" style="font-size:0.875rem; color:#374151;">Email / Username</label>
                    <div class="input-group" style="border-radius:10px; overflow:hidden;">
                        <span class="input-group-text bg-white border-end-0" style="border-color:#e2e8f0; color:#94a3b8;">
                            <i class="bi bi-person"></i>
                        </span>
                        <input type="text"
                               name="email"
                               class="form-control border-start-0 @error('email') is-invalid @enderror"
                               placeholder="Email atau username"
                               value="{{ old('email') }}"
                               autofocus
                               autocomplete="username"
                               style="border-color:#e2e8f0;">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold mb-1" style="font-size:0.875rem; color:#374151;">Password</label>
                    <div class="input-group" style="border-radius:10px; overflow:hidden;">
                        <span class="input-group-text bg-white border-end-0" style="border-color:#e2e8f0; color:#94a3b8;">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input type="password"
                               name="password"
                               id="passwordInput"
                               class="form-control border-start-0 @error('password') is-invalid @enderror"
                               placeholder="Masukkan password"
                               autocomplete="current-password"
                               style="border-color:#e2e8f0;">
                        <button class="btn btn-outline-secondary" type="button" style="border-color:#e2e8f0;" onclick="togglePassword()" tabindex="-1">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4 d-flex align-items-center justify-content-between">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember" style="font-size:0.85rem;">Ingat saya</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-login w-100">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
                </button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function togglePassword() {
        const input = document.getElementById('passwordInput');
        const icon  = document.getElementById('toggleIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye';
        }
    }
</script>
</body>
</html>