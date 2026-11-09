{{-- Badge status peminjaman --}}
@php
    $peta = [
        'menunggu' => ['Menunggu Persetujuan', 'var(--warning-soft, #F6EEDC)', 'var(--warning, #A8741A)', 'rgba(168,116,26,.15)'],
        'disetujui' => ['Disetujui', 'var(--bg-subtle, #E8EEF4)', 'var(--primary, #2F5D8A)', 'rgba(47,93,138,.18)'],
        'ditolak' => ['Ditolak', 'var(--danger-soft, #F6E7E5)', 'var(--danger, #B4473F)', 'rgba(180,71,63,.15)'],
        'selesai' => ['Dikembalikan', 'var(--success-soft, #E5F0EA)', 'var(--success, #3F7A5A)', 'rgba(63,122,90,.15)'],
        'dibatalkan' => ['Dibatalkan', 'var(--bg-subtle, #F1F3F5)', 'var(--text-secondary, #5F6C7B)', 'var(--border, #E3E6EA)'],
    ];
    $s = $peta[$status] ?? [$status, 'var(--bg-subtle, #F1F3F5)', 'var(--text-secondary, #5F6C7B)', 'var(--border, #E3E6EA)'];
@endphp
<span class="badge-status d-inline-flex align-items-center gap-1" style="background:{{ $s[1] }};color:{{ $s[2] }};border:1px solid {{ $s[3] }};">{{ $s[0] }}</span>
@if(($terlambat ?? false) && $status === 'disetujui')
    <span class="badge-status d-inline-flex align-items-center gap-1" style="background:var(--danger-soft, #F6E7E5);color:var(--danger, #B4473F);border:1px solid rgba(180,71,63,.15);">Terlambat</span>
@endif
