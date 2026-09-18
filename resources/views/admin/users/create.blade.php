@extends('layouts.app')

@section('title', 'Tambah User - Admin')
@section('page-title', 'Tambah User')
@section('page-subtitle', 'Lengkapi data akun dan profil sesuai role pengguna baru.')

@section('content')

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb mb-0" style="font-size:0.8rem; color:#94a3b8;">
        <li class="breadcrumb-item">
            <a href="{{ route('admin.users.index') }}" style="color:#64748b; text-decoration:none;">
                <i class="bi bi-people me-1"></i>Kelola User (Admin)
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
                    <div class="fw-bold" style="font-size:0.95rem; color:#1e293b;">Tambah User Baru</div>
                    <div style="font-size:0.75rem; color:#64748b;">Lengkapi data akun dan profil sesuai role</div>
                </div>
            </div>

            <form action="{{ route('admin.users.store') }}" method="POST" class="p-4">
                @csrf

                @if($errors->any())
                <div class="alert alert-danger d-flex align-items-start gap-2 mb-4 rounded-3" role="alert" style="font-size:0.83rem;">
                    <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                    <div>
                        <strong>Terdapat kesalahan:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif

                {{-- ── Data Akun ── --}}
                <h6 class="fw-semibold text-muted mb-3 d-flex align-items-center gap-2" style="font-size:0.85rem;">
                    <i class="bi bi-person"></i>Data Akun
                </h6>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">
                            Nama Lengkap <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="form-control @error('name') is-invalid @enderror"
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
                        <input type="text" name="username" value="{{ old('username') }}" required
                               class="form-control @error('username') is-invalid @enderror"
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
                        <input type="email" name="email" value="{{ old('email') }}" required
                               class="form-control @error('email') is-invalid @enderror"
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
                            Password <span class="text-danger">*</span>
                        </label>
                        <input type="password" name="password" required
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Minimal 6 karakter"
                               style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px;">
                        @error('password')
                            <div class="invalid-feedback d-flex align-items-center gap-1 mt-1" style="font-size:0.78rem;">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </div>
                        @enderror
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
                        <select id="role" name="role" required onchange="toggleProfileForm(this.value)"
                                class="form-select @error('role') is-invalid @enderror"
                                style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px;">
                            <option value="">— Pilih Role —</option>
                            <option value="mahasiswa" {{ old('role') === 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                            <option value="dosen" {{ old('role') === 'dosen' ? 'selected' : '' }}>Dosen</option>
                            <option value="laboran" {{ old('role') === 'laboran' ? 'selected' : '' }}>Laboran</option>
                            <option value="staf_prodi" {{ old('role') === 'staf_prodi' ? 'selected' : '' }}>Staf Prodi</option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback d-flex align-items-center gap-1 mt-1" style="font-size:0.78rem;">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-6 d-flex flex-column">
                        <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">
                            No. HP
                            <span class="ms-1" style="font-size:0.75rem; color:#94a3b8; font-weight:400;">(opsional)</span>
                        </label>
                        <input type="text" name="no_hp" value="{{ old('no_hp') }}"
                               class="form-control @error('no_hp') is-invalid @enderror"
                               placeholder="08xxxxxxxxxx"
                               style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px;">
                        @error('no_hp')
                            <div class="invalid-feedback d-flex align-items-center gap-1 mt-1" style="font-size:0.78rem;">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">
                        Status <span class="text-danger">*</span>
                    </label>
                    <select name="status" required
                            class="form-select @error('status') is-invalid @enderror"
                            style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px; max-width:280px;">
                        <option value="aktif" {{ old('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ old('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback d-flex align-items-center gap-1 mt-1" style="font-size:0.78rem;">
                            <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- ── Profil sesuai Role ── --}}
                <h6 class="fw-semibold text-muted mb-3 d-flex align-items-center gap-2" style="font-size:0.85rem;">
                    <i class="bi bi-person-badge"></i>Data Profil (sesuai role)
                </h6>

                {{-- Profil Mahasiswa --}}
                <div id="profile-mahasiswa" class="card-modern p-4 mb-3" style="display:none; background:#fafafa;">
                    <div class="fw-bold mb-3 d-flex align-items-center gap-2" style="font-size:0.85rem; color:#2563eb;">
                        <i class="bi bi-mortarboard"></i>Data Mahasiswa
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">NIM <span class="text-danger">*</span></label>
                            <input type="text" name="nim" value="{{ old('nim') }}"
                                   class="form-control"
                                   style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">Program Studi <span class="text-danger">*</span></label>
                            <input type="text" name="prodi" value="{{ old('prodi') }}"
                                   class="form-control"
                                   style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">Angkatan <span class="text-danger">*</span></label>
                            <input type="number" name="angkatan" value="{{ old('angkatan') }}" min="2000" max="{{ date('Y') + 1 }}"
                                   class="form-control"
                                   style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">Alamat</label>
                            <textarea name="alamat" rows="3" class="form-control"
                                      style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px; resize:vertical;">{{ old('alamat') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Profil Dosen --}}
                <div id="profile-dosen" class="card-modern p-4 mb-3" style="display:none; background:#fafafa;">
                    <div class="fw-bold mb-3 d-flex align-items-center gap-2" style="font-size:0.85rem; color:#059669;">
                        <i class="bi bi-person-badge"></i>Data Dosen
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">NIDN <span class="text-danger">*</span></label>
                            <input type="text" name="nidn" value="{{ old('nidn') }}"
                                   class="form-control"
                                   style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">Program Studi <span class="text-danger">*</span></label>
                            <input type="text" name="prodi" value="{{ old('prodi') }}"
                                   class="form-control"
                                   style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">Jabatan</label>
                            <input type="text" name="jabatan" value="{{ old('jabatan') }}"
                                   class="form-control"
                                   style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px;">
                        </div>
                    </div>
                </div>

                {{-- Profil Laboran --}}
                <div id="profile-laboran" class="card-modern p-4 mb-3" style="display:none; background:#fafafa;">
                    <div class="fw-bold mb-3 d-flex align-items-center gap-2" style="font-size:0.85rem; color:#e11d48;">
                        <i class="bi bi-pc-display"></i>Data Laboran
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">NIP <span class="text-danger">*</span></label>
                            <input type="text" name="nip" value="{{ old('nip') }}"
                                   class="form-control"
                                   style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">Laboratorium <span class="text-danger">*</span></label>
                            <input type="text" name="lab" value="{{ old('lab') }}"
                                   class="form-control"
                                   style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px;">
                        </div>
                    </div>
                </div>

                {{-- Profil Staf Prodi --}}
                <div id="profile-staf" class="card-modern p-4 mb-4" style="display:none; background:#fafafa;">
                    <div class="fw-bold mb-3 d-flex align-items-center gap-2" style="font-size:0.85rem; color:#ca8a04;">
                        <i class="bi bi-briefcase"></i>Data Staf Prodi
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">NIP <span class="text-danger">*</span></label>
                            <input type="text" name="nip" value="{{ old('nip') }}"
                                   class="form-control"
                                   style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">Program Studi <span class="text-danger">*</span></label>
                            <input type="text" name="prodi" value="{{ old('prodi') }}"
                                   class="form-control"
                                   style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">Jabatan</label>
                            <input type="text" name="jabatan" value="{{ old('jabatan') }}"
                                   class="form-control"
                                   style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px;">
                        </div>
                    </div>
                </div>

                <!-- Divider -->
                <hr style="border-color:#f1f5f9; margin: 0 0 20px;">

                <!-- Buttons -->
                <div class="d-flex flex-wrap gap-2">
                    <button type="submit"
                            class="btn d-flex align-items-center gap-2"
                            style="background:#16a34a; color:#fff; border-radius:10px; font-size:0.87rem; font-weight:600; padding:10px 22px;">
                        <i class="bi bi-save-fill"></i> Simpan User
                    </button>
                    <a href="{{ route('admin.users.index') }}"
                       class="btn d-flex align-items-center gap-2"
                       style="background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; border-radius:10px; font-size:0.87rem; font-weight:600; padding:10px 20px;">
                        <i class="bi bi-arrow-left"></i> Batal
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function toggleProfileForm(role) {
        document.querySelectorAll('.profile-section, #profile-mahasiswa, #profile-dosen, #profile-laboran, #profile-staf').forEach(section => {
            section.style.display = 'none';
        });

        if (role === 'mahasiswa') {
            document.getElementById('profile-mahasiswa').style.display = 'block';
        } else if (role === 'dosen') {
            document.getElementById('profile-dosen').style.display = 'block';
        } else if (role === 'laboran') {
            document.getElementById('profile-laboran').style.display = 'block';
        } else if (role === 'staf_prodi') {
            document.getElementById('profile-staf').style.display = 'block';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('role');
        if (roleSelect.value) {
            toggleProfileForm(roleSelect.value);
        }
    });
</script>
@endpush