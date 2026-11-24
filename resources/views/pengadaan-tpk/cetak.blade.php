<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rekomendasi Pengadaan TPK — {{ $bulan }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #111;
            background: #fff;
            padding: 20px;
        }
        .kop-surat {
            border-bottom: 3px double #000;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .kop-title {
            font-size: 14pt;
            font-weight: bold;
            line-height: 1.2;
            text-transform: uppercase;
        }
        .kop-sub {
            font-size: 12pt;
            font-weight: bold;
        }
        .kop-address {
            font-size: 9.5pt;
            font-style: italic;
            color: #333;
        }
        .judul-laporan {
            font-size: 13pt;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .subjudul-laporan {
            font-size: 10pt;
            text-align: center;
            color: #444;
            margin-bottom: 18px;
        }
        .meta-table {
            font-size: 9.5pt;
            margin-bottom: 15px;
        }
        .meta-table td {
            padding: 2px 6px;
        }
        .table-cetak {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }
        .table-cetak th, .table-cetak td {
            border: 1px solid #000;
            padding: 5px 6px;
        }
        .table-cetak th {
            background-color: #f2f2f2 !important;
            text-align: center;
            font-weight: bold;
        }
        .tanda-tangan {
            margin-top: 35px;
            font-size: 10pt;
            page-break-inside: avoid;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            @page {
                size: A4 portrait;
                margin: 1.5cm 1cm 1.5cm 1cm;
            }
        }
    </style>
</head>
<body>

    {{-- Tombol Aksi di Layar (disembunyikan saat dicetak) --}}
    <div class="no-print mb-4 p-3 bg-light rounded border d-flex justify-content-between align-items-center">
        <div>
            <strong>Mode Pratinjau Cetak / PDF</strong> — Pastikan tata letak dan data telah sesuai sebelum dicetak.
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary btn-sm">
                Cetak Dokumen / Simpan PDF
            </button>
            <button onclick="window.close()" class="btn btn-secondary btn-sm">
                Tutup
            </button>
        </div>
    </div>

    {{-- KOP SURAT RESMI --}}
    <div class="kop-surat text-center">
        <div class="kop-title">KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</div>
        <div class="kop-sub">POLITEKNIK NEGERI TANAH LAUT</div>
        <div class="kop-sub">JURUSAN TEKNOLOGI INFORMASI — LABORATORIUM KOMPUTER DAN BISNIS</div>
        <div class="kop-address">Jl. A. Yani Km. 06 Desa Panggung, Kec. Pelaihari, Kab. Tanah Laut, Kalimantan Selatan 70815 | Email: siarka@politala.ac.id</div>
    </div>

    {{-- JUDUL LAPORAN --}}
    <div class="judul-laporan">REKOMENDASI PRIORITAS PENGADAAN ALAT DAN BAHAN LABORATORIUM</div>
    <div class="subjudul-laporan">Berdasarkan pembobotan AHP dan pemeringkatan Simple Additive Weighting (SAW)</div>

    {{-- TABEL METADATA --}}
    <table class="meta-table">
        <tr>
            <td style="width:160px;"><strong>Bulan Periode</strong></td>
            <td style="width:10px;">:</td>
            <td>{{ \Carbon\Carbon::parse($bulan.'-01')->translatedFormat('F Y') }}</td>
            <td style="width:160px;"><strong>Tanggal Dicetak</strong></td>
            <td style="width:10px;">:</td>
            <td>{{ $tanggalCetak }}</td>
        </tr>
        <tr>
            <td><strong>Filter Kategori</strong></td>
            <td>:</td>
            <td>{{ ucfirst($jenis) }} (Total: {{ $totalItem }} Alternatif)</td>
            <td><strong>Petugas Laboran</strong></td>
            <td>:</td>
            <td>{{ $user['nama'] ?? 'Laboran' }}</td>
        </tr>
        <tr>
            <td><strong>Bobot Penilaian (W)</strong></td>
            <td>:</td>
            <td colspan="4">
                CI = {{ number_format($ahp['ci'], 4, ',', '.') }} &bull;
                CR = {{ number_format($ahp['cr'], 4, ',', '.') }} &bull;
                W1 (Stok - Cost) = {{ number_format($w1, 4, ',', '.') }} ({{ number_format($w1*100, 2, ',', '.') }}%) &bull;
                W2 (Peminjaman - Benefit) = {{ number_format($w2, 4, ',', '.') }} ({{ number_format($w2*100, 2, ',', '.') }}%) &bull;
                W3 (Harga - Cost) = {{ number_format($w3, 4, ',', '.') }} ({{ number_format($w3*100, 2, ',', '.') }}%)
            </td>
        </tr>
    </table>

    {{-- TABEL DATA REKOMENDASI PENGADAAN --}}
    <table class="table-cetak">
        <thead>
            <tr>
                <th style="width:35px;">Rank</th>
                <th>Nama Alat / Bahan</th>
                <th style="width:55px;">Kategori</th>
                <th style="width:50px;">Satuan</th>
                <th style="width:60px;">Sisa Stok</th>
                <th style="width:65px;">Total Pinjam</th>
                <th style="width:90px;">Harga Satuan</th>
                <th style="width:65px;">Nilai (V)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $it)
                <tr>
                    <td class="text-center font-monospace">{{ $it['ranking'] }}</td>
                    <td>
                        <strong>{{ $it['nama'] }}</strong>
                        <div style="font-size:8pt; color:#444;">Lokasi: {{ $it['nama_ruangan'] ?? 'Lab' }}</div>
                    </td>
                    <td class="text-center">{{ ucfirst($it['jenis']) }}</td>
                    <td class="text-center">{{ $it['nama_satuan'] ?? '-' }}</td>
                    <td class="text-end">{{ $it['stok'] }}</td>
                    <td class="text-center">{{ $it['jumlah_peminjaman'] }}</td>
                    <td class="text-end">Rp {{ number_format($it['harga'], 0, ',', '.') }}</td>
                    <td class="text-end font-monospace"><strong>{{ number_format($it['nilai'], 4) }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-3">Tidak ada data katalog untuk dianalisis.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- CATATAN PENJELASAN SINGKAT --}}
    <div style="font-size:8.5pt; margin-top:12px; color:#444;">
        <em>Catatan: Rekomendasi ini dihitung secara objektif menggunakan metode Simple Additive Weighting (SAW) berdasarkan data inventaris dan aktivitas peminjaman laboratorium. Alternatif dengan Nilai Preferensi (V) tertinggi menempati peringkat teratas sebagai rekomendasi utama pengadaan.</em>
    </div>

    {{-- BLOK TANDA TANGAN --}}
    <div class="row tanda-tangan">
        <div class="col-6 text-center">
            <p class="mb-1">Mengetahui,</p>
            <p class="mb-0"><strong>Kepala Laboratorium Komputer dan Bisnis</strong></p>
            <div style="height: 65px;"></div>
            <p class="mb-0 text-decoration-underline"><strong>Wiwik Kusrini, S.Kom., M.Cs.</strong></p>
            <p class="mb-0" style="font-size:9pt;">NIP/NIDN: .......................................</p>
        </div>
        <div class="col-6 text-center">
            <p class="mb-1">Pelaihari, {{ $tanggalCetak }}</p>
            <p class="mb-0"><strong>Laboran Pengelola Inventaris</strong></p>
            <div style="height: 65px;"></div>
            <p class="mb-0 text-decoration-underline"><strong>{{ $user['nama'] ?? 'Muhammad Shaufi, S.Kom.' }}</strong></p>
            <p class="mb-0" style="font-size:9pt;">Laboran / Admin SIARKA</p>
        </div>
    </div>

</body>
</html>
