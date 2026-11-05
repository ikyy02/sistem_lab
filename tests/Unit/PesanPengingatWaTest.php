<?php

namespace Tests\Unit;

use App\Support\PesanPengingatWa;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class PesanPengingatWaTest extends TestCase
{
    public function test_terlambat_dua_jam(): void
    {
        $batas = $this->batas();

        $this->assertSame('2 jam', PesanPengingatWa::formatTerlambat($batas, $batas->addHours(2)));
    }

    public function test_terlambat_satu_hari_tiga_jam(): void
    {
        $batas = $this->batas();

        $this->assertSame('1 hari 3 jam', PesanPengingatWa::formatTerlambat($batas, $batas->addHours(27)));
    }

    public function test_barang_lebih_dari_tiga_diringkas_menjadi_tiga_plus_sisanya(): void
    {
        $ringkasan = PesanPengingatWa::ringkasanBarang([
            ['nama' => 'Mikroskop', 'jumlah' => 1],
            ['nama' => 'Kabel UTP', 'jumlah' => 2],
            ['nama' => 'Router', 'jumlah' => 1],
            ['nama' => 'Switch', 'jumlah' => 3],
            ['nama' => 'Proyektor', 'jumlah' => 1],
        ]);

        $this->assertSame('Mikroskop × 1, Kabel UTP × 2, Router × 1, +2 barang lainnya', $ringkasan);
    }

    public function test_pengingat_ketiga_menyebut_terakhir_dan_hubungi_laboran(): void
    {
        $pesan = PesanPengingatWa::buat(
            'Budi',
            [['nama' => 'Mikroskop', 'jumlah' => 1]],
            $this->batas(),
            3,
            $this->batas()->addDay(),
            'Asia/Makassar',
        );

        $this->assertStringContainsString('terakhir', $pesan);
        $this->assertStringContainsString('hubungi laboran', $pesan);
    }

    public function test_batas_kembali_dikonversi_ke_zona_aplikasi(): void
    {
        $batas = CarbonImmutable::parse('2026-10-18 09:23:00', 'UTC');

        $this->assertSame(
            'Minggu, 18 Oktober 2026 pukul 17.23 WITA',
            PesanPengingatWa::formatBatas($batas, 'Asia/Makassar'),
        );
    }

    public function test_di_bawah_satu_menit_ditulis_baru_saja(): void
    {
        $batas = $this->batas();

        $this->assertSame('baru saja', PesanPengingatWa::formatTerlambat($batas, $batas->addSeconds(45)));
    }

    private function batas(): CarbonImmutable
    {
        return CarbonImmutable::parse('2026-10-18 10:00:00', 'Asia/Makassar');
    }
}
