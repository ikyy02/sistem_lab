@extends('layouts.app')

@section('title', 'Peminjaman Saya')
@section('page-title', 'Peminjaman Saya')
@section('page-subtitle', 'Pengajuan peminjaman Anda yang belum disetujui (menunggu persetujuan).')

@section('content')

<div class="d-flex flex-wrap justify-content-end mb-4">
    <a href="{{ route('peminjaman.index') }}" class="btn btn-primary d-flex align-items-center gap-2"><i class="bi bi-plus-lg"></i> Ajukan Peminjaman</a>
</div>

@include('peminjaman._daftar', [
    'kosongJudul' => 'Tidak ada pengajuan yang menunggu persetujuan',
    'kosongPesan' => 'Semua pengajuan Anda sudah diproses. Lihat Riwayat Peminjaman.',
])

@endsection
