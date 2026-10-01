@php $report = session('import_report') ?? session('import_report_' . $kategori); @endphp
<!-- Laporan import: baris bermasalah -->
@if($report && ! empty($report['errors']))
<div class="card-modern overflow-hidden mb-3 import-report" style="border-color:#fecdd3;">
    <div class="px-4 py-3 d-flex align-items-start gap-3" style="background:#fff1f2;border-bottom:1px solid #fecdd3;">
        <i class="bi bi-exclamation-triangle-fill" style="color:#be123c;font-size:1.2rem;"></i>
        <div class="flex-grow-1">
            <div class="fw-bold" style="font-size:.92rem;color:#9f1239;">Import dibatalkan: {{ $report['error_count'] }} dari {{ $report['total'] }} baris bermasalah</div>
            <div style="font-size:.8rem;color:#be123c;">Tidak ada data yang disimpan. Perbaiki baris berikut, lalu unggah ulang.</div>
        </div>
        <button type="button" class="btn-close" aria-label="Tutup" onclick="this.closest('.import-report').remove()"></button>
    </div>
    <div class="table-responsive" style="max-height:320px;overflow-y:auto;">
        <table class="table table-sm mb-0" style="font-size:.82rem;">
            <thead><tr><th class="ps-4" style="width:90px;">Baris</th><th style="width:220px;">Nama</th><th>Alasan</th></tr></thead>
            <tbody>
            @foreach($report['errors'] as $e)
                <tr>
                    <td class="ps-4 fw-semibold" style="color:#be123c;">{{ $e['row'] }}</td>
                    <td>{{ ($e['nama'] ?? '') !== '' ? $e['nama'] : '—' }}</td>
                    <td><ul class="mb-0 ps-3">@foreach($e['messages'] as $m)<li>{{ $m }}</li>@endforeach</ul></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @if($report['error_count'] > count($report['errors']))
    <div class="px-4 py-2" style="font-size:.78rem;color:var(--text-muted);border-top:1px solid #F1F1EF;">
        Menampilkan {{ count($report['errors']) }} dari {{ $report['error_count'] }} baris bermasalah. Perbaiki lalu unggah ulang untuk melihat sisanya.
    </div>
    @endif
</div>
@endif
