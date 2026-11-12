@extends('layouts.app')

@section('title', 'FQA — SIARKA')
@section('page-title', 'FQA')
@section('page-subtitle', 'Pertanyaan yang sering diajukan dan kontak layanan')

@section('content')

<div class="sop-screen-only">
    <div class="card-modern p-4 mb-4">
        <div class="d-flex align-items-center gap-3 mb-4 border-bottom pb-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-info"
                 style="width:48px;height:48px;background:var(--info-soft);color:#0369A1!important;font-size:1.3rem;">F</div>
            <div class="min-w-0 flex-grow-1">
                <h4 class="mb-0">FQA SIARKA</h4>
                <p class="mb-0 small text-muted">Jawaban ringkas mengenai akun pengguna, mekanisme peminjaman, dan prosedur laboratorium.</p>
            </div>
        </div>

        <hr class="my-4" style="border-color:var(--border-color);opacity:0.5">

        <div class="row g-4">
            @forelse($faqs as $kategori => $items)
            <div class="col-12 col-md-6 col-lg-4">
                <h5 class="sop-card-kode mb-3 fw-semibold" style="color:var(--primary);">{{ $kategori }}</h5>
                <ol class="list-group list-group-flush small text-muted">
                    @forelse($items as $item)
                    <li class="list-group-item border-0 bg-transparent py-2">
                        <b>{{ $item->pertanyaan }}</b>
                    </li>
                    @empty
                    <li class="list-group-item border-0 bg-transparent py-2">Belum ada pertanyaan di kategori ini.</li>
                    @endforelse>
                </ol>
            </div>
            @empty
            <p class="sop-panel-desc col-12">Data FAQ belum tersedia. Jalankan <code>php artisan db:seed --class=FaqSeeder</code>.</p>
            @endforelse
        </div>

        <hr class="my-4" style="border-color:var(--border-color);opacity:0.5">

        <div class="sop-panel">
            <h6 class="sop-step-head mb-3">Kontak Layanan Laboratorium</h6>
            <ul class="list-group list-group-flush small text-muted">
                <li class="list-group-item border-0 bg-transparent py-2">
                    <b>Jam operasional:</b> Senin–Kamis 08.00–16.00 WITA; Jumat 08.00–11.30 dan 13.30–16.30 WITA
                </li>
                <li class="list-group-item border-0 bg-transparent py-2">
                    <b>Tempat:</b> Meja Layanan Laboratorium (Lantai 1)
                </li>
                <li class="list-group-item border-0 bg-transparent py-2">
                    <b>Email:</b> silab@institusi.test
                </li>
                <li class="list-group-item border-0 bg-transparent py-2">
                    <b>Telepon:</b> (0xx) xxx-xxxx (ext. 101)
                </li>
            </ul>
        </div>
    </div>

    <div class="print-hidden" style="display:none;">
        @forelse($faqs as $kategori => $items)
        <div class="sop-print-item">
            <h3>{{ $kategori }}</h3>
            @forelse($items as $item)
            <p><b>{{ $item->pertanyaan }}</b>: {{ $item->jawaban }}</p>
            @empty
            <p>Belum ada pertanyaan.</p>
            @endforelse
        </div>
        @endforeach
        <p class="sop-print-foot">SIARKA — FQA pengguna.</p>
    </div>
</div>

{{-- Print rules: hanya tampilkan isi FAQ + print info, sembunyikan sidebar/topbar --}}
@push('styles')
<style>
    @media print {
        #sidebar, #sidebar-overlay, #topbar, .sidebar-collapse-toggle,
        .sop-screen-only, .sop-cetak, .sop-ringkasan-btn, .sop-dialog { display: none !important; }
        #main-wrapper { margin-left: 0 !important; }
        #page-content { padding: 0 !important; overflow: visible !important; }
        body { background: #fff !important; }
        .sop-print-only { display: block !important; }
        .sop-print-item { break-inside: avoid; page-break-inside: avoid; margin-bottom: 20pt; }
        .sop-print-item h3 { font-size: 12pt; margin: 0 0 4pt; }
        .sop-print-item p { font-size: 10pt; line-height: 1.5; color: #222; }
        .sop-print-foot { font-size: 9pt; color: #555; margin-top: 12pt; border-top: 1px solid #ccc; padding-top: 6pt; text-align: center; }
        @page { margin: 15mm; }
    }
</style>
@endpush

<script>
function bukaFaq(el) {
    // Menampilkan jawaban FAQ dalam modal sederhana jika perlu
    const modal = document.getElementById('faqModal');
    if (!modal) return;
    document.getElementById('faqModalLabel').textContent = el.dataset?.pertanyaan || 'FAQ';
    document.getElementById('faqModalBody').textContent = el.dataset?.jawaban || 'Belum ada jawaban.';
    if (typeof modal.showModal === 'function') {
        modal.showModal();
        modal.querySelector('.faq-close').focus();
    } else {
        modal.setAttribute('open', 'open');
    }
}

// detach print-hidden saat print
window.addEventListener('beforeprint', () => {
    document.querySelectorAll('.print-hidden').forEach(el => el.style.display = 'none');
});
window.addEventListener('afterprint', () => {
    document.querySelectorAll('.print-hidden').forEach(el => el.style.display = 'block');
});
</script>

<!-- Modal FAQ individual (fallback) -->
<dialog id="faqModal" class="sop-dialog" aria-labelledby="faqModalLabel" aria-modal="dialog" role="document">
    <div class="sop-dialog-head">
        <div style="min-width:0;">
            <h3 id="faqModalLabel">FAQ</h3>
        </div>
        <button type="button" class="sop-dialog-close" onclick="document.getElementById('faqModal').close()" aria-label="Tutup">&times;</button>
    </div>
    <div class="sop-dialog-body">
        <p id="faqModalBody">Pilih pertanyaan untuk melihat jawaban.</p>
    </div>
    <div class="sop-dialog-foot">
        <button type="button" class="btn btn-outline-silab" onclick="document.getElementById('faqModal').close">Tutup</button>
    </div>
</dialog>

@endsection