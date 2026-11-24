@extends('layouts.app')

@section('title', 'Riwayat Peminjaman')
@section('page-title', 'Riwayat Peminjaman')
@section('page-subtitle', 'Daftar pengajuan peminjaman dan riwayat status transaksi Anda.')

@section('content')

@include('peminjaman._daftar', [
    'kosongJudul' => 'Belum ada riwayat peminjaman',
    'kosongPesan' => 'Pengajuan peminjaman Anda akan tampil di sini.',
])

@endsection
