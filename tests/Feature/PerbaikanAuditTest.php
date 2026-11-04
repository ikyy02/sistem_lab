<?php

namespace Tests\Feature;

use App\Models\Akun;
use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\MataKuliah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

/** Perbaikan audit: dosen nonaktif, password awal, batas prodi Staf Prodi, jejak stok manual. */
class PerbaikanAuditTest extends TestCase
{
    use MembuatData, RefreshDatabase;

    private function sebagai(string $role, $profil): self
    {
        return $this->withSession(['silab_user' => ['role' => $role, 'key' => (string) $profil->getKey(), 'nama' => $profil->nama, 'email' => $profil->email]]);
    }

    // ---------------------------------------------------------------- B1 dosen nonaktif

    public function test_dosen_nonaktif_tidak_bisa_login(): void
    {
        $nonaktif = $this->dosen(['status' => 'nonaktif']);
        $aktif = $this->dosen();

        $this->post('/login', ['email' => $nonaktif->email, 'password' => 'rahasia'])
            ->assertSessionHasErrors(['email' => 'Email atau password salah.'])->assertSessionMissing('silab_user');
        $this->post('/login', ['email' => $aktif->email, 'password' => 'rahasia'])->assertSessionHas('silab_user.role', 'dosen');
    }

    public function test_dosen_yang_dinonaktifkan_saat_login_dipaksa_keluar(): void
    {
        $d = $this->dosen();
        $sesi = $this->sebagai('dosen', $d);
        $sesi->get(route('dashboard'))->assertOk();

        $d->update(['status' => 'nonaktif']);
        $sesi->get(route('dashboard'))->assertRedirect(route('login'));
        $sesi->get(route('pengajuan.index'))->assertRedirect(route('login'));
    }

    // ---------------------------------------------------------------- B2 password awal

    public function test_akun_wajib_ganti_password_dialihkan_ke_profil_sampai_diganti(): void
    {
        $m = $this->mahasiswa();
        Akun::find($m->email)->update(['wajib_ganti_password' => true]);
        $sesi = $this->sebagai('mahasiswa', $m);

        $sesi->get(route('dashboard'))->assertRedirect(route('profil.index'));
        $sesi->get(route('peminjaman.index'))->assertRedirect(route('profil.index'));
        $sesi->get(route('profil.index'))->assertOk();

        // password baru minimal 8 karakter dan tidak boleh sama dengan yang lama
        $sesi->put(route('profil.password'), ['password_lama' => 'rahasia', 'password_baru' => 'pendek', 'password_baru_confirmation' => 'pendek'])
            ->assertSessionHasErrors('password_baru');
        $this->assertTrue(Akun::find($m->email)->wajib_ganti_password);

        $sesi->put(route('profil.password'), ['password_lama' => 'rahasia', 'password_baru' => 'passbaru123', 'password_baru_confirmation' => 'passbaru123'])
            ->assertSessionHasNoErrors();
        $this->assertFalse(Akun::find($m->email)->wajib_ganti_password);
        $sesi->get(route('dashboard'))->assertOk();
    }

    // ---------------------------------------------------------------- B3 batas prodi Staf Prodi

    /** @return array{0:\App\Models\StaffProdi,1:Kelas,2:Kelas,3:Jadwal,4:Jadwal} staf prodi A, kelas A, kelas B, jadwal A, jadwal B */
    private function dunia(): array
    {
        $staf = $this->staff(); // prodi "Prodi Uji" (A)
        $kelasA = Kelas::create(['nama_kelas' => 'KLS-PRODI-A', 'id_prodi' => $this->prodi()->id_prodi]);
        $kelasB = Kelas::create(['nama_kelas' => 'KLS-PRODI-B', 'id_prodi' => $this->prodi('Prodi Lain')->id_prodi]);
        $r = $this->ruangan();
        $a = $this->jadwal($r, 'senin', '08:00:00', '09:00:00', ['id_kelas' => $kelasA->id_kelas]);
        $b = $this->jadwal($r, 'selasa', '08:00:00', '09:00:00', ['id_kelas' => $kelasB->id_kelas]);

        return [$staf, $kelasA, $kelasB, $a, $b];
    }

    private function payloadJadwal(Jadwal $j, array $o = []): array
    {
        return $o + [
            'id_mata_kuliah' => MataKuliah::firstOrCreate(['kode_mk' => 'IF-1', 'id_prodi' => $this->prodi()->id_prodi], ['nama_mk' => 'Basis Data', 'sks' => 3, 'semester' => 'ganjil'])->id_mata_kuliah,
            'id_kelas' => $j->id_kelas, 'nuptk_nidn' => $j->nuptk_nidn, 'id_ruangan' => $j->id_ruangan, 'hari' => $j->hari,
            'jam_mulai' => '10:00', 'jam_selesai' => '11:00', 'status' => 'aktif', 'tahun_akademik' => '2026/2027', 'semester' => 'ganjil',
        ];
    }

    public function test_staf_prodi_tidak_dapat_mengakses_jadwal_prodi_lain(): void
    {
        [$staf, $kelasA, $kelasB, $a, $b] = $this->dunia();
        $sesi = $this->sebagai('staff_prodi', $staf);

        $sesi->get(route('jadwal.index', ['periode' => 'semua']))->assertOk()->assertSee('KLS-PRODI-A')->assertDontSee('KLS-PRODI-B');
        $sesi->get(route('jadwal.show', $a))->assertOk();
        $sesi->get(route('jadwal.show', $b))->assertNotFound();
        $sesi->get(route('jadwal.edit', $b))->assertNotFound();
        $sesi->put(route('jadwal.update', $b), $this->payloadJadwal($b))->assertNotFound();
        $sesi->delete(route('jadwal.destroy', $b))->assertNotFound();
        $this->assertSame('08:00:00', $b->fresh()->jam_mulai);
        $this->assertNotNull(Jadwal::find($b->id_jadwal));

        // dropdown kelas hanya prodinya; kelas prodi lain ditolak di backend
        $sesi->get(route('jadwal.create'))->assertOk()->assertSee('KLS-PRODI-A')->assertDontSee('KLS-PRODI-B');
        $sesi->post(route('jadwal.store'), $this->payloadJadwal($a, ['id_kelas' => $kelasB->id_kelas]))->assertSessionHasErrors('id_kelas');
        $sesi->put(route('jadwal.update', $a), $this->payloadJadwal($a, ['id_kelas' => $kelasB->id_kelas]))->assertSessionHasErrors('id_kelas');
        $this->assertSame(2, Jadwal::count());

        // jadwal prodinya sendiri tetap bisa diubah dan dihapus
        $sesi->put(route('jadwal.update', $a), $this->payloadJadwal($a))->assertSessionHasNoErrors();
        $this->assertSame('10:00:00', $a->fresh()->jam_mulai);
        $sesi->delete(route('jadwal.destroy', $a))->assertSessionHas('success');
    }

