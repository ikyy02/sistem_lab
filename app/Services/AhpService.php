<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

/**
 * AHP untuk 3 kriteria SPK pengadaan: C1 Stok (cost), C2 Peminjaman/bulan (benefit), C3 Harga/unit (cost).
 * Input: tiga perbandingan berpasangan segitiga atas (a12, a13, a23) pada skala Saaty 1/9..9.
 * Output: matriks, normalisasi, bobot prioritas, λmax, CI, CR (RI n=3 = 0,58). Murni hitungan, tanpa DB.
 */
class AhpService
{
    public const RI = 0.58;

    public const BATAS_CR = 0.10;

    public const PASANGAN = ['a12' => [0, 1], 'a13' => [0, 2], 'a23' => [1, 2]];

    public const KRITERIA = [
        ['kode' => 'C1', 'nama' => 'Stok', 'tipe' => 'Cost'],
        ['kode' => 'C2', 'nama' => 'Peminjaman per bulan', 'tipe' => 'Benefit'],
        ['kode' => 'C3', 'nama' => 'Harga per unit', 'tipe' => 'Cost'],
    ];

    /** Nilai skala Saaty yang diizinkan, urut dari "kiri jauh lebih penting" ke "kanan jauh lebih penting". */
    public static function skala(): array
    {
        $opsi = [];
        for ($n = 9; $n >= 2; $n--) {
            $opsi["1/$n"] = 1 / $n;
        }
        for ($n = 1; $n <= 9; $n++) {
            $opsi[(string) $n] = (float) $n;
        }

        return $opsi;
    }

    /** "3" atau "1/3" -> float; null bila di luar skala Saaty. */
    public static function parseNilai(mixed $teks): ?float
    {
        $teks = trim((string) $teks);

        return self::skala()[$teks] ?? null;
    }

    public static function formatNilai(float $v): string
    {
        return $v >= 1 ? (string) (int) round($v) : '1/'.(int) round(1 / $v);
    }

    /**
     * @param  array{a12:mixed,a13:mixed,a23:mixed}  $pair  teks/angka skala Saaty
     * @return array{pair:array,matriks:array,jumlah_kolom:array,normal:array,bobot:array,aw:array,lambda_max:float,ci:float,cr:float,ri:float,konsisten:bool}
     */
    public function hitung(array $pair): array
    {
        $nilai = [];
        foreach (array_keys(self::PASANGAN) as $k) {
            $v = is_numeric($pair[$k] ?? null) ? $this->cocokkanSkala((float) $pair[$k]) : self::parseNilai($pair[$k] ?? '');
            if ($v === null) {
                throw ValidationException::withMessages([$k => 'Nilai perbandingan harus pada skala Saaty 1/9 sampai 9.']);
            }
            $nilai[$k] = $v;
        }

        $n = 3;
        $a = array_fill(0, $n, array_fill(0, $n, 1.0));
        foreach (self::PASANGAN as $k => [$i, $j]) {
            $a[$i][$j] = $nilai[$k];
            $a[$j][$i] = 1 / $nilai[$k];
        }

        $jumlahKolom = [];
        for ($j = 0; $j < $n; $j++) {
            $jumlahKolom[$j] = array_sum(array_column($a, $j));
        }
        $normal = [];
        $bobot = [];
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $normal[$i][$j] = $a[$i][$j] / $jumlahKolom[$j];
            }
            $bobot[$i] = array_sum($normal[$i]) / $n;
        }

        // λmax = rata-rata dari (A·w)_i / w_i
        $aw = [];
        $lambda = 0.0;
        for ($i = 0; $i < $n; $i++) {
            $aw[$i] = 0.0;
            for ($j = 0; $j < $n; $j++) {
                $aw[$i] += $a[$i][$j] * $bobot[$j];
            }
            $lambda += $aw[$i] / $bobot[$i];
        }
        $lambda /= $n;
        $ci = ($lambda - $n) / ($n - 1);
        $cr = max(0.0, $ci) / self::RI;

        return [
            'pair' => $nilai,
            'matriks' => $a,
            'jumlah_kolom' => $jumlahKolom,
            'normal' => $normal,
            'bobot' => $bobot,
            'aw' => $aw,
            'lambda_max' => $lambda,
            'ci' => $ci,
            'cr' => $cr,
            'ri' => self::RI,
            // Toleransi 1e-9 agar CR persis 0,10 tidak ditolak oleh galat floating point.
            'konsisten' => $cr <= self::BATAS_CR + 1e-9,
        ];
    }

    private function cocokkanSkala(float $v): ?float
    {
        foreach (self::skala() as $s) {
            if (abs($s - $v) < 1e-9) {
                return $s;
            }
        }

        return null;
    }
}
