@php $report = session('import_report') ?? session('import_report_' . $kategori); @endphp
<!-- Laporan import: baris bermasalah -->
@if($report && ! empty($report['errors']))
<div class="card-modern overflow-hidden mb-3 import-report" role="alert" style="border-color:#fecdd3;">
    <div class="px-4 py-3 d-flex align-items-start gap-3" style="background:#fff1f2;border-bottom:1px solid #fecdd3;">
        <i class="bi bi-exclamation-triangle-fill" aria-hidden="true" style="color:#be123c;font-size:1.2rem;"></i>
        <div class="flex-grow-1">
            <div class="fw-bold" style="font-size:.92rem;color:var(--danger-hover);">Import dibatalkan: {{ $report['error_count'] }} dari {{ $report['total'] }} baris bermasalah</div>
            <div style="font-size:.875rem;color:#be123c;">Tidak ada data yang disimpan. Perbaiki baris berikut, lalu unggah ulang.</div>
        </div>
        <button type="button" class="btn-close" aria-label="Tutup" data-tutup-laporan></button>
    </div>
    <div class="table-responsive" style="max-height:320px;overflow-y:auto;" tabindex="0" role="region" aria-label="Daftar baris bermasalah">
        <table class="table table-sm mb-0" style="font-size:.875rem;">
<caption class="visually-hidden">Baris impor yang bermasalah</caption>
            <thead><tr><th scope="col" class="ps-4" style="width:90px;">Baris</th><th scope="col" style="width:220px;">Nama</th><th scope="col">Alasan</th></tr></thead>
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
    <div class="px-4 py-2" style="font-size:.875rem;color:var(--text-muted);border-top:1px solid #F1F1EF;">
        Menampilkan {{ count($report['errors']) }} dari {{ $report['error_count'] }} baris bermasalah. Perbaiki lalu unggah ulang untuk melihat sisanya.
    </div>
    @endif
</div>
@endif

@if($report && ! empty($report['credentials']))
<!-- Password awal: tampil SEKALI, tidak disimpan di tempat lain -->
<div class="card-modern overflow-hidden mb-3 import-report" role="status" style="border-color:#fde68a;">
    <div class="px-4 py-3 d-flex align-items-start gap-3" style="background:#fffbeb;border-bottom:1px solid #fde68a;">
        <i class="bi bi-key-fill" aria-hidden="true" style="color:var(--warning-text);font-size:1.2rem;"></i>
        <div class="flex-grow-1">
            <div class="fw-bold" style="font-size:.92rem;color:var(--warning-text);">Password awal {{ count($report['credentials']) }} akun mahasiswa</div>
            <div style="font-size:.875rem;color:var(--warning-text);">Catat sekarang. Daftar ini hanya tampil sekali dan hilang saat halaman dimuat ulang. Mahasiswa wajib mengganti password saat login pertama.</div>
        </div>
        <button type="button" class="btn-close" aria-label="Tutup" data-konfirmasi="Daftar password tidak bisa dilihat lagi setelah ditutup. Lanjutkan?" data-tutup-laporan></button>
    </div>
    <div class="px-4 py-2 d-flex gap-2 flex-wrap" style="border-bottom:1px solid var(--border);">
        <button type="button" class="btn btn-outline-silab btn-sm" data-salin-password><i class="bi bi-clipboard" aria-hidden="true"></i> Salin semua</button>
        <button type="button" class="btn btn-outline-silab btn-sm" data-unduh-password><i class="bi bi-download" aria-hidden="true"></i> Unduh CSV</button>
        <span class="visually-hidden" role="status" id="aksi-password-status"></span>
    </div>
    <div class="table-responsive" style="max-height:320px;overflow-y:auto;" tabindex="0" role="region" aria-label="Daftar password awal">
        <table class="table table-sm mb-0" style="font-size:.875rem;">
<caption class="visually-hidden">Password awal akun mahasiswa hasil impor</caption>
            <thead><tr><th scope="col" class="ps-4">Email</th><th scope="col">Password awal</th></tr></thead>
            <tbody>
            @foreach($report['credentials'] as $c)
                <tr data-cred><td class="ps-4">{{ $c['email'] }}</td><td><code>{{ $c['password'] }}</code></td></tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@once
@push('scripts')
<script>
document.addEventListener('click', function (e) {
    const tutup = e.target.closest('[data-tutup-laporan]');
    if (tutup) {
        const pesan = tutup.dataset.konfirmasi;
        if (!pesan || confirm(pesan)) tutup.closest('.import-report').remove();
        return;
    }
    const kartu = e.target.closest('.import-report');
    const baris = () => Array.from(kartu.querySelectorAll('tr[data-cred]')).map(tr => [tr.cells[0].textContent.trim(), tr.cells[1].textContent.trim()]);
    const status = t => { const s = kartu.querySelector('#aksi-password-status'); if (s) s.textContent = t; };
    if (e.target.closest('[data-salin-password]')) {
        const teks = baris().map(b => b.join('\t')).join('\n');
        (navigator.clipboard ? navigator.clipboard.writeText(teks) : Promise.reject()).then(() => status('Daftar password disalin.'), () => status('Gagal menyalin. Gunakan Unduh CSV.'));
    }
    if (e.target.closest('[data-unduh-password]')) {
        const esc = v => '"' + v.replace(/"/g, '""') + '"';
        const csv = 'email,password\n' + baris().map(b => b.map(esc).join(',')).join('\n');
        const a = document.createElement('a');
        a.href = URL.createObjectURL(new Blob([csv], {type: 'text/csv'}));
        a.download = 'password-awal-mahasiswa.csv';
        a.click(); URL.revokeObjectURL(a.href);
        status('File CSV diunduh.');
    }
});
</script>
@endpush
@endonce
