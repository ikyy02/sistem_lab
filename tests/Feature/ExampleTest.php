<?php

namespace Tests\Feature;

use App\Models\Ruangan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Landing page (/) dan halaman login (/login) adalah dua halaman terpisah; data fasilitas berasal dari database. */
class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_berdiri_sendiri_tanpa_formulir_login_dan_tanpa_logo(): void
    {
        $html = $this->get('/')->assertOk()
            ->assertSee('SIARKA')
            ->assertSee('Masuk SIARKA')
            ->assertSee('href="'.route('login').'"', false)
            ->assertDontSee('name="password"', false)
            ->assertDontSee('name="email"', false)
            ->assertDontSee('bi-buildings', false)
            ->assertDontSee('brand-icon', false)
            ->getContent();
        $this->assertStringNotContainsString('labkom.politala.ac.id', $html);
    }

    public function test_fasilitas_landing_berasal_dari_database(): void
    {
        $this->get('/')->assertOk()->assertSee('Belum ada ruangan laboratorium yang terdaftar.')->assertDontSee('Lihat Semua');

        foreach (['Lab Uji 1', 'Lab Uji 2', 'Lab Uji 3', 'Lab Uji 4', 'Lab Uji 5'] as $i => $nama) {
            Ruangan::create(['nama_ruangan' => $nama, 'gambar' => $i === 0 ? 'uji.jpg' : null]);
        }

        $this->get('/')->assertOk()
            ->assertSee('Lab Uji 1')->assertSee('Lab Uji 4')->assertSee('Lab Uji 5')
            ->assertSee(asset('storage/'.Ruangan::GAMBAR_DIR.'/uji.jpg'), false)
            ->assertSee('Lihat Semua')
            ->assertSee('id="semua-fasilitas"', false);
    }

    public function test_halaman_login_hanya_berisi_formulir_dan_tautan_kembali(): void
    {
        $this->get(route('login'))->assertOk()
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false)
            ->assertSee('name="_token"', false)
            ->assertSee('href="'.route('landing').'"', false)
            ->assertDontSee('brand-icon', false)
            ->assertDontSee('Fasilitas dan Laboratorium')
            ->assertDontSee('Alur Peminjaman');
    }

    public function test_pengguna_yang_sudah_login_dialihkan_dari_landing_dan_login(): void
    {
        $this->withSession(['silab_user' => ['role' => 'mahasiswa', 'key' => '1', 'nama' => 'Uji', 'email' => 'uji@uji.test']]);
        $this->get('/')->assertRedirect(route('dashboard'));
        $this->get(route('login'))->assertRedirect(route('dashboard'));
    }
}
