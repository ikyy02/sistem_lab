{{ $judul }} - Peminjaman #{{ $id }}
========================================

Halo, {{ $nama }}.

{{ $pembuka }}

--- RINCIAN PEMINJAMAN ---

Nomor Peminjaman : #{{ $id }}
Jenis            : {{ $jenis }}
Peminjam         : {{ $nama }}
@if (count($items) > 0)
Barang           :
@foreach ($items as $item)
  - {{ $item['nama'] }} ({{ $item['jenis'] }}) x {{ $item['jumlah'] }}
@endforeach
@endif
@if (count($ruangan) > 0)
Ruangan          :
@foreach ($ruangan as $r)
  - {{ $r['nama'] }} ({{ $r['mulai'] }} - {{ $r['selesai'] }})
@endforeach
@endif
Batas Kembali    : {{ $batas_kembali }}
Status           : {{ $status }}

--------------------------

Pastikan Anda mengambil alat/bahan sesuai jadwal dan mengembalikan
tepat waktu sebelum batas kembali yang tertera.

Lihat detail peminjaman: {{ $tautan }}

________________________________________
Pesan otomatis dari SIARKA, Laboratorium Jurusan Komputer dan Bisnis.
noreply@siarka.dev — Jangan balas email ini.
