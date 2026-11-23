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
@php $alasan = $peminjaman->alasan_ditolak ?? null; @endphp
@if ($alasan)
Alasan Penolakan:
{{ $alasan }}

@else
Jika Anda merasa ada kekeliruan atau ingin mengajukan ulang,
silakan hubungi Laboran atau buat pengajuan baru.

@endif
Lihat detail peminjaman: {{ $tautan }}

________________________________________
Pesan otomatis dari SIARKA, Laboratorium Jurusan Komputer dan Bisnis.
noreply@siarka.dev — Jangan balas email ini.
