@extends('layouts.app')

@section('title', 'Edit Dosen')
@section('page-title', 'Edit Dosen')
@section('page-subtitle', 'Perbarui informasi data dosen laboratorium komputer bisnis.')

@section('content')

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb mb-0" style="font-size:0.8rem; color:#94a3b8;">
        <li class="breadcrumb-item">
            <a href="{{ route('dosen.index') }}" style="color:#64748b; text-decoration:none;">
                <i class="bi bi-person-badge me-1"></i>Data Dosen
            </a>
        </li>
        <li class="breadcrumb-item active" style="color:#1e293b; font-weight:600;">Edit Data</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-12 col-lg-7 col-xl-6">
        <div class="card-modern overflow-hidden">

            <!-- Form Header -->
            <div class="px-4 py-3 d-flex align-items-center gap-3" style="border-bottom:1px solid #e2e8f0; background:#fafafa;">
                <div style="width:38px;height:38px;background:#fefce8;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#ca8a04;flex-shrink:0;">
                    <i class="bi bi-pencil-fill"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size:0.95rem; color:#1e293b;">Edit: {{ $dosen->nama }}</div>
                    <div style="font-size:0.75rem; color:#64748b;">Perbarui informasi data di bawah ini</div>
                </div>
            </div>

            <!-- Form Body -->
            <form action="{{ route('dosen.update', $dosen) }}" method="POST" class="p-4">
                @csrf
                @method('PUT')

                <!-- NIDN -->
                <div class="mb-4">
                    <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">
                        NIDN <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           name="nidn"
                           class="form-control @error('nidn') is-invalid @enderror"
                           value="{{ old('nidn', $dosen->nidn) }}"
                           placeholder="Contoh: 0718129501"
                           style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px;">
                    @error('nidn')
                        <div class="invalid-feedback d-flex align-items-center gap-1 mt-1" style="font-size:0.78rem;">
                            <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- NIP -->
                <div class="mb-4">
                    <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">
                        NIP
                    </label>
                    <input type="text"
                           name="nip"
                           class="form-control @error('nip') is-invalid @enderror"
                           value="{{ old('nip', $dosen->nip) }}"
                           placeholder="Contoh: 197812182001121001 (opsional)"
                           style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px;">
                    @error('nip')
                        <div class="invalid-feedback d-flex align-items-center gap-1 mt-1" style="font-size:0.78rem;">
                            <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Nama -->
                <div class="mb-4">
                    <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">
                        Nama <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           name="nama"
                           class="form-control @error('nama') is-invalid @enderror"
                           value="{{ old('nama', $dosen->nama) }}"
                           placeholder="Nama lengkap dosen"
                           style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px;">
                    @error('nama')
                        <div class="invalid-feedback d-flex align-items-center gap-1 mt-1" style="font-size:0.78rem;">
                            <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Program Studi -->
                <div class="mb-4">
                    <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">
                        Program Studi <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           name="program_studi"
                           class="form-control @error('program_studi') is-invalid @enderror"
                           value="{{ old('program_studi', $dosen->program_studi) }}"
                           placeholder="Contoh: Manajemen"
                           style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px;">
                    @error('program_studi')
                        <div class="invalid-feedback d-flex align-items-center gap-1 mt-1" style="font-size:0.78rem;">
                            <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mb-4">
                    <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">
                        Email <span class="text-danger">*</span>
                    </label>
                    <input type="email"
                           name="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email', $dosen->email) }}"
                           placeholder="nama@kampus.ac.id"
                           style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px;">
                    @error('email')
                        <div class="invalid-feedback d-flex align-items-center gap-1 mt-1" style="font-size:0.78rem;">
                            <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- No. WhatsApp -->
                <div class="mb-4">
                    <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">
                        Nomor WhatsApp <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           name="no_whatsapp"
                           class="form-control @error('no_whatsapp') is-invalid @enderror"
                           value="{{ old('no_whatsapp', $dosen->no_whatsapp) }}"
                           placeholder="Contoh: 081234567890"
                           style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px;">
                    @error('no_whatsapp')
                        <div class="invalid-feedback d-flex align-items-center gap-1 mt-1" style="font-size:0.78rem;">
                            <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Divider -->
                <hr style="border-color:#f1f5f9; margin: 0 0 20px;">

                <!-- Buttons -->
                <div class="d-flex gap-2">
                    <button type="submit"
                            class="btn d-flex align-items-center gap-2"
                            style="background:#ca8a04; color:#fff; border-radius:10px; font-size:0.87rem; font-weight:600; padding:10px 22px;">
                        <i class="bi bi-save-fill"></i> Simpan Perubahan
                    </button>
                    <a href="{{ route('dosen.index') }}"
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