@extends('layouts.app')

@section('title', 'Kelola User')
@section('page-title', 'Kelola User')
@section('page-subtitle', 'Daftar seluruh pengguna sistem laboratorium komputer bisnis.')

@section('content')

<!-- Page Header -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1" style="font-size:1.3rem; color:#1e293b;">Kelola User</h2>
        <p class="mb-0" style="font-size:0.83rem; color:#64748b;">Daftar seluruh pengguna sistem.</p>
    </div>
    @if(auth()->user()->isLaboran())
    <a href="{{ route('users.create') }}"
       class="btn d-flex align-items-center gap-2"
       style="background:#16a34a;color:#fff;border-radius:10px;font-size:0.85rem;font-weight:600;padding:9px 18px;white-space:nowrap;">
        <i class="bi bi-person-plus-fill"></i>
        Tambah User
    </a>
    @endif
</div>

<!-- Filter Bar -->
<div class="card-modern p-3 mb-4">
    <form method="GET" action="{{ route('users.index') }}">
        <div class="row g-2 align-items-center">
            <div class="col-12 col-md-5">
                <div class="input-group" style="border-radius:10px; overflow:hidden;">
                    <span class="input-group-text bg-white border-end-0" style="border-color:#e2e8f0; color:#94a3b8;">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="form-control border-start-0 ps-0"
                           placeholder="Cari nama, email, username, NIM, NIDN, NIP..."
                           style="border-color:#e2e8f0; font-size:0.85rem;">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <select name="role" class="form-select" style="font-size:0.85rem; border-color:#e2e8f0; border-radius:10px; color:#475569;">
                    <option value="">Semua Role</option>
                    <option value="mahasiswa"  {{ request('role') === 'mahasiswa'  ? 'selected' : '' }}>Mahasiswa</option>
                    <option value="dosen"      {{ request('role') === 'dosen'      ? 'selected' : '' }}>Dosen</option>
                    <option value="staf_prodi" {{ request('role') === 'staf_prodi' ? 'selected' : '' }}>Staf Prodi</option>
                    <option value="laboran"    {{ request('role') === 'laboran'    ? 'selected' : '' }}>Laboran</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="status" class="form-select" style="font-size:0.85rem; border-color:#e2e8f0; border-radius:10px; color:#475569;">
                    <option value="">Semua Status</option>
                    <option value="aktif"    {{ request('status') === 'aktif'    ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2 justify-content-md-end">
                <button type="submit"
                        class="btn d-flex align-items-center justify-content-center gap-2 flex-grow-1 flex-md-grow-0"
                        style="background:#16a34a;color:#fff;border-radius:10px;font-size:0.83rem;font-weight:600;padding:8px 18px;">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                <a href="{{ route('users.index') }}"
                   class="btn d-flex align-items-center justify-content-center"
                   style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;border-radius:10px;font-size:0.83rem;font-weight:600;padding:8px 14px;"
                   title="Reset filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Table Card -->
<div class="card-modern overflow-hidden">
    <div class="d-flex align-items-center justify-content-between px-4 py-3" style="border-bottom:1px solid #e2e8f0;">
        <div>
            <h6 class="fw-bold mb-0" style="font-size:0.9rem;">Daftar User</h6>
            <p class="mb-0" style="font-size:0.75rem; color:#64748b;">Total {{ $users->total() }} user</p>
        </div>
        <span class="badge rounded-pill" style="background:#f0fdf4; color:#16a34a; font-size:0.73rem; font-weight:600; padding:5px 12px;">
            <i class="bi bi-people me-1"></i>{{ $users->total() }} user
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0" style="font-size:0.85rem;">
            <thead style="background:#f8fafc; color:#64748b; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.04em;">
                <tr>
                    <th class="ps-4 py-3 fw-semibold border-0" style="width:48px;">#</th>
                    <th class="py-3 fw-semibold border-0">Nama</th>
                    <th class="py-3 fw-semibold border-0">Username / Email</th>
                    <th class="py-3 fw-semibold border-0">Role</th>
                    <th class="py-3 fw-semibold border-0">Identitas</th>
                    <th class="py-3 fw-semibold border-0">No HP</th>
                    <th class="py-3 fw-semibold border-0">Status</th>
                    <th class="py-3 pe-4 fw-semibold border-0 text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $index => $u)
                <tr style="border-top:1px solid #f1f5f9;">
                    <td class="ps-4 py-3 align-middle text-muted" style="font-size:0.78rem;">{{ $users->firstItem() + $index }}</td>

                    <td class="py-3 align-middle">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                 style="width:32px;height:32px;font-size:0.75rem;
                                 background:{{ ['mahasiswa'=>'#2563eb','dosen'=>'#059669','staf_prodi'=>'#d97706','laboran'=>'#e11d48'][$u->role] ?? '#64748b' }}">
                                {{ strtoupper(substr($u->name, 0, 1)) }}
                            </div>
                            <span class="fw-semibold" style="color:#1e293b;">{{ $u->name }}</span>
                        </div>
                    </td>

                    <td class="py-3 align-middle">
                        <div style="color:#1e293b;">{{ $u->username ?? '-' }}</div>
                        <div style="font-size:0.78rem; color:#64748b;">{{ $u->email }}</div>
                    </td>

                    <td class="py-3 align-middle">
                        <span class="badge badge-{{ $u->role }} rounded-pill px-2" style="font-size:0.73rem; font-weight:600; padding:5px 12px;">
                            {{ $u->role_label }}
                        </span>
                    </td>

                    <td class="py-3 align-middle">
                        @if($u->identitas)
                            <span class="text-muted" style="font-size:0.75rem;">{{ $u->label_identitas }}</span><br>
                            <span class="fw-semibold" style="color:#1e293b;">{{ $u->identitas }}</span>
                        @else
                            <span style="color:#cbd5e1;">—</span>
                        @endif
                    </td>

                    <td class="py-3 align-middle" style="color:#475569;">{{ $u->no_hp ?? '—' }}</td>

                    <td class="py-3 align-middle">
                        @if($u->status === 'aktif')
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
                            <a href="{{ route('users.show', $u) }}"
                               class="btn btn-sm d-flex align-items-center gap-1"
                               style="background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe; border-radius:8px; font-size:0.78rem; font-weight:600; padding:5px 12px; white-space:nowrap;">
                                <i class="bi bi-eye" style="font-size:0.7rem;"></i> Detail
                            </a>
                            @if(auth()->user()->isLaboran())
                            <a href="{{ route('users.edit', $u) }}"
                               class="btn btn-sm d-flex align-items-center gap-1"
                               style="background:#fefce8; color:#ca8a04; border:1px solid #fde68a; border-radius:8px; font-size:0.78rem; font-weight:600; padding:5px 12px; white-space:nowrap;">
                                <i class="bi bi-pencil" style="font-size:0.7rem;"></i> Edit
                            </a>
                            @if($u->id !== auth()->id())
                            <button type="button"
                                    onclick="confirmDelete({{ $u->id }}, '{{ addslashes($u->name) }}')"
                                    class="btn btn-sm d-flex align-items-center gap-1"
                                    style="background:#fff1f2; color:#e11d48; border:1px solid #fecdd3; border-radius:8px; font-size:0.78rem; font-weight:600; padding:5px 12px; white-space:nowrap;">
                                <i class="bi bi-trash" style="font-size:0.7rem;"></i> Hapus
                            </button>
                            @endif
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="text-center py-5" style="color:#94a3b8;">
                            <div class="mb-3">
                                <span style="display:inline-flex;align-items:center;justify-content:center;width:64px;height:64px;background:#f1f5f9;border-radius:50%;">
                                    <i class="bi bi-inbox fs-2" style="color:#cbd5e1;"></i>
                                </span>
                            </div>
                            <div class="fw-semibold mb-1" style="font-size:0.95rem; color:#475569;">Belum ada data</div>
                            <div class="mb-4" style="font-size:0.83rem; color:#94a3b8;">Tidak ada user yang ditemukan.</div>
                            @if(auth()->user()->isLaboran())
                            <a href="{{ route('users.create') }}"
                               class="btn d-inline-flex align-items-center gap-2"
                               style="background:#16a34a;color:#fff;border-radius:10px;font-size:0.83rem;font-weight:600;padding:9px 20px;">
                                <i class="bi bi-person-plus-fill"></i> Tambah User
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 px-4 py-3" style="border-top:1px solid #f1f5f9; background:#fafafa;">
        <p class="mb-0" style="font-size:0.78rem; color:#94a3b8;">
            Menampilkan {{ $users->firstItem() }}–{{ $users->lastItem() }} dari {{ $users->total() }} user
        </p>
        <div class="pagination-modern">
            {{ $users->links() }}
        </div>
    </div>
    @endif
</div>

<!-- Modal Konfirmasi Hapus -->
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
                <p>Anda yakin ingin menghapus user <strong id="deleteUserName"></strong>?</p>
                <p class="text-danger small mb-0"><i class="bi bi-info-circle me-1"></i>Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" style="border-radius:9px;" data-bs-dismiss="modal">Batal</button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn d-flex align-items-center gap-1" style="background:#dc2626;color:#fff;border-radius:9px;font-weight:600;">
                        <i class="bi bi-trash"></i> Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function confirmDelete(id, name) {
    document.getElementById('deleteUserName').textContent = name;
    document.getElementById('deleteForm').action = '/users/' + id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush