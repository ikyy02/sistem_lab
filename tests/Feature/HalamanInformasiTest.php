<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\Sop;
use Database\Seeders\FaqSeeder;
use Database\Seeders\SopSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

/** Halaman informasi (SOP, Bantuan, Jadwal Praktikum) harus bisa dibuka semua role. */
class HalamanInformasiTest extends TestCase
{
    use MembuatData, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SopSeeder::class);
        $this->seed(FaqSeeder::class);
    }

    /** Login via session sesuai mekanisme AuthService SIARKA. */
    private function sebagaiMahasiswa(): self
    {
        $m = $this->mahasiswa();

        return $this->withSession(['silab_user' => [
            'role' => 'mahasiswa', 'key' => (string) $m->getKey(), 'nama' => $m->nama, 'email' => $m->email,
        ]]);
    }

    public function test_halaman_sop_bisa_dibuka_dan_judul_bersih(): void
    {
        $res = $this->sebagaiMahasiswa()->get(route('sop.index'));

        $res->assertOk();
        // HTML sah menyimpan '&' sebagai '&amp;'; yang dilarang adalah escape ganda.
        $res->assertSee('SOP & Panduan SIARKA');
        $res->assertDontSee('&amp;amp;', false);
        $res->assertSee('Tata Tertib Ruang Laboratorium', false);
        $this->assertSame(4, Sop::count());
    }

    public function test_halaman_bantuan_bisa_dibuka(): void
    {
        $res = $this->sebagaiMahasiswa()->get(route('bantuan.index'));

        $res->assertOk();
        $res->assertSee('FQA SIARKA', false);
        $res->assertSee('Kontak Layanan Laboratorium', false);
        $this->assertGreaterThan(0, Faq::where('aktif', true)->count());
    }

    public function test_halaman_jadwal_praktikum_bisa_dibuka(): void
    {
        $res = $this->sebagaiMahasiswa()->get(route('jadwal-praktikum.index'));

        $res->assertOk();
        $res->assertSee('Jadwal Praktikum', false);
    }

    public function test_dosen_bisa_buka_jadwal_praktikum_dan_sop_lewat_sidebar(): void
    {
        $d = $this->dosen();
        $sess = ['silab_user' => [
            'role' => 'dosen', 'key' => (string) $d->getKey(), 'nama' => $d->nama, 'email' => $d->email,
        ]];

        $this->withSession($sess)->get(route('jadwal-praktikum.index'))->assertOk();
        $this->withSession($sess)->get(route('sop.index'))->assertOk();

        // Cek bahwa sidebar dosen memiliki link ke jadwal-praktikum dan sop yang benar, bukan jadwal staf
        $dash = $this->withSession($sess)->get(route('dashboard'))->assertOk();
        $dash->assertSee('href="' . route('jadwal-praktikum.index') . '"', false);
        $dash->assertSee('href="' . route('sop.index') . '"', false);
        $dash->assertDontSee('href="' . route('jadwal.index') . '"', false);
    }
}
