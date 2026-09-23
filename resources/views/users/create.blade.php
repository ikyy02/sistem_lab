@extends('layouts.app')

@section('title', 'Tambah User')
@section('page-title', 'Tambah User')
@section('page-subtitle', 'Tambah pengguna baru ke sistem laboratorium komputer bisnis.')

@section('content')

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb mb-0" style="font-size:0.8rem; color:#94a3b8;">
        <li class="breadcrumb-item">
            <a href="{{ route('users.index') }}" style="color:#64748b; text-decoration:none;">
                <i class="bi bi-people me-1"></i>Kelola User
            </a>
        </li>
        <li class="breadcrumb-item active" style="color:#1e293b; font-weight:600;">Tambah User</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8 col-xl-7">
        <div class="card-modern overflow-hidden">

            <!-- Form Header -->
            <div class="px-4 py-3 d-flex align-items-center gap-3" style="border-bottom:1px solid #e2e8f0; background:#fafafa;">
                <div style="width:38px;height:38px;background:#f0fdf4;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#16a34a;flex-shrink:0;">
                    <i class="bi bi-person-plus-fill"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size:0.95rem; color:#1e293b;">Form Tambah User</div>
                    <div style="font-size:0.75rem; color:#64748b;">Isi semua field yang diperlukan</div>
                </div>
            </div>

            <form method="POST" action="{{ route('users.store') }}" id="userForm" class="p-4">
                @csrf

                @if($errors->any())
                <div class="alert alert-danger d-flex align-items-start gap-2 mb-4 rounded-3" role="alert" style="font-size:0.83rem;">
                    <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                    <div>
                        <strong>Terdapat kesalahan input:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif

                {{-- ── Informasi Dasar ── --}}
                <h6 class="fw-semibold text-muted mb-3 d-flex align-items-center gap-2" style="font-size:0.85rem;">
                    <i class="bi bi-person"></i>Informasi Dasar
                </h6>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">
                            Nama Lengkap <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}"
                               placeholder="Nama lengkap pengguna"
                               style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px;">
                        @error('name')
                            <div class="invalid-feedback d-flex align-items-center gap-1 mt-1" style="font-size:0.78rem;">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">
                            Username <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="username"
                               class="form-control @error('username') is-invalid @enderror"
                               value="{{ old('username') }}"
                               placeholder="Username unik"
                               style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px;">
                        @error('username')
                            <div class="invalid-feedback d-flex align-items-center gap-1 mt-1" style="font-size:0.78rem;">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">
                            Email <span class="text-danger">*</span>
                        </label>
                        <input type="email"
                               name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}"
                               placeholder="contoh@email.com"
                               style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px;">
                        @error('email')
                            <div class="invalid-feedback d-flex align-items-center gap-1 mt-1" style="font-size:0.78rem;">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">
                            No HP <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="no_hp"
                               required
                               class="form-control @error('no_hp') is-invalid @enderror"
                               value="{{ old('no_hp') }}"
                               placeholder="08xxxxxxxxxx"
                               style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px;">
                        @error('no_hp')
                            <div class="invalid-feedback d-flex align-items-center gap-1 mt-1" style="font-size:0.78rem;">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                {{-- ── Password ── --}}
                <h6 class="fw-semibold text-muted mb-3 d-flex align-items-center gap-2" style="font-size:0.85rem;">
                    <i class="bi bi-lock"></i>Password
                </h6>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">
                            Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group" style="border-radius:10px; overflow:hidden;">
                            <input type="password"
                                   name="password"
                                   id="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Minimal 8 karakter"
                                   style="border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px;">
                            <button class="btn btn-outline-secondary" type="button" style="border-color:#e2e8f0;" onclick="togglePwd('password','eyeIcon1')">
                                <i class="bi bi-eye" id="eyeIcon1"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-flex align-items-center gap-1 mt-1" style="font-size:0.78rem; display:block;">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">
                            Konfirmasi Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group" style="border-radius:10px; overflow:hidden;">
                            <input type="password"
                                   name="password_confirmation"
                                   id="password_confirmation"
                                   class="form-control"
                                   placeholder="Ulangi password"
                                   style="border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px;">
                            <button class="btn btn-outline-secondary" type="button" style="border-color:#e2e8f0;" onclick="togglePwd('password_confirmation','eyeIcon2')">
                                <i class="bi bi-eye" id="eyeIcon2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ── Role & Status ── --}}
                <h6 class="fw-semibold text-muted mb-3 d-flex align-items-center gap-2" style="font-size:0.85rem;">
                    <i class="bi bi-shield"></i>Role & Status
                </h6>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">
                            Role <span class="text-danger">*</span>
                        </label>
                        <select name="role"
                                id="roleSelect"
                                class="form-select @error('role') is-invalid @enderror"
                                onchange="handleRoleChange(this.value)"
                                style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px;">
                            <option value="">— Pilih Role —</option>
                            <option value="mahasiswa"  {{ old('role') === 'mahasiswa'  ? 'selected' : '' }}>Mahasiswa</option>
                            <option value="dosen"      {{ old('role') === 'dosen'      ? 'selected' : '' }}>Dosen</option>
                            <option value="staf_prodi" {{ old('role') === 'staf_prodi' ? 'selected' : '' }}>Staf Prodi</option>
                            <option value="laboran"    {{ old('role') === 'laboran'    ? 'selected' : '' }}>Laboran</option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback d-flex align-items-center gap-1 mt-1" style="font-size:0.78rem;">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">
                            Status <span class="text-danger">*</span>
                        </label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror"
                                style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px;">
                            <option value="aktif"    {{ old('status', 'aktif') === 'aktif'    ? 'selected' : '' }}>Aktif</option>
                            <option value="nonaktif" {{ old('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback d-flex align-items-center gap-1 mt-1" style="font-size:0.78rem;">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                {{-- ── Nomor Identitas (dinamis) ── --}}
                <div id="identitasSection" class="row g-3 mb-4">
                    <div class="col-md-6" id="nimField" style="display:none;">
                        <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">
                            NIM <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="nim"
                               class="form-control @error('nim') is-invalid @enderror"
                               value="{{ old('nim') }}"
                               placeholder="Nomor Induk Mahasiswa"
                               style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px;">
                        @error('nim')
                            <div class="invalid-feedback d-flex align-items-center gap-1 mt-1" style="font-size:0.78rem;">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-6" id="nidnField" style="display:none;">
                        <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">
                            NIDN <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="nidn"
                               class="form-control @error('nidn') is-invalid @enderror"
                               value="{{ old('nidn') }}"
                               placeholder="Nomor Induk Dosen Nasional"
                               style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px;">
                        @error('nidn')
                            <div class="invalid-feedback d-flex align-items-center gap-1 mt-1" style="font-size:0.78rem;">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-6" id="nipField" style="display:none;">
                        <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">
                            NIP <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="nip"
                               class="form-control @error('nip') is-invalid @enderror"
                               value="{{ old('nip') }}"
                               placeholder="Nomor Induk Pegawai"
                               style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px;">
                        @error('nip')
                            <div class="invalid-feedback d-flex align-items-center gap-1 mt-1" style="font-size:0.78rem;">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <!-- Divider -->
                <hr style="border-color:#f1f5f9; margin: 0 0 20px;">

                <!-- Buttons -->
                <div class="d-flex flex-wrap gap-2">
                    <button type="submit"
                            class="btn d-flex align-items-center gap-2"
                            style="background:#16a34a; color:#fff; border-radius:10px; font-size:0.87rem; font-weight:600; padding:10px 22px;">
                        <i class="bi bi-check-circle-fill"></i> Simpan User
                    </button>
                    <a href="{{ route('users.index') }}"
                       class="btn d-flex align-items-center gap-2"
                       style="background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; border-radius:10px; font-size:0.87rem; font-weight:600; padding:10px 20px;">
                        <i class="bi bi-x-circle"></i> Batal
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function handleRoleChange(role) {
        document.getElementById('nimField').style.display  = (role === 'mahasiswa')                                  ? 'block' : 'none';
        document.getElementById('nidnField').style.display = (role === 'dosen')                                     ? 'block' : 'none';
        document.getElementById('nipField').style.display  = (['staf_prodi','laboran'].includes(role))              ? 'block' : 'none';
    }

    function togglePwd(fieldId, iconId) {
        const field = document.getElementById(fieldId);
        const icon  = document.getElementById(iconId);
        if (field.type === 'password') { field.type = 'text';    icon.className = 'bi bi-eye-slash'; }
        else                           { field.type = 'password'; icon.className = 'bi bi-eye'; }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const role = document.getElementById('roleSelect').value;
        if (role) handleRoleChange(role);
    });
</script>
@endpush