@extends('layouts.app')

@section('title', 'Notifikasi')
@section('page-title', 'Notifikasi')
@section('page-subtitle', 'Pemberitahuan aktivitas peminjaman dan akun Anda.')

@section('content')

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="font-display fw-semibold mb-1" style="font-size:1.5rem;">Notifikasi</h2>
        <p class="mb-0" style="font-size:var(--fs-base);color:var(--text-muted);">
            {{ $unread }} notifikasi belum dibaca dari total {{ $data->total() }} notifikasi.
        </p>
    </div>
    @if($unread > 0)
        <form action="{{ route('notifikasi.baca-semua') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-silab d-flex align-items-center gap-2">
                <i class="bi bi-check2-all" aria-hidden="true"></i> Tandai semua dibaca
            </button>
        </form>
    @endif
</div>

<div class="card-modern overflow-hidden">
    @forelse($data as $notif)
        @php $baru = ! $notif->read_at; @endphp
        <div class="d-flex gap-3 p-3 p-lg-4 {{ $baru ? 'notif-baris-baru' : '' }}" style="{{ $loop->last ? '' : 'border-bottom:1px solid var(--border);' }}">
            <span class="notif-dot mt-1 {{ $baru ? '' : 'notif-dot-read' }}"></span>
            <div class="flex-grow-1 min-w-0">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="notif-judul" style="font-size:var(--fs-base);">{{ $notif->data['judul'] ?? 'Notifikasi' }}</span>
                    @if($baru)
                        <span class="badge-status" style="background:var(--bg-subtle);color:var(--primary);border:1px solid rgba(47,93,138,.18);">Baru</span>
                    @endif
                </div>
                <div class="notif-pesan" style="font-size:var(--fs-base);margin-top:3px;">{{ $notif->data['pesan'] ?? '' }}</div>
                <div class="notif-waktu mt-1">{{ $notif->created_at?->format('d/m/Y H:i') }}</div>
            </div>
            @if($baru)
                <form action="{{ route('notifikasi.baca', $notif->id) }}" method="POST" class="align-self-center flex-shrink-0">
                    @csrf
                    <button type="submit" class="btn btn-edit d-flex align-items-center gap-1">
                        <i class="bi bi-check2" aria-hidden="true"></i> Tandai dibaca
                    </button>
                </form>
            @endif
        </div>
    @empty
        <div class="text-center py-5" style="color:var(--text-muted);">
            <i class="bi bi-bell-slash fs-1 d-block mb-2" aria-hidden="true" style="color:#c9bdb4;"></i>
            <div class="fw-semibold mb-1" style="font-size:var(--fs-base);color:var(--text-primary);">Belum ada notifikasi</div>
            <div style="font-size:var(--fs-base);">Notifikasi aktivitas peminjaman dan akun Anda akan muncul di sini.</div>
        </div>
    @endforelse
</div>

@if($data->hasPages())
<div class="card-modern mt-3">
    <div class="table-footer mb-0" style="border-radius:inherit;">
        <div>Menampilkan {{ $data->firstItem() }}–{{ $data->lastItem() }} dari {{ $data->total() }} data</div>
        {{ $data->links('pagination.silab') }}
    </div>
</div>
@endif

@endsection
