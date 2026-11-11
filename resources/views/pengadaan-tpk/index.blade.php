@extends('layouts.app')

@section('title', 'Pengadaan TPK')
@section('page-title', 'Pengadaan TPK')
@section('page-subtitle', 'Prioritas pengadaan alat dan bahan')

@push('styles')
<style>
    /* Modul TPK: memakai token global siarka.css; tidak ada palet sendiri. */
    .tpk-bar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; }
    .tpk-konteks { margin: 0; font-size: var(--fs-base); color: var(--text-secondary); }
    .tpk-konteks strong { color: var(--text-primary); font-weight: 600; }

    .tpk-panel { background: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-lg); }
    .tpk-param { padding: 16px; margin-bottom: 16px; }
    .tpk-label { display: block; font-size: var(--fs-sm); font-weight: 500; color: var(--text-secondary); margin-bottom: 6px; }
    .tpk-sub { font-size: var(--fs-xs); color: var(--text-muted); }

    .tpk-presets { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
    .tpk-preset {
        text-align: left; padding: 8px 12px; background: var(--bg-surface); color: var(--text-primary);
        border: 1px solid var(--border-input); border-radius: var(--radius-md); cursor: pointer; line-height: 1.3;
        transition: background-color 150ms var(--ease-standard), border-color 150ms var(--ease-standard);
    }
    .tpk-preset:hover { background: var(--bg-subtle); }
    .tpk-preset[aria-pressed="true"] { background: var(--brand-soft); border-color: var(--brand); box-shadow: inset 0 0 0 1px var(--brand); }
    .tpk-preset b { display: block; font-size: var(--fs-sm); font-weight: 600; }
    .tpk-preset span { font-size: var(--fs-xs); color: var(--text-secondary); font-variant-numeric: tabular-nums; }

    .tpk-manual { margin-top: 12px; padding: 12px 16px; background: var(--bg-subtle); border-radius: var(--radius-md); }
    .tpk-manual-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)) auto; gap: 12px; align-items: end; }
    .tpk-total { font-size: var(--fs-sm); font-weight: 600; padding: 6px 10px; border-radius: var(--radius-md); white-space: nowrap; }
    .tpk-total.ok { background: var(--success-soft); color: var(--success-text); }
    .tpk-total.bad { background: var(--danger-soft); color: var(--danger-text); }

    .tpk-ringkas { display: grid; grid-template-columns: 2fr 1fr 1fr; margin-bottom: 16px; }
    .tpk-ringkas > div { padding: 14px 16px; min-width: 0; }
    .tpk-ringkas > div + div { border-left: 1px solid var(--border); }
    .tpk-ringkas .k { font-size: var(--fs-xs); font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--text-secondary); }
    .tpk-ringkas .v { font-size: var(--fs-lg); font-weight: 600; color: var(--text-primary); line-height: 1.3; margin-top: 2px; }

    .tpk-tabs { display: flex; gap: 4px; padding: 0 12px; border-bottom: 1px solid var(--border); overflow-x: auto; }
    .tpk-tab {
        border: 0; background: none; padding: 12px; font-size: var(--fs-base); font-weight: 500; color: var(--text-secondary);
        border-bottom: 2px solid transparent; margin-bottom: -1px; white-space: nowrap; display: inline-flex; align-items: center; gap: 8px;
    }
    .tpk-tab:hover { color: var(--text-primary); }
    .tpk-tab.active { color: var(--brand); border-bottom-color: var(--brand); font-weight: 600; }
    .tpk-count { font-size: var(--fs-xs); padding: 1px 7px; border-radius: 999px; background: var(--bg-subtle); color: var(--text-secondary); font-weight: 600; }

    .tpk-table { width: 100%; margin: 0; border-collapse: collapse; font-variant-numeric: tabular-nums; }
    .tpk-table th { background: var(--bg-subtle); color: var(--text-secondary); font-size: var(--fs-xs); font-weight: 600; text-transform: uppercase; letter-spacing: .04em; padding: 10px 12px; white-space: nowrap; border-bottom: 1px solid var(--border); }
    .tpk-table td { padding: 10px 12px; font-size: var(--fs-base); border-bottom: 1px solid var(--border); vertical-align: middle; }
    .tpk-table tbody tr:hover td { background: var(--bg-subtle); }
    .tpk-table .nama { font-weight: 500; color: var(--text-primary); }
    .tpk-table .meta { font-size: var(--fs-xs); color: var(--text-secondary); }
    .tpk-rank { display: inline-flex; align-items: center; justify-content: center; min-width: 28px; height: 28px; border-radius: var(--radius-md); font-weight: 600; font-size: var(--fs-sm); background: var(--bg-subtle); color: var(--text-secondary); }
    .tpk-rank.top { background: var(--brand); color: #fff; }
    .tpk-skor { display: flex; align-items: center; gap: 8px; min-width: 150px; }
    .tpk-skor b { font-weight: 600; min-width: 48px; }
    .tpk-bar-track { flex: 1; height: 6px; background: var(--border); border-radius: 999px; overflow: hidden; }
    .tpk-bar-fill { height: 100%; background: var(--brand); border-radius: 999px; }
    .tpk-stok-kritis { color: var(--danger-text); font-weight: 600; }
    .tpk-prio { display: inline-block; padding: 2px 8px; border-radius: var(--radius-md); font-size: var(--fs-xs); font-weight: 600; white-space: nowrap; }
    .tpk-prio-1 { background: var(--danger-soft); color: var(--danger-text); }
    .tpk-prio-2 { background: var(--warning-soft); color: var(--warning-text); }
    .tpk-prio-3 { background: var(--info-soft); color: var(--info-text); }
    .tpk-prio-4 { background: var(--bg-subtle); color: var(--text-secondary); }
    .tpk-qty { width: 80px; text-align: center; }

    .tpk-usulan {
        position: sticky; bottom: 0; z-index: 5; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 8px 16px;
        padding: 12px 16px; background: var(--bg-surface); border-top: 1px solid var(--border-strong); border-radius: 0 0 var(--radius-lg) var(--radius-lg);
    }
    .tpk-usulan .total { font-size: var(--fs-lg); font-weight: 600; font-variant-numeric: tabular-nums; }

    .tpk-pane { padding: 16px; }
    .tpk-acuan { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin: 12px 0 16px; }
    .tpk-acuan > div { padding: 10px 12px; background: var(--bg-subtle); border-radius: var(--radius-md); }
    .tpk-acuan .k { display: block; font-size: var(--fs-xs); color: var(--text-secondary); }
    .tpk-acuan .v { font-size: var(--fs-md); font-weight: 600; }
    .tpk-rumus { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: var(--fs-sm); padding: 10px 12px; background: var(--bg-subtle); border-radius: var(--radius-md); }
    .tpk-matriks th, .tpk-matriks td { text-align: right; }
    .tpk-matriks th:nth-child(-n+2), .tpk-matriks td:nth-child(-n+2) { text-align: left; }

    @media (max-width: 991.98px) {
        .tpk-presets { grid-template-columns: 1fr; }
        .tpk-manual-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .tpk-manual-grid .tpk-total { grid-column: 1 / -1; }
        .tpk-ringkas { grid-template-columns: 1fr 1fr; }
        .tpk-ringkas > div:first-child { grid-column: 1 / -1; border-bottom: 1px solid var(--border); }
        .tpk-ringkas > div:nth-child(2) { border-left: 0; }
        .tpk-acuan { grid-template-columns: 1fr; }
    }
    /* Ponsel: baris tabel menjadi kartu bertumpuk agar tidak perlu geser horizontal. */
    @media (max-width: 767.98px) {
        .tpk-rank-table thead { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); }
        .tpk-rank-table, .tpk-rank-table tbody, .tpk-rank-table tr, .tpk-rank-table td { display: block; width: 100%; }
        .tpk-rank-table tr { padding: 12px 0; border-bottom: 1px solid var(--border); }
        .tpk-rank-table td { border: 0; padding: 4px 16px; display: flex; justify-content: space-between; align-items: center; gap: 12px; text-align: right; }
        .tpk-rank-table td[data-label]::before { content: attr(data-label); font-size: var(--fs-xs); color: var(--text-secondary); text-align: left; }
        .tpk-rank-table td.judul { justify-content: flex-start; text-align: left; }
        .tpk-rank-table td.judul::before { content: none; }
        .tpk-rank-table tbody tr:hover td { background: none; }
        .tpk-manual-grid { grid-template-columns: 1fr; }
        .tpk-bantuan { display: none; }
        .tpk-usulan { justify-content: flex-end; padding: 8px 16px; }
        .tpk-ringkas { grid-template-columns: 1fr; }
        .tpk-ringkas > div + div { border-left: 0; border-top: 1px solid var(--border); }
    }
