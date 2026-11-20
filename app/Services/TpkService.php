<?php

namespace App\Services;

use App\Models\AlatBahan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * SAW (hanya Laboran/Admin). Bobot kriteria berasal dari AhpService (AHP), bukan input manual.
 * C1 Stok (cost), C2 Jumlah peminjaman per bulan (benefit), C3 Harga (cost).
 * Ranking dihitung dinamis dan tidak disimpan.
 */
class TpkService
{
    /**
     * @param  array<int,float|int|string>  $bobot  W1, W2, W3 (jumlah = 1)
     * @param  string  $bulan  format YYYY-MM
     * @param  string|null  $jenis  'alat'|'bahan'|null
     * @return array<int,array{id_katalog:int,nama:string,jenis:string,stok:float,jumlah_peminjaman:int,harga:float,r:array<int,float>,nilai:float,ranking:int,nama_satuan?:string,nama_ruangan?:string}>
     */
    public function hitung(array $bobot, string $bulan, ?string $jenis = null): array
    {
        return $this->analisis($bobot, $bulan, $jenis)['items'];
    }

    /**
     * Hitung SAW lengkap dengan statistik min/max kriteria dan metadata.
     *
     * @param  array<int,float|int|string>  $bobot  W1, W2, W3 (jumlah = 1)
     * @param  string  $bulan  format YYYY-MM
     * @param  string|null  $jenis  'alat'|'bahan'|null
     * @return array{items:array,bobot:array,bulan:string,jenis:?string,stats:array}
     */
    public function analisis(array $bobot, string $bulan, ?string $jenis = null): array
    {
        $bobot = array_values($bobot);
        if (count($bobot) !== 3 || array_filter($bobot, fn ($w) => ! is_numeric($w) || $w < 0)
            || abs(array_sum($bobot) - 1.0) > 0.001) {
            throw ValidationException::withMessages(['bobot' => 'Bobot W1, W2, W3 harus tiga angka tidak negatif dengan jumlah 1.']);
        }
        $bobot = array_map('floatval', $bobot);
        if (! preg_match('/^(\d{4})-(0[1-9]|1[0-2])$/', $bulan, $m)) {
            throw ValidationException::withMessages(['bulan' => 'Bulan tidak valid. Gunakan format YYYY-MM.']);
        }
        $awal = Carbon::create((int) $m[1], (int) $m[2], 1, 0, 0, 0);
        $akhir = $awal->copy()->addMonth();

        // Jumlah peminjaman/bulan = jumlah transaksi terealisasi (disetujui/selesai) yang memuat katalog,
        // menurut tanggal_peminjaman (bukan sekadar jumlah pengajuan).
        $pinjam = DB::table('peminjaman_details as d')
            ->join('peminjamans as p', 'p.id_peminjaman', '=', 'd.id_peminjaman')
            ->whereIn('p.status', ['disetujui', 'selesai'])
            ->where('p.tanggal_peminjaman', '>=', $awal)->where('p.tanggal_peminjaman', '<', $akhir)
            ->groupBy('d.id_katalog')->selectRaw('d.id_katalog as id, COUNT(DISTINCT p.id_peminjaman) as total')
            ->pluck('total', 'id');

        $query = AlatBahan::with(['satuan', 'ruangan'])->orderBy('id_katalog');
        if ($jenis && in_array($jenis, ['alat', 'bahan'], true)) {
            $query->where('jenis', $jenis);
        }

        $items = $query->get(['id_katalog', 'nama', 'jenis', 'id_satuan', 'id_ruangan', 'stok', 'harga', 'per_unit', 'harga_total', 'unit_dasar_harga'])->map(function ($k) use ($pinjam) {
            // Konversi satuan asli -> unit (SRS D.2) hanya untuk penilaian SAW; stok dan harga tampilan tetap apa adanya.
            $perUnit = (float) $k->per_unit;
            $dasar = (float) $k->unit_dasar_harga;
            $stokUnit = $perUnit > 0 ? (float) $k->stok / $perUnit : (float) $k->stok;
            $hargaUnit = ($dasar > 0 && (float) $k->harga_total > 0) ? (float) $k->harga_total / $dasar : (float) $k->harga;

            return [
                'id_katalog' => $k->id_katalog,
                'nama' => $k->nama,
                'jenis' => $k->jenis,
                'nama_satuan' => $k->satuan->nama_satuan ?? '-',
                'nama_ruangan' => $k->ruangan->nama_ruangan ?? '-',
                'stok' => (float) $k->stok,
                'jumlah_peminjaman' => (int) ($pinjam[$k->id_katalog] ?? 0),
                'harga' => (float) $k->harga,
                'stok_skor' => $stokUnit,
                'harga_skor' => $hargaUnit,
            ];
        })->all();

        if ($items === []) {
            return [
                'items' => [],
                'bobot' => $bobot,
                'bulan' => $bulan,
                'jenis' => $jenis,
                'stats' => [
                    'min_stok' => 0.0,
                    'max_pinjam' => 0,
                    'min_harga' => 0.0,
                    'total_item' => 0,
                ],
            ];
        }

        $minStok = min(array_column($items, 'stok_skor'));
        $minHarga = min(array_column($items, 'harga_skor'));
        $maxPinjam = max(array_column($items, 'jumlah_peminjaman'));

        foreach ($items as &$it) {
            // Cost: Min/Xij (Xij sama dengan minimum → 1, sehingga nilai 0 tidak membagi nol). Benefit: Xij/Max (Max = 0 → 0).
            $r1 = $it['stok_skor'] == $minStok ? 1.0 : $minStok / $it['stok_skor'];
            $r2 = $maxPinjam > 0 ? $it['jumlah_peminjaman'] / $maxPinjam : 0.0;
            $r3 = $it['harga_skor'] == $minHarga ? 1.0 : $minHarga / $it['harga_skor'];
            $it['r'] = [$r1, $r2, $r3];
            $it['nilai'] = $bobot[0] * $r1 + $bobot[1] * $r2 + $bobot[2] * $r3;
        }
        unset($it);

        usort($items, fn ($a, $b) => $b['nilai'] <=> $a['nilai'] ?: $a['id_katalog'] <=> $b['id_katalog']);
        foreach ($items as $i => $it) {
            $items[$i]['ranking'] = $i + 1;
        }

        return [
            'items' => $items,
            'bobot' => $bobot,
            'bulan' => $bulan,
            'jenis' => $jenis,
            'stats' => [
                'min_stok' => (float) $minStok,
                'max_pinjam' => (int) $maxPinjam,
                'min_harga' => (float) $minHarga,
                'total_item' => count($items),
            ],
        ];
    }
}
