<?php

namespace App\Http\Controllers;

use App\Services\AhpService;
use App\Services\AuthService;
use App\Services\TpkService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * SPK Pengadaan (AHP -> SAW) untuk prioritas pengadaan alat dan bahan laboratorium.
 * AHP menghasilkan bobot kriteria; SAW memakai bobot itu untuk meranking. Khusus role Laboran/Admin.
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
            'kriteria' => AhpService::KRITERIA,
            'daftarBulan' => $this->daftarBulanPilihan(),
        ]));
    }

    public function cetak(Request $request)
    {
        $params = $this->resolveParams($request);
        $hasil = $this->hitung($params);
        if ($tolak = $this->tolakBilaTidakKonsisten($hasil, $params)) {
            return $tolak;
        }
        $user = AuthService::user();

        return view('pengadaan-tpk.cetak', array_merge($params, $hasil, [
            'user' => $user,
            'tanggalCetak' => Carbon::now()->translatedFormat('d F Y'),
        ]));
    }

    public function exportCsv(Request $request): StreamedResponse|RedirectResponse
    {
        $params = $this->resolveParams($request);
        $hasil = $this->hitung($params);
        if ($tolak = $this->tolakBilaTidakKonsisten($hasil, $params)) {
            return $tolak;
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
            fputcsv($handle, ['Sistem Informasi SIARKA - SPK AHP (bobot kriteria) dan SAW (peringkat)']);
            fputcsv($handle, ['Bulan Periode', $params['bulan']]);
            fputcsv($handle, ['Filter Kategori', ucfirst($params['jenis'])]);
            $ahp = $hasil['ahp'];
            fputcsv($handle, ['Perbandingan AHP C1:C2 / C1:C3 / C2:C3',
                AhpService::formatNilai($ahp['pair']['a12']).' / '.AhpService::formatNilai($ahp['pair']['a13']).' / '.AhpService::formatNilai($ahp['pair']['a23'])]);
            fputcsv($handle, ['Bobot AHP W1 (Stok)', round($ahp['bobot'][0], 6)]);
            fputcsv($handle, ['Bobot AHP W2 (Peminjaman)', round($ahp['bobot'][1], 6)]);
            fputcsv($handle, ['Bobot AHP W3 (Harga)', round($ahp['bobot'][2], 6)]);
            fputcsv($handle, ['Lambda max', round($ahp['lambda_max'], 6)]);
            fputcsv($handle, ['CI', round($ahp['ci'], 6)]);
            fputcsv($handle, ['CR (RI 0,58)', round($ahp['cr'], 6)]);
            fputcsv($handle, []);

            // Header kolom
            fputcsv($handle, [
                'Peringkat',
                'ID Katalog',
                'Nama Barang',
                'Jenis',
                'Satuan',
                'Ruangan',
                'Stok Saat Ini (C1, satuan standar)',
                'Jumlah Peminjaman/Bulan (C2)',
                'Harga per Unit (Rp) (C3)',
                'Normalisasi R1 (Stok)',
                'Normalisasi R2 (Peminjaman)',
                'Normalisasi R3 (Harga)',
                'Nilai Preferensi (V)',
                'Status Prioritas',
            ]);

            // Cegah CSV injection: sel teks berawalan = + - @ diberi awalan '.
            $aman = fn ($v) => is_string($v) && preg_match('/^[=+\-@]/', $v) ? "'".$v : $v;

            foreach ($hasil['items'] as $it) {
                fputcsv($handle, [
                    $it['ranking'],
                    $it['id_katalog'],
                    $aman($it['nama']),
                    ucfirst($it['jenis']),
                    $aman($it['nama_satuan']) ?? '-',
                    $aman($it['nama_ruangan']) ?? '-',
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

        // Perbandingan berpasangan AHP (skala Saaty). Nilai awal 1 = semua kriteria setara (netral):
        // bukan bobot permanen, Laboran mengisi sendiri penilaiannya.
        $pair = [];
        $pairValid = true;
        foreach (array_keys(AhpService::PASANGAN) as $k) {
            $teks = (string) $request->query($k, '1');
            if (AhpService::parseNilai($teks) === null) {
                $teks = '1';
                $pairValid = false;
            }
            $pair[$k] = $teks;
        }

        $search = mb_substr(trim((string) $request->query('search', '')), 0, 100);

        return [
            'bulan' => $bulan,
            'jenis' => $jenis,
            'pair' => $pair,
            'pairValid' => $pairValid,
            'search' => $search,
        ];
    }

    private function hitung(array $params): array
    {
        $ahp = $this->ahpService->hitung($params['pair']);
        $jenisFilter = $params['jenis'] === 'semua' ? null : $params['jenis'];

        // Ranking resmi hanya bila AHP konsisten (CR <= 0,10); bobot SAW selalu dari AHP.
        if (! $ahp['konsisten']) {
            return [
                'ahp' => $ahp, 'resmi' => false, 'items' => [], 'filteredItems' => [],
                'stats' => ['min_stok' => 0.0, 'max_pinjam' => 0, 'min_harga' => 0.0, 'total_item' => 0],
                'totalItem' => 0, 'topItem' => null, 'avgNilai' => 0,
            ];
        }

        $analisis = $this->tpkService->analisis($ahp['bobot'], $params['bulan'], $jenisFilter);
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
            'ahp' => $ahp,
            'resmi' => true,
            'items' => $items,
            'filteredItems' => $filteredItems,
            'stats' => $analisis['stats'],
            'totalItem' => $total,
            'topItem' => $items[0] ?? null,
            'avgNilai' => $total > 0 ? array_sum(array_column($items, 'nilai')) / $total : 0,
        ];
    }

    private function tolakBilaTidakKonsisten(array $hasil, array $params): ?RedirectResponse
    {
        if ($hasil['resmi']) {
            return null;
        }

        return redirect()->route('pengadaan-tpk.index', request()->query())
            ->with('error', 'Ranking resmi belum tersedia: perbandingan AHP belum konsisten (CR > 0,10). Sesuaikan perbandingan lebih dulu.');
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
