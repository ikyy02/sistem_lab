@extends('layouts.app')

@section('title', 'Admin Kelola User')
@section('page-title', 'Admin Kelola User')
@section('page-subtitle', 'Kelola seluruh akun pengguna beserta profil sesuai role.')

@section('content')

<!-- Page Header -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1" style="font-size:1.3rem; color:#1e293b;">Kelola User (Admin)</h2>
        <p class="mb-0" style="font-size:0.83rem; color:#64748b;">Daftar seluruh user beserta profil terpisah.</p>
    </div>
    <a href="{{ route('admin.users.create') }}"
       class="btn d-flex align-items-center gap-2"
       style="background:#16a34a;color:#fff;border-radius:10px;font-size:0.85rem;font-weight:600;padding:9px 18px;white-space:nowrap;">
        <i class="bi bi-person-plus-fill"></i>
        Tambah User Baru
    </a>
</div>

<!-- Table Card -->
<div class="card-modern overflow-hidden">
    <div class="d-flex align-items-center justify-content-between px-4 py-3" style="border-bottom:1px solid #e2e8f0;">
        <div>
            <h6 class="fw-bold mb-0" style="font-size:0.9rem;">Daftar User</h6>
            <p class="mb-0" style="font-size:0.75rem; color:#64748b;">Total {{ $users->count() }} user</p>
        </div>
        <span class="badge rounded-pill" style="background:#f0fdf4; color:#16a34a; font-size:0.73rem; font-weight:600; padding:5px 12px;">
            <i class="bi bi-people me-1"></i>{{ $users->count() }} user
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0" style="font-size:0.85rem;">
            <thead style="background:#f8fafc; color:#64748b; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.04em;">
                <tr>
                    <th class="ps-4 py-3 fw-semibold border-0">Nama</th>
                    <th class="py-3 fw-semibold border-0">Username / Email</th>
                    <th class="py-3 fw-semibold border-0">Role</th>
                    <th class="py-3 fw-semibold border-0">Identitas</th>
                    <th class="py-3 fw-semibold border-0">Status</th>
                    <th class="py-3 pe-4 fw-semibold border-0 text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr style="border-top:1px solid #f1f5f9;">
                    <td class="ps-4 py-3 align-middle">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                 style="width:32px;height:32px;font-size:0.75rem;
                                 background:{{ ['mahasiswa'=>'#2563eb','dosen'=>'#059669','staf_prodi'=>'#d97706','laboran'=>'#e11d48'][$user->role] ?? '#64748b' }}">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <span class="fw-semibold" style="color:#1e293b;">{{ $user->name }}</span>
                        </div>
                    </td>

                    <td class="py-3 align-middle">
                        <div style="color:#1e293b;">{{ $user->username }}</div>
                        <div style="font-size:0.78rem; color:#64748b;">{{ $user->email }}</div>
                    </td>

                    <td class="py-3 align-middle">
                        <span class="badge badge-{{ $user->role }} rounded-pill px-2" style="font-size:0.73rem; font-weight:600; padding:5px 12px;">
                            {{ $user->role_label }}
                        </span>
                    </td>

                    <td class="py-3 align-middle">
                        @if($user->role === 'mahasiswa' && $user->profilMahasiswa)
                            <span class="text-muted" style="font-size:0.75rem;">NIM</span><br>
                            <span class="fw-semibold" style="color:#1e293b;">{{ $user->profilMahasiswa->nim }}</span>
                        @elseif($user->role === 'dosen' && $user->profilDosen)
                            <span class="text-muted" style="font-size:0.75rem;">NIDN</span><br>
                            <span class="fw-semibold" style="color:#1e293b;">{{ $user->profilDosen->nidn }}</span>
                        @elseif($user->role === 'laboran' && $user->profilLaboran)
                            <span class="text-muted" style="font-size:0.75rem;">NIP</span><br>
                            <span class="fw-semibold" style="color:#1e293b;">{{ $user->profilLaboran->nip }}</span>
                        @elseif($user->role === 'staf_prodi' && $user->profilStaf)
                            <span class="text-muted" style="font-size:0.75rem;">NIP</span><br>
                            <span class="fw-semibold" style="color:#1e293b;">{{ $user->profilStaf->nip }}</span>
                        @else
                            <span style="color:#cbd5e1;">—</span>
                        @endif
                    </td>

                    <td class="py-3 align-middle">
                        @if($user->status === 'aktif')
                            <span class="badge badge-aktif rounded-pill" style="font-size:0.73rem; font-weight:600; padding:5px 12px;">
                                <i class="bi bi-circle-fill me-1" style="font-size:0.5rem;"></i>Aktif
                            </span>
                        @else
                            <span class="badge badge-nonaktif rounded-pill" style="font-size:0.73rem; font-weight:600; padding:5px 12px;">
                                <i class="bi bi-circle-fill me-1" style="font-size:0.5rem;"></i>Nonaktif
                            </span>
                        @endif
                    </td>

                    <td class="py-3 pe-4 align-middle text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <a href="{{ route('admin.users.show', $user) }}"
                               class="btn btn-sm d-flex align-items-center gap-1"
                               style="background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe; border-radius:8px; font-size:0.78rem; font-weight:600; padding:5px 12px; white-space:nowrap;">
                                <i class="bi bi-eye" style="font-size:0.7rem;"></i> Lihat
                            </a>
                            <a href="{{ route('admin.users.edit', $user) }}"
                               class="btn btn-sm d-flex align-items-center gap-1"
                               style="background:#fefce8; color:#ca8a04; border:1px solid #fde68a; border-radius:8px; font-size:0.78rem; font-weight:600; padding:5px 12px; white-space:nowrap;">
                                <i class="bi bi-pencil" style="font-size:0.7rem;"></i> Edit
                            </a>
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                  onsubmit="return confirm('Yakin ingin menghapus user \'{{ addslashes($user->name) }}\'? Ini akan menghapus seluruh profil terkait.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="btn btn-sm d-flex align-items-center gap-1"
                                        style="background:#fff1f2; color:#e11d48; border:1px solid #fecdd3; border-radius:8px; font-size:0.78rem; font-weight:600; padding:5px 12px; white-space:nowrap;">
                                    <i class="bi bi-trash" style="font-size:0.7rem;"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="text-center py-5" style="color:#94a3b8;">
                            <div class="mb-3">
                                <span style="display:inline-flex;align-items:center;justify-content:center;width:64px;height:64px;background:#f1f5f9;border-radius:50%;">
                                    <i class="bi bi-inbox fs-2" style="color:#cbd5e1;"></i>
                                </span>
                            </div>
                            <div class="fw-semibold mb-1" style="font-size:0.95rem; color:#475569;">Belum ada data</div>
                            <div class="mb-4" style="font-size:0.83rem; color:#94a3b8;">Belum ada user yang terdaftar.</div>
                            <a href="{{ route('admin.users.create') }}"
                               class="btn d-inline-flex align-items-center gap-2"
                               style="background:#16a34a;color:#fff;border-radius:10px;font-size:0.83rem;font-weight:600;padding:9px 20px;">
                                <i class="bi bi-person-plus-fill"></i> Tambah User
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection