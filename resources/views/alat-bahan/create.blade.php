@extends('layouts.app')

@section('title', 'Tambah Alat & Bahan')
@section('page-title', 'Tambah Alat & Bahan')
@section('page-subtitle', 'Tambah data alat atau bahan baru ke inventaris laboratorium komputer bisnis.')

@section('content')

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb mb-0" style="font-size:0.8rem; color:#94a3b8;">
        <li class="breadcrumb-item">
            <a href="{{ route('alat-bahan.index') }}" style="color:#64748b; text-decoration:none;">
                <i class="bi bi-boxes me-1"></i>Alat &amp; Bahan
            </a>
        </li>
        <li class="breadcrumb-item active" style="color:#1e293b; font-weight:600;">Tambah Data</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-12 col-lg-7 col-xl-6">
        <div class="card-modern overflow-hidden">

            <!-- Form Header -->
            <div class="px-4 py-3 d-flex align-items-center gap-3" style="border-bottom:1px solid #e2e8f0; background:#fafafa;">
                <div style="width:38px;height:38px;background:#f0fdf4;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#16a34a;flex-shrink:0;">
                    <i class="bi bi-plus-circle-fill"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size:0.95rem; color:#1e293b;">Tambah Alat &amp; Bahan</div>
                    <div style="font-size:0.75rem; color:#64748b;">Isi semua field yang diperlukan</div>
                </div>
            </div>

            <!-- Form Body -->
            <form action="{{ route('alat-bahan.store') }}" method="POST" class="p-4">
                @csrf

                <!-- Nama -->
                <div class="mb-4">
                    <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">
                        Nama <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           name="nama"
                           class="form-control @error('nama') is-invalid @enderror"
                           value="{{ old('nama') }}"
                           placeholder="Contoh: Tabung Reaksi, Asam Sulfat..."
                           style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px;">
                    @error('nama')
                        <div class="invalid-feedback d-flex align-items-center gap-1 mt-1" style="font-size:0.78rem;">
                            <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Jenis -->
                <div class="mb-4">
                    <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">
                        Jenis <span class="text-danger">*</span>
                    </label>
                    <select name="jenis"
                            class="form-select @error('jenis') is-invalid @enderror"
                            style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px;">
                        <option value="">— Pilih Jenis —</option>
                        <option value="alat"  {{ old('jenis') == 'alat'  ? 'selected' : '' }}>🔧 Alat</option>
                        <option value="bahan" {{ old('jenis') == 'bahan' ? 'selected' : '' }}>🧪 Bahan</option>
                    </select>
                    @error('jenis')
                        <div class="invalid-feedback d-flex align-items-center gap-1 mt-1" style="font-size:0.78rem;">
                            <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Satuan & Stok (2 kolom) -->
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">
                            Satuan <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="satuan"
                               class="form-control @error('satuan') is-invalid @enderror"
                               value="{{ old('satuan') }}"
                               placeholder="pcs, liter, gram..."
                               style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px;">
                        @error('satuan')
                            <div class="invalid-feedback d-flex align-items-center gap-1 mt-1" style="font-size:0.78rem;">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">
                            Stok <span class="text-danger">*</span>
                        </label>
                        <input type="number"
                               name="stok"
                               class="form-control @error('stok') is-invalid @enderror"
                               value="{{ old('stok', 0) }}"
                               min="0"
                               style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px;">
                        @error('stok')
                            <div class="invalid-feedback d-flex align-items-center gap-1 mt-1" style="font-size:0.78rem;">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <!-- Kondisi -->
                <div class="mb-4">
                    <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">
                        Kondisi
                        <span class="ms-1" style="font-size:0.75rem; color:#94a3b8; font-weight:400;">(opsional)</span>
                    </label>
                    <input type="text"
                           name="kondisi"
                           class="form-control @error('kondisi') is-invalid @enderror"
                           value="{{ old('kondisi') }}"
                           placeholder="Contoh: Baik, Rusak, Perlu Perbaikan..."
                           style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px;">
                    @error('kondisi')
                        <div class="invalid-feedback d-flex align-items-center gap-1 mt-1" style="font-size:0.78rem;">
                            <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Keterangan -->
                <div class="mb-4">
                    <label class="form-label fw-semibold mb-1" style="font-size:0.84rem; color:#374151;">
                        Keterangan
                        <span class="ms-1" style="font-size:0.75rem; color:#94a3b8; font-weight:400;">(opsional)</span>
                    </label>
                    <textarea name="keterangan"
                              class="form-control @error('keterangan') is-invalid @enderror"
                              rows="3"
                              placeholder="Catatan tambahan mengenai alat atau bahan ini..."
                              style="border-radius:10px; border-color:#e2e8f0; font-size:0.87rem; padding:10px 14px; resize:vertical;">{{ old('keterangan') }}</textarea>
                    @error('keterangan')
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
                            style="background:#16a34a; color:#fff; border-radius:10px; font-size:0.87rem; font-weight:600; padding:10px 22px;">
                        <i class="bi bi-save-fill"></i> Simpan Data
                    </button>
                    <a href="{{ route('alat-bahan.index') }}"
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
