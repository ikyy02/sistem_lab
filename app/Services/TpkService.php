<?php

namespace App\Services;

use App\Models\AlatBahan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * TPK/SAW (hanya Laboran/Admin). AHP dihitung manual di luar sistem; sistem hanya menerima bobot final.
 * C1 Stok (cost), C2 Jumlah peminjaman per bulan (benefit), C3 Harga (cost).
 * Ranking dihitung dinamis dan tidak disimpan.
 */
class TpkService
{
    /**
     * @param  array<int,float|int|string>  $bobot  W1, W2, W3 (jumlah = 1)
     * @param  string  $bulan  format YYYY-MM
     * @return array<int,array{id_katalog:int,nama:string,jenis:string,stok:float,jumlah_peminjaman:int,harga:float,r:array<int,float>,nilai:float,ranking:int}>
     */
    public function hitung(array $bobot, string $bulan): array
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

        $items = AlatBahan::orderBy('id_katalog')->get(['id_katalog', 'nama', 'jenis', 'stok', 'harga'])->map(fn ($k) => [
            'id_katalog' => $k->id_katalog, 'nama' => $k->nama, 'jenis' => $k->jenis,
            'stok' => (float) $k->stok, 'jumlah_peminjaman' => (int) ($pinjam[$k->id_katalog] ?? 0), 'harga' => (float) $k->harga,
        ])->all();
        if ($items === []) {
            return [];
        }

        $minStok = min(array_column($items, 'stok'));
        $minHarga = min(array_column($items, 'harga'));
        $maxPinjam = max(array_column($items, 'jumlah_peminjaman'));
        foreach ($items as &$it) {
            // Cost: Min/Xij (Xij sama dengan minimum → 1, sehingga nilai 0 tidak membagi nol). Benefit: Xij/Max (Max = 0 → 0).
            $r1 = $it['stok'] == $minStok ? 1.0 : $minStok / $it['stok'];
            $r2 = $maxPinjam > 0 ? $it['jumlah_peminjaman'] / $maxPinjam : 0.0;
            $r3 = $it['harga'] == $minHarga ? 1.0 : $minHarga / $it['harga'];
            $it['r'] = [$r1, $r2, $r3];
            $it['nilai'] = $bobot[0] * $r1 + $bobot[1] * $r2 + $bobot[2] * $r3;
        }
        unset($it);
        usort($items, fn ($a, $b) => $b['nilai'] <=> $a['nilai'] ?: $a['id_katalog'] <=> $b['id_katalog']);
        foreach ($items as $i => $it) {
            $items[$i]['ranking'] = $i + 1;
        }

        return $items;
    }
}
