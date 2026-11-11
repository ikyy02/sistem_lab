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

    .tpk-langkah { margin-bottom: 16px; }
    .tpk-langkah-head { display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-bottom: 1px solid var(--border); }
    .tpk-no { flex: none; width: 28px; height: 28px; border-radius: 999px; background: var(--brand); color: #fff; font-weight: 600; font-size: var(--fs-sm); display: inline-flex; align-items: center; justify-content: center; }
    .tpk-langkah-head h2 { margin: 0; font-size: var(--fs-md); font-weight: 600; }
    .tpk-langkah-head p { margin: 0; font-size: var(--fs-sm); color: var(--text-secondary); }
    .tpk-ahp-input { display: grid; grid-template-columns: minmax(0, 1fr) 300px; gap: 24px; align-items: start; }
    .tpk-pair-row { border: 0; margin: 0; padding: 16px 0; min-width: 0; }
    .tpk-pair-row + .tpk-pair-row { border-top: 1px solid var(--border); }
    .tpk-pair-row:first-child { padding-top: 0; }
    .tpk-pair-q { display: block; margin-bottom: 6px; font-size: var(--fs-base); color: var(--text-secondary); }
    .tpk-pair-q b { color: var(--text-primary); font-weight: 600; }
    .tpk-pair-q .vs { margin: 0 6px; font-size: var(--fs-xs); font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: var(--text-muted); }
    .tpk-baca { margin: 8px 0 0; font-size: var(--fs-sm); color: var(--text-secondary); }
    .tpk-baca strong { color: var(--text-primary); font-weight: 600; }
    .tpk-hasil { position: sticky; top: 16px; padding: 16px; background: var(--bg-subtle); border-radius: var(--radius-md); }
    .tpk-hasil h3 { font-size: var(--fs-md); font-weight: 600; margin: 0 0 12px; }
    .tpk-w + .tpk-w { margin-top: 10px; }
    .tpk-w-row { display: flex; justify-content: space-between; gap: 8px; font-size: var(--fs-sm); }
    .tpk-w-row b { font-variant-numeric: tabular-nums; }
    .tpk-w-track { height: 8px; margin-top: 4px; background: var(--border); border-radius: 999px; overflow: hidden; }
    .tpk-w-fill { height: 100%; background: var(--brand); border-radius: 999px; transition: width 250ms var(--ease-standard); }
    .tpk-hasil .tpk-status { margin-top: 16px; padding: 10px 12px; font-size: var(--fs-sm); }
    .tpk-hasil .tpk-sub { margin: 8px 0 0; font-variant-numeric: tabular-nums; }
    .tpk-dirty { margin: 8px 0 0; font-size: var(--fs-sm); color: var(--warning-text); }
    .tpk-ahp-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
    .tpk-status { display: flex; flex-wrap: wrap; gap: 8px 24px; align-items: center; padding: 12px 16px; border-radius: var(--radius-md); font-size: var(--fs-base); }
    .tpk-status.ok { background: var(--success-soft); color: var(--success-text); }
    .tpk-status.bad { background: var(--danger-soft); color: var(--danger-text); }
    .tpk-num th, .tpk-num td { text-align: right; }
    .tpk-num th:first-child, .tpk-num td:first-child { text-align: left; }
    .tpk-detail summary { cursor: pointer; font-weight: 600; font-size: var(--fs-base); padding: 12px 16px; }
    .tpk-detail[open] summary { border-bottom: 1px solid var(--border); }

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
        .tpk-ahp-grid, .tpk-ahp-input { grid-template-columns: 1fr; }
        .tpk-hasil { position: static; }
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
    $f = fn ($x, $d = 4) => number_format($x, $d, ',', '.');
    $bulanLabel = \Carbon\Carbon::parse($bulan.'-01')->translatedFormat('F Y');
    $jenisLabel = ['semua' => 'Alat dan bahan', 'alat' => 'Alat', 'bahan' => 'Bahan'][$jenis] ?? 'Alat dan bahan';
    $jumlahPrio = array_count_values(array_column($items, 'prioritas_label'));
    $kelasPrio = ['Sangat Tinggi' => 'tpk-prio-1', 'Tinggi' => 'tpk-prio-2', 'Sedang' => 'tpk-prio-3', 'Rendah' => 'tpk-prio-4'];
    $pairLabel = ['a12' => [0, 1], 'a13' => [0, 2], 'a23' => [1, 2]];
    $netral = $pair['a12'] === '1' && $pair['a13'] === '1' && $pair['a23'] === '1';
@endphp

<div class="tpk-bar">
    <p class="tpk-konteks">
        SPK Pengadaan <strong>AHP &rarr; SAW</strong> &middot; Periode <strong>{{ $bulanLabel }}</strong> &middot; {{ $jenisLabel }}
    </p>
    @if($resmi)
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('pengadaan-tpk.cetak', request()->query()) }}" target="_blank" rel="noopener" class="btn btn-outline-silab btn-sm">
            <i class="bi bi-printer" aria-hidden="true"></i> Cetak rekomendasi
        </a>
        <a href="{{ route('pengadaan-tpk.export-csv', request()->query()) }}" class="btn btn-outline-silab btn-sm">
            <i class="bi bi-download" aria-hidden="true"></i> Unduh CSV
        </a>
    </div>
    @endif
