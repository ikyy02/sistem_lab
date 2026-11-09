@extends('layouts.app')

@section('title', 'Pengadaan TPK')
@section('page-title', 'Pengadaan TPK')
@section('page-subtitle', 'Sistem Pendukung Keputusan prioritas pengadaan alat dan bahan laboratorium (Metode SAW).')

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
        box-shadow: 0 1px 2px rgba(16,24,40,.05);
        height: 100%;
    }
    .summary-card.active-top {
        border-top-color: var(--primary);
    }
    .summary-label {
        font-size: 12px;
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
        box-shadow: 0 1px 2px rgba(16,24,40,.05);
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
        background: var(--primary);
        border: 1px solid var(--primary);
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
        background: var(--bg-subtle);
        color: var(--primary);
        border-color: var(--primary);
    }
    .btn-preset-quiet.active {
        background: var(--bg-subtle);
        border-color: var(--primary);
        color: var(--primary);
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
        color: var(--primary);
        border-bottom-color: var(--primary);
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
        font-size: 12px;
        font-weight: 500;
        color: var(--text-secondary);
        background: var(--bg-subtle);
        border: 1px solid var(--border);
    }
    .badge-rank.rank-1 {
        border-color: var(--primary);
        color: var(--primary);
        font-weight: 600;
        background: var(--bg-subtle);
    }

    /* Badge Kategori: Satu gaya netral untuk Alat dan Bahan */
    .badge-kategori-netral {
        background: var(--bg-subtle);
        color: var(--text-secondary);
        font-size: 12px;
        font-weight: 500;
        padding: 2px 7px;
        border-radius: 4px;
        border: 1px solid var(--border);
    }

    /* Badge Prioritas (Warna Redup / Muted Status) */
    .badge-prio-sangat-tinggi {
        background: var(--danger-soft);
        color: var(--danger);
        font-size: 12px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 4px;
        display: inline-block;
    }
    .badge-prio-tinggi {
        background: var(--warning-soft);
        color: var(--warning);
        font-size: 12px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 4px;
        display: inline-block;
    }
    .badge-prio-sedang {
        background: var(--info-soft);
        color: var(--info);
        font-size: 12px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 4px;
        display: inline-block;
    }
    .badge-prio-rendah {
        background: var(--bg-subtle);
        color: var(--text-secondary);
        font-size: 12px;
        font-weight: 500;
        padding: 3px 8px;
        border-radius: 4px;
        display: inline-block;
    }

    /* Progress bar: 5px, satu warna --accent seragam */
    .bar-track {
        height: 5px;
        background: var(--border);
        border-radius: 999px;
        overflow: hidden;
    }
    .bar-fill {
        height: 100%;
        background: var(--primary);
        border-radius: 999px;
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
        border-color: var(--primary);
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
                Sistem Pendukung Keputusan penentuan prioritas pengadaan alat dan bahan laboratorium menggunakan kombinasi metode <strong>AHP & SAW</strong>.
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('pengadaan-tpk.cetak', request()->query()) }}" target="_blank" class="btn-admin-secondary">
                <i class="bi bi-printer" aria-hidden="true"></i>
                <span>Cetak Rekomendasi</span>
            </a>
            <a href="{{ route('pengadaan-tpk.export-csv', request()->query()) }}" class="btn-admin-secondary">
                <i class="bi bi-download" aria-hidden="true"></i>
                <span>Unduh CSV</span>
            </a>
        </div>
    </div>

    {{-- Alert Validasi Bobot --}}
    @if(isset($bobotValid) && !$bobotValid)
        <div class="alert mb-3 d-flex align-items-center gap-2" style="background:var(--warning-soft); color:var(--warning); border:1px solid #EAD8B5; border-radius:6px; font-size:13px;" role="alert">
            <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
            <div>
                Jumlah bobot W1 + W2 + W3 harus tepat 1.0 (100%). Sistem secara otomatis menerapkan bobot acuan standar (0.40, 0.30, 0.30).
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close" style="font-size:12px;"></button>
        </div>
    @endif

    {{-- Kartu Ringkasan (Gaya Seragam & Tenang) --}}
    <div class="row g-3 mb-3">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="summary-card active-top">
                <div class="summary-label">
                    <span>Total Alternatif</span>
                    <i class="bi bi-boxes summary-icon" aria-hidden="true"></i>
                </div>
                <div class="summary-val">{{ $totalItem }}</div>
                <div class="summary-sub">Katalog dianalisis ({{ ucfirst($jenis) }})</div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="summary-card">
                <div class="summary-label">
                    <span>Prioritas Utama (#1)</span>
                    <i class="bi bi-award summary-icon" aria-hidden="true"></i>
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
                    <i class="bi bi-calendar3 summary-icon" aria-hidden="true"></i>
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
                    <i class="bi bi-sliders summary-icon" aria-hidden="true"></i>
                </div>
                <div class="summary-val">
                    {{ round($w1 * 100) }}% : {{ round($w2 * 100) }}% : {{ round($w3 * 100) }}%
                </div>
                <div class="summary-sub">C1 (Stok) : C2 (Pinjam) : C3 (Harga)</div>
            </div>
        </div>
    </div>

    {{-- Panel Pengaturan & Pembobotan Kriteria --}}
    <div class="admin-card mb-3">
        <div class="admin-card-header">
            <h6 class="admin-card-title">
                <i class="bi bi-funnel text-secondary" aria-hidden="true"></i>
                <span>Parameter & Pembobotan Kriteria</span>
            </h6>
            <div>
                <span id="total-bobot-badge" style="background:var(--success-soft); color:var(--success); font-size:12px; font-weight:600; padding:2px 8px; border-radius:4px;">
                    Total Bobot: 100% (Valid)
                </span>
            </div>
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

                    {{-- Preset Bobot AHP --}}
                    <div class="col-12 col-md-6">
                        <label class="form-label">Preset Pembobotan Cepat</label>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn-preset-quiet {{ $preset === 'standar' || (!$preset && $w1==0.4 && $w2==0.3 && $w3==0.3) ? 'active' : '' }}" onclick="terapkanPreset(0.40, 0.30, 0.30, 'standar')">
                                Standar (40-30-30)
                            </button>
                            <button type="button" class="btn-preset-quiet {{ $preset === 'peminjaman_tinggi' ? 'active' : '' }}" onclick="terapkanPreset(0.30, 0.50, 0.20, 'peminjaman_tinggi')">
                                Kebutuhan Praktikum (30-50-20)
                            </button>
                            <button type="button" class="btn-preset-quiet {{ $preset === 'stok_kritis' ? 'active' : '' }}" onclick="terapkanPreset(0.60, 0.25, 0.15, 'stok_kritis')">
                                Kelangkaan Stok (60-25-15)
                            </button>
                        </div>
                    </div>

                    {{-- Kotak Rincian Bobot --}}
                    <div class="col-12">
                        <div class="p-3 rounded-2" style="background:var(--bg-subtle); border:1px solid var(--border);">
                            <div class="row g-2 align-items-center">
                                <div class="col-12 col-md-3">
                                    <span style="font-size:12px; font-weight:600; color:var(--text-primary);">Nilai Bobot (W):</span>
                                    <div style="font-size:12px; color:var(--text-muted);">Jumlah harus tepat 1.0 (100%)</div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text" style="font-size:12px; background:var(--bg-surface); color:var(--text-secondary); width:85px;">W1: Stok</span>
                                        <input type="number" step="0.05" min="0" max="1" name="w1" id="w1" class="form-control" value="{{ $w1 }}" oninput="updateTotalBobot()">
                                        <span class="input-group-text" style="font-size:12px; background:var(--bg-surface); color:var(--text-muted);">{{ round($w1 * 100) }}%</span>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text" style="font-size:12px; background:var(--bg-surface); color:var(--text-secondary); width:85px;">W2: Pinjam</span>
                                        <input type="number" step="0.05" min="0" max="1" name="w2" id="w2" class="form-control" value="{{ $w2 }}" oninput="updateTotalBobot()">
                                        <span class="input-group-text" style="font-size:12px; background:var(--bg-surface); color:var(--text-muted);">{{ round($w2 * 100) }}%</span>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text" style="font-size:12px; background:var(--bg-surface); color:var(--text-secondary); width:85px;">W3: Harga</span>
                                        <input type="number" step="0.05" min="0" max="1" name="w3" id="w3" class="form-control" value="{{ $w3 }}" oninput="updateTotalBobot()">
                                        <span class="input-group-text" style="font-size:12px; background:var(--bg-surface); color:var(--text-muted);">{{ round($w3 * 100) }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="preset" id="preset_input" value="{{ $preset }}">
                    </div>

                    {{-- Form Pencarian & Submit --}}
                    <div class="col-12 col-md-8">
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control" placeholder="Cari nama barang, jenis, atau lokasi..." value="{{ $search }}">
                            @if($search)
                                <a href="{{ route('pengadaan-tpk.index', request()->except('search')) }}" class="btn-admin-secondary" style="border-radius:0;">Reset</a>
                            @endif
                            <button type="submit" class="btn-admin-primary">
                                <span>Terapkan</span>
                            </button>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 text-md-end">
                        <a href="{{ route('pengadaan-tpk.index') }}" style="font-size:12px; color:var(--text-secondary); text-decoration:none;">
                            Kembalikan ke Standar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabs Konten Utama --}}
    <div class="admin-card overflow-hidden">
        {{-- Navigation Tabs --}}
        <div class="admin-tabs" id="tpkTabs" role="tablist">
            <button class="admin-tab-btn active" id="tab-rekomendasi" data-bs-toggle="tab" data-bs-target="#content-rekomendasi" type="button" role="tab">
                <span>Rekomendasi Prioritas</span>
                <span style="font-size:12px; padding:1px 6px; border-radius:4px; background:var(--bg-subtle); color:var(--text-secondary);">
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
                {{-- Banner Simulasi Anggaran --}}
                <div class="simulasi-banner d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                    <div>
                        <div style="font-size:13px; font-weight:600; color:var(--text-primary);">
                            Simulasi Estimasi Usulan Anggaran
                        </div>
                        <div style="font-size:12px; color:var(--text-secondary);">
                            Ketik jumlah kebutuhan pada kolom <strong>Usulan Qty</strong> untuk menghitung estimasi biaya otomatis.
                        </div>
                    </div>
                    <div class="text-md-end">
                        <span style="font-size:12px; text-transform:uppercase; color:var(--text-muted); display:block;">Total Estimasi Usulan</span>
                        <span style="font-size:16px; font-weight:600; color:var(--text-primary);" id="total-anggaran-badge">Rp 0</span>
                    </div>
                </div>

                {{-- Tabel Rekomendasi Prioritas --}}
                <div class="table-responsive">
                    <table class="table-admin">
