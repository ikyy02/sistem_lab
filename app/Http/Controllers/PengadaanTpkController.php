<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Services\TpkService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Pengadaan TPK (Sistem Pendukung Keputusan SAW) untuk prioritas pengadaan alat dan bahan laboratorium.
 * Khusus role Laboran/Admin.
 */
class PengadaanTpkController extends Controller
{
    public const PRESETS = [
        'standar' => [
            'nama' => 'Standar Seimbang',
            'deskripsi' => 'Stok 40%, Peminjaman 30%, Harga 30%',
            'bobot' => [0.40, 0.30, 0.30],
        ],
        'peminjaman_tinggi' => [
            'nama' => 'Prioritas Kebutuhan Praktikum',
            'deskripsi' => 'Peminjaman dominan (50%), Stok 30%, Harga 20%',
            'bobot' => [0.30, 0.50, 0.20],
        ],
        'stok_kritis' => [
            'nama' => 'Prioritas Kelangkaan / Stok Habis',
            'deskripsi' => 'Stok kritis dominan (60%), Peminjaman 25%, Harga 15%',
            'bobot' => [0.60, 0.25, 0.15],
        ],
    ];

    public function __construct(
        protected TpkService $tpkService
    ) {}

    public function index(Request $request)
    {
        $params = $this->resolveParams($request);
        $hasil = $this->hitung($params);

        return view('pengadaan-tpk.index', array_merge($params, $hasil, [
            'presets' => self::PRESETS,
            'daftarBulan' => $this->daftarBulanPilihan(),
        ]));
    }

    public function cetak(Request $request)
    {
        $params = $this->resolveParams($request);
        $hasil = $this->hitung($params);
        $user = AuthService::user();

        return view('pengadaan-tpk.cetak', array_merge($params, $hasil, [
            'user' => $user,
            'tanggalCetak' => Carbon::now()->translatedFormat('d F Y'),
        ]));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $params = $this->resolveParams($request);
        $hasil = $this->hitung($params);

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
            fputcsv($handle, ['Sistem Informasi SIARKA - Metode Simple Additive Weighting (SAW)']);
            fputcsv($handle, ['Bulan Periode', $params['bulan']]);
            fputcsv($handle, ['Filter Kategori', ucfirst($params['jenis'])]);
            fputcsv($handle, ['Bobot W1 (Stok)', $params['w1']]);
            fputcsv($handle, ['Bobot W2 (Peminjaman)', $params['w2']]);
            fputcsv($handle, ['Bobot W3 (Harga)', $params['w3']]);
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

        $preset = (string) $request->query('preset', '');
        if (isset(self::PRESETS[$preset])) {
            [$w1, $w2, $w3] = self::PRESETS[$preset]['bobot'];
        } else {
            $w1 = $request->has('w1') ? (float) $request->query('w1') : 0.40;
            $w2 = $request->has('w2') ? (float) $request->query('w2') : 0.30;
            $w3 = $request->has('w3') ? (float) $request->query('w3') : 0.30;
        }

        // Validasi bobot harus positif dan jumlah = 1.0 (toleransi 0.005)
        $totalW = $w1 + $w2 + $w3;
        $bobotValid = $w1 >= 0 && $w2 >= 0 && $w3 >= 0 && abs($totalW - 1.0) <= 0.005;

        if (! $bobotValid) {
            // Fallback ke default 0.40, 0.30, 0.30 jika bobot tidak seimbang
            $w1 = 0.40;
            $w2 = 0.30;
            $w3 = 0.30;
        }

        $search = mb_substr(trim((string) $request->query('search', '')), 0, 100);

        return [
            'bulan' => $bulan,
            'jenis' => $jenis,
            'w1' => $w1,
            'w2' => $w2,
            'w3' => $w3,
            'preset' => $preset,
            'search' => $search,
            'bobotValid' => $bobotValid,
        ];
    }

    private function hitung(array $params): array
    {
        $bobot = [$params['w1'], $params['w2'], $params['w3']];
        $jenisFilter = $params['jenis'] === 'semua' ? null : $params['jenis'];

        $analisis = $this->tpkService->analisis($bobot, $params['bulan'], $jenisFilter);
        $items = $analisis['items'];
        $total = count($items);

        // Beri label prioritas pada tiap item
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

        // Filter pencarian nama jika ada
        $filteredItems = $items;
        if ($params['search'] !== '') {
            $kw = mb_strtolower($params['search']);
            $filteredItems = array_values(array_filter($items, function ($it) use ($kw) {
                return str_contains(mb_strtolower($it['nama']), $kw)
                    || str_contains(mb_strtolower($it['jenis']), $kw)
                    || str_contains(mb_strtolower($it['nama_satuan'] ?? ''), $kw);
            }));
        }

        return [
            'items' => $items,
            'filteredItems' => $filteredItems,
            'stats' => $analisis['stats'],
            'totalItem' => $total,
            'topItem' => $items[0] ?? null,
            'avgNilai' => $total > 0 ? array_sum(array_column($items, 'nilai')) / $total : 0,
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
