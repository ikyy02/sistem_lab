{{ $judul }} - peminjaman #{{ $id }}

Halo, {{ $nama }}.

{{ $pembuka }}

Nama penerima: {{ $penerima }}
Waktu pencatatan: {{ $waktu_pencatatan }}

Barang yang dikembalikan:
@forelse ($dikembalikan as $d)
- {{ $d['nama'] }}: {{ $d['jumlah'] }} ({{ $d['kondisi'] }})
@empty
- Tidak ada data barang yang dikembalikan.
@endforelse

Sisa yang belum kembali:
@if (count($sisa) > 0)
@foreach ($sisa as $r)
- {{ $r['nama'] }}: {{ $r['jumlah'] }}
@endforeach
@else
Semua barang sudah dikembalikan.
@endif

@if (count($rusak) > 0)
Perhatian: ada alat yang dikembalikan dalam kondisi rusak.
Alat terkait: {{ collect($rusak)->map(fn ($r) => $r['nama'].' ('.$r['jumlah'].')')->implode(', ') }}. Mohon ditindaklanjuti sesuai prosedur laboratorium.
@endif

@include('emails.partials.peminjaman-rincian-text')

Lihat detail: {{ $tautan }}

Pesan otomatis dari SIARKA, Laboratorium Jurusan Komputer dan Bisnis.
