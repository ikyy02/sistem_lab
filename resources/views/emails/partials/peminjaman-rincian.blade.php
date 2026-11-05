<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse;font-size:14px;color:#334155;">
    <tr>
        <td style="width:140px;padding:5px 0;vertical-align:top;color:#64748b;">Jenis peminjaman</td>
        <td style="padding:5px 0;vertical-align:top;">{{ $jenis }}</td>
    </tr>
    <tr>
        <td style="width:140px;padding:5px 0;vertical-align:top;color:#64748b;">Peminjam</td>
        <td style="padding:5px 0;vertical-align:top;">{{ $nama }}</td>
    </tr>
    @if (count($items) > 0)
        <tr>
            <td style="width:140px;padding:5px 0;vertical-align:top;color:#64748b;">Barang</td>
            <td style="padding:5px 0;vertical-align:top;">
                @foreach ($items as $item)
                    <div>{{ $item['nama'] }} ({{ $item['jenis'] }}) &times; {{ $item['jumlah'] }}</div>
                @endforeach
            </td>
        </tr>
    @endif
    @if (count($ruangan) > 0)
        <tr>
            <td style="width:140px;padding:5px 0;vertical-align:top;color:#64748b;">Ruangan</td>
            <td style="padding:5px 0;vertical-align:top;">
                @foreach ($ruangan as $r)
                    <div>{{ $r['nama'] }} ({{ $r['mulai'] }} &ndash; {{ $r['selesai'] }})</div>
                @endforeach
            </td>
        </tr>
    @endif
    <tr>
        <td style="width:140px;padding:5px 0;vertical-align:top;color:#64748b;">Batas kembali</td>
        <td style="padding:5px 0;vertical-align:top;">{{ $batas_kembali }}</td>
    </tr>
    <tr>
        <td style="width:140px;padding:5px 0;vertical-align:top;color:#64748b;">Status</td>
        <td style="padding:5px 0;vertical-align:top;">{{ $status }}</td>
    </tr>
</table>
