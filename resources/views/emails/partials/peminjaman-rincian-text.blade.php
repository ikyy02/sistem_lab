Jenis peminjaman: {{ $jenis }}
Peminjam: {{ $nama }}
@if (count($items) > 0)
Barang:
@foreach ($items as $item)
- {{ $item['nama'] }} ({{ $item['jenis'] }}) x {{ $item['jumlah'] }}
@endforeach
@endif
@if (count($ruangan) > 0)
Ruangan:
@foreach ($ruangan as $r)
- {{ $r['nama'] }} ({{ $r['mulai'] }} - {{ $r['selesai'] }})
@endforeach
@endif
Batas kembali: {{ $batas_kembali }}
Status: {{ $status }}