</style>
@endpush

@section('content')
@php
    $persen = fn ($x) => (int) round($x * 100);
    $presetAktif = '';
    foreach ($presets as $kunci => $pr) {
        if (abs($pr['bobot'][0] - $w1) < 0.0001 && abs($pr['bobot'][1] - $w2) < 0.0001 && abs($pr['bobot'][2] - $w3) < 0.0001) { $presetAktif = $kunci; break; }
    }
    $bulanLabel = \Carbon\Carbon::parse($bulan.'-01')->translatedFormat('F Y');
    $jenisLabel = ['semua' => 'Alat dan bahan', 'alat' => 'Alat', 'bahan' => 'Bahan'][$jenis] ?? 'Alat dan bahan';
    $jumlahPrio = array_count_values(array_column($items, 'prioritas_label'));
    $kelasPrio = ['Sangat Tinggi' => 'tpk-prio-1', 'Tinggi' => 'tpk-prio-2', 'Sedang' => 'tpk-prio-3', 'Rendah' => 'tpk-prio-4'];
    $manualTerbuka = ! $bobotValid || $presetAktif === '';
@endphp

<div class="tpk-bar">
    <p class="tpk-konteks">
        Periode <strong>{{ $bulanLabel }}</strong> &middot; {{ $jenisLabel }} &middot;
        Bobot Stok <strong>{{ $persen($w1) }}</strong> / Peminjaman <strong>{{ $persen($w2) }}</strong> / Harga <strong>{{ $persen($w3) }}</strong>
    </p>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('pengadaan-tpk.cetak', request()->query()) }}" target="_blank" rel="noopener" class="btn btn-outline-silab btn-sm">
            <i class="bi bi-printer" aria-hidden="true"></i> Cetak rekomendasi
        </a>
        <a href="{{ route('pengadaan-tpk.export-csv', request()->query()) }}" class="btn btn-outline-silab btn-sm">
            <i class="bi bi-download" aria-hidden="true"></i> Unduh CSV
        </a>
    </div>
