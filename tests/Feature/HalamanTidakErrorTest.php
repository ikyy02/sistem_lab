<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

/** Semua halaman GET tanpa parameter harus terbuka (bukan 500) untuk tiap role: menangkap view/kode yang rusak. */
class HalamanTidakErrorTest extends TestCase
{
    use MembuatData, RefreshDatabase;

    public function test_semua_halaman_get_tidak_error_untuk_setiap_role(): void
    {
        $this->prodi();
        $profil = ['mahasiswa' => $this->mahasiswa(), 'dosen' => $this->dosen(), 'staff_prodi' => $this->staff(), 'laboran' => $this->laboran()];
        $urls = collect(Route::getRoutes()->getRoutesByMethod()['GET'])
            ->filter(fn ($r) => ! str_contains($r->uri(), '{') && ! in_array($r->uri(), ['up', 'storage/{path}'], true))
            ->map(fn ($r) => '/'.ltrim($r->uri(), '/'))->unique();

        foreach ($profil as $role => $p) {
            foreach ($urls as $url) {
                $res = $this->withSession(['silab_user' => ['role' => $role, 'key' => (string) $p->getKey(), 'nama' => $p->nama, 'email' => $p->email]])->get($url);
                $this->assertLessThan(500, $res->getStatusCode(), "{$role} GET {$url} -> {$res->getStatusCode()}");
                if ($res->getStatusCode() === 200 && str_contains((string) $res->headers->get('Content-Type'), 'text/html')) {
                    $this->assertKolomDiDalamRow($res->getContent(), "{$role} GET {$url}");
                }
            }
        }
    }

    public function test_login_dibatasi_per_ip_walau_email_berganti(): void
    {
        for ($i = 0; $i < 31; $i++) {
            $res = $this->post('/login', ['email' => "x{$i}@uji.test", 'password' => 'salah']);
        }
        $res->assertSessionHasErrors('email');
        $this->assertStringContainsString('Terlalu banyak', session('errors')->first('email'));
    }

    public function test_sidebar_satu_daftar_tanpa_label_kelompok_dan_tanpa_menu_ganda(): void
    {
        $html = $this->withSession(['silab_user' => ['role' => 'laboran', 'key' => (string) ($l = $this->laboran())->getKey(), 'nama' => $l->nama, 'email' => $l->email]])
            ->get(route('dashboard'))->assertOk()
            ->assertSee('Pengaturan Laboratorium')
            ->assertDontSee('Kelola Data Master')->assertDontSee('Pengaturan Operasional')
            ->assertDontSee('sidebar-section-label', false)->getContent();
        foreach (['Layanan Peminjaman', 'Katalog &amp; Stok', '>Administrasi<'] as $grup) {
            $this->assertStringNotContainsString($grup, $html);
        }
        $this->assertSame(1, substr_count($html, 'Pengaturan Laboratorium</span>'));
    }

    /** Setiap div .col* harus anak langsung div .row (kartu yang lepas dari grid melebar sendiri). */
    private function assertKolomDiDalamRow(string $html, string $konteks): void
    {
        $token = fn (\DOMElement $e) => preg_split('/\s+/', trim($e->getAttribute('class')), -1, PREG_SPLIT_NO_EMPTY);
        $dom = new \DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8"?>'.$html);
        libxml_clear_errors();
        foreach ($dom->getElementsByTagName('div') as $div) {
            if (! array_filter($token($div), fn ($c) => $c === 'col' || str_starts_with($c, 'col-'))) {
                continue;
            }
            $induk = $div->parentNode;
            $this->assertTrue(
                $induk instanceof \DOMElement && in_array('row', $token($induk), true),
                "{$konteks}: div.col di luar .row (class \"{$div->getAttribute('class')}\")"
            );
        }
    }

    public function test_katalog_tanpa_judul_ganda_dan_tanpa_tombol_kelola_katalog_ganda(): void
    {
        $l = $this->laboran();
        $res = $this->withSession(['silab_user' => ['role' => 'laboran', 'key' => (string) $l->getKey(), 'nama' => $l->nama, 'email' => $l->email]])
            ->get(route('katalog'))->assertOk();
        $this->assertSame(1, substr_count($res->getContent(), 'href="'.route('kelola-katalog.index').'"'), 'Kelola Katalog hanya di sidebar');
        $res->assertDontSee('font-size:1.5rem', false); // h2 judul halaman di konten (sudah ada di header)
    }
}
