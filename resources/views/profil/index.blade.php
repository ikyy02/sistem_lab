@extends('layouts.app')

@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')
@section('page-subtitle', 'Kelola informasi identitas akun dan perbarui kata sandi Anda.')

@push('styles')
<style>
    /* Scope Halaman Profil: Latar #f8fafc, Kartu Putih Maks 760px Rata Kiri */
    .profil-page-container {
        max-width: 760px;
        margin-left: 0;
        margin-right: auto;
    }
    .profil-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 32px;
        box-shadow: none;
    }
    .profil-avatar-circle {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: #0f172a;
        color: #ffffff;
        font-size: 1.5rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    /* Tab Navigasi Minimalis */
    .profil-nav-tabs {
        display: flex;
        gap: 8px;
        border-bottom: 1px solid #e2e8f0;
        margin-bottom: 24px;
        padding-bottom: 0;
    }
    .profil-tab-btn {
        background: none;
        border: none;
        padding: 10px 16px;
        font-size: 14px;
        font-weight: 600;
        color: #64748b;
        border-bottom: 2px solid transparent;
        cursor: pointer;
        transition: color 150ms ease, border-color 150ms ease;
    }
    .profil-tab-btn:hover {
        color: #0f172a;
    }
    .profil-tab-btn.active {
        color: #2563eb;
        border-bottom-color: #2563eb;
    }
    /* Form & Input: Jarak antar-field 24px, Label 14px semibold, Input 46px radius 10px border #cbd5e1 */
    .profil-form-group {
        margin-bottom: 24px;
    }
    .profil-label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #0f172a;
        margin-bottom: 8px;
    }
    .profil-input {
        width: 100%;
        height: 46px;
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        padding: 0 14px;
        font-size: 14px;
        color: #0f172a;
        background-color: #ffffff;
        transition: border-color 150ms ease, box-shadow 150ms ease;
        outline: none;
    }
    .profil-input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }
    .profil-input.is-invalid {
        border-color: #dc2626;
    }
    .profil-error-msg {
        font-size: 12px;
        color: #dc2626;
        margin-top: 6px;
    }
    .profil-help-text {
        font-size: 12px;
        color: #64748b;
        margin-top: 6px;
    }
    /* Tombol Aksi */
    .profil-form-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
        padding-top: 20px;
        border-top: 1px solid #e2e8f0;
        margin-top: 24px;
    }
    .btn-profil-cancel {
        height: 46px;
        padding: 0 20px;
        border-radius: 10px;
        background: transparent;
        color: #64748b;
        border: 1px solid #cbd5e1;
        font-size: 14px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 150ms ease;
    }
    .btn-profil-cancel:hover {
        background-color: #f1f5f9;
        color: #0f172a;
        border-color: #94a3b8;
    }
    .btn-profil-submit {
        height: 46px;
        padding: 0 24px;
        border-radius: 10px;
        background-color: #2563eb;
        color: #ffffff;
        border: 1px solid #2563eb;
        font-size: 14px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background-color 150ms ease;
    }
    .btn-profil-submit:hover {
        background-color: #1d4ed8;
        border-color: #1d4ed8;
    }
    /* Checkbox Tampilkan Password */
    .profil-checkbox-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #64748b;
        cursor: pointer;
        user-select: none;
    }
    .profil-checkbox-label input[type="checkbox"] {
        width: 16px;
        height: 16px;
        accent-color: #2563eb;
        cursor: pointer;
    }
</style>
@endpush

