@extends('layouts.app')

@section('title', 'Pengadaan TPK')
@section('page-title', 'Pengadaan TPK')
@section('page-subtitle', 'Sistem Pendukung Keputusan prioritas pengadaan alat dan bahan laboratorium (Metode AHP–SAW).')

@push('styles')
<style>
    /* ===== PALET WARNA RESMI & KONSISTEN (ADMINISTRATIF) ===== */
    :root {
        --bg-page:        #F6F7F9;
        --bg-surface:     #FFFFFF;
        --bg-subtle:      #F1F3F5;
        --border:         #E3E6EA;
        --text-primary:   #1F2933;
        --text-secondary: #5F6C7B;
        --text-muted:     #8A94A0;
        --accent:         #2F5D8A;
        --accent-soft:    #E8EEF4;

        --danger:         #B4473F;
        --danger-bg:      #F6E7E5;
        --warning:        #A8741A;
        --warning-bg:     #F6EEDC;
        --success:        #3F7A5A;
        --success-bg:     #E5F0EA;
        --info:           #4A6C8C;
        --info-bg:        #E8EEF4;
    }

    /* Penataan Tipografi Dasar */
    .tpk-container {
        color: var(--text-primary);
        font-size: 14px;
        line-height: 1.5;
    }
    .tpk-page-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0 0 2px;
    }
    .tpk-page-desc {
        font-size: 13px;
        color: var(--text-secondary);
        margin: 0;
    }

    /* Kartu Ringkasan (Satu gaya seragam, tanpa gradien & tanpa warna-warni) */
    .summary-card {
        background: var(--bg-surface);
        border: 1px solid var(--border);
        border-top: 2px solid var(--border);
        border-radius: 8px;
        padding: 14px 16px;
        height: 100%;
    }
    .summary-card.active-top {
        border-top-color: var(--accent);
    }
    .summary-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-secondary);
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .summary-icon {
        color: var(--text-secondary);
        font-size: 14px;
    }
    .summary-val {
        font-size: 17px;
        font-weight: 600;
        color: var(--text-primary);
        line-height: 1.3;
    }
    .summary-sub {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 2px;
    }

    /* Panel & Kartu Konten */
    .admin-card {
        background: var(--bg-surface);
        border: 1px solid var(--border);
        border-radius: 8px;
    }
    .admin-card-header {
        background: var(--bg-surface);
        border-bottom: 1px solid var(--border);
        padding: 12px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .admin-card-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Tombol Aksi */
    .btn-admin-primary {
        background: var(--accent);
        border: 1px solid var(--accent);
        color: #ffffff;
        font-size: 13px;
        font-weight: 500;
        padding: 6px 14px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        transition: background-color 0.15s ease, border-color 0.15s ease;
    }
    .btn-admin-primary:hover, .btn-admin-primary:focus {
        background: #254b70;
        border-color: #254b70;
        color: #ffffff;
    }
    .btn-admin-secondary {
        background: var(--bg-surface);
        border: 1px solid var(--border);
        color: var(--text-secondary);
        font-size: 13px;
        font-weight: 500;
        padding: 6px 14px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    }
    .btn-admin-secondary:hover {
        background: var(--bg-subtle);
        border-color: #CBD5E1;
        color: var(--text-primary);
    }

    /* Preset Bobot Cepat */
    .btn-preset-quiet {
        font-size: 12px;
        font-weight: 500;
        padding: 5px 10px;
        border-radius: 6px;
        border: 1px solid var(--border);
        background: var(--bg-surface);
        color: var(--text-secondary);
        transition: all 0.15s ease;
    }
    .btn-preset-quiet:hover {
        background: var(--accent-soft);
        color: var(--accent);
        border-color: var(--accent);
    }
    .btn-preset-quiet.active {
        background: var(--accent-soft);
        border-color: var(--accent);
        color: var(--accent);
        font-weight: 600;
    }

    /* Tabs Datar & Tenang */
    .admin-tabs {
        display: flex;
        gap: 4px;
        border-bottom: 1px solid var(--border);
        background: var(--bg-surface);
        padding: 0 16px;
    }
    .admin-tab-btn {
        border: 0;
        border-bottom: 2px solid transparent;
        background: transparent;
        color: var(--text-secondary);
        font-size: 13px;
        font-weight: 500;
        padding: 12px 14px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 0;
        text-decoration: none;
        transition: all 0.15s ease;
    }
    .admin-tab-btn:hover {
        color: var(--text-primary);
    }
    .admin-tab-btn.active {
        color: var(--accent);
        border-bottom-color: var(--accent);
        font-weight: 600;
    }

    /* Tabel Bersih (Administratif) */
    .table-admin {
        width: 100%;
        margin-bottom: 0;
        border-collapse: collapse;
    }
    .table-admin th {
        background: var(--bg-subtle);
        color: var(--text-secondary);
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-bottom: 1px solid var(--border);
        padding: 10px 12px;
        white-space: nowrap;
    }
    .table-admin td {
        padding: 10px 12px;
        border-bottom: 1px solid var(--border);
        font-size: 13px;
        color: var(--text-primary);
        vertical-align: middle;
    }
    .table-admin tr:hover td {
        background: #FAFBFD;
    }

    /* Lingkaran Peringkat Netral */
    .badge-rank {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        font-size: 11px;
        font-weight: 500;
        color: var(--text-secondary);
        background: var(--bg-subtle);
        border: 1px solid var(--border);
    }
    .badge-rank.rank-1 {
        border-color: var(--accent);
        color: var(--accent);
        font-weight: 600;
        background: var(--accent-soft);
    }

    /* Badge Kategori: Satu gaya netral untuk Alat dan Bahan */
    .badge-kategori-netral {
        background: var(--bg-subtle);
        color: var(--text-secondary);
        font-size: 11px;
        font-weight: 500;
        padding: 2px 7px;
        border-radius: 4px;
        border: 1px solid var(--border);
    }

    /* Badge Prioritas (Warna Redup / Muted Status) */
    .badge-prio-sangat-tinggi {
        background: var(--danger-bg);
        color: var(--danger);
        font-size: 11px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 4px;
        display: inline-block;
    }
    .badge-prio-tinggi {
        background: var(--warning-bg);
        color: var(--warning);
        font-size: 11px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 4px;
        display: inline-block;
    }
    .badge-prio-sedang {
        background: var(--info-bg);
        color: var(--info);
        font-size: 11px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 4px;
        display: inline-block;
    }
    .badge-prio-rendah {
        background: var(--bg-subtle);
        color: var(--text-secondary);
        font-size: 11px;
        font-weight: 500;
        padding: 3px 8px;
        border-radius: 4px;
        display: inline-block;
    }

    /* Stok rendah: cukup teks --danger tanpa latar */
    .text-stok-kritis {
        color: var(--danger);
        font-weight: 600;
    }

    /* Banner Simulasi Usulan */
    .simulasi-banner {
        background: var(--bg-subtle);
        border: 1px solid var(--border);
        border-radius: 6px;
        padding: 10px 14px;
    }

    /* Form Controls */
    .form-control, .form-select {
        border-color: var(--border);
        border-radius: 6px;
        font-size: 13px;
        color: var(--text-primary);
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 2px rgba(47,93,138,0.15);
    }
    .form-label {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 4px;
    }
</style>
@endpush

@section('content')
<div class="tpk-container">

    {{-- Header Halaman & Aksi --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <div>
            <h2 class="tpk-page-title">Pengadaan TPK</h2>
            <p class="tpk-page-desc">
                Sistem Pendukung Keputusan (SPK) penentuan prioritas pengadaan alat dan bahan laboratorium menggunakan metode <strong>AHP</strong> untuk pembobotan kriteria dan <strong>SAW (Simple Additive Weighting)</strong> untuk pemeringkatan alternatif. Rekomendasi dihasilkan berdasarkan tiga kriteria: <strong>C1&nbsp;Stok</strong> (cost — semakin sedikit stok, semakin diprioritaskan), <strong>C2&nbsp;Frekuensi Peminjaman</strong> (benefit — semakin sering dipinjam, semakin diprioritaskan), dan <strong>C3&nbsp;Harga Satuan</strong> (cost — semakin mahal, semakin diprioritaskan). Alternatif dengan <em>Nilai Preferensi (V) tertinggi</em> mendapat peringkat pertama sebagai rekomendasi utama pengadaan.
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if($resmi)
            <a href="{{ route('pengadaan-tpk.cetak', request()->query()) }}" target="_blank" class="btn-admin-secondary">
                <i class="bi bi-printer"></i>
                <span>Cetak Rekomendasi</span>
            </a>
            <a href="{{ route('pengadaan-tpk.export-excel', request()->query()) }}" class="btn-admin-secondary">
                <i class="bi bi-file-earmark-arrow-down"></i>
                <span>Export Excel</span>
            </a>
            @endif
            <button type="button" class="btn-admin-secondary" data-bs-toggle="modal" data-bs-target="#importTpkModal">
                <i class="bi bi-file-earmark-arrow-up"></i>
                <span>Import Excel</span>
            </button>
        </div>
    </div>

    {{-- Ringkasan dan ranking hanya ditampilkan untuk matriks AHP yang konsisten. --}}
    @if($resmi)
    <div class="row g-3 mb-3">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="summary-card active-top">
                <div class="summary-label">
                    <span>Total Alternatif</span>
                    <i class="bi bi-boxes summary-icon"></i>
                </div>
                <div class="summary-val">{{ $totalItem }}</div>
                <div class="summary-sub">Katalog dianalisis ({{ ucfirst($jenis) }})</div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="summary-card">
                <div class="summary-label">
                    <span>Prioritas Utama (#1)</span>
                    <i class="bi bi-award summary-icon"></i>
                </div>
                <div class="summary-val text-truncate" title="{{ $topItem['nama'] ?? '-' }}">
                    {{ $topItem['nama'] ?? '-' }}
                </div>
                <div class="summary-sub">
                    Skor: {{ isset($topItem['nilai']) ? number_format($topItem['nilai'], 4) : '-' }} &bull; Sisa: {{ $topItem['stok'] ?? 0 }}
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="summary-card">
                <div class="summary-label">
                    <span>Periode Analisis</span>
                    <i class="bi bi-calendar3 summary-icon"></i>
                </div>
                <div class="summary-val">
                    {{ \Carbon\Carbon::parse($bulan.'-01')->translatedFormat('F Y') }}
                </div>
                <div class="summary-sub">Data peminjaman terealisasi</div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="summary-card">
                <div class="summary-label">
                    <span>Bobot Kriteria AHP</span>
                    <i class="bi bi-sliders summary-icon"></i>
                </div>
                <div class="summary-val">
                    {{ round($w1 * 100) }}% : {{ round($w2 * 100) }}% : {{ round($w3 * 100) }}%
                </div>
                <div class="summary-sub">C1 (Stok) : C2 (Pinjam) : C3 (Harga)</div>
            </div>
        </div>
    </div>

    @endif

    {{-- Panel Perbandingan Berpasangan AHP --}}
    <div class="admin-card mb-3">
        <div class="admin-card-header">
            <h6 class="admin-card-title">
                <i class="bi bi-funnel text-secondary"></i>
                <span>Parameter dan Perbandingan Kriteria AHP</span>
            </h6>
        </div>

        <div class="p-3">
            <form action="{{ route('pengadaan-tpk.index') }}" method="GET" id="form-tpk">
                <div class="row g-3">
                    {{-- Periode Bulan --}}
                    <div class="col-12 col-md-3">
                        <label class="form-label">Bulan Peminjaman</label>
                        <select name="bulan" class="form-select form-select-sm" onchange="this.form.submit()">
                            @foreach($daftarBulan as $val => $lbl)
                                <option value="{{ $val }}" {{ $bulan === $val ? 'selected' : '' }}>
                                    {{ $lbl }} ({{ $val }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Kategori --}}
                    <div class="col-12 col-md-3">
                        <label class="form-label">Kategori Katalog</label>
                        <select name="jenis" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="semua" {{ $jenis === 'semua' ? 'selected' : '' }}>Semua (Alat & Bahan)</option>
                            <option value="alat" {{ $jenis === 'alat' ? 'selected' : '' }}>Khusus Alat</option>
                            <option value="bahan" {{ $jenis === 'bahan' ? 'selected' : '' }}>Khusus Bahan</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <div class="p-3 rounded-2" style="background:var(--bg-subtle); border:1px solid var(--border);">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                                <div>
                                    <div style="font-size:13px; font-weight:600; color:var(--text-primary);">Perbandingan Berpasangan Kriteria (Skala Saaty)</div>
                                    <div style="font-size:11px; color:var(--text-muted);">Tentukan tingkat kepentingan antar-kriteria. Bobot SAW dihitung otomatis dari AHP.</div>
                                </div>
                                <span style="font-size:11px; color:var(--text-secondary);">Nilai di luar skala dikembalikan ke 1</span>
                            </div>
                            <div class="row g-3">
                                @foreach([
                                    'a12' => 'C1 Stok dibanding C2 Peminjaman',
                                    'a13' => 'C1 Stok dibanding C3 Harga',
                                    'a23' => 'C2 Peminjaman dibanding C3 Harga',
                                ] as $key => $label)
                                    <div class="col-12 col-md-4">
                                        <label class="form-label" for="{{ $key }}">{{ $label }}</label>
                                        <select name="{{ $key }}" id="{{ $key }}" class="form-select form-select-sm">
                                            @foreach($skala as $nilai)
                                                <option value="{{ $nilai }}" {{ $pair[$key] === $nilai ? 'selected' : '' }}>{{ $nilai }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="p-3 rounded-2" style="background:var(--bg-surface); border:1px solid var(--border);">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                <strong style="font-size:13px;">Hasil Analisis AHP</strong>
                                @if($ahp['konsisten'])
                                    <span class="badge bg-success">Konsisten</span>
                                @else
                                    <span class="badge bg-danger">Tidak konsisten</span>
                                @endif
                            </div>
                            <div class="row g-2">
                                <div class="col-12 col-md-3">
                                    <div style="font-size:11px; color:var(--text-muted);">Consistency Index (CI)</div>
                                    <strong>{{ number_format($ahp['ci'], 4, ',', '.') }}</strong>
                                    <span style="font-size:11px; color:var(--text-secondary);">(RI 0,58)</span>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div style="font-size:11px; color:var(--text-muted);">Consistency Ratio (CR)</div>
                                    <strong>{{ number_format($ahp['cr'], 4, ',', '.') }}</strong>
                                    <span style="font-size:11px; color:var(--text-secondary);">(batas 0,10)</span>
                                </div>
                                @foreach(['C1 Stok', 'C2 Peminjaman', 'C3 Harga'] as $i => $namaKriteria)
                                    <div class="col-12 col-md-2">
                                        <div style="font-size:11px; color:var(--text-muted);">Bobot {{ $namaKriteria }}</div>
                                        <strong>{{ number_format($ahp['bobot'][$i], 4, ',', '.') }}</strong>
                                        <span style="font-size:11px; color:var(--text-secondary);">({{ number_format($ahp['bobot'][$i] * 100, 2, ',', '.') }}%)</span>
                                    </div>
                                @endforeach
                            </div>
                            @unless($ahp['konsisten'])
                                <div class="alert alert-warning mt-3 mb-0" role="alert" style="font-size:12px;">
                                    Matriks perbandingan belum konsisten (CR lebih dari 0,10). Perbaiki perbandingan kriteria untuk menghasilkan ranking SAW resmi. Ranking, cetak, dan export tidak tersedia selama matriks belum konsisten.
                                </div>
                            @endunless
                        </div>
                    </div>
                    {{-- Tombol Submit --}}
                    <div class="col-12">
                        <button type="submit" id="btn-terapkan-tpk" class="btn-admin-primary">
                            <span>Hitung AHP–SAW</span>
                        </button>
                    </div>
                    <div class="col-12 col-md-4 text-md-end">
                        <a href="{{ route('pengadaan-tpk.index') }}" style="font-size:12px; color:var(--text-secondary); text-decoration:none;">
                            Reset Perbandingan
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($resmi)
    {{-- Hasil pemeringkatan SAW hanya tersedia jika CR memenuhi batas. --}}
    <div class="admin-card overflow-hidden">
        {{-- Navigation Tabs --}}
        <div class="admin-tabs" id="tpkTabs" role="tablist">
            <button class="admin-tab-btn active" id="tab-rekomendasi" data-bs-toggle="tab" data-bs-target="#content-rekomendasi" type="button" role="tab">
                <span>Rekomendasi Prioritas</span>
                <span style="font-size:11px; padding:1px 6px; border-radius:4px; background:var(--bg-subtle); color:var(--text-secondary);">
                    {{ count($filteredItems) }}
                </span>
            </button>
            <button class="admin-tab-btn" id="tab-matriks" data-bs-toggle="tab" data-bs-target="#content-matriks" type="button" role="tab">
                <span>Matriks Keputusan & Normalisasi (R)</span>
            </button>
            <button class="admin-tab-btn" id="tab-teori" data-bs-toggle="tab" data-bs-target="#content-teori" type="button" role="tab">
                <span>Metodologi (AHP & SAW)</span>
            </button>
        </div>

        <div class="tab-content" id="tpkTabContent">
            {{-- TAB 1: REKOMENDASI PRIORITAS --}}
            <div class="tab-pane fade show active p-3" id="content-rekomendasi" role="tabpanel">

                {{-- Tabel Rekomendasi Prioritas --}}
                <div class="table-responsive">
                    <table class="table-admin">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:50px;">Rank</th>
                                <th>Nama Alat / Bahan</th>
                                <th class="text-center" style="width:80px;">Kategori</th>
                                <th class="text-end" style="width:90px;">Sisa Stok (C1)</th>
                                <th class="text-center" style="width:90px;">Pinjam (C2)</th>
                                <th class="text-end" style="width:120px;">Harga Satuan (C3)</th>
                                <th class="text-end" style="width:110px;">Skor Preferensi (V)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($filteredItems as $item)
                                @php
                                    $rank = $item['ranking'];
                                @endphp
                                <tr>
                                    {{-- Rank (Netral tanpa warna emas) --}}
                                    <td class="text-center">
                                        <span class="badge-rank {{ $rank === 1 ? 'rank-1' : '' }}">
                                            {{ $rank }}
                                        </span>
                                    </td>

                                    {{-- Nama & Lokasi --}}
                                    <td>
                                        <div style="font-weight:500; color:var(--text-primary);">{{ $item['nama'] }}</div>
                                        <div style="font-size:11px; color:var(--text-muted);">
                                            {{ $item['nama_ruangan'] ?? 'Laboratorium' }} &bull; Satuan: {{ $item['nama_satuan'] ?? 'Unit' }}
                                        </div>
                                    </td>

                                    {{-- Kategori (Satu gaya netral seragam) --}}
                                    <td class="text-center">
                                        <span class="badge-kategori-netral">
                                            {{ ucfirst($item['jenis']) }}
                                        </span>
                                    </td>

                                    {{-- Sisa Stok (Cukup teks merah jika rendah, tanpa latar) --}}
                                    <td class="text-end">
                                        <span class="{{ $item['stok'] <= 2 ? 'text-stok-kritis' : '' }}">
                                            {{ $item['stok'] }}
                                        </span>
                                        <span style="font-size:11px; color:var(--text-muted);">{{ $item['nama_satuan'] ?? '' }}</span>
                                    </td>

                                    {{-- Peminjaman --}}
                                    <td class="text-center">
                                        <span style="font-size:12px; color:var(--text-secondary);">
                                            {{ $item['jumlah_peminjaman'] }}x
                                        </span>
                                    </td>

                                    {{-- Harga Satuan --}}
                                    <td class="text-end" style="font-size:12px; color:var(--text-secondary);">
                                        Rp {{ number_format($item['harga'], 0, ',', '.') }}
                                    </td>

                                    {{-- Skor Preferensi (V) — teks saja, tanpa bar --}}
                                    <td class="text-end">
                                        <span style="font-weight:600; color:var(--accent); font-family:monospace; font-size:13px;">
                                            {{ number_format($item['nilai'], 4) }}
                                        </span>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4" style="color:var(--text-muted); font-size:13px;">
                                        Tidak ada data alat/bahan yang sesuai dengan parameter pencarian.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 p-2 mt-2" style="font-size:12px; color:var(--text-muted);">
                    <div>
                        Menampilkan <strong>{{ count($filteredItems) }}</strong> dari <strong>{{ $totalItem }}</strong> katalog.
                    </div>
                    <div>
                        Metode Perhitungan: Simple Additive Weighting (SAW)
                    </div>
                </div>
            </div>

            {{-- TAB 2: MATRIKS KEPUTUSAN & NORMALISASI --}}
            <div class="tab-pane fade p-3" id="content-matriks" role="tabpanel">
                {{-- Nilai Acuan Ekstrem Kriteria --}}
                <div class="mb-3">
                    <div style="font-size:13px; font-weight:600; color:var(--text-primary); margin-bottom:2px;">
                        Titik Acuan Ekstrem Kriteria
                    </div>
                    <p style="font-size:12px; color:var(--text-secondary); margin-bottom:12px;">
                        Kriteria <strong>Cost</strong> dinormalisasi dengan formula Min(X<sub>j</sub>) / X<sub>ij</sub>, sedangkan kriteria <strong>Benefit</strong> dinormalisasi dengan formula X<sub>ij</sub> / Max(X<sub>j</sub>).
                    </p>
                    <div class="row g-2">
                        <div class="col-12 col-md-4">
                            <div class="p-2 rounded-2" style="background:var(--bg-subtle); border:1px solid var(--border);">
                                <span style="font-size:11px; text-transform:uppercase; color:var(--text-secondary); display:block;">C1: Stok Minimum (Cost)</span>
                                <span style="font-size:15px; font-weight:600; color:var(--text-primary);">{{ $stats['min_stok'] ?? 0 }}</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-2 rounded-2" style="background:var(--bg-subtle); border:1px solid var(--border);">
                                <span style="font-size:11px; text-transform:uppercase; color:var(--text-secondary); display:block;">C2: Peminjaman Maksimum (Benefit)</span>
                                <span style="font-size:15px; font-weight:600; color:var(--text-primary);">{{ $stats['max_pinjam'] ?? 0 }} transaksi</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-2 rounded-2" style="background:var(--bg-subtle); border:1px solid var(--border);">
                                <span style="font-size:11px; text-transform:uppercase; color:var(--text-secondary); display:block;">C3: Harga Minimum (Cost)</span>
                                <span style="font-size:15px; font-weight:600; color:var(--text-primary);">Rp {{ number_format($stats['min_harga'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="font-size:13px; font-weight:600; color:var(--text-primary); margin-bottom:6px;">
                    Matriks Keputusan: Nilai Preferensi Alternatif dan Normalisasi (R)
                </div>
                <div class="table-responsive">
                    <table class="table-admin" style="font-size:12px;">
                        <thead>
                            <tr class="text-center">
                                <th rowspan="2" style="width:40px;">No</th>
                                <th rowspan="2" class="text-start">Alternatif (Nama Barang)</th>
                                <th colspan="3">Nilai Normalisasi (R)</th>
                                <th rowspan="2" style="width:100px;">Nilai V</th>
                                <th rowspan="2" style="width:60px;">Rank</th>
                            </tr>
                            <tr class="text-center">
                                <th>R1 (Stok)</th>
                                <th>R2 (Pinjam)</th>
                                <th>R3 (Harga)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($filteredItems as $idx => $it)
                                <tr>
                                    <td class="text-center" style="color:var(--text-muted);">{{ $idx + 1 }}</td>
                                    <td style="font-weight:500;">{{ $it['nama'] }}</td>
                                    <td class="text-end" style="font-family:monospace; color:var(--text-secondary);">{{ number_format($it['r'][0], 4) }}</td>
                                    <td class="text-end" style="font-family:monospace; color:var(--text-secondary);">{{ number_format($it['r'][1], 4) }}</td>
                                    <td class="text-end" style="font-family:monospace; color:var(--text-secondary);">{{ number_format($it['r'][2], 4) }}</td>
                                    <td class="text-end" style="font-family:monospace; font-weight:600; color:var(--accent);">{{ number_format($it['nilai'], 4) }}</td>
                                    <td class="text-center">{{ $it['ranking'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- TAB 3: METODOLOGI & DASAR TEORI --}}
            <div class="tab-pane fade p-3" id="content-teori" role="tabpanel">
                <div class="row g-3">
                    <div class="col-12 col-lg-6">
                        <div class="p-3 rounded-2" style="background:var(--bg-subtle); border:1px solid var(--border); height:100%;">
                            <div style="font-size:13px; font-weight:600; color:var(--text-primary); margin-bottom:8px;">
                                Konsep Integrasi AHP dan SAW
                            </div>
                            <p style="font-size:12px; color:var(--text-secondary); text-align:justify; margin-bottom:8px;">
                                Sistem Pendukung Keputusan (SPK) di laboratorium SIARKA menggabungkan keunggulan dua metode pengambilan keputusan multikriteria (*Multi-Criteria Decision Making*):
                            </p>
                            <div class="mb-2 p-2 rounded-1" style="background:var(--bg-surface); border:1px solid var(--border);">
                                <strong style="font-size:12px; color:var(--text-primary); display:block; margin-bottom:2px;">1. Analytical Hierarchy Process (AHP)</strong>
                                <span style="font-size:11px; color:var(--text-secondary);">
                                    Digunakan untuk menguji konsistensi dan menetapkan bobot kepentingan kriteria berdasarkan pertimbangan kepentingan institusi.
                                </span>
                                <div style="font-family:monospace; font-size:11px; color:var(--accent); margin-top:4px; padding-top:4px; border-top:1px dashed var(--border);">
                                    &bull; &lambda;<sub>max</sub> = {{ number_format($ahp['lambda_max'], 4, ',', '.') }}<br>
                                    &bull; CI = (&lambda;<sub>max</sub> &minus; n) / (n &minus; 1) = {{ number_format($ahp['ci'], 4, ',', '.') }}<br>
                                    &bull; CR = CI / RI (0,58) = {{ number_format($ahp['cr'], 4, ',', '.') }} ({{ $ahp['konsisten'] ? 'Konsisten' : 'Tidak Konsisten' }})
                                </div>
                            </div>
                            <div class="p-2 rounded-1" style="background:var(--bg-surface); border:1px solid var(--border);">
                                <strong style="font-size:12px; color:var(--text-primary); display:block; margin-bottom:2px;">2. Simple Additive Weighting (SAW)</strong>
                                <span style="font-size:11px; color:var(--text-secondary);">
                                    Dikenal sebagai metode penjumlahan terbobot. Mencari penjumlahan terbobot dari rating kinerja pada setiap alternatif di semua atribut.
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="p-3 rounded-2" style="background:var(--bg-subtle); border:1px solid var(--border); height:100%;">
                            <div style="font-size:13px; font-weight:600; color:var(--text-primary); margin-bottom:8px;">
                                Persamaan Matematis SAW
                            </div>
                            <div class="p-2 mb-2 rounded-1" style="background:var(--bg-surface); border:1px solid var(--border); font-size:12px;">
                                <strong style="color:var(--text-primary); display:block; margin-bottom:2px;">Normalisasi Matriks (R<sub>ij</sub>):</strong>
                                <div style="color:var(--text-secondary); font-size:11px;">
                                    &bull; <strong>Benefit</strong> (C2 Pinjam): R<sub>ij</sub> = X<sub>ij</sub> / Max(X<sub>j</sub>)<br>
                                    &bull; <strong>Cost</strong> (C1 Stok & C3 Harga): R<sub>ij</sub> = Min(X<sub>j</sub>) / X<sub>ij</sub>
                                </div>
                            </div>
                            <div class="p-2 rounded-1" style="background:var(--bg-surface); border:1px solid var(--border); font-size:12px;">
                                <strong style="color:var(--text-primary); display:block; margin-bottom:2px;">Nilai Preferensi (V<sub>i</sub>):</strong>
                                <div style="font-family:monospace; color:var(--accent); font-weight:600; font-size:12px; margin-bottom:2px;">
                                    V<sub>i</sub> = (W<sub>1</sub> &times; R<sub>i1</sub>) + (W<sub>2</sub> &times; R<sub>i2</sub>) + (W<sub>3</sub> &times; R<sub>i3</sub>)
                                </div>
                                <span style="font-size:11px; color:var(--text-secondary);">
                                    Alternatif dengan nilai preferensi tertinggi menempati peringkat teratas sebagai rekomendasi prioritas pengadaan.
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
        <div class="alert alert-warning" role="alert" style="font-size:13px;">
            Ranking SAW belum ditampilkan karena matriks AHP tidak konsisten. Sesuaikan perbandingan kriteria di atas sampai CR maksimal 0,10.
        </div>
    @endif

    {{-- Flash Messages Import --}}
    @if(session('import_success'))
        <div class="alert d-flex align-items-center gap-2 mt-3" style="background:var(--success-bg); color:var(--success); border:1px solid #B6D4C4; border-radius:6px; font-size:13px;" role="alert">
            <i class="bi bi-check-circle"></i>
            <div>{!! session('import_success') !!}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close" style="font-size:10px;"></button>
        </div>
    @endif

    @if(session('import_error'))
        <div class="alert d-flex align-items-center gap-2 mt-3" style="background:var(--danger-bg); color:var(--danger); border:1px solid #D4A5A2; border-radius:6px; font-size:13px;" role="alert">
            <i class="bi bi-exclamation-circle"></i>
            <div>{!! session('import_error') !!}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close" style="font-size:10px;"></button>
        </div>
    @endif

    @if(session('inv_import_report'))
        @php $report = session('inv_import_report'); @endphp
        <div class="alert mt-3" style="background:var(--warning-bg); color:var(--warning); border:1px solid #EAD8B5; border-radius:6px; font-size:13px;" role="alert">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Import gagal — {{ $report['error_count'] }} dari {{ $report['total'] }} baris bermasalah.</strong>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close" style="font-size:10px;"></button>
            </div>
            <ul class="mb-0 ps-3" style="font-size:12px; max-height:200px; overflow-y:auto;">
                @foreach($report['errors'] as $err)
                    <li>Baris {{ $err['row'] }} ({{ $err['nama'] }}): {{ implode(' ', $err['messages']) }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Modal Import Excel --}}
    <div class="modal fade" id="importTpkModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('pengadaan-tpk.import') }}" method="POST" enctype="multipart/form-data" class="modal-content" style="border-radius:8px; border:1px solid var(--border);">
                @csrf
                <div class="modal-header" style="border-bottom:1px solid var(--border); padding:14px 18px;">
                    <h5 class="modal-title" style="font-size:15px; font-weight:600; color:var(--text-primary);">
                        Import Excel Pengadaan TPK
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup" style="font-size:10px;"></button>
                </div>

                <div class="modal-body" style="padding:18px;">
                    <ol class="ps-3 mb-3" style="font-size:13px; line-height:1.8; color:var(--text-secondary);">
                        <li>
                            Unduh template:
                            <a href="{{ route('pengadaan-tpk.template') }}" style="font-weight:600; color:var(--accent);">Template Excel (.xlsx)</a>.
                        </li>
                        <li>
                            Isi data mulai baris ke-2 sesuai kolom template
                            (Nama, Jenis, Satuan, Stok, Harga, Ruangan).
                        </li>
                        <li>
                            Pilih file lalu klik <strong>Import Data</strong>.
                        </li>
                    </ol>

                    <label class="form-label" for="importTpkFile">File Excel</label>
                    <input
                        type="file"
                        id="importTpkFile"
                        name="file"
                        accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel"
                        class="form-control @error('file') is-invalid @enderror"
                    >
                    @error('file')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror

                    <div style="font-size:11px; color:var(--text-muted); margin-top:6px;">
                        Format: .xlsx / .xls. Maksimal 5 MB, 2.000 baris data.
                    </div>

                    <div class="d-flex align-items-start gap-2 mt-3" style="background:var(--bg-subtle); border:1px solid var(--border); border-radius:6px; padding:10px 12px; font-size:12px; color:var(--text-secondary);">
                        <i class="bi bi-info-circle" style="color:var(--accent); margin-top:2px;"></i>
                        <span>
                            Semua baris diperiksa lebih dulu. Jika ada yang bermasalah,
                            tidak ada data yang disimpan dan baris yang salah akan ditampilkan beserta alasannya.
                        </span>
                    </div>
                </div>

                <div class="modal-footer" style="border-top:1px solid var(--border); padding:12px 18px;">
                    <button type="button" class="btn-admin-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-admin-primary">
                        <i class="bi bi-upload"></i> Import Data
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
    // Auto-open import modal if there's a file validation error
    @if($errors->has('file'))
        document.addEventListener('DOMContentLoaded', function() {
            var modal = new bootstrap.Modal(document.getElementById('importTpkModal'));
            modal.show();
        });
    @endif

</script>
@endpush
@endsection
