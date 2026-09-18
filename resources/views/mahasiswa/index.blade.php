@extends('layouts.app')

@section('title', 'Data Mahasiswa')
@section('page-title', 'Data Mahasiswa')
@section('page-subtitle', 'Kelola data mahasiswa pengguna laboratorium komputer bisnis.')

@section('content')

<!-- Page Header -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1" style="font-size:1.3rem; color:#1e293b;">Kelola Data Mahasiswa</h2>
        <p class="mb-0" style="font-size:0.83rem; color:#64748b;">Kelola data mahasiswa pengguna laboratorium komputer bisnis.</p>
    </div>
    <a href="{{ route('mahasiswa.create') }}" class="btn d-flex align-items-center gap-2" style="background:#16a34a;color:#fff;border-radius:10px;font-size:0.85rem;font-weight:600;padding:9px 18px;white-space:nowrap;">
        <i class="bi bi-plus-circle-fill"></i>
        Tambah Data
    </a>
</div>

<!-- Table Card -->
<div class="card-modern overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover mb-0" style="font-size:0.85rem;">
            <thead style="background:#f8fafc; color:#64748b; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.04em;">
                <tr>
                    <th class="ps-4 py-3 fw-semibold border-0" style="width:48px;">#</th>
                    <th class="py-3 fw-semibold border-0">NIM</th>
                    <th class="py-3 fw-semibold border-0">Nama</th>
                    <th class="py-3 fw-semibold border-0">Program Studi</th>
                    <th class="py-3 fw-semibold border-0">Email</th>
                    <th class="py-3 fw-semibold border-0">No. WhatsApp</th>
                    <th class="py-3 pe-4 fw-semibold border-0 text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($data as $item)
                <tr style="border-top:1px solid #f1f5f9;">
                    <td class="ps-4 py-3 align-middle text-muted" style="font-size:0.78rem;">{{ $loop->iteration }}</td>

                    <td class="py-3 align-middle" style="color:#475569;">{{ $item->nim }}</td>

                    <td class="py-3 align-middle">
                        <span class="fw-semibold" style="color:#1e293b;">{{ $item->nama }}</span>
                    </td>

                    <td class="py-3 align-middle" style="color:#475569;">{{ $item->program_studi }}</td>

                    <td class="py-3 align-middle" style="color:#475569;">{{ $item->email }}</td>

                    <td class="py-3 align-middle" style="color:#475569;">{{ $item->no_whatsapp }}</td>

                    <td class="py-3 pe-4 align-middle text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <a href="{{ route('mahasiswa.show', $item) }}"
                               class="btn btn-sm d-flex align-items-center gap-1"
                               style="background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe; border-radius:8px; font-size:0.78rem; font-weight:600; padding:5px 12px; white-space:nowrap;">
                                <i class="bi bi-eye" style="font-size:0.7rem;"></i> Lihat
                            </a>
                            <a href="{{ route('mahasiswa.edit', $item) }}"
                               class="btn btn-sm d-flex align-items-center gap-1"
                               style="background:#fefce8; color:#ca8a04; border:1px solid #fde68a; border-radius:8px; font-size:0.78rem; font-weight:600; padding:5px 12px; white-space:nowrap;">
                                <i class="bi bi-pencil" style="font-size:0.7rem;"></i> Edit
                            </a>
                            <form action="{{ route('mahasiswa.destroy', $item) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Yakin ingin menghapus data \'{{ addslashes($item->nama) }}\'?')">
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
                    <td colspan="7">
                        <div class="text-center py-5" style="color:#94a3b8;">
                            <div class="mb-3">
                                <span style="display:inline-flex;align-items:center;justify-content:center;width:64px;height:64px;background:#f1f5f9;border-radius:50%;">
                                    <i class="bi bi-person-lines-fill fs-2" style="color:#cbd5e1;"></i>
                                </span>
                            </div>
                            <div class="fw-semibold mb-1" style="font-size:0.95rem; color:#475569;">Belum ada data</div>
                            <div class="mb-4" style="font-size:0.83rem; color:#94a3b8;">Belum ada data mahasiswa yang tersedia.</div>
                            <a href="{{ route('mahasiswa.create') }}" class="btn d-inline-flex align-items-center gap-2" style="background:#16a34a;color:#fff;border-radius:10px;font-size:0.83rem;font-weight:600;padding:9px 20px;">
                                <i class="bi bi-plus-circle-fill"></i> Tambah Data
                            </a>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($data->hasPages())
    <div class="d-flex align-items-center justify-content-between px-4 py-3" style="border-top:1px solid #f1f5f9; background:#fafafa;">
        <p class="mb-0" style="font-size:0.78rem; color:#94a3b8;">
            Menampilkan {{ $data->firstItem() }}–{{ $data->lastItem() }} dari {{ $data->total() }} data
        </p>
        <div class="pagination-modern">
            {{ $data->links() }}
        </div>
    </div>
    @endif
</div>

@endsection

@push('styles')
<style>
    /* Override Bootstrap pagination */
    .pagination-modern .pagination {
        margin: 0;
        gap: 4px;
    }
    .pagination-modern .page-item .page-link {
        border-radius: 8px !important;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: 0.8rem;
        padding: 5px 11px;
        font-weight: 500;
    }
    .pagination-modern .page-item.active .page-link {
        background: #16a34a;
        border-color: #16a34a;
        color: #fff;
    }
    .pagination-modern .page-item .page-link:hover {
        background: #f1f5f9;
        color: #1e293b;
    }
    .pagination-modern .page-item.disabled .page-link {
        color: #cbd5e1;
        background: transparent;
    }
</style>
@endpush
