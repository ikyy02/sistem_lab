@php $report = session('import_report'); @endphp
<!-- Laporan import: baris bermasalah -->
@if($report && ! empty($report['errors']))
<div class="card-modern overflow-hidden mb-4 import-report" style="border-color:#fecdd3;">
    <div class="px-4 py-3 d-flex align-items-start gap-3" style="background:#fff1f2; border-bottom:1px solid #fecdd3;">
        <i class="bi bi-exclamation-triangle-fill" style="color:#e11d48; font-size:1.25rem; line-height:1.4;"></i>
        <div class="flex-grow-1">
            <div class="fw-bold" style="font-size:0.92rem; color:#9f1239;">
                Import dibatalkan: {{ $report['error_count'] }} dari {{ $report['total'] }} baris bermasalah
            </div>
            <div style="font-size:0.8rem; color:#be123c;">
                Tidak ada data yang disimpan. Perbaiki baris di bawah pada file Excel, lalu unggah ulang.
            </div>
        </div>
        <button type="button" class="btn-close" aria-label="Tutup laporan"
                onclick="this.closest('.import-report').remove()"></button>
    </div>

    <div class="table-responsive" style="max-height:360px; overflow-y:auto;">
        <table class="table table-sm mb-0" style="font-size:0.82rem;">
            <thead style="background:#f8fafc; color:#64748b; font-size:0.72rem; text-transform:uppercase; letter-spacing:0.04em; position:sticky; top:0;">
                <tr>
                    <th class="ps-4 py-2 fw-semibold border-0" style="width:90px;">Baris Excel</th>
                    <th class="py-2 fw-semibold border-0" style="width:140px;">NIM</th>
                    <th class="py-2 fw-semibold border-0" style="width:200px;">Nama</th>
                    <th class="py-2 pe-4 fw-semibold border-0">Alasan kesalahan</th>
                </tr>
            </thead>
            <tbody>
            @foreach($report['errors'] as $error)
                <tr style="border-top:1px solid #f1f5f9;">
                    <td class="ps-4 py-2 align-top fw-semibold" style="color:#e11d48;">{{ $error['row'] }}</td>
                    <td class="py-2 align-top" style="color:#475569;">{{ $error['nim'] !== '' ? $error['nim'] : '—' }}</td>
                    <td class="py-2 align-top" style="color:#475569;">{{ $error['nama'] !== '' ? $error['nama'] : '—' }}</td>
                    <td class="py-2 pe-4 align-top" style="color:#334155;">
                        <ul class="mb-0 ps-3">
                            @foreach($error['messages'] as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    @if($report['error_count'] > count($report['errors']))
    <div class="px-4 py-2" style="font-size:0.78rem; color:#94a3b8; border-top:1px solid #f1f5f9; background:#fafafa;">
        Menampilkan {{ count($report['errors']) }} dari {{ $report['error_count'] }} baris bermasalah.
        Perbaiki lalu unggah ulang untuk melihat sisanya.
    </div>
    @endif
</div>
@endif