</div>

@if(! $pairValid)
    <div class="alert alert-warning d-flex align-items-start gap-2 mb-3" role="alert">
        <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
        <div>Ada nilai perbandingan di luar skala Saaty (1/9 sampai 9). Nilai tersebut dikembalikan ke 1.</div>
    </div>
@endif

<form action="{{ route('pengadaan-tpk.index') }}" method="GET" id="form-tpk">
    {{-- Data yang dianalisis --}}
    <div class="tpk-panel tpk-param">
        <div class="row g-3 align-items-end">
            <div class="col-6 col-lg-3">
                <label class="tpk-label" for="f-bulan">Bulan peminjaman</label>
                <select name="bulan" id="f-bulan" class="form-select form-select-sm">
                    @foreach($daftarBulan as $val => $lbl)
                        <option value="{{ $val }}" {{ $bulan === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <label class="tpk-label" for="f-jenis">Kategori</label>
                <select name="jenis" id="f-jenis" class="form-select form-select-sm">
                    <option value="semua" {{ $jenis === 'semua' ? 'selected' : '' }}>Semua</option>
                    <option value="alat" {{ $jenis === 'alat' ? 'selected' : '' }}>Alat</option>
                    <option value="bahan" {{ $jenis === 'bahan' ? 'selected' : '' }}>Bahan</option>
                </select>
            </div>
            <div class="col-12 col-lg-7">
                <label class="tpk-label" for="f-cari">Cari barang <span class="tpk-sub">(hanya menyaring tampilan, tidak mengubah ranking)</span></label>
                <input type="search" name="search" id="f-cari" class="form-control form-control-sm" placeholder="Nama, jenis, atau satuan" value="{{ $search }}">
            </div>
        </div>
    </div>

    {{-- LANGKAH 1: AHP --}}
    <section class="tpk-panel tpk-langkah" aria-labelledby="h-ahp">
        <div class="tpk-langkah-head">
            <span class="tpk-no" aria-hidden="true">1</span>
            <div>
                <h2 id="h-ahp">Bobot kriteria dengan AHP</h2>
                <p>Bandingkan tingkat kepentingan kriteria berpasangan. Sistem menghitung bobot dan memeriksa konsistensinya.</p>
            </div>
        </div>
        @php
            $istilah = [2 => 'sedikit lebih penting', 3 => 'agak lebih penting', 4 => 'cukup lebih penting', 5 => 'lebih penting', 6 => 'jauh lebih penting', 7 => 'sangat lebih penting', 8 => 'sangat jauh lebih penting', 9 => 'mutlak lebih penting'];
            $kalimat = function ($teks, $i, $j) use ($istilah, $kriteria) {
                $v = \App\Services\AhpService::parseNilai($teks);
                [$a, $b] = [$kriteria[$i]['nama'], $kriteria[$j]['nama']];
                if ($v == 1.0) { return "$a dan $b sama penting."; }
                $n = $v > 1 ? (int) round($v) : (int) round(1 / $v);
                [$menang, $kalah] = $v > 1 ? [$a, $b] : [$b, $a];
                return "$menang {$istilah[$n]} dari $kalah.";
            };
        @endphp
        <div class="tpk-pane">
            @if($netral)
                <div class="alert alert-info py-2 mb-3" role="note">Pilih kriteria mana yang lebih penting di tiap pasangan beserta tingkatnya (1 = sama penting, 9 = mutlak lebih penting). Nilai awal ini netral, bukan penilaian Laboran.</div>
            @endif
            <div class="tpk-ahp-input">
                <div>
                    @foreach($pairLabel as $key => [$i, $j])
                        <fieldset class="tpk-pair-row" data-key="{{ $key }}" data-i="{{ $i }}" data-j="{{ $j }}">
                            <legend class="tpk-pair-q">
                                <b>{{ $kriteria[$i]['kode'] }} {{ $kriteria[$i]['nama'] }}</b><span class="vs">dibanding</span><b>{{ $kriteria[$j]['kode'] }} {{ $kriteria[$j]['nama'] }}</b>
                            </legend>
                            <select name="{{ $key }}" id="p-{{ $key }}" class="form-select" aria-describedby="baca-{{ $key }}">
                                @for($n = 9; $n >= 2; $n--)
                                    <option value="{{ $n }}" {{ $pair[$key] === (string) $n ? 'selected' : '' }}>{{ $n }} &ndash; {{ $kriteria[$i]['nama'] }} {{ $istilah[$n] }} dari {{ $kriteria[$j]['nama'] }}</option>
                                @endfor
                                <option value="1" {{ $pair[$key] === '1' ? 'selected' : '' }}>1 &ndash; Sama penting</option>
                                @for($n = 2; $n <= 9; $n++)
                                    <option value="1/{{ $n }}" {{ $pair[$key] === '1/'.$n ? 'selected' : '' }}>1/{{ $n }} &ndash; {{ $kriteria[$j]['nama'] }} {{ $istilah[$n] }} dari {{ $kriteria[$i]['nama'] }}</option>
                                @endfor
                            </select>
                            <p class="tpk-baca" id="baca-{{ $key }}" aria-live="polite">{{ $kalimat($pair[$key], $i, $j) }}</p>
                        </fieldset>
                    @endforeach
                </div>

                <aside class="tpk-hasil" aria-labelledby="h-bobot">
                    <h3 id="h-bobot">Bobot kriteria</h3>
                    @foreach($kriteria as $i => $kr)
                        <div class="tpk-w">
                            <div class="tpk-w-row"><span>{{ $kr['kode'] }} {{ $kr['nama'] }} <span class="tpk-sub">{{ $kr['tipe'] }}</span></span><b data-w="{{ $i }}">{{ $f($ahp['bobot'][$i] * 100, 2) }}%</b></div>
                            <div class="tpk-w-track" aria-hidden="true"><div class="tpk-w-fill" data-fill="{{ $i }}" style="width: {{ $f($ahp['bobot'][$i] * 100, 2) }}%;"></div></div>
                        </div>
                    @endforeach
                    <div class="tpk-status {{ $ahp['konsisten'] ? 'ok' : 'bad' }}" id="ahp-status" role="status">
                        <strong id="ahp-label">{{ $ahp['konsisten'] ? 'Konsisten' : 'Tidak konsisten' }}</strong>
                        <span>CR <b id="ahp-cr">{{ $f($ahp['cr']) }}</b> (batas 0,1000)</span>
                    </div>
                    <p class="tpk-sub" id="ahp-hint" style="color:var(--danger-text);" {{ $ahp['konsisten'] ? 'hidden' : '' }}>CR di atas 0,10, jadi ranking resmi tidak ditampilkan. Selaraskan pilihan: bila C1 lebih penting dari C2 dan C2 dari C3, maka C1 harus lebih penting dari C3.</p>
                    <button type="submit" class="btn btn-primary btn-sm w-100 mt-3">Hitung ranking</button>
                    <p class="tpk-dirty" id="ahp-dirty" role="status" hidden>Pilihan berubah. Ranking di bawah belum diperbarui.</p>
                    <button type="button" class="btn btn-link btn-sm px-0 mt-1" id="ahp-reset">Setel ulang ke sama penting</button>
                </aside>
            </div>

            <details class="tpk-detail tpk-panel mt-4">
                <summary>Lihat proses perhitungan AHP</summary>
                <div class="tpk-pane">
                    <div class="tpk-ahp-grid">
                        <div>
                            <h3 class="h-card mb-2">Matriks perbandingan berpasangan</h3>
                            <div class="table-responsive"><table class="tpk-table tpk-num">
                                <caption class="visually-hidden">Matriks perbandingan berpasangan</caption>
                                <thead><tr><th scope="col">Kriteria</th>@foreach($kriteria as $kr)<th scope="col">{{ $kr['kode'] }}</th>@endforeach</tr></thead>
                                <tbody>
                                    @foreach($kriteria as $i => $kr)<tr><th scope="row">{{ $kr['kode'] }}</th>@foreach($kriteria as $j => $_)<td>{{ $f($ahp['matriks'][$i][$j]) }}</td>@endforeach</tr>@endforeach
                                    <tr><th scope="row">Jumlah</th>@foreach($ahp['jumlah_kolom'] as $v)<td><strong>{{ $f($v) }}</strong></td>@endforeach</tr>
                                </tbody>
                            </table></div>
                        </div>
                        <div>
                            <h3 class="h-card mb-2">Normalisasi dan bobot prioritas</h3>
                            <div class="table-responsive"><table class="tpk-table tpk-num">
                                <caption class="visually-hidden">Matriks ternormalisasi, bobot, dan perkalian matriks dengan bobot</caption>
                                <thead><tr><th scope="col">Kriteria</th>@foreach($kriteria as $kr)<th scope="col">{{ $kr['kode'] }}</th>@endforeach<th scope="col">Bobot (rata-rata baris)</th><th scope="col">A&middot;w</th><th scope="col">(A&middot;w)/w</th></tr></thead>
                                <tbody>
                                    @foreach($kriteria as $i => $kr)<tr><th scope="row">{{ $kr['kode'] }}</th>@foreach($kriteria as $j => $_)<td>{{ $f($ahp['normal'][$i][$j]) }}</td>@endforeach<td><strong>{{ $f($ahp['bobot'][$i]) }}</strong></td><td>{{ $f($ahp['aw'][$i]) }}</td><td>{{ $f($ahp['aw'][$i] / $ahp['bobot'][$i]) }}</td></tr>@endforeach
                                </tbody>
                            </table></div>
                        </div>
                    </div>
                    <div class="tpk-rumus mt-3">&lambda;max = rata-rata (A&middot;w)/w = {{ $f($ahp['lambda_max']) }}<br>CI = (&lambda;max &minus; n) / (n &minus; 1) = ({{ $f($ahp['lambda_max']) }} &minus; 3) / 2 = {{ $f($ahp['ci']) }}<br>CR = CI / RI = {{ $f($ahp['ci']) }} / 0,58 = {{ $f($ahp['cr']) }}</div>
                </div>
            </details>
        </div>
    </section>
</form>

@if(! $resmi)
    <div class="tpk-panel tpk-pane" role="status">
        <h2 class="h-card mb-1"><span class="tpk-no me-2" aria-hidden="true">2</span>Peringkat SAW belum tersedia</h2>
        <p class="mb-0" style="color:var(--text-secondary);">Langkah 2 berjalan otomatis setelah bobot AHP konsisten (CR &le; 0,10). Tidak ada ranking sementara atau bobot cadangan yang dipakai.</p>
    </div>
@else
<div class="tpk-langkah-head tpk-panel mb-3" style="border-bottom:1px solid var(--border);">
    <span class="tpk-no" aria-hidden="true">2</span>
    <div>
        <h2>Peringkat prioritas dengan SAW</h2>
        <p>Memakai bobot AHP: Stok {{ $f($ahp['bobot'][0]) }} &middot; Peminjaman {{ $f($ahp['bobot'][1]) }} &middot; Harga {{ $f($ahp['bobot'][2]) }}. Data stok dan harga memakai satuan standar katalog (harga per 1 unit).</p>
    </div>
</div>

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
                            <th scope="col">W1&middot;R1</th>
                            <th scope="col">W2&middot;R2</th>
                            <th scope="col">W3&middot;R3</th>
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
                                <td>{{ number_format($ahp['bobot'][0] * $it['r'][0], 4) }}</td>
                                <td>{{ number_format($ahp['bobot'][1] * $it['r'][1], 4) }}</td>
                                <td>{{ number_format($ahp['bobot'][2] * $it['r'][2], 4) }}</td>
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
                    <p style="color:var(--text-secondary);">Bobot tiap kriteria dihitung dengan <strong>AHP</strong> dari perbandingan berpasangan (skala Saaty) dan hanya dipakai bila CR &le; 0,10. Bobot itu lalu dipakai <strong>Simple Additive Weighting (SAW)</strong> untuk meranking alat dan bahan dari data stok, peminjaman, dan harga per unit.</p>
                    <ul class="ps-3 mb-0" style="color:var(--text-secondary);">
                        <li><strong>C1 Stok</strong> (cost, satuan standar katalog): makin sedikit, makin prioritas.</li>
                        <li><strong>C2 Peminjaman</strong> (benefit): jumlah transaksi disetujui atau selesai pada bulan terpilih.</li>
                        <li><strong>C3 Harga per unit</strong> (cost): makin murah, makin mudah diusulkan.</li>
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

@endif

@push('scripts')
<script>
    var formatRp = function (n) { return 'Rp ' + n.toLocaleString('id-ID'); };

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

</script>
@endpush
@push('scripts')
<script>
(function () {
    var D = { istilah: @json($istilah), nama: @json(array_column($kriteria, 'nama')), awal: @json($pair) };
    var form = document.getElementById('form-tpk');
    if (!form) return;
    var fmt = function (x, d) { return x.toLocaleString('id-ID', { minimumFractionDigits: d, maximumFractionDigits: d }); };
    var nilai = function (t) { return t.indexOf('/') > -1 ? 1 / parseInt(t.split('/')[1], 10) : parseFloat(t); };
    var baca = function (k) { return form.querySelector('select[name="' + k + '"]').value; };

    function kalimat(t, i, j) {
        var v = nilai(t), a = D.nama[i], b = D.nama[j];
        if (v === 1) return a + ' dan ' + b + ' sama penting.';
        var n = Math.round(v > 1 ? v : 1 / v);
        return (v > 1 ? a : b) + ' ' + D.istilah[n] + ' dari ' + (v > 1 ? b : a) + '.';
    }

    function hitung() {
        var p = { a12: nilai(baca('a12')), a13: nilai(baca('a13')), a23: nilai(baca('a23')) };
        var A = [[1, p.a12, p.a13], [1 / p.a12, 1, p.a23], [1 / p.a13, 1 / p.a23, 1]];
        var cs = [0, 1, 2].map(function (j) { return A[0][j] + A[1][j] + A[2][j]; });
        var w = A.map(function (r) { return (r[0] / cs[0] + r[1] / cs[1] + r[2] / cs[2]) / 3; });
        var lam = A.reduce(function (s, r, i) { return s + (r[0] * w[0] + r[1] * w[1] + r[2] * w[2]) / w[i]; }, 0) / 3;
        var ci = (lam - 3) / 2, cr = Math.max(0, ci) / 0.58;
        return { w: w, lam: lam, ci: ci, cr: cr, ok: cr <= 0.1 + 1e-9 };
    }

    function perbarui() {
        var h = hitung(), berubah = false;
        h.w.forEach(function (x, i) {
            form.querySelector('[data-w="' + i + '"]').textContent = fmt(x * 100, 2) + '%';
            form.querySelector('[data-fill="' + i + '"]').style.width = (x * 100) + '%';
        });
        document.getElementById('ahp-cr').textContent = fmt(h.cr, 4);
        document.getElementById('ahp-label').textContent = h.ok ? 'Konsisten' : 'Tidak konsisten';
        document.getElementById('ahp-status').className = 'tpk-status ' + (h.ok ? 'ok' : 'bad');
        document.getElementById('ahp-hint').hidden = h.ok;
        form.querySelectorAll('.tpk-pair-row').forEach(function (row) {
            var k = row.dataset.key, i = +row.dataset.i, j = +row.dataset.j, t = baca(k), v = nilai(t);
            row.querySelector('.tpk-baca').textContent = kalimat(t, i, j);
            if (t !== D.awal[k]) berubah = true;
        });
        document.getElementById('ahp-dirty').hidden = !berubah;
    }

    form.addEventListener('change', function (e) { if (e.target.matches('.tpk-pair-row select')) perbarui(); });
    document.getElementById('ahp-reset').addEventListener('click', function () {
        form.querySelectorAll('.tpk-pair-row select').forEach(function (el) { el.value = '1'; });
        perbarui();
    });
})();
</script>
@endpush
@endsection
