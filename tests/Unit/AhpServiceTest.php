<?php

namespace Tests\Unit;

use App\Services\AhpService;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Acuan: Data_Input_TPK_PBL_Kelompok_6 (sheet PERHITUNGAN AHP) dan hitungan independen numpy.
 */
class AhpServiceTest extends TestCase
{
    public function test_bobot_lambda_ci_cr_sesuai_acuan(): void
    {
        $r = (new AhpService)->hitung(['a12' => '3', 'a13' => '5', 'a23' => '2']);

        $this->assertEqualsWithDelta([0.647947, 0.229871, 0.122182], $r['bobot'], 1e-6);
        $this->assertEqualsWithDelta(1.0, array_sum($r['bobot']), 1e-12);
        $this->assertEqualsWithDelta([1.533333, 4.5, 8.0], $r['jumlah_kolom'], 1e-6);
        $this->assertEqualsWithDelta(3.003697, $r['lambda_max'], 1e-6);
        $this->assertEqualsWithDelta(0.001848, $r['ci'], 1e-6);
        $this->assertEqualsWithDelta(0.003187, $r['cr'], 1e-6);
        $this->assertSame(0.58, $r['ri']);
        $this->assertTrue($r['konsisten']);
    }

    public function test_sesuai_matriks_excel_pbl_kelompok_6(): void
    {
        // Excel: Stok:Pinjam = 3, Stok:Harga = 2, Pinjam:Harga = 1/3
        $r = (new AhpService)->hitung(['a12' => '3', 'a13' => '2', 'a23' => '1/3']);

        $this->assertEqualsWithDelta([0.5246753247, 0.1415584416, 0.3337662338], $r['bobot'], 1e-9);
        $this->assertEqualsWithDelta(3.053818552, $r['lambda_max'], 1e-8);
        $this->assertEqualsWithDelta(0.02690927582, $r['ci'], 1e-9);
        $this->assertEqualsWithDelta(0.04639530313, $r['cr'], 1e-9);
        $this->assertTrue($r['konsisten']);
    }

    public function test_matriks_resiprokal_dan_diagonal_satu(): void
    {
        $a = (new AhpService)->hitung(['a12' => '1/3', 'a13' => '5', 'a23' => '1/2'])['matriks'];

        foreach ([0, 1, 2] as $i) {
            $this->assertSame(1.0, $a[$i][$i]);
            foreach ([0, 1, 2] as $j) {
                $this->assertEqualsWithDelta(1.0, $a[$i][$j] * $a[$j][$i], 1e-12);
            }
        }
        $this->assertEqualsWithDelta(1 / 3, $a[0][1], 1e-12);
    }

    public function test_semua_sama_penting_konsisten_dengan_bobot_sepertiga(): void
    {
        $r = (new AhpService)->hitung(['a12' => '1', 'a13' => '1', 'a23' => '1']);

        $this->assertEqualsWithDelta([1 / 3, 1 / 3, 1 / 3], $r['bobot'], 1e-12);
        $this->assertEqualsWithDelta(0.0, $r['cr'], 1e-12);
        $this->assertTrue($r['konsisten']);
    }

    public function test_perbandingan_tidak_selaras_ditandai_tidak_konsisten(): void
    {
        $r = (new AhpService)->hitung(['a12' => '9', 'a13' => '1/9', 'a23' => '9']);

        $this->assertEqualsWithDelta(6.130268, $r['cr'], 1e-5);
        $this->assertFalse($r['konsisten']);
    }

    public function test_batas_cr_nilai_di_atas_dan_di_bawah_batas(): void
    {
        $svc = new AhpService;
        // a12=3, a13=5, a23=3 -> CR 0,0334 (konsisten)
        $this->assertTrue($svc->hitung(['a12' => '3', 'a13' => '5', 'a23' => '3'])['konsisten']);
        // a12=9, a13=1, a23=9 -> jelas melewati 0,10
        $this->assertFalse($svc->hitung(['a12' => '9', 'a13' => '1', 'a23' => '9'])['konsisten']);
    }

    public function test_nilai_di_luar_skala_saaty_ditolak(): void
    {
        $this->expectException(ValidationException::class);
        (new AhpService)->hitung(['a12' => '10', 'a13' => '1', 'a23' => '1']);
    }

    public function test_format_dan_parse_nilai(): void
    {
        $this->assertSame('1/4', AhpService::formatNilai(0.25));
        $this->assertSame('7', AhpService::formatNilai(7.0));
        $this->assertNull(AhpService::parseNilai('0'));
        $this->assertNull(AhpService::parseNilai('1/10'));
        $this->assertSame(1 / 3, AhpService::parseNilai(' 1/3 '));
    }
}
