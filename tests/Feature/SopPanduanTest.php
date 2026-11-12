<?php

namespace Tests\Feature;

use App\Models\Sop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

/** Halaman SOP & Panduan: judul tidak ter-escape ganda, data tampil, modal & cetak tersedia. */
class SopPanduanTest extends TestCase
{
    use MembuatData, RefreshDatabase;

    private function sebagaiMahasiswa()
    {
        $p = $this->mahasiswa();

        return $this->withSession(['silab_user' => [
            'role' => 'mahasiswa', 'key' => (string) $p->getKey(),
            'nama' => $p->nama, 'email' => $p->email,
        ]]);
    }

    public function test_judul_halaman_tampil_sebagai_ampersand_tunggal(): void
    {
        $this->seed(\Database\Seeders\SopSeeder::class);

        $res = $this->sebagaiMahasiswa()->get(route('sop.index'));
        $res->assertOk();

        $html = $res->getContent();

        // Tidak boleh ada entitas yang ter-escape ganda.
        $this->assertStringNotContainsString('&amp;amp;', $html);
        $this->assertStringNotContainsString('amp;amp', $html);

        // Topbar + <title> memuat teks yang benar.
        $this->assertMatchesRegularExpression('/<title>\s*SOP &amp; Panduan/u', $html);
        $this->assertStringContainsString('<h1 class="font-display">SOP &amp; Panduan</h1>', $html);
    }

    public function test_empat_sop_dan_panduan_tampil_dari_database(): void
    {
        $this->seed(\Database\Seeders\SopSeeder::class);

        $res = $this->sebagaiMahasiswa()->get(route('sop.index'));
        $res->assertOk();

        foreach (['SOP-01', 'SOP-02', 'SOP-03', 'SOP-04'] as $kode) {
            $res->assertSee($kode, false);
        }
        $res->assertSee('Tata Tertib Ruang Laboratorium', false);
        $res->assertSee('Peminjaman Ruang &amp; Alat', false);
        $res->assertSee('Keselamatan &amp; Kesehatan Kerja', false);
        $res->assertSee('Pengembalian &amp; Pelaporan', false);
        $res->assertSee('Panduan Peminjaman (Ringkasan)', false);
        $res->assertSee('berstatus', false);

        // Lima langkah panduan.
        $res->assertSee('Ajukan.', false);
        $res->assertSee('Tunggu verifikasi.', false);
        $res->assertSee('Ambil kunci.', false);
        $res->assertSee('Gunakan.', false);
        $res->assertSee('Kembalikan.', false);
    }

    public function test_modal_dan_versi_cetak_tersedia(): void
    {
        $this->seed(\Database\Seeders\SopSeeder::class);

        $res = $this->sebagaiMahasiswa()->get(route('sop.index'));
        $res->assertOk();

        $res->assertSee('<dialog id="sopDialog"', false);
        $res->assertSee('sop-print-only', false);
        $res->assertSee('@media print', false);
        $res->assertSee('window.print()', false);
    }

    public function test_poin_sop_tersimpan_sebagai_array(): void
    {
        $this->seed(\Database\Seeders\SopSeeder::class);

        $sop = Sop::where('kode', 'SOP-02')->firstOrFail();
        $this->assertIsArray($sop->poin);
        $this->assertNotEmpty($sop->poin);
        $this->assertStringContainsString('satu peminjaman aktif per hari', mb_strtolower(implode(' ', $sop->poin)));
    }

    public function test_seeder_idempoten(): void
    {
        $this->seed(\Database\Seeders\SopSeeder::class);
        $this->seed(\Database\Seeders\SopSeeder::class);

        $this->assertSame(4, Sop::count());
    }
}
