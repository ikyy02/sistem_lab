@extends('layouts.app')

@section('title', 'Detail Dosen')
@section('page-title', 'Detail Dosen')
@section('page-subtitle', 'Informasi lengkap data dosen.')

@section('content')

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb mb-0" style="font-size:0.8rem; color:#94a3b8;">
        <li class="breadcrumb-item">
            <a href="{{ route('dosen.index') }}" style="color:#64748b; text-decoration:none;">
                <i class="bi bi-person-badge me-1"></i>Data Dosen
            </a>
        </li>
        <li class="breadcrumb-item active" style="color:#1e293b; font-weight:600;">Detail Data</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-12 col-lg-7 col-xl-6">
        <div class="card-modern overflow-hidden">

            <!-- Header -->
            <div class="px-4 py-3 d-flex align-items-center gap-3" style="border-bottom:1px solid #e2e8f0; background:#fafafa;">
                <div style="width:38px;height:38px;background:#eff6ff;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#2563eb;flex-shrink:0;">
                    <i class="bi bi-person-badge-fill"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size:0.95rem; color:#1e293b;">{{ $dosen->nama }}</div>
                    <div style="font-size:0.75rem; color:#64748b;">Detail informasi dosen</div>
                </div>
            </div>

            <!-- Body -->
            <div class="p-4">
                <div class="mb-3">
                    <div style="font-size:0.75rem; color:#94a3b8; text-transform:uppercase; letter-spacing:0.04em;">NIDN</div>
                    <div style="font-size:0.9rem; color:#1e293b; font-weight:600;">{{ $dosen->nidn }}</div>
                </div>
                <div class="mb-3">
                    <div style="font-size:0.75rem; color:#94a3b8; text-transform:uppercase; letter-spacing:0.04em;">NIP</div>
                    <div style="font-size:0.9rem; color:#1e293b; font-weight:600;">{{ $dosen->nip ?? '—' }}</div>
                </div>
                <div class="mb-3">
                    <div style="font-size:0.75rem; color:#94a3b8; text-transform:uppercase; letter-spacing:0.04em;">Nama</div>
                    <div style="font-size:0.9rem; color:#1e293b; font-weight:600;">{{ $dosen->nama }}</div>
                </div>
                <div class="mb-3">
                    <div style="font-size:0.75rem; color:#94a3b8; text-transform:uppercase; letter-spacing:0.04em;">Program Studi</div>
                    <div style="font-size:0.9rem; color:#1e293b; font-weight:600;">{{ $dosen->program_studi }}</div>
                </div>
                <div class="mb-3">
                    <div style="font-size:0.75rem; color:#94a3b8; text-transform:uppercase; letter-spacing:0.04em;">Email</div>
                    <div style="font-size:0.9rem; color:#1e293b; font-weight:600;">{{ $dosen->email }}</div>
                </div>
                <div class="mb-4">
                    <div style="font-size:0.75rem; color:#94a3b8; text-transform:uppercase; letter-spacing:0.04em;">Nomor WhatsApp</div>
                    <div style="font-size:0.9rem; color:#1e293b; font-weight:600;">{{ $dosen->no_whatsapp }}</div>
                </div>

                <hr style="border-color:#f1f5f9; margin: 0 0 20px;">

                <div class="d-flex gap-2">
                    <a href="{{ route('dosen.edit', $dosen) }}"
                       class="btn d-flex align-items-center gap-2"
                       style="background:#ca8a04; color:#fff; border-radius:10px; font-size:0.87rem; font-weight:600; padding:10px 22px;">
                        <i class="bi bi-pencil-fill"></i> Edit Data
                    </a>
                    <a href="{{ route('dosen.index') }}"
                       class="btn d-flex align-items-center gap-2"
                       style="background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; border-radius:10px; font-size:0.87rem; font-weight:600; padding:10px 20px;">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection