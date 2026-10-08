<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** Data pada struktur lama harus terbawa ke struktur 16 tabel (migration perubahan baru, bukan reset database). */
class MigrasiDataLamaTest extends TestCase
{
    private const FINAL = '2026_11_02_000001_revisi_final_struktur_16_tabel.php';

    /** Migration yang hanya bisa berjalan setelah struktur final tersedia. */
    private const SESUDAH_FINAL = ['2026_11_10_000001_create_mata_kuliahs_dan_relasi_jadwal.php'];

    public function test_data_lama_dipindahkan_ke_struktur_final(): void
    {
        $dir = sys_get_temp_dir() . '/mig_lama_' . uniqid();
        File::makeDirectory($dir);
        foreach (File::files(database_path('migrations')) as $f) {
            if ($f->getFilename() !== self::FINAL && ! in_array($f->getFilename(), self::SESUDAH_FINAL, true)) {
                File::copy($f->getPathname(), $dir . '/' . $f->getFilename());
            }
        }
        Artisan::call('db:wipe');
        Artisan::call('migrate', ['--path' => $dir, '--realpath' => true]);

        $now = now();
        DB::table('mahasiswas')->insert(['nim' => '2210101001', 'nama' => 'Budi', 'kelas' => '1A', 'program_studi' => 'D3 Teknologi Informasi', 'email' => 'Budi@mhs.politala.ac.id', 'no_whatsapp' => '0812', 'password' => 'pwlama', 'created_at' => $now, 'updated_at' => $now]);
        DB::table('dosens')->insert(['nuptk_nidn' => '0011223344', 'nama' => 'Dosen A', 'program_studi' => 'Prodi Belum Terdaftar', 'email' => 'dosen@politala.ac.id', 'no_whatsapp' => '0813', 'password' => 'pwdosen', 'created_at' => $now, 'updated_at' => $now]);
        foreach ([
            ['nama' => 'Kabel UTP', 'jenis' => 'bahan', 'satuan' => 'Meter', 'per_unit' => 5, 'stok' => 100, 'harga_total' => 500000, 'unit_dasar_harga' => 50, 'keterangan' => 'Rak 1', 'gambar' => 'k.jpg', 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Lab Komputer 1', 'jenis' => 'ruangan', 'satuan' => '', 'stok' => 30, 'kondisi' => 'Tersedia', 'keterangan' => 'Lantai 2', 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Obeng', 'jenis' => 'alat', 'satuan' => 'Buah', 'stok' => 7, 'keterangan' => null, 'created_at' => $now, 'updated_at' => $now],
        ] as $row) {
            DB::table('alat_bahans')->insert($row); // kolom tiap baris berbeda, jadi disisipkan satu per satu
        }

        Artisan::call('migrate'); // hanya migration baru yang tersisa
        File::deleteDirectory($dir);

        foreach (['mahasiswas_lama', 'dosens_lama', 'alat_bahans_lama', 'users'] as $t) {
            $this->assertFalse(Schema::hasTable($t), "{$t} harus sudah dibuang");
        }
        $akun = DB::table('akuns')->where('email', 'budi@mhs.politala.ac.id')->first();
        $this->assertSame('mahasiswa', $akun->role);
        $this->assertSame('pwlama', $akun->password);
        $this->assertSame('dosen', DB::table('akuns')->where('email', 'dosen@politala.ac.id')->value('role'));
        $this->assertSame('laboran', DB::table('akuns')->where('email', 'admin@silab.test')->value('role'));
        $this->assertNotNull(DB::table('laborans')->where('email', 'admin@silab.test')->first());

        $m = DB::table('mahasiswas')->where('nim', '2210101001')->first();
        $this->assertSame('D3 Teknologi Informasi', DB::table('prodis')->where('id_prodi', $m->id_prodi)->value('nama_prodi'));
        $this->assertSame('Prodi Belum Terdaftar', DB::table('prodis')->where('id_prodi', DB::table('dosens')->value('id_prodi'))->value('nama_prodi'));

        $kabel = DB::table('alat_bahans')->where('nama', 'Kabel UTP')->first();
        $this->assertEqualsWithDelta(2000.0, (float) $kabel->harga, 0.001); // (500000/50)/5 per meter
        $this->assertSame(100, (int) $kabel->stok);
        $this->assertSame('Rak 1', $kabel->keterangan);
        $this->assertSame('Meter', DB::table('satuans')->where('id_satuan', $kabel->id_satuan)->value('nama_satuan'));
        $this->assertSame('Lab Komputer 1', DB::table('ruangans')->where('nama_ruangan', 'Lab Komputer 1')->value('nama_ruangan'));
        $this->assertSame(0, DB::table('alat_bahans')->where('nama', 'Lab Komputer 1')->count()); // ruangan keluar dari katalog
        $this->assertSame(1.0, (float) DB::table('alat_bahans')->where('nama', 'Obeng')->value('harga')); // tanpa harga -> 1.00
        $this->assertNotNull(DB::table('kelas')->where('nama_kelas', '1A')->first());
    }
}
