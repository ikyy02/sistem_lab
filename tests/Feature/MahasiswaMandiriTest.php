<?php

namespace Tests\Feature;

use App\Models\Akun;
use App\Models\Jadwal;
use App\Models\Mahasiswa;
use App\Models\Peminjaman;
use App\Services\PeminjamanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

/** Mahasiswa berdiri sendiri: tidak terhubung ke Kelas/Mata Kuliah; modul jadwal tetap utuh. */
class MahasiswaMandiriTest extends TestCase
{
    use MembuatData, RefreshDatabase;

    private function sebagai(string $role, $profil): self
    {
        return $this->withSession(['silab_user' => [
            'role' => $role, 'key' => (string) $profil->getKey(), 'nama' => $profil->nama, 'email' => $profil->email,
        ]]);
    }

    private function hariKerja(int $jam): string
    {
        $t = now()->addDay()->startOfDay();
        while ($t->isWeekend()) {
            $t->addDay();
        }

        return $t->setTime($jam, 0)->format('Y-m-d H:i:s');
    }

    public function test_tidak_ada_relasi_mahasiswa_ke_kelas_atau_mata_kuliah(): void
    {
        $this->assertFalse(Schema::hasTable('kelas_mahasiswas'));
        $this->assertFalse(method_exists(Mahasiswa::class, 'kelasMahasiswas'));
        foreach (Schema::getColumnListing('mahasiswas') as $kolom) {
            $this->assertStringNotContainsString('kelas', $kolom);
            $this->assertStringNotContainsString('mata_kuliah', $kolom);
        }
        // Jadwal tetap memakai kelas & mata kuliah
        $this->assertTrue(Schema::hasColumns('jadwals', ['id_kelas', 'id_mata_kuliah']));
    }

    public function test_tambah_ubah_hapus_mahasiswa_tanpa_kelas_dan_mata_kuliah(): void
    {
        $prodi = $this->prodi();
        $admin = $this->sebagai('laboran', $this->laboran());

        $admin->post(route('kelola-user.store', 'mahasiswa'), [
            'nim' => '2210109001', 'nama' => 'Tanpa Kelas', 'id_prodi' => $prodi->id_prodi,
            'email' => 'tanpakelas@mhs.politala.ac.id', 'password' => 'abcd',
        ])->assertSessionHasNoErrors();
        $this->assertNotNull(Mahasiswa::find('2210109001'));

        $admin->put(route('kelola-user.update', ['kategori' => 'mahasiswa', 'key' => '2210109001']), [
            'nim' => '2210109001', 'nama' => 'Nama Baru', 'id_prodi' => $prodi->id_prodi, 'email' => 'tanpakelas@mhs.politala.ac.id',
        ])->assertSessionHasNoErrors();
        $this->assertSame('Nama Baru', Mahasiswa::find('2210109001')->nama);

        $admin->get(route('kelola-user.index', ['kategori' => 'mahasiswa']))->assertOk()->assertDontSee('Kelas')->assertDontSee('Mata Kuliah');

        $admin->delete(route('kelola-user.destroy', ['kategori' => 'mahasiswa', 'key' => '2210109001']))->assertSessionHas('success');
        $this->assertNull(Mahasiswa::find('2210109001'));
    }

    public function test_mahasiswa_tanpa_kelas_mengajukan_peminjaman_dan_membuka_dashboard(): void
    {
        $m = $this->mahasiswa();
        $dosen = $this->dosen();
        $k = $this->katalog('alat', 5);
        $svc = app(PeminjamanService::class);
        $data = [
            'tanggal_peminjaman' => $this->hariKerja(9), 'tanggal_rencana_kembali' => $this->hariKerja(11),
            'keterangan' => 'Praktikum', 'detail' => [['id_katalog' => $k->id_katalog, 'jumlah' => 1]], 'ruangan' => [],
        ];

        $mandiri = $svc->ajukan($m->email, $data + ['jenis_peminjaman' => 'pribadi']);
        $this->assertSame('menunggu', $mandiri->status);
        $svc->tolak($mandiri->id_peminjaman, $this->laboran()->email, 'uji'); // bebaskan batas pengajuan
        $atas = $svc->ajukan($m->email, $data + ['jenis_peminjaman' => 'atas_dosen', 'email_dosen' => $dosen->email]);
        $this->assertSame($dosen->email, $atas->email_dosen);

        $this->sebagai('mahasiswa', $m)->get(route('dashboard'))->assertOk()->assertSee('Ajukan Peminjaman');
        $this->get(route('peminjaman.index'))->assertOk();
        $this->get(route('peminjaman.riwayat'))->assertOk();
    }

    public function test_kelas_dan_jadwal_tetap_berfungsi_terpisah_dari_mahasiswa(): void
    {
        $kelas = $this->kelas();
        $bebas = $this->kelas();
        $this->jadwal($this->ruangan('Ruang Kuliah'), 'rabu', '08:00', '09:40', ['id_kelas' => $kelas->id_kelas]);
        $this->assertSame(1, Jadwal::where('id_kelas', $kelas->id_kelas)->count());

        $admin = $this->sebagai('laboran', $this->laboran());
        $admin->delete(route('data-akademik.destroy', ['tab' => 'kelas', 'id' => $bebas->id_kelas]))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('kelas', ['id_kelas' => $bebas->id_kelas]);
        $admin->delete(route('data-akademik.destroy', ['tab' => 'kelas', 'id' => $kelas->id_kelas])); // masih dipakai jadwal
        $this->assertDatabaseHas('kelas', ['id_kelas' => $kelas->id_kelas]);

        $this->sebagai('staff_prodi', $this->staff())->get(route('jadwal.index'))->assertOk();
    }

    public function test_migrasi_pelepasan_mempertahankan_baris_lama_dan_tidak_memblokir_hapus_mahasiswa(): void
    {
        $m = $this->mahasiswa();
        $kelas = $this->kelas();
        (require database_path('migrations/2026_11_17_000001_create_kelas_mahasiswas_table.php'))->up();
        DB::table('kelas_mahasiswas')->insert(['nim' => $m->nim, 'id_kelas' => $kelas->id_kelas, 'tahun_akademik' => '2026/2027', 'semester' => 'ganjil']);

        (require database_path('migrations/2026_12_03_000001_lepas_relasi_kelas_mahasiswas.php'))->up();

        $this->assertSame(1, DB::table('kelas_mahasiswas')->count()); // data lama utuh sebagai arsip
        $m->delete();                                                  // tidak lagi diblokir FK
        $this->assertNull(Mahasiswa::find($m->nim));
        $this->assertNotNull(Akun::find($m->email)); // akun tidak ikut tersentuh oleh migrasi
    }
}
