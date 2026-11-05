@extends('emails.layout')

@section('content')
    <h1 style="margin:0 0 16px;font-size:18px;line-height:1.4;color:#1e293b;">{{ $judul }}</h1>

    <p style="margin:0 0 12px;">Halo, {{ $nama }}.</p>
    <p style="margin:0 0 16px;">{{ $pembuka }}</p>

    @include('emails.partials.peminjaman-rincian')

    <p style="margin:18px 0 0;">
        <a href="{{ $tautan }}" style="color:#1e293b;">Lihat detail peminjaman #{{ $id }}</a>
    </p>
@endsection