<caption class="visually-hidden">{{ trim($__env->yieldContent('page-title')) }}</caption>
                        <thead>
                            <tr>
                                <th scope="col" class="text-center" style="width:50px;">Rank</th>
                                <th scope="col">Nama Alat / Bahan</th>
                                <th scope="col" class="text-center" style="width:80px;">Kategori</th>
                                <th scope="col" class="text-end" style="width:90px;">Sisa Stok (C1)</th>
                                <th scope="col" class="text-center" style="width:90px;">Pinjam (C2)</th>
                                <th scope="col" class="text-end" style="width:120px;">Harga Satuan (C3)</th>
                                <th scope="col" style="min-width:160px;">Skor Preferensi (V)</th>
                                <th scope="col" class="text-center" style="width:110px;">Prioritas</th>
                                <th scope="col" class="text-center" style="width:90px;">Usulan Qty</th>
                                <th scope="col" class="text-end" style="width:130px;">Estimasi Biaya</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($filteredItems as $item)
                                @php
                                    $rank = $item['ranking'];
                                    $percent = min(100, max(5, round($item['nilai'] * 100)));
                                    $prioClass = match($item['prioritas_label']) {
                                        'Sangat Tinggi' => 'badge-prio-sangat-tinggi',
                                        'Tinggi' => 'badge-prio-tinggi',
                                        'Sedang' => 'badge-prio-sedang',
                                        default => 'badge-prio-rendah',
                                    };
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
                                        <div style="font-size:12px; color:var(--text-muted);">
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
                                        <span style="font-size:12px; color:var(--text-muted);">{{ $item['nama_satuan'] ?? '' }}</span>
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

                                    {{-- Skor V & Progress Bar (Satu warna --accent seragam) --}}
                                    <td>
                                        <div class="d-flex align-items-center justify-content-between mb-1" style="font-size:12px;">
                                            <span style="font-weight:600; color:var(--text-primary); font-family:monospace;">
                                                {{ number_format($item['nilai'], 4) }}
                                            </span>
                                            <span style="color:var(--text-muted);">{{ $percent }}%</span>
                                        </div>
                                        <div class="bar-track">
                                            <div class="bar-fill" style="width: {{ $percent }}%;"></div>
                                        </div>
                                    </td>

                                    {{-- Status Prioritas (Versi Redup) --}}
                                    <td class="text-center">
                                        <span class="{{ $prioClass }}">
                                            {{ $item['prioritas_label'] }}
                                        </span>
                                    </td>

                                    {{-- Usulan Qty --}}
                                    <td class="text-center">
                                        <input type="number" min="0" step="1" class="form-control form-control-sm text-center usulan-qty" data-harga="{{ $item['harga'] }}" placeholder="0" style="width:68px; margin:auto; font-size:12px; padding:3px 6px;" oninput="hitungSimulasi(this)">
                                    </td>

                                    {{-- Estimasi Biaya --}}
                                    <td class="text-end estimasi-baris" style="font-size:12px; font-weight:500; color:var(--text-primary);">
                                        Rp 0
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4" style="color:var(--text-muted); font-size:13px;">
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
                                <span style="font-size:12px; text-transform:uppercase; color:var(--text-secondary); display:block;">C1: Stok Minimum (Cost)</span>
                                <span style="font-size:15px; font-weight:600; color:var(--text-primary);">{{ $stats['min_stok'] ?? 0 }}</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-2 rounded-2" style="background:var(--bg-subtle); border:1px solid var(--border);">
                                <span style="font-size:12px; text-transform:uppercase; color:var(--text-secondary); display:block;">C2: Peminjaman Maksimum (Benefit)</span>
                                <span style="font-size:15px; font-weight:600; color:var(--text-primary);">{{ $stats['max_pinjam'] ?? 0 }} transaksi</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-2 rounded-2" style="background:var(--bg-subtle); border:1px solid var(--border);">
                                <span style="font-size:12px; text-transform:uppercase; color:var(--text-secondary); display:block;">C3: Harga Minimum (Cost)</span>
                                <span style="font-size:15px; font-weight:600; color:var(--text-primary);">Rp {{ number_format($stats['min_harga'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="font-size:13px; font-weight:600; color:var(--text-primary); margin-bottom:6px;">
                    Matriks Keputusan Awal (X) dan Matriks Normalisasi (R)
                </div>
                <div class="table-responsive">
                    <table class="table-admin" style="font-size:12px;">
<caption class="visually-hidden">{{ trim($__env->yieldContent('page-title')) }}</caption>
                        <thead>
                            <tr class="text-center">
                                <th scope="col" rowspan="2" style="width:35px;">No</th>
                                <th scope="col" rowspan="2" class="text-start">Alternatif (Nama Barang)</th>
                                <th scope="col" colspan="3">Matriks Awal (X)</th>
                                <th scope="col" colspan="3">Matriks Normalisasi (R)</th>
                                <th scope="col" rowspan="2" style="width:100px;">Nilai V</th>
                                <th scope="col" rowspan="2" style="width:50px;">Rank</th>
                            </tr>
                            <tr class="text-center">
                                <th scope="col">C1: Stok</th>
                                <th scope="col">C2: Pinjam</th>
                                <th scope="col">C3: Harga</th>
                                <th scope="col">R1 (W1={{ $w1 }})</th>
                                <th scope="col">R2 (W2={{ $w2 }})</th>
                                <th scope="col">R3 (W3={{ $w3 }})</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($filteredItems as $idx => $it)
                                <tr>
                                    <td class="text-center" style="color:var(--text-muted);">{{ $idx + 1 }}</td>
                                    <td style="font-weight:500;">{{ $it['nama'] }}</td>
                                    <td class="text-end" style="color:var(--text-secondary);">{{ $it['stok'] }}</td>
                                    <td class="text-center" style="color:var(--text-secondary);">{{ $it['jumlah_peminjaman'] }}</td>
                                    <td class="text-end" style="color:var(--text-secondary);">Rp {{ number_format($it['harga'], 0, ',', '.') }}</td>
                                    <td class="text-end" style="font-family:monospace; color:var(--text-primary);">{{ number_format($it['r'][0], 4) }}</td>
                                    <td class="text-end" style="font-family:monospace; color:var(--text-primary);">{{ number_format($it['r'][1], 4) }}</td>
                                    <td class="text-end" style="font-family:monospace; color:var(--text-primary);">{{ number_format($it['r'][2], 4) }}</td>
                                    <td class="text-end" style="font-family:monospace; font-weight:600; color:var(--primary);">{{ number_format($it['nilai'], 4) }}</td>
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
                                <span style="font-size:12px; color:var(--text-secondary);">
                                    Digunakan untuk menguji konsistensi dan menetapkan bobot kepentingan kriteria berdasarkan pertimbangan kepentingan institusi.
                                </span>
                            </div>
                            <div class="p-2 rounded-1" style="background:var(--bg-surface); border:1px solid var(--border);">
                                <strong style="font-size:12px; color:var(--text-primary); display:block; margin-bottom:2px;">2. Simple Additive Weighting (SAW)</strong>
                                <span style="font-size:12px; color:var(--text-secondary);">
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
                                <div style="color:var(--text-secondary); font-size:12px;">
                                    &bull; <strong>Benefit</strong> (C2 Pinjam): R<sub>ij</sub> = X<sub>ij</sub> / Max(X<sub>j</sub>)<br>
                                    &bull; <strong>Cost</strong> (C1 Stok & C3 Harga): R<sub>ij</sub> = Min(X<sub>j</sub>) / X<sub>ij</sub>
                                </div>
                            </div>
                            <div class="p-2 rounded-1" style="background:var(--bg-surface); border:1px solid var(--border); font-size:12px;">
                                <strong style="color:var(--text-primary); display:block; margin-bottom:2px;">Nilai Preferensi (V<sub>i</sub>):</strong>
                                <div style="font-family:monospace; color:var(--primary); font-weight:600; font-size:12px; margin-bottom:2px;">
                                    V<sub>i</sub> = (W<sub>1</sub> &times; R<sub>i1</sub>) + (W<sub>2</sub> &times; R<sub>i2</sub>) + (W<sub>3</sub> &times; R<sub>i3</sub>)
                                </div>
                                <span style="font-size:12px; color:var(--text-secondary);">
                                    Alternatif dengan nilai preferensi tertinggi menempati peringkat teratas sebagai rekomendasi prioritas pengadaan.
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function terapkanPreset(w1, w2, w3, presetName) {
        document.getElementById('w1').value = w1.toFixed(2);
        document.getElementById('w2').value = w2.toFixed(2);
        document.getElementById('w3').value = w3.toFixed(2);
        document.getElementById('preset_input').value = presetName;
        updateTotalBobot();
        document.getElementById('form-tpk').submit();
    }

    function updateTotalBobot() {
        const w1 = parseFloat(document.getElementById('w1').value) || 0;
        const w2 = parseFloat(document.getElementById('w2').value) || 0;
        const w3 = parseFloat(document.getElementById('w3').value) || 0;
        const total = Math.round((w1 + w2 + w3) * 100);
        const badge = document.getElementById('total-bobot-badge');

        if (total === 100) {
            badge.style.background = 'var(--success-soft)';
            badge.style.color = 'var(--success)';
            badge.textContent = 'Total Bobot: 100% (Valid)';
        } else {
            badge.style.background = 'var(--danger-soft)';
            badge.style.color = 'var(--danger)';
            badge.textContent = 'Total Bobot: ' + total + '% (Harus 100%)';
        }
    }

    function hitungSimulasi(input) {
        const qty = parseInt(input.value) || 0;
        const harga = parseFloat(input.dataset.harga) || 0;
        const subtotal = qty * harga;

        const row = input.closest('tr');
        const estimasiCell = row.querySelector('.estimasi-baris');
        estimasiCell.textContent = 'Rp ' + subtotal.toLocaleString('id-ID');

        let total = 0;
        document.querySelectorAll('.usulan-qty').forEach(function(el) {
            const q = parseInt(el.value) || 0;
            const h = parseFloat(el.dataset.harga) || 0;
            total += (q * h);
        });

        document.getElementById('total-anggaran-badge').textContent = 'Rp ' + total.toLocaleString('id-ID');
    }
</script>
@endpush
@endsection
