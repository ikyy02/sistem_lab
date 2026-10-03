<?php

namespace Tests\Feature;

use App\Models\Akun;
use App\Models\AlatBahan;
use App\Models\Mahasiswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

class ImportTest extends TestCase
{
    use MembuatData, RefreshDatabase;

    private function excel(array $header, array $rows): UploadedFile
    {
        $book = new Spreadsheet();
        $sheet = $book->getActiveSheet();
        foreach (array_merge([$header], $rows) as $r => $cells) {
            foreach (array_values($cells) as $c => $v) {
                $sheet->setCellValueExplicit(chr(65 + $c) . ($r + 1), (string) $v, DataType::TYPE_STRING);
            }
        }
        $path = tempnam(sys_get_temp_dir(), 'x');
        (new Xlsx($book))->save($path);
        $content = file_get_contents($path);
        unlink($path);

        return UploadedFile::fake()->createWithContent('data.xlsx', $content);
    }

    private function admin(): self
    {
        $l = $this->laboran();

        return $this->withSession(['silab_user' => ['role' => 'laboran', 'key' => $l->id_pegawai, 'nama' => $l->nama, 'email' => $l->email]]);
    }

    public function test_import_mahasiswa_membuat_akun_dan_profil_dengan_prodi_dari_nama(): void
    {
        $prodi = $this->prodi('D3 Uji');
        $file = $this->excel(['NIM', 'Nama', 'Program Studi', 'No WhatsApp', 'Email'], [
            ['3310100001', 'Budi', 'd3 uji', '081234567890', 'Budi1@mhs.politala.ac.id'],
            ['3310100002', 'Siti', 'D3 Uji', '', 'siti@mhs.politala.ac.id'],
        ]);
        $this->admin()->post(route('mahasiswa.import'), ['file' => $file])->assertSessionHas('success');

        $m = Mahasiswa::find('3310100001');
        $this->assertSame($prodi->id_prodi, $m->id_prodi);
        $this->assertNull(Mahasiswa::find('3310100002')->no_whatsapp);
        $akun = Akun::find('budi1@mhs.politala.ac.id');
        $this->assertSame('mahasiswa', $akun->role);
        $this->assertSame('3310100001', $akun->password); // password awal = NIM
    }

    public function test_import_mahasiswa_all_or_nothing_dan_email_unik_lintas_role(): void
    {
        $this->prodi('D3 Uji');
        $dosen = $this->dosen();
        $file = $this->excel(['NIM', 'Nama', 'Program Studi', 'No WhatsApp', 'Email'], [
            ['3310100001', 'Valid', 'D3 Uji', '081234567890', 'valid@mhs.politala.ac.id'],
            ['3310100002', 'Email Dosen', 'D3 Uji', '081234567890', $dosen->email],
            ['3310100003', 'Prodi Salah', 'Tidak Ada', '081234567890', 'x@mhs.politala.ac.id'],
        ]);
        $this->admin()->post(route('mahasiswa.import'), ['file' => $file])
            ->assertSessionHas('import_report', fn ($r) => $r['error_count'] === 2 && collect($r['errors'])->pluck('row')->all() === [3, 4]);
        $this->assertSame(0, Mahasiswa::count());
        $this->assertNull(Akun::find('valid@mhs.politala.ac.id'));
    }

    public function test_import_dosen_membuat_akun_dan_profil(): void
    {
        $this->prodi('D3 Uji');
        $file = $this->excel(['NUPTK/NIDN', 'Nama', 'Prodi', 'Email', 'WhatsApp', 'Password'], [
            ['0011', 'Dosen Satu', 'D3 Uji', 'd1@politala.ac.id', '', 'abcd'],
        ]);
        $this->admin()->post(route('kelola-user.import', 'dosen'), ['file' => $file])->assertSessionHas('success');
        $this->assertSame('dosen', Akun::find('d1@politala.ac.id')->role);
        $this->assertSame('Dosen Satu', \App\Models\Dosen::find('0011')->nama);
    }

    public function test_import_katalog_dan_ruangan(): void
    {
        $this->satuan('meter');
        $r = $this->ruangan('Gudang');
        $admin = $this->admin();
        $file = $this->excel(['Nama', 'Satuan', 'Stok', 'Harga', 'Ruangan', 'Keterangan'], [
            ['Kabel UTP', 'Meter', '100', '491,80', 'gudang', ''],
        ]);
        $admin->post(route('kelola-katalog.import', 'bahan'), ['file' => $file])->assertSessionHas('success');
        $k = AlatBahan::where('nama', 'Kabel UTP')->first();
        $this->assertSame([$r->id_ruangan, 100, '491.80', 'bahan'], [$k->id_ruangan, $k->stok, $k->harga, $k->jenis]);

        $bad = $this->excel(['Nama', 'Satuan', 'Stok', 'Harga', 'Ruangan', 'Keterangan'], [
            ['Kabel UTP', 'meter', '1', '1', 'Gudang', ''],      // nama sudah ada
            ['Baru', 'meter', '1', '0', 'Gudang', ''],           // harga 0
            ['Baru2', 'kg', '1', '5', 'Tidak Ada', ''],          // satuan & ruangan tidak ada
        ]);
        $admin->post(route('kelola-katalog.import', 'alat'), ['file' => $bad])
            ->assertSessionHas('inv_import_report', fn ($rep) => $rep['error_count'] === 3);
        $this->assertSame(1, AlatBahan::count());

        $ruang = $this->excel(['Nama', 'Keterangan'], [['Lab Baru', 'Lt 3'], ['Lab Baru 2', '']]);
        $admin->post(route('kelola-katalog.import', 'ruangan'), ['file' => $ruang])->assertSessionHas('success');
        $this->assertDatabaseHas('ruangans', ['nama_ruangan' => 'Lab Baru', 'keterangan' => 'Lt 3']);
    }

    public function test_template_katalog_dan_user_bisa_diunduh(): void
    {
        $admin = $this->admin();
        $admin->get(route('kelola-katalog.template', ['kategori' => 'alat', 'format' => 'xlsx']))->assertOk();
        $admin->get(route('kelola-katalog.template', ['kategori' => 'ruangan', 'format' => 'csv']))->assertOk();
        $admin->get(route('kelola-user.template', ['kategori' => 'staff_prodi', 'format' => 'xlsx']))->assertOk();
        $admin->get(route('mahasiswa.template'))->assertOk();
    }
}
