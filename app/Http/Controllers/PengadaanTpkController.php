<?php

namespace App\Http\Controllers;

use App\Services\AhpService;
use App\Services\AuthService;
use App\Services\TpkService;
use App\Support\ExcelFile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Pengadaan TPK (Sistem Pendukung Keputusan AHP–SAW) untuk prioritas pengadaan alat dan bahan laboratorium.
 * Khusus role Laboran/Admin.
 */
class PengadaanTpkController extends Controller
{
    public function __construct(
        protected TpkService $tpkService,
        protected AhpService $ahpService
    ) {}

    public function index(Request $request)
    {
        $params = $this->resolveParams($request);
        $hasil = $this->hitung($params);

        return view('pengadaan-tpk.index', array_merge($params, $hasil, [
            'skala' => array_keys(AhpService::skala()),
            'daftarBulan' => $this->daftarBulanPilihan(),
        ]));
    }

    public function cetak(Request $request)
    {
        $params = $this->resolveParams($request);
        $hasil = $this->hitung($params);
        if (! $hasil['resmi']) {
            return redirect()->route('pengadaan-tpk.index', $request->query());
        }
        $user = AuthService::user();

        return view('pengadaan-tpk.cetak', array_merge($params, $hasil, [
            'user' => $user,
            'tanggalCetak' => Carbon::now()->translatedFormat('d F Y'),
        ]));
    }

