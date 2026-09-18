<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f8fafc; }
    </style>
</head>
<body>
<div class="min-vh-100 d-flex align-items-center justify-content-center">
    <div class="text-center px-3">
        <div class="display-1 fw-bold mb-3" style="color:#16a34a;">403</div>
        <span style="display:inline-flex;align-items:center;justify-content:center;width:72px;height:72px;background:#f0fdf4;border-radius:50%;">
            <i class="bi bi-shield-x" style="color:#e11d48; font-size:2.2rem;"></i>
        </span>
        <h2 class="fw-bold mt-3 mb-2">Akses Ditolak</h2>
        <p class="text-muted mb-4">{{ $exception->getMessage() ?: 'Anda tidak memiliki izin untuk mengakses halaman ini.' }}</p>
        <div class="d-flex gap-2 justify-content-center">
            <a href="{{ url()->previous() }}" class="btn d-flex align-items-center gap-2" style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;border-radius:10px;font-weight:600;padding:9px 20px;">
                <i class="bi bi-arrow-left"></i>Kembali
            </a>
            @auth
            <a href="{{ route('dashboard') }}" class="btn d-flex align-items-center gap-2" style="background:#16a34a;color:#fff;border-radius:10px;font-weight:600;padding:9px 20px;">
                <i class="bi bi-speedometer2"></i>Dashboard
            </a>
            @endauth
        </div>
    </div>
</div>
</body>
</html>