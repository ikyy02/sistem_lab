<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

/** Notifikasi: satu sistem modal; flash sekali tampil; konfirmasi lewat data-konfirmasi; tanpa alert/toast/confirm() lama. */
class NotifikasiTest extends TestCase
{
    use MembuatData, RefreshDatabase;

    private function admin(): self
    {
        $l = $this->laboran();

        return $this->withSession(['silab_user' => ['role' => 'laboran', 'key' => (string) $l->getKey(), 'nama' => $l->nama, 'email' => $l->email]]);
    }

    public function test_sukses_menjadi_popup_sekali_saja(): void
    {
        $a = $this->admin();
        $a->post(route('pengaturan-lab.satuan.store'), ['nama' => 'liter'])->assertSessionHas('success');

        $a->get(route('pengaturan-lab.index'))->assertOk()
            ->assertSee('id="notifModal"', false)
            ->assertSee('SIARKA_FLASH = [', false)
            ->assertSee('"tipe":"success"', false)
            ->assertDontSee('class="alert alert-success', false);
        // refresh/navigasi berikutnya: tidak ada pesan, tidak ada popup
        $a->get(route('pengaturan-lab.index'))->assertDontSee('SIARKA_FLASH = [', false);
    }

    public function test_gagal_ditampilkan_sebagai_error_bukan_sukses(): void
    {
        $a = $this->admin();
        $satuan = \App\Models\Satuan::create(['nama_satuan' => 'dipakai']);
        $this->katalog('alat', 1, ['id_satuan' => $satuan->id_satuan]);

        $a->delete(route('pengaturan-lab.satuan.destroy', ['id' => $satuan->id_satuan]))
            ->assertSessionHas('error')->assertSessionMissing('success');
        $a->get(route('pengaturan-lab.index'))->assertOk()
            ->assertSee('"tipe":"error"', false)
            ->assertDontSee('"tipe":"success"', false);
        $this->assertDatabaseHas('satuans', ['id_satuan' => $satuan->id_satuan]);
    }

    public function test_halaman_tanpa_pesan_tidak_memicu_popup(): void
    {
        $this->admin()->get(route('dashboard'))->assertOk()->assertDontSee('SIARKA_FLASH = [', false)->assertDontSee('alert-dismissible', false);
    }

    public function test_penghapusan_meminta_konfirmasi_lewat_atribut_dan_tanpa_confirm_bawaan(): void
    {
        $this->satuan('meter');
        $this->admin()->get(route('pengaturan-lab.index'))->assertOk()
            ->assertSee('data-konfirmasi="Hapus', false)
            ->assertDontSee('confirm(', false);
    }

    public function test_tidak_ada_confirm_atau_alert_notifikasi_lama_di_view(): void
    {
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('views'), \FilesystemIterator::SKIP_DOTS));
        foreach ($it as $f) {
            if (! str_ends_with($f->getFilename(), '.blade.php')) {
                continue;
            }
            $isi = file_get_contents($f->getPathname());
            $this->assertStringNotContainsString('return confirm(', $isi, $f->getFilename());
            $this->assertStringNotContainsString("session('success')", $f->getFilename() === '_notifikasi.blade.php' ? '' : $isi, $f->getFilename());
            $this->assertStringNotContainsString('alert-dismissible', $isi, $f->getFilename());
        }
    }

    public function test_hapus_tanpa_konfirmasi_ui_tetap_ditegakkan_backend_dan_penolakan_tidak_menghapus(): void
    {
        // Konfirmasi hanya lapisan UI; backend tetap menolak hak akses dan data terpakai.
        $s = $this->satuan('terpakai');
        $this->katalog('alat', 1, ['id_satuan' => $s->id_satuan]);
        $this->admin()->delete(route('pengaturan-lab.satuan.destroy', ['id' => $s->id_satuan]))->assertSessionHas('error');
        $this->assertDatabaseHas('satuans', ['id_satuan' => $s->id_satuan]);
    }
}