    public function exportCsv(Request $request): StreamedResponse|\Illuminate\Http\RedirectResponse
    {
        $params = $this->resolveParams($request);
        $hasil = $this->hitung($params);
        if (! $hasil['resmi']) {
            return redirect()->route('pengadaan-tpk.index', $request->query());
        }

        $filename = 'Rekomendasi_Pengadaan_TPK_' . $params['bulan'] . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($hasil, $params) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM so Excel opens with proper accents and formatting
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Judul dan metadata
            fputcsv($handle, ['REKOMENDASI PRIORITAS PENGADAAN ALAT DAN BAHAN LABORATORIUM']);
            fputcsv($handle, ['Sistem Informasi SIARKA - Metode AHP dan Simple Additive Weighting (SAW)']);
            fputcsv($handle, ['Bulan Periode', $params['bulan']]);
            fputcsv($handle, ['Filter Kategori', ucfirst($params['jenis'])]);
            fputcsv($handle, ['Consistency Ratio (CR)', $params['ahp']['cr']]);
            fputcsv($handle, ['Bobot W1 (Stok) dari AHP', $params['w1']]);
            fputcsv($handle, ['Bobot W2 (Peminjaman) dari AHP', $params['w2']]);
            fputcsv($handle, ['Bobot W3 (Harga) dari AHP', $params['w3']]);
            fputcsv($handle, []);

            // Header kolom
            fputcsv($handle, [
                'Peringkat',
                'ID Katalog',
                'Nama Barang',
                'Jenis',
                'Satuan',
                'Ruangan',
                'Stok Saat Ini (C1)',
                'Jumlah Peminjaman/Bulan (C2)',
                'Harga per Satuan (Rp) (C3)',
                'Normalisasi R1 (Stok)',
                'Normalisasi R2 (Peminjaman)',
                'Normalisasi R3 (Harga)',
                'Nilai Preferensi (V)',
                'Status Prioritas',
            ]);

            foreach ($hasil['items'] as $it) {
                fputcsv($handle, [
                    $it['ranking'],
                    $it['id_katalog'],
                    $it['nama'],
                    ucfirst($it['jenis']),
                    $it['nama_satuan'] ?? '-',
                    $it['nama_ruangan'] ?? '-',
                    $it['stok'],
                    $it['jumlah_peminjaman'],
                    $it['harga'],
                    round($it['r'][0], 4),
                    round($it['r'][1], 4),
                    round($it['r'][2], 4),
                    round($it['nilai'], 4),
                    $it['prioritas_label'],
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Export hasil rekomendasi TPK ke file Excel (.xlsx).
     */
    public function exportExcel(Request $request): StreamedResponse|\Illuminate\Http\RedirectResponse
    {
        $params = $this->resolveParams($request);
        $hasil = $this->hitung($params);
        if (! $hasil['resmi']) {
            return redirect()->route('pengadaan-tpk.index', $request->query());
        }

        $filename = 'Rekomendasi_Pengadaan_TPK_' . $params['bulan'] . '.xlsx';

        $book = new Spreadsheet();
        $sheet = $book->getActiveSheet()->setTitle('Rekomendasi TPK');

        // Metadata header
        $sheet->setCellValue('A1', 'REKOMENDASI PRIORITAS PENGADAAN ALAT DAN BAHAN LABORATORIUM');
        $sheet->setCellValue('A2', 'Sistem Informasi SIARKA - Metode AHP dan Simple Additive Weighting (SAW)');
        $sheet->setCellValue('A3', 'Bulan Periode');
        $sheet->setCellValue('B3', $params['bulan']);
        $sheet->setCellValue('A4', 'Filter Kategori');
        $sheet->setCellValue('B4', ucfirst($params['jenis']));
        $sheet->setCellValue('A5', 'Consistency Ratio (CR)');
        $sheet->setCellValue('B5', $params['ahp']['cr']);
        $sheet->setCellValue('A6', 'Bobot W1 (Stok) dari AHP');
        $sheet->setCellValue('B6', $params['w1']);
        $sheet->setCellValue('A7', 'Bobot W2 (Peminjaman) dari AHP');
        $sheet->setCellValue('B7', $params['w2']);
        $sheet->setCellValue('A8', 'Bobot W3 (Harga) dari AHP');
        $sheet->setCellValue('B8', $params['w3']);

        // Bold metadata
        $sheet->getStyle('A1:A8')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(12);

        // Kolom header data (baris 10)
        $columns = [
            'Peringkat', 'ID Katalog', 'Nama Barang', 'Jenis', 'Satuan', 'Ruangan',
            'Stok Saat Ini (C1)', 'Jumlah Peminjaman (C2)', 'Harga per Satuan (C3)',
            'Normalisasi R1', 'Normalisasi R2', 'Normalisasi R3',
            'Nilai Preferensi (V)', 'Status Prioritas',
        ];
        $sheet->fromArray($columns, null, 'A10');
        $lastCol = chr(64 + count($columns));
        $sheet->getStyle("A10:{$lastCol}10")->getFont()->setBold(true);
        $sheet->getStyle("A10:{$lastCol}10")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F1F3F5');

        // Data rows
        $row = 11;
        foreach ($hasil['items'] as $it) {
            $sheet->fromArray([
                $it['ranking'],
                $it['id_katalog'],
                $it['nama'],
                ucfirst($it['jenis']),
                $it['nama_satuan'] ?? '-',
                $it['nama_ruangan'] ?? '-',
                $it['stok'],
                $it['jumlah_peminjaman'],
                $it['harga'],
                round($it['r'][0], 4),
                round($it['r'][1], 4),
                round($it['r'][2], 4),
                round($it['nilai'], 4),
                $it['prioritas_label'],
            ], null, "A{$row}");
            $row++;
        }

        // Auto-size columns
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($book);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Download template Excel untuk import data pengadaan TPK.
     */
    public function template()
    {
        $headers = ['Nama', 'Jenis', 'Satuan', 'Stok', 'Harga', 'Ruangan', 'Keterangan'];

        $book = new Spreadsheet();
        $sheet = $book->getActiveSheet()->setTitle('Data');
        $sheet->fromArray([$headers], null, 'A1');
        $lastCol = chr(64 + count($headers));
        $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);
        foreach ($headers as $i => $h) {
            $sheet->getColumnDimensionByColumn($i + 1)->setWidth(max(16, min(40, mb_strlen($h) + 12)));
        }

        // Sheet Petunjuk
        $guide = $book->createSheet()->setTitle('Petunjuk');
        $guide->fromArray(array_map(fn ($g) => [$g], [
            'Petunjuk pengisian',
            'Isi data pada sheet "Data" mulai baris ke-2. Jangan ubah judul kolom.',
            'Nama: wajib diisi, maksimal 50 karakter.',
            'Jenis: "alat" atau "bahan".',
            'Satuan: nama satuan yang sudah terdaftar di sistem.',
            'Stok: angka bulat 0 atau lebih.',
            'Harga: harga per satuan, lebih dari 0.',
            'Ruangan: nama ruangan yang sudah terdaftar di sistem.',
            'Keterangan: boleh dikosongkan.',
        ]), null, 'A1');
        $guide->getStyle('A1')->getFont()->setBold(true);
        $guide->getColumnDimension('A')->setWidth(100);
        $book->setActiveSheetIndex(0);

        $writer = new Xlsx($book);
        $filename = 'template-pengadaan-tpk.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Import data katalog dari file Excel (sama seperti import katalog).
     */
    public function importExcel(Request $request)
    {
        $maxKb = 5120; // 5 MB
        $request->validate(
            ['file' => ExcelFile::rules($maxKb)],
            ExcelFile::messages($maxKb)
        );

        try {
            $service = app(\App\Services\KatalogImportService::class);

            // Detect jenis from file content or default to 'alat'
            $rows = ExcelFile::rows($request->file('file'), 2010);
            $header = $rows[0] ?? [];
            $hasJenis = false;
            foreach ($header as $cell) {
                if (mb_strtolower(trim((string) $cell)) === 'jenis') {
                    $hasJenis = true;
                    break;
                }
            }

            // Use the KatalogImportService for 'alat' category as default
            // The service handles both alat and bahan based on the 'jenis' column in file
            $result = $service->import($request->file('file'), 'alat');

            if ($result['success']) {
                return redirect()->route('pengadaan-tpk.index', request()->query())
                    ->with('import_success', "Berhasil mengimpor <strong>{$result['inserted']}</strong> dari {$result['total']} baris data.");
            }

            return redirect()->route('pengadaan-tpk.index', request()->query())
                ->with('inv_import_report', $result);

        } catch (\RuntimeException $e) {
            return redirect()->route('pengadaan-tpk.index', request()->query())
                ->with('import_error', $e->getMessage());
        }
    }

    private function resolveParams(Request $request): array
    {
        $bulanSekarang = Carbon::now()->format('Y-m');
        $bulan = (string) $request->query('bulan', $bulanSekarang);
        if (! preg_match('/^(\d{4})-(0[1-9]|1[0-2])$/', $bulan)) {
            $bulan = $bulanSekarang;
        }

        $jenis = strtolower((string) $request->query('jenis', 'semua'));
        if (! in_array($jenis, ['semua', 'alat', 'bahan'], true)) {
            $jenis = 'semua';
        }

        // Bobot tidak lagi diterima dari input manual atau preset lama.
        // Nilai perbandingan yang bukan skala Saaty dinormalisasi ke 1.
        $pair = [];
        foreach (array_keys(AhpService::PASANGAN) as $key) {
            $raw = (string) $request->query($key, '1');
            $nilai = AhpService::parseNilai($raw);
            $pair[$key] = $nilai === null ? '1' : AhpService::formatNilai($nilai);
        }

        $ahp = $this->ahpService->hitung($pair);
        [$w1, $w2, $w3] = $ahp['bobot'];

        return [
            'bulan' => $bulan,
            'jenis' => $jenis,
            'pair' => $pair,
            'ahp' => $ahp,
            'w1' => $w1,
            'w2' => $w2,
            'w3' => $w3,
        ];
    }

    private function hitung(array $params): array
    {
        $resmi = (bool) $params['ahp']['konsisten'];
        $jenisFilter = $params['jenis'] === 'semua' ? null : $params['jenis'];

        if ($resmi) {
            $analisis = $this->tpkService->analisis($params['ahp']['bobot'], $params['bulan'], $jenisFilter);
            $items = $analisis['items'];
            $stats = $analisis['stats'];
        } else {
            // Matriks yang tidak konsisten boleh ditinjau, tetapi tidak menghasilkan ranking resmi.
            $items = [];
            $stats = [
                'min_stok' => 0.0,
                'max_pinjam' => 0,
                'min_harga' => 0.0,
                'total_item' => 0,
            ];
        }

        $total = count($items);
        foreach ($items as &$it) {
            $rank = $it['ranking'];
            if ($total > 0 && $rank <= max(1, (int) ceil($total * 0.20))) {
                $it['prioritas_label'] = 'Sangat Tinggi';
                $it['prioritas_badge'] = 'danger';
            } elseif ($total > 0 && $rank <= max(2, (int) ceil($total * 0.50))) {
                $it['prioritas_label'] = 'Tinggi';
                $it['prioritas_badge'] = 'warning text-dark';
            } elseif ($total > 0 && $rank <= max(3, (int) ceil($total * 0.80))) {
                $it['prioritas_label'] = 'Sedang';
                $it['prioritas_badge'] = 'info text-dark';
            } else {
                $it['prioritas_label'] = 'Rendah';
                $it['prioritas_badge'] = 'secondary';
            }
        }
        unset($it);

        return [
            'items' => $items,
            'filteredItems' => $items,
            'stats' => $stats,
            'totalItem' => $total,
            'topItem' => $items[0] ?? null,
            'avgNilai' => $total > 0 ? array_sum(array_column($items, 'nilai')) / $total : 0,
            'resmi' => $resmi,
        ];
    }

    private function daftarBulanPilihan(): array
    {
        $bulanList = [];
        $cursor = Carbon::now()->startOfMonth();

        // 12 bulan terakhir sampai 2 bulan ke depan
        $cursor->addMonths(2);
        for ($i = 0; $i < 15; $i++) {
            $key = $cursor->format('Y-m');
            $label = $cursor->translatedFormat('F Y');
            $bulanList[$key] = $label;
            $cursor->subMonth();
        }

        return $bulanList;
    }
}