</div>

@if(! $bobotValid)
    <div class="alert alert-warning d-flex align-items-start gap-2 mb-3" role="alert">
        <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
        <div>Jumlah bobot harus tepat 100%. Sistem memakai bobot standar (40 / 30 / 30) sampai bobot diperbaiki.</div>
    </div>
@endif

{{-- 1. Atur parameter --}}
<form action="{{ route('pengadaan-tpk.index') }}" method="GET" id="form-tpk" class="tpk-panel tpk-param">
    <div class="row g-3 align-items-end">
        <div class="col-6 col-lg-3">
            <label class="tpk-label" for="f-bulan">Bulan peminjaman</label>
            <select name="bulan" id="f-bulan" class="form-select form-select-sm" onchange="this.form.submit()">
                @foreach($daftarBulan as $val => $lbl)
                    <option value="{{ $val }}" {{ $bulan === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-lg-2">
            <label class="tpk-label" for="f-jenis">Kategori</label>
            <select name="jenis" id="f-jenis" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="semua" {{ $jenis === 'semua' ? 'selected' : '' }}>Semua</option>
                <option value="alat" {{ $jenis === 'alat' ? 'selected' : '' }}>Alat</option>
                <option value="bahan" {{ $jenis === 'bahan' ? 'selected' : '' }}>Bahan</option>
            </select>
        </div>
        <div class="col-12 col-lg-7">
            <label class="tpk-label" for="f-cari">Cari barang</label>
            <div class="input-group input-group-sm">
                <input type="search" name="search" id="f-cari" class="form-control" placeholder="Nama, jenis, atau satuan" value="{{ $search }}">
                @if($search !== '')
                    <a href="{{ route('pengadaan-tpk.index', request()->except('search')) }}" class="btn btn-outline-silab btn-sm">Hapus</a>
                @endif
                <button type="submit" class="btn btn-primary btn-sm js-submit-bobot">Terapkan</button>
            </div>
        </div>

        <div class="col-12">
            <span class="tpk-label" id="lbl-preset">Bobot kriteria <span class="tpk-sub">(Stok &middot; Peminjaman &middot; Harga)</span></span>
            <div class="tpk-presets" role="group" aria-labelledby="lbl-preset">
                @foreach($presets as $kunci => $pr)
                    <button type="submit" name="preset" value="{{ $kunci }}" class="tpk-preset" aria-pressed="{{ $presetAktif === $kunci ? 'true' : 'false' }}">
                        <b>{{ $pr['nama'] }}</b>
                        <span>{{ $persen($pr['bobot'][0]) }} &middot; {{ $persen($pr['bobot'][1]) }} &middot; {{ $persen($pr['bobot'][2]) }}</span>
                    </button>
                @endforeach
            </div>
            <button type="button" class="btn btn-link btn-sm px-0 mt-2" data-bs-toggle="collapse" data-bs-target="#bobot-manual" aria-expanded="{{ $manualTerbuka ? 'true' : 'false' }}" aria-controls="bobot-manual">
                Atur bobot sendiri
            </button>
            <div class="collapse {{ $manualTerbuka ? 'show' : '' }}" id="bobot-manual">
                <div class="tpk-manual">
                    <div class="tpk-manual-grid">
                        <div>
                            <label class="tpk-label" for="w1">Stok (W1)</label>
                            <input type="number" step="0.05" min="0" max="1" name="w1" id="w1" class="form-control form-control-sm" value="{{ $w1 }}" oninput="updateTotalBobot()">
                        </div>
                        <div>
                            <label class="tpk-label" for="w2">Peminjaman (W2)</label>
                            <input type="number" step="0.05" min="0" max="1" name="w2" id="w2" class="form-control form-control-sm" value="{{ $w2 }}" oninput="updateTotalBobot()">
                        </div>
                        <div>
                            <label class="tpk-label" for="w3">Harga (W3)</label>
                            <input type="number" step="0.05" min="0" max="1" name="w3" id="w3" class="form-control form-control-sm" value="{{ $w3 }}" oninput="updateTotalBobot()">
                        </div>
                        <div class="tpk-total ok" id="total-bobot-badge" role="status">Total 100%</div>
                    </div>
                    <div class="tpk-sub mt-2">Jumlah tiga bobot harus 1,0 (100%). Klik Terapkan untuk menghitung ulang.</div>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- 2. Ringkasan hasil --}}
