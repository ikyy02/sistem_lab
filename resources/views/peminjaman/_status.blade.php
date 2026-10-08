{{-- Badge status peminjaman. Pakai: @include('peminjaman._status', ['status' => $p->status, 'terlambat' => $p->terlambat]) --}}
@php
    $peta = [
        'menunggu' => ['Menunggu Persetujuan', '#fffbeb', '#b45309'],
        'disetujui' => ['Disetujui', '#eff6ff', '#1d4ed8'],
        'ditolak' => ['Ditolak', '#fef2f2', '#b42318'],
        'selesai' => ['Dikembalikan', '#ecfdf3', '#146c43'],
    ];
    $s = $peta[$status] ?? [$status, '#f1f5f9', '#475569'];
@endphp
<span class="badge-status" style="background:{{ $s[1] }};color:{{ $s[2] }};">{{ $s[0] }}</span>
@if(($terlambat ?? false) && $status === 'disetujui')
    <span class="badge-status" style="background:#fef2f2;color:#b42318;">Terlambat</span>
@endif