@section('content')
<div class="profil-page-container">
    <div class="profil-card">
        {{-- Header Kartu: Avatar, Nama, Badge Peran, dan Teks Kecil Identitas --}}
        <div class="d-flex align-items-center gap-3 pb-4 border-bottom mb-4">
            <div class="profil-avatar-circle">
                {{ mb_strtoupper(mb_substr($profil->nama ?? 'U', 0, 1)) }}
            </div>
            <div class="min-w-0 flex-grow-1">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <h2 class="h5 fw-bold mb-0 text-truncate" style="color:#0f172a;font-size:1.2rem;">
                        {{ $profil->nama }}
                    </h2>
                    <span class="badge rounded-pill px-2.5 py-1" style="background:#e0f2fe;color:#0369a1;font-weight:600;font-size:0.75rem;">
                        <i class="bi bi-shield-check me-1"></i>{{ $roleLabel }}
                    </span>
                </div>
                <div style="font-size:13px;color:#64748b;">
                    {{ $idLabel }}: <strong style="color:#0f172a;">{{ $profil->getKey() }}</strong>
                    @if($prodi)
                        <span class="mx-1.5">·</span>
                        <span>{{ $prodi }}</span>
                    @endif
                </div>
            </div>
        </div>

        @php
            $adaErrorPassword = $errors->has('password_lama') || $errors->has('password_baru') || $errors->has('password_baru_confirmation');
        @endphp

        {{-- Tab Navigasi --}}
        <div class="profil-nav-tabs" role="tablist">
            <button type="button" class="profil-tab-btn {{ $adaErrorPassword ? '' : 'active' }}" id="tab-btn-biodata" onclick="bukaTabProfil('biodata')" role="tab" aria-selected="{{ $adaErrorPassword ? 'false' : 'true' }}">
                Informasi Akun
            </button>
            <button type="button" class="profil-tab-btn {{ $adaErrorPassword ? 'active' : '' }}" id="tab-btn-password" onclick="bukaTabProfil('password')" role="tab" aria-selected="{{ $adaErrorPassword ? 'true' : 'false' }}">
                Ubah Password
            </button>
        </div>

        {{-- Tab 1: Informasi Akun (Nama Lengkap, Email & WhatsApp 2 Kolom) --}}
        <div id="tab-panel-biodata" style="{{ $adaErrorPassword ? 'display:none;' : '' }}" role="tabpanel">
            <form method="POST" action="{{ route('profil.update') }}" autocomplete="off">
                @csrf @method('PUT')

                {{-- Nama Lengkap (Lebar Penuh) --}}
                <div class="profil-form-group">
                    <label class="profil-label" for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama', $profil->nama) }}" maxlength="100" class="profil-input @error('nama') is-invalid @enderror" placeholder="Masukkan nama lengkap" required>
                    @error('nama')<div class="profil-error-msg">{{ $message }}</div>@enderror
                </div>

                {{-- Alamat Email & Nomor WhatsApp (2 Kolom di Desktop, 1 Kolom di Layar Kecil) --}}
                <div class="row g-3 profil-form-group">
                    <div class="col-12 col-md-6">
                        <label class="profil-label" for="email">Alamat Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $profil->email) }}" maxlength="50" class="profil-input @error('email') is-invalid @enderror" placeholder="nama@email.com" required>
                        @error('email')<div class="profil-error-msg">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="profil-label" for="no_whatsapp">Nomor WhatsApp</label>
                        <input type="text" id="no_whatsapp" name="no_whatsapp" value="{{ old('no_whatsapp', $profil->no_whatsapp) }}" maxlength="20" placeholder="08xxxxxxxxxx" class="profil-input @error('no_whatsapp') is-invalid @enderror">
                        <div class="profil-help-text">Untuk notifikasi dan pengingat peminjaman.</div>
                        @error('no_whatsapp')<div class="profil-error-msg">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Tombol Batal & Simpan Perubahan (Rata Kanan, Dipisah Garis Tipis) --}}
                <div class="profil-form-actions">
                    <a href="{{ route('profil.index') }}" class="btn-profil-cancel">Batal</a>
                    <button type="submit" class="btn-profil-submit">Simpan Perubahan</button>
                </div>
            </form>
        </div>

        {{-- Tab 2: Ubah Password --}}
        <div id="tab-panel-password" style="{{ $adaErrorPassword ? '' : 'display:none;' }}" role="tabpanel">
            <form method="POST" action="{{ route('profil.password') }}" autocomplete="off">
                @csrf @method('PUT')

                {{-- Password Lama --}}
                <div class="profil-form-group">
                    <label class="profil-label" for="password_lama">Password Lama</label>
                    <input type="password" id="password_lama" name="password_lama" class="profil-input input-password-field @error('password_lama') is-invalid @enderror" placeholder="Masukkan password saat ini" required>
                    @error('password_lama')<div class="profil-error-msg">{{ $message }}</div>@enderror
                </div>

                {{-- Password Baru --}}
                <div class="profil-form-group">
                    <label class="profil-label" for="password_baru">Password Baru</label>
                    <input type="password" id="password_baru" name="password_baru" class="profil-input input-password-field @error('password_baru') is-invalid @enderror" placeholder="Masukkan password baru" required>
                    <div class="profil-help-text">Minimal 8 karakter, kombinasi huruf, angka, atau simbol.</div>
                    @error('password_baru')<div class="profil-error-msg">{{ $message }}</div>@enderror
                </div>

                {{-- Konfirmasi Password Baru --}}
                <div class="profil-form-group mb-3">
                    <label class="profil-label" for="password_baru_confirmation">Konfirmasi Password Baru</label>
                    <input type="password" id="password_baru_confirmation" name="password_baru_confirmation" class="profil-input input-password-field @error('password_baru_confirmation') is-invalid @enderror" placeholder="Ulangi password baru" required>
                    @error('password_baru_confirmation')<div class="profil-error-msg">{{ $message }}</div>@enderror
                </div>

                {{-- Checkbox Tampilkan Password Tunggal --}}
                <div class="profil-form-group mb-0">
                    <label class="profil-checkbox-label" for="toggleShowPassword">
                        <input type="checkbox" id="toggleShowPassword" onchange="toggleAllPasswords(this.checked)">
                        <span>Tampilkan password</span>
                    </label>
                </div>

                {{-- Tombol Batal & Perbarui Password (Rata Kanan, Dipisah Garis Tipis) --}}
                <div class="profil-form-actions">
                    <a href="{{ route('profil.index') }}" class="btn-profil-cancel">Batal</a>
                    <button type="submit" class="btn-profil-submit">Perbarui Password</button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    function bukaTabProfil(namaTab) {
        const panelBio = document.getElementById('tab-panel-biodata');
        const panelPass = document.getElementById('tab-panel-password');
        const btnBio = document.getElementById('tab-btn-biodata');
        const btnPass = document.getElementById('tab-btn-password');

        if (namaTab === 'password') {
            panelBio.style.display = 'none';
            panelPass.style.display = 'block';
            btnBio.classList.remove('active');
            btnPass.classList.add('active');
            btnBio.setAttribute('aria-selected', 'false');
            btnPass.setAttribute('aria-selected', 'true');
        } else {
            panelBio.style.display = 'block';
            panelPass.style.display = 'none';
            btnBio.classList.add('active');
            btnPass.classList.remove('active');
            btnBio.setAttribute('aria-selected', 'true');
            btnPass.setAttribute('aria-selected', 'false');
        }
    }

    function toggleAllPasswords(show) {
        const passwordInputs = document.querySelectorAll('.input-password-field');
        passwordInputs.forEach(input => {
            input.type = show ? 'text' : 'password';
        });
    }

    @if($adaErrorPassword)
        document.addEventListener('DOMContentLoaded', function () {
            bukaTabProfil('password');
        });
    @endif
</script>
@endpush
