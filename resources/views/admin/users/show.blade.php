@extends('layouts.app')

@section('title', 'Detail User Admin - ' . $user->name)
@section('page-title', 'Detail User')
@section('page-subtitle', 'Informasi lengkap akun dan profil pengguna.')

@section('content')

<!-- Page Header -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1" style="font-size:1.3rem; color:#1e293b;">{{ $user->name }}</h2>
        <p class="mb-0" style="font-size:0.83rem; color:#64748b;">Informasi lengkap akun beserta profil sesuai role.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.users.edit', $user) }}"
           class="btn d-flex align-items-center gap-2"
           style="background:#fefce8; color:#ca8a04; border:1px solid #fde68a; border-radius:10px; font-size:0.83rem; font-weight:600; padding:8px 16px;">
            <i class="bi bi-pencil"></i> Edit
        </a>
        @if($user->id !== auth()->id())
        <button type="button"
                class="btn d-flex align-items-center gap-2"
                style="background:#fff1f2; color:#e11d48; border:1px solid #fecdd3; border-radius:10px; font-size:0.83rem; font-weight:600; padding:8px 16px;"
                onclick="confirmDelete({{ $user->id }}, '{{ addslashes($user->name) }}')">
            <i class="bi bi-trash"></i> Hapus
        </button>
        @endif
        <a href="{{ route('admin.users.index') }}"
           class="btn d-flex align-items-center gap-2"
           style="background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; border-radius:10px; font-size:0.83rem; font-weight:600; padding:8px 16px;">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
</div>

@php
    $profil = $user->profil();
@endphp

