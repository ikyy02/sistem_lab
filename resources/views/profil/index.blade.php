@extends('layouts.app')

@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')
@section('page-subtitle', 'Ubah foto, nomor WhatsApp, dan password akun Anda.')

@push('styles')
<style>
    .profil-page-container { max-width: 1080px; margin-inline: auto; }
    .profil-page-grid { display: grid; grid-template-columns: minmax(0, 1.1fr) minmax(320px, .9fr); gap: 20px; align-items: start; }
    .profil-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 26px; box-shadow: none; min-width: 0; }
    .profil-avatar-circle, .profil-avatar-img { width: 76px; height: 76px; border-radius: 50%; flex-shrink: 0; }
    .profil-avatar-circle { background: #0f172a; color: #fff; font-size: 1.7rem; font-weight: 700; display:flex; align-items:center; justify-content:center; }
    .profil-avatar-img { object-fit: cover; display: block; border: 1px solid #e2e8f0; }
    .profil-foto-actions { margin-top: 10px; }
    .btn-foto-profil { height: 34px; padding: 0 14px; border-radius: 8px; background: #fff; color: #2563eb; border: 1px solid #cbd5e1; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; }
    .btn-foto-profil:hover { background: #eff6ff; border-color: #2563eb; }
    .profil-section-title { font-size: 1.05rem; font-weight: 700; color:#0f172a; margin: 0 0 5px; }
    .profil-section-help { font-size: .82rem; color:#64748b; margin: 0 0 22px; }
    .profil-form-group { margin-bottom: 18px; }
    .profil-label { display:block; font-size:14px; font-weight:600; color:#0f172a; margin-bottom:7px; }
    .profil-input { width:100%; min-height:44px; border-radius:9px; border:1px solid #cbd5e1; padding:9px 12px; font-size:14px; color:#0f172a; background:#fff; }
    .profil-input:focus { border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.13); outline:0; }
    .profil-input[readonly] { background:#f8fafc; color:#475569; cursor:default; }
    .profil-input.is-invalid { border-color:#dc2626; }
    .profil-help-text { font-size:12px; color:#64748b; margin-top:6px; }
    .profil-error-msg { font-size:12px; color:#dc2626; margin-top:6px; }
    .profil-form-actions { display:flex; justify-content:flex-end; gap:10px; padding-top:18px; border-top:1px solid #e2e8f0; margin-top:20px; }
    .btn-profil-submit { min-height:42px; padding:0 20px; border-radius:9px; background:#0f172a; color:#fff; border:1px solid #0f172a; font-size:14px; font-weight:600; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; }
    .btn-profil-submit:hover { background:#1e293b; }
    .profil-checkbox-label { display:inline-flex; align-items:center; gap:8px; font-size:13px; color:#64748b; cursor:pointer; user-select:none; }
    .profil-checkbox-label input { width:16px; height:16px; accent-color:#2563eb; }
    @media (max-width: 767.98px) { .profil-page-grid { grid-template-columns:1fr; } .profil-card { padding:20px; } }
</style>
@endpush

@section('content')
@php $adaErrorPassword = $errors->has('password_lama') || $errors->has('password_baru') || $errors->has('password_baru_confirmation'); @endphp
<div class="profil-page-container">
    <div class="profil-page-grid">
        <section class="profil-card" aria-labelledby="profil-identitas-title">
            <div class="d-flex align-items-center gap-3 pb-4 border-bottom mb-4">
                <div class="flex-shrink-0">
                    @if($fotoUrl)
                        <img src="{{ $fotoUrl }}" alt="Foto profil {{ $profil->nama }}" class="profil-avatar-img">
                    @else
                        <div class="profil-avatar-circle" aria-hidden="true">{{ mb_strtoupper(mb_substr($profil->nama ?? 'U', 0, 1)) }}</div>
                    @endif
                </div>
                <div class="min-w-0 flex-grow-1">
                    <h2 class="h5 fw-bold mb-1 text-truncate" id="profil-identitas-title" style="color:#0f172a;">{{ $profil->nama }}</h2>
                    <span class="badge rounded-pill px-2 py-1" style="background:#e0f2fe;color:#0369a1;font-weight:600;font-size:.75rem;">{{ $roleLabel }}</span>
                    <div class="small mt-1" style="color:#64748b;">{{ $idLabel }}: <strong style="color:#0f172a;">{{ $profil->getKey() }}</strong>@if($prodi)<span class="mx-1">·</span>{{ $prodi }}@endif</div>
                    <form method="POST" action="{{ route('profil.foto') }}" enctype="multipart/form-data" class="profil-foto-actions">
                        @csrf
                        <input type="file" name="foto" id="inputFotoProfil" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="d-none" onchange="this.closest('form').submit()">
                        <button type="button" class="btn-foto-profil" onclick="document.getElementById('inputFotoProfil').click()">
                            <i class="bi bi-{{ $fotoUrl ? 'camera' : 'plus-lg' }} me-1" aria-hidden="true"></i>{{ $fotoUrl ? 'Ganti Foto' : 'Tambah Foto' }}
                        </button>
                    </form>
                    @error('foto')<div class="profil-error-msg">{{ $message }}</div>@enderror
                </div>
            </div>

            <h3 class="profil-section-title">Informasi Akun</h3>
            <p class="profil-section-help">Nama, nomor identitas, program studi, dan email hanya dapat dilihat.</p>
            <div class="profil-form-group">
                <label class="profil-label" for="id-profil">{{ $idLabel }}</label>
                <input type="text" id="id-profil" value="{{ $profil->getKey() }}" class="profil-input" readonly>
            </div>
            @if($prodi)
            <div class="profil-form-group">
                <label class="profil-label" for="prodi-profil">Program Studi</label>
                <input type="text" id="prodi-profil" value="{{ $prodi }}" class="profil-input" readonly>
            </div>
            @endif
            <div class="profil-form-group">
                <label class="profil-label" for="nama-profil">Nama Lengkap</label>
                <input type="text" id="nama-profil" value="{{ $profil->nama }}" class="profil-input" readonly>
            </div>
            <div class="profil-form-group">
                <label class="profil-label" for="email-profil">Alamat Email</label>
                <input type="email" id="email-profil" value="{{ $profil->email }}" class="profil-input" readonly>
            </div>
            <form method="POST" action="{{ route('profil.update') }}" autocomplete="off">
                @csrf @method('PUT')
                <div class="profil-form-group mb-0">
                    <label class="profil-label" for="no_whatsapp">Nomor WhatsApp</label>
                    <input type="text" id="no_whatsapp" name="no_whatsapp" value="{{ old('no_whatsapp', $profil->no_whatsapp) }}" maxlength="20" placeholder="08xxxxxxxxxx" class="profil-input @error('no_whatsapp') is-invalid @enderror">
                    <div class="profil-help-text">Digunakan untuk notifikasi dan pengingat peminjaman.</div>
                    @error('no_whatsapp')<div class="profil-error-msg">{{ $message }}</div>@enderror
                </div>
                <div class="profil-form-actions">
                    <button type="submit" class="btn-profil-submit">Simpan Nomor WhatsApp</button>
                </div>
            </form>
        </section>

        <section class="profil-card" aria-labelledby="ubah-password-title">
            <h2 class="profil-section-title" id="ubah-password-title">Ubah Password</h2>
            <p class="profil-section-help">Gunakan password baru yang aman dan mudah Anda ingat.</p>
            <form method="POST" action="{{ route('profil.password') }}" autocomplete="off">
                @csrf @method('PUT')
                <div class="profil-form-group">
                    <label class="profil-label" for="password_lama">Password Lama</label>
                    <input type="password" id="password_lama" name="password_lama" class="profil-input input-password-field @error('password_lama') is-invalid @enderror" placeholder="Masukkan password saat ini" required>
                    @error('password_lama')<div class="profil-error-msg">{{ $message }}</div>@enderror
                </div>
                <div class="profil-form-group">
                    <label class="profil-label" for="password_baru">Password Baru</label>
                    <input type="password" id="password_baru" name="password_baru" class="profil-input input-password-field @error('password_baru') is-invalid @enderror" placeholder="Masukkan password baru" required>
                    <div class="profil-help-text">Minimal 8 karakter.</div>
                    @error('password_baru')<div class="profil-error-msg">{{ $message }}</div>@enderror
                </div>
                <div class="profil-form-group">
                    <label class="profil-label" for="password_baru_confirmation">Konfirmasi Password Baru</label>
                    <input type="password" id="password_baru_confirmation" name="password_baru_confirmation" class="profil-input input-password-field @error('password_baru_confirmation') is-invalid @enderror" placeholder="Ulangi password baru" required>
                    @error('password_baru_confirmation')<div class="profil-error-msg">{{ $message }}</div>@enderror
                </div>
                <div class="profil-form-group mb-0">
                    <label class="profil-checkbox-label" for="toggleShowPassword">
                        <input type="checkbox" id="toggleShowPassword" onchange="toggleAllPasswords(this.checked)">
                        <span>Tampilkan password</span>
                    </label>
                </div>
                <div class="profil-form-actions">
                    <button type="submit" class="btn-profil-submit">Perbarui Password</button>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleAllPasswords(show) {
        document.querySelectorAll('.input-password-field').forEach(input => { input.type = show ? 'text' : 'password'; });
    }
</script>
@endpush