<div class="tpk-panel tpk-ringkas">
    <div>
        <div class="k">Prioritas teratas</div>
        <div class="v text-truncate" title="{{ $topItem['nama'] ?? '' }}">{{ $topItem['nama'] ?? 'Belum ada data' }}</div>
        @if($topItem)
            <div class="tpk-sub">Skor {{ number_format($topItem['nilai'], 4) }} &middot; sisa stok {{ $topItem['stok'] }} {{ $topItem['nama_satuan'] ?? '' }}</div>
        @endif
    </div>
    <div>
        <div class="k">Item dianalisis</div>
        <div class="v">{{ $totalItem }}</div>
        <div class="tpk-sub">{{ $jenisLabel }}</div>
    </div>
    <div>
        <div class="k">Prioritas sangat tinggi</div>
        <div class="v">{{ $jumlahPrio['Sangat Tinggi'] ?? 0 }}</div>
        <div class="tpk-sub">20% peringkat teratas</div>
    </div>
</div>

{{-- 3. Peringkat, perhitungan, metode --}}
<div class="tpk-panel">
    <div class="tpk-tabs" role="tablist" aria-label="Hasil TPK">
        <button class="tpk-tab active" id="tab-rekomendasi" data-bs-toggle="tab" data-bs-target="#content-rekomendasi" type="button" role="tab" aria-controls="content-rekomendasi" aria-selected="true">
            Peringkat prioritas <span class="tpk-count">{{ count($filteredItems) }}</span>
        </button>
        <button class="tpk-tab" id="tab-matriks" data-bs-toggle="tab" data-bs-target="#content-matriks" type="button" role="tab" aria-controls="content-matriks" aria-selected="false" tabindex="-1">
            Matriks Keputusan
        </button>
        <button class="tpk-tab" id="tab-teori" data-bs-toggle="tab" data-bs-target="#content-teori" type="button" role="tab" aria-controls="content-teori" aria-selected="false" tabindex="-1">
            Metode
        </button>
    </div>

    <div class="tab-content">
        {{-- Peringkat + simulasi usulan --}}
        <div class="tab-pane fade show active" id="content-rekomendasi" role="tabpanel" aria-labelledby="tab-rekomendasi" tabindex="0">
            <div class="table-responsive">
                <table class="tpk-table tpk-rank-table">
                    <caption class="visually-hidden">Peringkat prioritas pengadaan, diurutkan dari skor preferensi tertinggi</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="text-center" style="width:64px;">Rank</th>
                            <th scope="col">Alat / bahan</th>
                            <th scope="col" class="text-end">Sisa stok</th>
                            <th scope="col" class="text-end">Dipinjam</th>
                            <th scope="col" class="text-end">Harga satuan</th>
                            <th scope="col">Skor Preferensi (V)</th>
                            <th scope="col">Prioritas</th>
                            <th scope="col" class="text-center">Usulan</th>
                            <th scope="col" class="text-end">Estimasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($filteredItems as $item)
                            @php $skor = min(100, max(5, $persen($item['nilai']))); @endphp
                            <tr>
                                <td class="text-center judul"><span class="tpk-rank {{ $item['ranking'] === 1 ? 'top' : '' }}">{{ $item['ranking'] }}</span></td>
                                <td class="judul">
                                    <div class="nama">{{ $item['nama'] }}</div>
                                    <div class="meta">{{ ucfirst($item['jenis']) }} &middot; {{ $item['nama_ruangan'] ?? '-' }}</div>
                                </td>
                                <td class="text-end" data-label="Sisa stok">
                                    <span class="{{ $item['stok'] <= 2 ? 'tpk-stok-kritis' : '' }}">{{ $item['stok'] }}</span>
                                    <span class="meta">{{ $item['nama_satuan'] ?? '' }}</span>
                                </td>
                                <td class="text-end" data-label="Dipinjam">{{ $item['jumlah_peminjaman'] }}&times;</td>
                                <td class="text-end" data-label="Harga satuan">Rp {{ number_format($item['harga'], 0, ',', '.') }}</td>
                                <td data-label="Skor">
                                    <div class="tpk-skor">
                                        <b>{{ number_format($item['nilai'], 4) }}</b>
                                        <div class="tpk-bar-track" aria-hidden="true"><div class="tpk-bar-fill" style="width:{{ $skor }}%;"></div></div>
                                    </div>
                                </td>
                                <td data-label="Prioritas"><span class="tpk-prio {{ $kelasPrio[$item['prioritas_label']] ?? 'tpk-prio-4' }}">{{ $item['prioritas_label'] }}</span></td>
                                <td class="text-center" data-label="Usulan">
                                    <input type="number" min="0" step="1" inputmode="numeric" class="form-control form-control-sm tpk-qty usulan-qty" data-harga="{{ $item['harga'] }}" placeholder="0" aria-label="Jumlah usulan {{ $item['nama'] }}" oninput="hitungSimulasi(this)">
                                </td>
                                <td class="text-end estimasi-baris" data-label="Estimasi">Rp 0</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center py-4" style="color:var(--text-secondary);">Tidak ada alat atau bahan yang sesuai dengan pencarian.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="tpk-usulan">
                <div class="tpk-sub tpk-bantuan" style="font-size:var(--fs-sm);">
                    Menampilkan <strong>{{ count($filteredItems) }}</strong> dari <strong>{{ $totalItem }}</strong> item. Isi kolom <strong>Usulan</strong> untuk menyusun estimasi anggaran.
                </div>
                <div class="text-end" aria-live="polite">
                    <span class="tpk-sub" id="jumlah-usulan">0 item diusulkan</span>
                    <div class="total" id="total-anggaran-badge">Rp 0</div>
                </div>
            </div>
        </div>

        {{-- Matriks keputusan --}}
        <div class="tab-pane fade tpk-pane" id="content-matriks" role="tabpanel" aria-labelledby="tab-matriks" tabindex="0">
            <h2 class="h-card">Titik acuan normalisasi</h2>
            <div class="tpk-acuan">
                <div><span class="k">Stok terendah (cost)</span><span class="v">{{ $stats['min_stok'] ?? 0 }}</span></div>
                <div><span class="k">Peminjaman tertinggi (benefit)</span><span class="v">{{ $stats['max_pinjam'] ?? 0 }} transaksi</span></div>
                <div><span class="k">Harga terendah (cost)</span><span class="v">Rp {{ number_format($stats['min_harga'] ?? 0, 0, ',', '.') }}</span></div>
            </div>
            <h2 class="h-card mb-2">Matriks awal (X) dan normalisasi (R)</h2>
            <div class="table-responsive">
                <table class="tpk-table tpk-matriks">
                    <caption class="visually-hidden">Matriks keputusan awal dan matriks normalisasi tiap alternatif</caption>
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Alternatif</th>
                            <th scope="col">C1 Stok</th>
                            <th scope="col">C2 Pinjam</th>
                            <th scope="col">C3 Harga</th>
                            <th scope="col">R1</th>
                            <th scope="col">R2</th>
                            <th scope="col">R3</th>
                            <th scope="col">Nilai V</th>
                            <th scope="col">Rank</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($filteredItems as $idx => $it)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td class="nama">{{ $it['nama'] }}</td>
                                <td>{{ $it['stok'] }}</td>
                                <td>{{ $it['jumlah_peminjaman'] }}</td>
                                <td>Rp {{ number_format($it['harga'], 0, ',', '.') }}</td>
                                <td>{{ number_format($it['r'][0], 4) }}</td>
                                <td>{{ number_format($it['r'][1], 4) }}</td>
                                <td>{{ number_format($it['r'][2], 4) }}</td>
                                <td><strong>{{ number_format($it['nilai'], 4) }}</strong></td>
                                <td>{{ $it['ranking'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Metode --}}
        <div class="tab-pane fade tpk-pane" id="content-teori" role="tabpanel" aria-labelledby="tab-teori" tabindex="0">
            <div class="row g-4">
                <div class="col-12 col-lg-6">
                    <h2 class="h-card mb-2">Cara kerja</h2>
                    <p style="color:var(--text-secondary);">Peringkat dihitung dengan metode <strong>Simple Additive Weighting (SAW)</strong>. Bobot tiap kriteria ditetapkan lebih dulu lewat pertimbangan AHP di luar sistem; SIARKA hanya menerima bobot akhir dan menghitung peringkat dari data stok, peminjaman, dan harga.</p>
                    <ul class="ps-3 mb-0" style="color:var(--text-secondary);">
                        <li><strong>C1 Stok</strong> (cost): makin sedikit, makin prioritas.</li>
                        <li><strong>C2 Peminjaman</strong> (benefit): jumlah transaksi disetujui atau selesai pada bulan terpilih.</li>
                        <li><strong>C3 Harga</strong> (cost): makin murah, makin mudah diusulkan.</li>
                    </ul>
                </div>
                <div class="col-12 col-lg-6">
                    <h2 class="h-card mb-2">Rumus</h2>
                    <div class="tpk-rumus mb-2">Benefit: R = X / Max(X)<br>Cost: R = Min(X) / X</div>
                    <div class="tpk-rumus mb-3">V = (W1 &times; R1) + (W2 &times; R2) + (W3 &times; R3)</div>
                    <h2 class="h-card mb-2">Label prioritas</h2>
                    <p class="mb-0" style="color:var(--text-secondary);">Dibagi menurut peringkat: 20% teratas <em>Sangat tinggi</em>, sampai 50% <em>Tinggi</em>, sampai 80% <em>Sedang</em>, sisanya <em>Rendah</em>.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    var formatRp = function (n) { return 'Rp ' + n.toLocaleString('id-ID'); };

    function updateTotalBobot() {
        var w = ['w1', 'w2', 'w3'].map(function (id) { return parseFloat(document.getElementById(id).value) || 0; });
        var total = Math.round((w[0] + w[1] + w[2]) * 100);
        var ok = total === 100;
        var badge = document.getElementById('total-bobot-badge');
        badge.className = 'tpk-total ' + (ok ? 'ok' : 'bad');
        badge.textContent = ok ? 'Total 100%' : 'Total ' + total + '% (harus 100%)';
        document.querySelectorAll('.js-submit-bobot').forEach(function (b) { b.disabled = !ok; });
    }

    function hitungSimulasi(input) {
        var harga = parseFloat(input.dataset.harga) || 0;
        input.closest('tr').querySelector('.estimasi-baris').textContent = formatRp((parseInt(input.value) || 0) * harga);

        var total = 0, jumlah = 0;
        document.querySelectorAll('.usulan-qty').forEach(function (el) {
            var q = parseInt(el.value) || 0;
            if (q > 0) jumlah++;
            total += q * (parseFloat(el.dataset.harga) || 0);
        });
        document.getElementById('total-anggaran-badge').textContent = formatRp(total);
        document.getElementById('jumlah-usulan').textContent = jumlah + ' item diusulkan';
    }

    updateTotalBobot();
</script>
@endpush
@endsection