<div class="row g-4">
    {{-- ── Kartu Profil ── --}}
    <div class="col-md-4">
        <div class="card-modern overflow-hidden">
            <div class="p-4 text-center">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold mx-auto mb-3"
                     style="width:80px;height:80px;font-size:2rem;
                     background:{{ ['mahasiswa'=>'#2563eb','dosen'=>'#059669','staf_prodi'=>'#d97706','laboran'=>'#e11d48'][$user->role] ?? '#64748b' }}">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <h5 class="fw-bold mb-1" style="color:#0f172a;">{{ $user->name }}</h5>
                <p class="text-muted mb-3" style="font-size:0.85rem;">{{ $user->email }}</p>

                <span class="badge badge-{{ $user->role }} rounded-pill px-3 py-1 mb-2 d-inline-block" style="font-weight:600;">
                    {{ $user->role_label }}
                </span>
                <br>
                @if($user->status === 'aktif')
                    <span class="badge badge-aktif rounded-pill px-3" style="font-weight:600;">
                        <i class="bi bi-circle-fill me-1" style="font-size:0.5rem;"></i>Aktif
                    </span>
                @else
                    <span class="badge badge-nonaktif rounded-pill px-3" style="font-weight:600;">
                        <i class="bi bi-circle-fill me-1" style="font-size:0.5rem;"></i>Nonaktif
                    </span>
                @endif
            </div>
            <div class="px-4 py-3 text-center" style="border-top:1px solid #f1f5f9; background:#fafafa;">
                <small class="text-muted">
                    Bergabung: {{ $user->created_at->isoFormat('D MMM Y') }}
                </small>
            </div>
        </div>

        {{-- Ringkasan Profil --}}
        @if($profil)
        <div class="card-modern mt-4 overflow-hidden">
            <div class="px-4 py-3 d-flex align-items-center gap-2" style="border-bottom:1px solid #f1f5f9;">
                <i class="bi bi-person-badge text-success"></i>
                <span class="fw-bold" style="font-size:0.85rem;">Ringkasan Profil</span>
            </div>
            <div class="p-4">
                <table class="table table-borderless mb-0" style="font-size:0.85rem;">
                    <tbody>
                        @if($user->role === 'mahasiswa' && $user->profilMahasiswa)
                            <tr><td class="fw-medium text-muted">NIM</td><td>: &nbsp;<strong>{{ $user->profilMahasiswa->nim }}</strong></td></tr>
                            <tr><td class="fw-medium text-muted">Prodi</td><td>: &nbsp;{{ $user->profilMahasiswa->prodi }}</td></tr>
                            <tr><td class="fw-medium text-muted">Angkatan</td><td>: &nbsp;{{ $user->profilMahasiswa->angkatan }}</td></tr>
                            @if($user->profilMahasiswa->alamat)<tr><td class="fw-medium text-muted">Alamat</td><td>: &nbsp;{{ $user->profilMahasiswa->alamat }}</td></tr>@endif
                        @elseif($user->role === 'dosen' && $user->profilDosen)
                            <tr><td class="fw-medium text-muted">NIDN</td><td>: &nbsp;<strong>{{ $user->profilDosen->nidn }}</strong></td></tr>
                            <tr><td class="fw-medium text-muted">Prodi</td><td>: &nbsp;{{ $user->profilDosen->prodi }}</td></tr>
                            @if($user->profilDosen->jabatan)<tr><td class="fw-medium text-muted">Jabatan</td><td>: &nbsp;{{ $user->profilDosen->jabatan }}</td></tr>@endif
                        @elseif($user->role === 'laboran' && $user->profilLaboran)
                            <tr><td class="fw-medium text-muted">NIP</td><td>: &nbsp;<strong>{{ $user->profilLaboran->nip }}</strong></td></tr>
                            <tr><td class="fw-medium text-muted">Lab</td><td>: &nbsp;{{ $user->profilLaboran->lab }}</td></tr>
                        @elseif($user->role === 'staf_prodi' && $user->profilStaf)
                            <tr><td class="fw-medium text-muted">NIP</td><td>: &nbsp;<strong>{{ $user->profilStaf->nip }}</strong></td></tr>
                            <tr><td class="fw-medium text-muted">Prodi</td><td>: &nbsp;{{ $user->profilStaf->prodi }}</td></tr>
                            @if($user->profilStaf->jabatan)<tr><td class="fw-medium text-muted">Jabatan</td><td>: &nbsp;{{ $user->profilStaf->jabatan }}</td></tr>@endif
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    {{-- ── Detail Data ── --}}
    <div class="col-md-8">
        <div class="card-modern overflow-hidden">
            <div class="px-4 py-3 d-flex align-items-center gap-2" style="border-bottom:1px solid #f1f5f9;">
                <i class="bi bi-person-lines-fill text-success"></i>
                <span class="fw-bold" style="font-size:0.85rem;">Data Lengkap</span>
            </div>
            <div class="p-4">
                <table class="table table-borderless mb-0" style="font-size:0.87rem;">
                    <tbody>
                        <tr><td class="fw-medium text-muted" width="160">Nama Lengkap</td><td>: &nbsp;<strong>{{ $user->name }}</strong></td></tr>
                        <tr><td class="fw-medium text-muted">Username</td><td>: &nbsp;{{ $user->username }}</td></tr>
                        <tr><td class="fw-medium text-muted">Email</td><td>: &nbsp;{{ $user->email }}</td></tr>
                        <tr><td class="fw-medium text-muted">No HP</td><td>: &nbsp;{{ $user->no_hp ?? '-' }}</td></tr>
                        <tr>
                            <td class="fw-medium text-muted">Role</td>
                            <td>: &nbsp;
                                <span class="badge badge-{{ $user->role }} rounded-pill px-2" style="font-weight:600;">
                                    {{ $user->role_label }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-medium text-muted">Status</td>
                            <td>: &nbsp;
                                @if($user->status === 'aktif')
                                    <span class="badge badge-aktif rounded-pill px-2" style="font-weight:600;">Aktif</span>
                                @else
                                    <span class="badge badge-nonaktif rounded-pill px-2" style="font-weight:600;">Nonaktif</span>
                                @endif
                            </td>
                        </tr>
                        <tr><td class="fw-medium text-muted">Dibuat</td><td>: &nbsp;{{ $user->created_at->isoFormat('D MMMM Y, HH:mm') }}</td></tr>
                        <tr><td class="fw-medium text-muted">Diperbarui</td><td>: &nbsp;{{ $user->updated_at->isoFormat('D MMMM Y, HH:mm') }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Hak Akses --}}
        <div class="card-modern mt-4 overflow-hidden">
            <div class="px-4 py-3 d-flex align-items-center gap-2" style="border-bottom:1px solid #f1f5f9;">
                <i class="bi bi-shield-check text-success"></i>
                <span class="fw-bold" style="font-size:0.85rem;">Hak Akses</span>
            </div>
            <div class="p-4">
                @php
                    $permissions = match($user->role) {
                        'mahasiswa'  => ['Lihat informasi lab', 'Lihat jadwal peminjaman', 'Ajukan peminjaman', 'Lihat status pengajuan', 'Lihat riwayat peminjaman'],
                        'dosen'      => ['Lihat informasi lab', 'Lihat jadwal peminjaman', 'Ajukan peminjaman', 'Lihat status pengajuan', 'Lihat riwayat peminjaman'],
                        'staf_prodi' => ['Lihat data user', 'Verifikasi pengajuan', 'Lihat data peminjaman', 'Lihat jadwal lab', 'Kelola informasi peminjaman'],
                        'laboran'    => ['CRUD seluruh user', 'Kelola laboratorium', 'Kelola peminjaman', 'Verifikasi semua pengajuan', 'Kelola jadwal', 'Lihat laporan'],
                        default      => [],
                    };
                @endphp
                <ul class="list-unstyled mb-0">
                    @foreach($permissions as $perm)
                    <li class="py-2 border-bottom d-flex align-items-center gap-2" style="font-size:0.83rem; border-color:#f1f5f9 !important;">
                        <i class="bi bi-check-circle-fill text-success" style="font-size:0.8rem;"></i>
                        {{ $perm }}
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- Modal Hapus --}}
@if($user->id !== auth()->id())
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px; border:1px solid #e2e8f0;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Anda yakin ingin menghapus user <strong>{{ $user->name }}</strong>? Ini akan menghapus seluruh profil terkait.</p>
                <p class="text-danger small mb-0"><i class="bi bi-info-circle me-1"></i>Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" style="border-radius:9px;" data-bs-dismiss="modal">Batal</button>
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn d-flex align-items-center gap-1" style="background:#dc2626;color:#fff;border-radius:9px;font-weight:600;">
                        <i class="bi bi-trash"></i> Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
function confirmDelete(id, name) {
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush