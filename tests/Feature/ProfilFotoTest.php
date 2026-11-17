<?php

namespace Tests\Feature;

use App\Models\Akun;
use App\Support\GambarProfil;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

/** Foto profil: semua role bisa tambah, ganti, dan hapus foto profil. */
class ProfilFotoTest extends TestCase
{
    use MembuatData, RefreshDatabase;

    /** Sesi login dengan role dan profil apa pun. */
    private function sebagai(string $role, object $profil): self
    {
        return $this->withSession(['silab_user' => [
            'role' => $role, 'key' => (string) $profil->getKey(),
            'nama' => $profil->nama, 'email' => $profil->email,
        ]]);
    }

    public function test_mahasiswa_bisa_menambah_foto_profil(): void
    {
        Storage::fake('public');
        $mhs = $this->mahasiswa();

        $res = $this->sebagai('mahasiswa', $mhs)->post(route('profil.foto'), [
            'foto' => UploadedFile::fake()->image('foto.jpg', 300, 300),
        ]);

        $res->assertRedirect(route('profil.index'))->assertSessionHas('success');
        $akun = Akun::findOrFail($mhs->email);
        $this->assertNotNull($akun->foto);
        Storage::disk('public')->assertExists(GambarProfil::DIR.'/'.$akun->foto);

        // Halaman profil menampilkan foto, tombol ganti, dan tombol hapus.
        $halaman = $this->sebagai('mahasiswa', $mhs)->get(route('profil.index'));
        $halaman->assertOk();
        $halaman->assertSee('profil-avatar-img', false);
        $halaman->assertSee('Ganti Foto');
        $halaman->assertSee('Hapus Foto');
        $halaman->assertSee(asset('storage/'.GambarProfil::DIR.'/'.$akun->foto), false);

        GambarProfil::hapus($akun->foto); // rapikan file uji
    }

    public function test_file_bukan_gambar_ditolak(): void
    {
        $mhs = $this->mahasiswa();

        $res = $this->sebagai('mahasiswa', $mhs)->post(route('profil.foto'), [
            'foto' => UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'),
        ]);

        // Validasi gagal: kembali ke halaman sebelumnya (di pengujian = URL tanpa riwayat) dengan error.
        $res->assertRedirect()->assertSessionHasErrors('foto');
        $this->assertNull(Akun::findOrFail($mhs->email)->foto);
    }

    public function test_ganti_foto_menghapus_file_lama(): void
    {
        Storage::fake('public');
        $mhs = $this->mahasiswa();

        $this->sebagai('mahasiswa', $mhs)->post(route('profil.foto'), [
            'foto' => UploadedFile::fake()->image('lama.png'),
        ]);
        $lama = Akun::findOrFail($mhs->email)->foto;

        $this->sebagai('mahasiswa', $mhs)->post(route('profil.foto'), [
            'foto' => UploadedFile::fake()->image('baru.jpg'),
        ]);
        $akun = Akun::findOrFail($mhs->email);

        $this->assertNotSame($lama, $akun->foto);
        Storage::disk('public')->assertExists(GambarProfil::DIR.'/'.$akun->foto);
        Storage::disk('public')->assertMissing(GambarProfil::DIR.'/'.$lama);

        GambarProfil::hapus($akun->foto);
    }

    public function test_hapus_foto_profil(): void
    {
        Storage::fake('public');
        $mhs = $this->mahasiswa();

        $this->sebagai('mahasiswa', $mhs)->post(route('profil.foto'), [
            'foto' => UploadedFile::fake()->image('foto.png'),
        ]);
        $nama = Akun::findOrFail($mhs->email)->foto;

        $res = $this->sebagai('mahasiswa', $mhs)->post(route('profil.foto.hapus'));

        $res->assertRedirect(route('profil.index'))->assertSessionHas('success');
        $akun = Akun::findOrFail($mhs->email);
        $this->assertNull($akun->foto);
        Storage::disk('public')->assertMissing(GambarProfil::DIR.'/'.$nama);

        // Halaman profil kembali menampilkan inisial, tanpa tombol hapus.
        $halaman = $this->sebagai('mahasiswa', $mhs)->get(route('profil.index'));
        $halaman->assertSee('Tambah Foto');
        $halaman->assertDontSee('Hapus Foto');
    }

    public function test_semua_role_bisa_memakai_foto_profil(): void
    {
        Storage::fake('public');
        $dosen = $this->dosen();
        $staff = $this->staff();
        $laboran = $this->laboran();

        foreach ([['dosen', $dosen], ['staff_prodi', $staff], ['laboran', $laboran]] as [$role, $profil]) {
            $res = $this->sebagai($role, $profil)->post(route('profil.foto'), [
                'foto' => UploadedFile::fake()->image('foto.jpg'),
            ]);
            $res->assertRedirect(route('profil.index'))->assertSessionHas('success');

            $akun = Akun::findOrFail($profil->email);
            Storage::disk('public')->assertExists(GambarProfil::DIR.'/'.$akun->foto);
            GambarProfil::hapus($akun->foto);
            Storage::disk('public')->assertMissing(GambarProfil::DIR.'/'.$akun->foto);
        }
    }
}
