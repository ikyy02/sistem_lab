@extends('emails.layout')

@section('content')
    <h1 style="margin:0 0 16px;font-size:18px;line-height:1.4;color:#1e293b;">{{ $judul }}</h1>

    <p style="margin:0 0 12px;">Halo, {{ $nama }}.</p>
    <p style="margin:0 0 16px;">{{ $pembuka }}</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse;font-size:14px;color:#334155;margin:0 0 16px;">
        <tr>
            <td style="width:140px;padding:5px 0;vertical-align:top;color:#64748b;">Nama penerima</td>
            <td style="padding:5px 0;vertical-align:top;">{{ $penerima }}</td>
        </tr>
        <tr>
            <td style="width:140px;padding:5px 0;vertical-align:top;color:#64748b;">Waktu pencatatan</td>
            <td style="padding:5px 0;vertical-align:top;">{{ $waktu_pencatatan }}</td>
        </tr>
    </table>

    <h2 style="margin:0 0 8px;font-size:15px;line-height:1.4;color:#1e293b;">Barang yang dikembalikan</h2>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse;font-size:14px;color:#334155;margin:0 0 16px;">
        @forelse ($dikembalikan as $d)
            <tr>
                <td style="padding:5px 0;border-bottom:1px solid #f1f5f9;">{{ $d['nama'] }}</td>
                <td style="padding:5px 0;border-bottom:1px solid #f1f5f9;width:70px;text-align:right;">{{ $d['jumlah'] }}</td>
                <td style="padding:5px 0;border-bottom:1px solid #f1f5f9;width:80px;text-align:right;">{{ $d['kondisi'] }}</td>
            </tr>
        @empty
            <tr>
                <td style="padding:5px 0;">Tidak ada data barang yang dikembalikan.</td>
            </tr>
        @endforelse
    </table>

    <h2 style="margin:0 0 8px;font-size:15px;line-height:1.4;color:#1e293b;">Sisa yang belum kembali</h2>
    @if (count($sisa) > 0)
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse;font-size:14px;color:#334155;margin:0 0 16px;">
            @foreach ($sisa as $r)
                <tr>
                    <td style="padding:5px 0;border-bottom:1px solid #f1f5f9;">{{ $r['nama'] }}</td>
                    <td style="padding:5px 0;border-bottom:1px solid #f1f5f9;width:70px;text-align:right;">{{ $r['jumlah'] }}</td>
                </tr>
            @endforeach
        </table>
    @else
        <p style="margin:0 0 16px;">Semua barang sudah dikembalikan.</p>
    @endif

    @if (count($rusak) > 0)
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;margin:0 0 16px;background-color:#fef2f2;">
            <tr>
                <td style="padding:12px 14px;font-size:14px;color:#7f1d1d;">
                    <strong>Perhatian: ada alat yang dikembalikan dalam kondisi rusak.</strong><br>
                    Alat terkait:
                    {{ collect($rusak)->map(fn ($r) => $r['nama'].' ('.$r['jumlah'].')')->implode(', ') }}.
                    Mohon ditindaklanjuti sesuai prosedur laboratorium.
                </td>
            </tr>
        </table>
    @endif

    @include('emails.partials.peminjaman-rincian')

    <p style="margin:18px 0 0;">
        <a href="{{ $tautan }}" style="color:#1e293b;">Lihat detail peminjaman #{{ $id }}</a>
    </p>
@endsection
