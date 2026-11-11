@extends('layouts.app')

@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')
@section('page-subtitle', 'Lihat dan ubah data profil serta password Anda.')

@section('content')
<div class="row g-3 align-items-stretch">
    {{-- Kartu Kiri: Data Profil --}}
    <div class="col-12 col-lg-7 d-flex flex-column">
        <form method="POST" action="{{ route('profil.update') }}" class="card-modern p-4 d-flex flex-column h-100" autocomplete="off">
            @csrf @method('PUT')

            {{-- Header Profil: Avatar Inisial, Nama, Peran --}}
            <div class="d-flex align-items-center gap-3 pb-3 mb-3 border-bottom">
                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-primary"
                     style="width:52px;height:52px;background:var(--info-soft);color:#0369A1!important;font-size:1.3rem;flex-shrink:0;">
                    {{ mb_strtoupper(mb_substr($profil->nama ?? 'U', 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <h2 class="h5 fw-bold mb-1 text-truncate" style="color:var(--text-primary);font-size:1.05rem;">{{ $profil->nama }}</h2>
                    <span class="badge" style="background:var(--bg-subtle);color:var(--text-secondary);border:1px solid var(--border);font-size:var(--fs-xs);font-weight:500;">
                        {{ $roleLabel }}
                    </span>
                </div>
            </div>

            <div class="row g-3 flex-grow-1">
                <div class="col-12 col-md-6">
                    <label class="form-label" for="profil_id">{{ $idLabel }}</label>
                    <input type="text" id="profil_id" class="form-control" value="{{ $profil->getKey() }}" readonly tabindex="-1"
                           style="background:var(--bg-subtle);cursor:not-allowed;color:var(--text-secondary);">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label" for="profil_role">Peran</label>
                    <input type="text" id="profil_role" class="form-control" value="{{ $roleLabel }}" readonly tabindex="-1"
                           style="background:var(--bg-subtle);cursor:not-allowed;color:var(--text-secondary);">
                </div>
                @if($prodi)
                <div class="col-12">
                    <label class="form-label" for="profil_prodi">Program Studi</label>
                    <input type="text" id="profil_prodi" class="form-control" value="{{ $prodi }}" readonly tabindex="-1"
                           style="background:var(--bg-subtle);cursor:not-allowed;color:var(--text-secondary);">
                </div>
                @endif
                <div class="col-12">
                    <label class="form-label" for="nama">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama', $profil->nama) }}" maxlength="100" class="form-control @error('nama') is-invalid @enderror">
                    @error('nama')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label" for="email">Alamat Email <span class="text-danger">*</span></label>
                    <input type="email" id="email" name="email" value="{{ old('email', $profil->email) }}" maxlength="50" class="form-control @error('email') is-invalid @enderror">
                    @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label" for="no_whatsapp">Nomor WhatsApp</label>
                    <input type="text" id="no_whatsapp" name="no_whatsapp" value="{{ old('no_whatsapp', $profil->no_whatsapp) }}" maxlength="20" placeholder="08xxxxxxxxxx" class="form-control @error('no_whatsapp') is-invalid @enderror">
                    @error('no_whatsapp')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mt-auto pt-4">
                <button type="submit" class="btn btn-primary">Simpan Profil</button>
            </div>
        </form>
    </div>

    {{-- Kartu Kanan: Ubah Password --}}
    <div class="col-12 col-lg-5 d-flex flex-column">
        <form method="POST" action="{{ route('profil.password') }}" class="card-modern p-4 d-flex flex-column h-100" autocomplete="off">
            @csrf @method('PUT')

            <div class="pb-3 mb-3 border-bottom">
                <h2 class="h5 fw-bold mb-1" style="font-size:1.05rem;color:var(--text-primary);">Ubah Password</h2>
                <p class="mb-0 text-muted" style="font-size:var(--fs-base);">Pastikan menggunakan password yang aman dan unik.</p>
            </div>

            <div class="row g-3 flex-grow-1">
                @foreach([['password_lama', 'Password Lama'], ['password_baru', 'Password Baru'], ['password_baru_confirmation', 'Konfirmasi Password Baru']] as [$name, $text])
                <div class="col-12">
                    <label class="form-label" for="{{ $name }}">{{ $text }} <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" id="{{ $name }}" name="{{ $name }}" class="form-control @error($name) is-invalid @enderror">
                        <button class="btn btn-outline-silab px-3" type="button" onclick="togglePasswordVisibility('{{ $name }}', this)" title="Tampilkan/sembunyikan password" aria-label="Tampilkan atau sembunyikan password">
                            <i class="bi bi-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                    @if($name === 'password_baru')
                        <div class="form-text mt-1" style="font-size:var(--fs-xs);color:var(--text-muted);">Minimal 8 karakter.</div>
                    @endif
                    @error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                @endforeach
            </div>

            <div class="mt-auto pt-4">
                <button type="submit" class="btn btn-primary">Ubah Password</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePasswordVisibility(fieldId, btn) {
        const input = document.getElementById(fieldId);
        if (!input) return;
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        const icon = btn.querySelector('i');
        if (icon) {
            icon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
        }
    }
</script>
@endpush