    public function test_staf_prodi_tidak_dapat_mengubah_kelas_prodi_lain_tetapi_laboran_bisa(): void
    {
        [$staf, $kelasA, $kelasB] = $this->dunia();
        $sesi = $this->sebagai('staff_prodi', $staf);
        $ubah = fn ($k, $nama) => ['tab' => 'kelas', 'id' => $k->id_kelas];

        $sesi->put(route('data-akademik.update', $ubah($kelasB, 'X')), ['nama_kelas' => 'X', 'id_prodi' => $kelasB->id_prodi])->assertNotFound();
        $sesi->delete(route('data-akademik.destroy', $ubah($kelasB, 'X')))->assertNotFound();
        $sesi->post(route('data-akademik.store', ['tab' => 'kelas']), ['nama_kelas' => 'BARU', 'id_prodi' => $kelasB->id_prodi])->assertForbidden();
        $sesi->put(route('data-akademik.update', $ubah($kelasA, 'X')), ['nama_kelas' => 'KLS-PRODI-A', 'id_prodi' => $kelasB->id_prodi])->assertForbidden(); // dipindah ke prodi lain
        $this->assertSame('KLS-PRODI-B', $kelasB->fresh()->nama_kelas);
        $this->assertNull(Kelas::where('nama_kelas', 'BARU')->first());
        $sesi->get(route('data-akademik.index', ['tab' => 'kelas']))->assertOk()->assertSee('KLS-PRODI-A')->assertDontSee('KLS-PRODI-B');

        // Laboran tetap mengakses semuanya
        $lab = $this->sebagai('laboran', $this->laboran());
        $lab->put(route('data-akademik.update', $ubah($kelasB, 'X')), ['nama_kelas' => 'KLS-B2', 'id_prodi' => $kelasB->id_prodi])->assertSessionHas('success');
        $this->assertSame('KLS-B2', $kelasB->fresh()->nama_kelas);
        // Kelas yang dipakai jadwal dilindungi (FK RESTRICT), bahkan untuk Laboran; kelas kosong boleh dihapus.
        $lab->delete(route('data-akademik.destroy', $ubah($kelasB, 'X')))->assertSessionHas('error');
        $this->assertNotNull(Kelas::find($kelasB->id_kelas));
        $kosong = Kelas::create(['nama_kelas' => 'KLS-B-KOSONG', 'id_prodi' => $kelasB->id_prodi]);
        $lab->delete(route('data-akademik.destroy', $ubah($kosong, 'X')))->assertSessionHas('success');
        $this->assertNull(Kelas::find($kosong->id_kelas));
    }

    public function test_laboran_tetap_melihat_dan_mengubah_jadwal_semua_prodi(): void
    {
        [, , , $a, $b] = $this->dunia();
        $lab = $this->sebagai('laboran', $this->laboran());

        $lab->get(route('jadwal.index', ['periode' => 'semua']))->assertOk()->assertSee('KLS-PRODI-A')->assertSee('KLS-PRODI-B');
        $lab->get(route('jadwal.show', $b))->assertOk();
        $lab->put(route('jadwal.update', $b), $this->payloadJadwal($b))->assertSessionHasNoErrors();
        $this->assertSame('10:00:00', $b->fresh()->jam_mulai);
        $lab->delete(route('jadwal.destroy', $b))->assertSessionHas('success');
    }

    // ---------------------------------------------------------------- B4 + B12 stok manual

    public function test_stok_manual_tambah_kurangi_tidak_negatif_dan_tercatat_di_log(): void
    {
        Log::spy();
        $lab = $this->laboran();
        $k = $this->katalog('alat', 5);
        $url = route('kelola-katalog.stok', ['kategori' => 'alat', 'id' => $k->id_katalog]);
        $sesi = $this->sebagai('laboran', $lab);

        $sesi->post($url, ['aksi' => 'tambah', 'jumlah' => 3])->assertSessionHas('success');
        $this->assertSame(8, $k->fresh()->stok);
        $sesi->post($url, ['aksi' => 'kurangi', 'jumlah' => 2])->assertSessionHas('success');
        $this->assertSame(6, $k->fresh()->stok);

        $sesi->post($url, ['aksi' => 'kurangi', 'jumlah' => 100])->assertSessionHas('error'); // tidak boleh negatif
        $sesi->post($url, ['aksi' => 'tambah', 'jumlah' => 0])->assertSessionHas('error');
        $this->assertSame(6, $k->fresh()->stok);

        Log::shouldHaveReceived('info')->withArgs(fn ($pesan, $ctx) => $pesan === 'Stok katalog diubah manual.'
            && $ctx['pelaku'] === $lab->email && $ctx['id_katalog'] === $k->id_katalog && $ctx['nama'] === $k->nama
            && $ctx['aksi'] === 'tambah' && $ctx['jumlah'] === 3 && $ctx['stok_lama'] === 5 && $ctx['stok_baru'] === 8)->once();
        Log::shouldHaveReceived('info')->withArgs(fn ($pesan, $ctx) => $pesan === 'Stok katalog diubah manual.'
            && $ctx['aksi'] === 'kurangi' && $ctx['stok_lama'] === 8 && $ctx['stok_baru'] === 6)->once();
    }
}
