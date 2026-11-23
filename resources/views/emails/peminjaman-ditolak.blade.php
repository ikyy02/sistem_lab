@extends('emails.layout')

@section('content')
    {{-- Badge Status --}}
    <div style="margin:0 0 20px;">
        <span style="display:inline-block;padding:5px 14px;border-radius:999px;background-color:#fee2e2;color:#b91c1c;font-size:13px;font-weight:600;letter-spacing:.3px;">
            ✕ Ditolak
        </span>
    </div>

    <h1 style="margin:0 0 8px;font-size:20px;line-height:1.3;color:#1e293b;font-weight:700;">
        {{ $judul }}
    </h1>

    <p style="margin:0 0 20px;color:#64748b;font-size:14px;">Peminjaman #{{ $id }}</p>

    <p style="margin:0 0 8px;">Halo, <strong>{{ $nama }}</strong>.</p>
    <p style="margin:0 0 20px;">{{ $pembuka }}</p>

    {{-- Kotak Rincian --}}
    <div style="background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:16px 20px;margin:0 0 20px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
               style="width:100%;border-collapse:collapse;font-size:14px;color:#334155;">
            <tr>
                <td style="width:140px;padding:6px 0;vertical-align:top;color:#64748b;white-space:nowrap;">Nomor Peminjaman</td>
                <td style="padding:6px 0;vertical-align:top;font-weight:600;">#{{ $id }}</td>
            </tr>
            <tr>
                <td style="width:140px;padding:6px 0;vertical-align:top;color:#64748b;">Jenis</td>
                <td style="padding:6px 0;vertical-align:top;">{{ $jenis }}</td>
            </tr>
            <tr>
                <td style="width:140px;padding:6px 0;vertical-align:top;color:#64748b;">Peminjam</td>
                <td style="padding:6px 0;vertical-align:top;">{{ $nama }}</td>
            </tr>
            @if (count($items) > 0)
                <tr>
                    <td style="width:140px;padding:6px 0;vertical-align:top;color:#64748b;">Barang</td>
                    <td style="padding:6px 0;vertical-align:top;">
                        @foreach ($items as $item)
                            <div style="margin-bottom:2px;">
                                {{ $item['nama'] }}
                                <span style="color:#64748b;">({{ $item['jenis'] }})</span>
                                &times; {{ $item['jumlah'] }}
                            </div>
                        @endforeach
                    </td>
                </tr>
            @endif
            @if (count($ruangan) > 0)
                <tr>
                    <td style="width:140px;padding:6px 0;vertical-align:top;color:#64748b;">Ruangan</td>
                    <td style="padding:6px 0;vertical-align:top;">
                        @foreach ($ruangan as $r)
                            <div style="margin-bottom:2px;">
                                <strong>{{ $r['nama'] }}</strong><br>
                                <span style="color:#64748b;font-size:13px;">{{ $r['mulai'] }} &ndash; {{ $r['selesai'] }}</span>
                            </div>
                        @endforeach
                    </td>
                </tr>
            @endif
            <tr>
                <td style="width:140px;padding:6px 0;vertical-align:top;color:#64748b;">Batas Kembali</td>
                <td style="padding:6px 0;vertical-align:top;">{{ $batas_kembali }}</td>
            </tr>
            <tr>
                <td style="width:140px;padding:6px 0;vertical-align:top;color:#64748b;">Status</td>
                <td style="padding:6px 0;vertical-align:top;">
                    <span style="display:inline-block;padding:2px 10px;border-radius:999px;background-color:#fee2e2;color:#b91c1c;font-size:12px;font-weight:600;">
                        {{ $status }}
                    </span>
                </td>
            </tr>
        </table>
    </div>

    {{-- Kotak Alasan --}}
    @php
        $alasan = $peminjaman->alasan_ditolak ?? null;
    @endphp
    @if ($alasan)
        <div style="border-left:3px solid #ef4444;padding:10px 14px;background:#fef2f2;border-radius:0 4px 4px 0;margin:0 0 24px;">
            <p style="margin:0 0 4px;font-size:13px;font-weight:600;color:#991b1b;">Alasan Penolakan</p>
            <p style="margin:0;font-size:14px;color:#7f1d1d;">{{ $alasan }}</p>
        </div>
    @else
        <div style="border-left:3px solid #f59e0b;padding:10px 14px;background:#fffbeb;border-radius:0 4px 4px 0;margin:0 0 24px;font-size:14px;color:#92400e;">
            Jika Anda merasa ada kekeliruan atau ingin mengajukan ulang, silakan hubungi Laboran atau buat pengajuan baru.
        </div>
    @endif

    {{-- Tombol CTA --}}
    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 8px;">
        <tr>
            <td style="border-radius:6px;background-color:#dc2626;">
                <a href="{{ $tautan }}"
                   style="display:inline-block;padding:11px 24px;color:#ffffff;font-size:14px;font-weight:600;text-decoration:none;border-radius:6px;letter-spacing:.2px;">
                    Lihat Detail Peminjaman
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:16px 0 0;font-size:13px;color:#94a3b8;">
        Atau salin tautan ini ke browser:<br>
        <a href="{{ $tautan }}" style="color:#64748b;word-break:break-all;">{{ $tautan }}</a>
    </p>
@endsection
