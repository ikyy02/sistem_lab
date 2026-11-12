@extends('layouts.app')

@section('title', 'Riwayat Peminjaman')
@section('page-title', 'Riwayat Peminjaman')
@section('page-subtitle', 'Daftar pengajuan peminjaman dan riwayat status transaksi Anda.')

@section('content')

<div class="d-flex flex-wrap justify-content-end mb-4">
    <a href="{{ route('peminjaman.index') }}" class="btn btn-primary d-flex align-items-center gap-2"><i class="bi bi-plus-lg"></i> Ajukan Peminjaman</a>
</div>

@include('peminjaman._daftar', [
    'kosongJudul' => 'Belum ada riwayat peminjaman',
    'kosongPesan' => 'Pengajuan peminjaman Anda akan tampil di sini.',
])

@endsection
