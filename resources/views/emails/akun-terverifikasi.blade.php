@extends('emails.layout')

@section('content')
    <h1 style="margin:0 0 16px;font-size:18px;line-height:1.4;color:#1e293b;">{{ $judul }}</h1>

    <p style="margin:0 0 12px;">Halo, {{ $nama }}.</p>
    <p style="margin:0 0 16px;">
        Akun SIARKA Anda telah dibuat atau diaktifkan oleh Laboran dengan peran {{ $role }}.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse;font-size:14px;color:#334155;">
        <tr>
            <td style="width:140px;padding:5px 0;vertical-align:top;color:#64748b;">Email login</td>
            <td style="padding:5px 0;vertical-align:top;">{{ $email }}</td>
        </tr>
    </table>

    <p style="margin:16px 0 0;">Password diberikan terpisah oleh Laboran.</p>

    <p style="margin:18px 0 0;">
        <a href="{{ $tautan }}" style="color:#1e293b;">Masuk ke SIARKA</a>
    </p>
@endsection
