<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Services\AkademikImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

/** Mata Kuliah dan dosen dibatasi per prodi untuk Staf Prodi; Laboran tetap global. Termasuk migrasi data lama. */
class BatasProdiMataKuliahDosenTest extends TestCase
{
    use MembuatData, RefreshDatabase;

    private function sebagai(string $role, $profil): self
    {
        return $this->withSession(['silab_user' => ['role' => $role, 'key' => (string) $profil->getKey(), 'nama' => $profil->nama, 'email' => $profil->email]]);
    }

    /** Dua prodi: A ("Prodi Uji", prodi Staf) dan B ("Prodi Lain"), masing-masing punya mata kuliah, dosen, dan kelas. */
    private function dunia(): array
    {
        $staf = $this->staff();
        $a = $this->prodi();
        $b = $this->prodi('Prodi Lain');

        return [
            'staf' => $staf, 'a' => $a, 'b' => $b,
            'mkA' => MataKuliah::create(['kode_mk' => 'MK-A1', 'nama_mk' => 'Matkul Milik Prodi A', 'sks' => 3, 'semester' => 'ganjil', 'id_prodi' => $a->id_prodi]),
            'mkB' => MataKuliah::create(['kode_mk' => 'MK-B1', 'nama_mk' => 'Matkul Milik Prodi B', 'sks' => 3, 'semester' => 'ganjil', 'id_prodi' => $b->id_prodi]),
            'dosenA' => $this->dosen(['nama' => 'Dosen Milik Prodi A']),
            'dosenB' => $this->dosen(['nama' => 'Dosen Milik Prodi B', 'id_prodi' => $b->id_prodi]),
            'kelasA' => Kelas::create(['nama_kelas' => 'KLS-A', 'id_prodi' => $a->id_prodi]),
            'kelasB' => Kelas::create(['nama_kelas' => 'KLS-B', 'id_prodi' => $b->id_prodi]),
            'ruang' => $this->ruangan(),
        ];
    }

    private function jadwalLangsung(array $d, string $prodi, string $hari): Jadwal
    {
        return Jadwal::create([
            'id_kelas' => $d['kelas' . $prodi]->id_kelas, 'id_mata_kuliah' => $d['mk' . $prodi]->id_mata_kuliah,
            'nuptk_nidn' => $d['dosen' . $prodi]->nuptk_nidn, 'id_ruangan' => $d['ruang']->id_ruangan, 'hari' => $hari,
            'jam_mulai' => '08:00:00', 'jam_selesai' => '09:00:00', 'status' => 'aktif', 'tahun_akademik' => '2026/2027', 'semester' => 'ganjil',
        ]);
    }

    private function payloadJadwal(array $d, array $o = []): array
    {
        return $o + [
            'id_mata_kuliah' => $d['mkA']->id_mata_kuliah, 'id_kelas' => $d['kelasA']->id_kelas, 'nuptk_nidn' => $d['dosenA']->nuptk_nidn,
            'id_ruangan' => $d['ruang']->id_ruangan, 'hari' => 'senin', 'jam_mulai' => '08:00', 'jam_selesai' => '10:00',
            'status' => 'aktif', 'tahun_akademik' => '2026/2027', 'semester' => 'ganjil',
        ];
    }

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

    // ---------------------------------------------------------------- CRUD Mata Kuliah lintas prodi

    public function test_staf_prodi_hanya_melihat_dan_mengelola_mata_kuliah_prodinya(): void
    {
        $d = $this->dunia();
        $sesi = $this->sebagai('staff_prodi', $d['staf']);
        $ubah = fn ($mk) => ['tab' => 'mata-kuliah', 'id' => $mk->id_mata_kuliah];
        $isi = ['kode_mk' => 'MK-A1', 'nama_mk' => 'Diubah Paksa', 'sks' => 4, 'semester' => 'genap'];

        // daftar dan pencarian
        $sesi->get(route('data-akademik.index'))->assertOk()->assertSee('Matkul Milik Prodi A')->assertDontSee('Matkul Milik Prodi B');
        $sesi->get(route('data-akademik.index', ['search' => 'Milik Prodi B']))->assertOk()->assertDontSee('Matkul Milik Prodi B');
        $sesi->get(route('data-akademik.index', ['search' => 'MK-B1']))->assertOk()->assertDontSee('Matkul Milik Prodi B');

        // ubah/hapus mata kuliah prodi lain: 404 dan data tidak berubah
        $sesi->put(route('data-akademik.update', $ubah($d['mkB'])), $isi)->assertNotFound();
        $sesi->delete(route('data-akademik.destroy', $ubah($d['mkB'])))->assertNotFound();
        $this->assertSame('Matkul Milik Prodi B', $d['mkB']->fresh()->nama_mk);

        // tambah: prodi otomatis prodinya; menyebut prodi lain ditolak 403
        $sesi->post(route('data-akademik.store', ['tab' => 'mata-kuliah']), ['kode_mk' => 'MK-A2', 'nama_mk' => 'Baru A', 'sks' => 2, 'semester' => 'ganjil'])
            ->assertSessionHas('success');
        $this->assertEquals($d['a']->id_prodi, MataKuliah::where('kode_mk', 'MK-A2')->value('id_prodi'));
        $sesi->post(route('data-akademik.store', ['tab' => 'mata-kuliah']), ['kode_mk' => 'MK-X', 'nama_mk' => 'Susupan', 'sks' => 2, 'semester' => 'ganjil', 'id_prodi' => $d['b']->id_prodi])
            ->assertForbidden();
        $this->assertNull(MataKuliah::where('kode_mk', 'MK-X')->first());

        // memindahkan mata kuliah sendiri ke prodi lain ditolak
        $sesi->put(route('data-akademik.update', $ubah($d['mkA'])), $isi + ['id_prodi' => $d['b']->id_prodi])->assertForbidden();
        $this->assertEquals($d['a']->id_prodi, $d['mkA']->fresh()->id_prodi);

        // kode boleh sama dengan milik prodi lain, tetapi tidak boleh ganda di prodinya sendiri
        $sesi->post(route('data-akademik.store', ['tab' => 'mata-kuliah']), ['kode_mk' => 'MK-B1', 'nama_mk' => 'Nama Lain', 'sks' => 2, 'semester' => 'ganjil'])
            ->assertSessionHas('success');
        $sesi->post(route('data-akademik.store', ['tab' => 'mata-kuliah']), ['kode_mk' => 'MK-A1', 'nama_mk' => 'Nama Lain 2', 'sks' => 2, 'semester' => 'ganjil'])
            ->assertSessionHasErrors('kode_mk');

        // mata kuliah sendiri bisa diubah dan dihapus
        $sesi->put(route('data-akademik.update', $ubah($d['mkA'])), ['kode_mk' => 'MK-A1', 'nama_mk' => 'A Diperbarui', 'sks' => 4, 'semester' => 'genap'])
            ->assertSessionHas('success');
        $this->assertSame('A Diperbarui', $d['mkA']->fresh()->nama_mk);
        $sesi->delete(route('data-akademik.destroy', $ubah($d['mkA'])))->assertSessionHas('success');
        $this->assertNull(MataKuliah::find($d['mkA']->id_mata_kuliah));
    }

    public function test_laboran_mengelola_mata_kuliah_semua_prodi(): void
    {
        $d = $this->dunia();
        $lab = $this->sebagai('laboran', $this->laboran());
        $ubah = fn ($mk) => ['tab' => 'mata-kuliah', 'id' => $mk->id_mata_kuliah];
        $isi = fn (array $o = []) => $o + ['kode_mk' => 'MK-B1', 'nama_mk' => 'B Diperbarui', 'sks' => 3, 'semester' => 'ganjil', 'id_prodi' => $d['b']->id_prodi];

        $lab->get(route('data-akademik.index'))->assertOk()->assertSee('Matkul Milik Prodi A')->assertSee('Matkul Milik Prodi B')->assertSee('Prodi Lain');
        $lab->get(route('data-akademik.index', ['search' => 'Prodi Lain']))->assertOk()->assertSee('Matkul Milik Prodi B')->assertDontSee('Matkul Milik Prodi A');

        // Laboran wajib memilih prodi saat menambah
        $lab->post(route('data-akademik.store', ['tab' => 'mata-kuliah']), $isi(['kode_mk' => 'MK-B9', 'nama_mk' => 'Tanpa Prodi', 'id_prodi' => '']))
            ->assertSessionHasErrors('id_prodi');
        $lab->post(route('data-akademik.store', ['tab' => 'mata-kuliah']), $isi(['kode_mk' => 'MK-B9', 'nama_mk' => 'Baru B']))->assertSessionHas('success');
        $this->assertEquals($d['b']->id_prodi, MataKuliah::where('kode_mk', 'MK-B9')->value('id_prodi'));

        $lab->put(route('data-akademik.update', $ubah($d['mkB'])), $isi())->assertSessionHas('success');
        $this->assertSame('B Diperbarui', $d['mkB']->fresh()->nama_mk);

        // prodi tidak boleh dipindah bila mata kuliah sudah dipakai jadwal; bila belum dipakai, boleh
        $this->jadwalLangsung($d, 'B', 'selasa');
        $lab->put(route('data-akademik.update', $ubah($d['mkB'])), $isi(['id_prodi' => $d['a']->id_prodi]))->assertSessionHasErrors('id_prodi');
        $this->assertEquals($d['b']->id_prodi, $d['mkB']->fresh()->id_prodi);
        $lab->put(route('data-akademik.update', $ubah($d['mkA'])), $isi(['kode_mk' => 'MK-A1', 'nama_mk' => 'Matkul Milik Prodi A']))->assertSessionHas('success');
        $this->assertEquals($d['b']->id_prodi, $d['mkA']->fresh()->id_prodi);

        $lab->delete(route('data-akademik.destroy', $ubah(MataKuliah::where('kode_mk', 'MK-B9')->first())))->assertSessionHas('success');
        $this->assertNull(MataKuliah::where('kode_mk', 'MK-B9')->first());
    }

    // ---------------------------------------------------------------- dropdown, dashboard, manipulasi request

    public function test_dropdown_mata_kuliah_dan_dosen_form_jadwal_sesuai_prodi(): void
    {
        $d = $this->dunia();
        $jadwal = $this->jadwalLangsung($d, 'A', 'senin');
        $staf = $this->sebagai('staff_prodi', $d['staf']);

        foreach ([route('jadwal.create'), route('jadwal.edit', $jadwal), route('jadwal.show', $jadwal)] as $url) {
            $staf->get($url)->assertOk()
                ->assertSee('Matkul Milik Prodi A')->assertSee('Dosen Milik Prodi A')
                ->assertDontSee('Matkul Milik Prodi B')->assertDontSee('Dosen Milik Prodi B');
        }

        // Laboran melihat semua prodi
        $this->sebagai('laboran', $this->laboran())->get(route('jadwal.create'))->assertOk()
            ->assertSee('Matkul Milik Prodi A')->assertSee('Matkul Milik Prodi B')
            ->assertSee('Dosen Milik Prodi A')->assertSee('Dosen Milik Prodi B');
    }

    public function test_tab_dosen_staf_prodi_hanya_dosen_prodinya_dan_laboran_tidak_punya_tab(): void
    {
        $d = $this->dunia();

        // Daftar: hanya dosen prodi Staf.
        $this->sebagai('staff_prodi', $d['staf'])->get(route('data-akademik.index', ['tab' => 'dosen']))->assertOk()
            ->assertSee('Dosen Milik Prodi A')->assertDontSee('Dosen Milik Prodi B');

        // Pencarian tidak membuka dosen prodi lain.
        $this->sebagai('staff_prodi', $d['staf'])->get(route('data-akademik.index', ['tab' => 'dosen', 'search' => 'Milik Prodi B']))->assertOk()
            ->assertDontSee('Dosen Milik Prodi B');

        // Laboran tidak memakai tab Dosen (dikelola di Kelola Data User); tab itu jatuh ke Mata Kuliah.
        $this->sebagai('laboran', $this->laboran())->get(route('data-akademik.index', ['tab' => 'dosen']))->assertOk()
            ->assertDontSee('Dosen Milik Prodi A')->assertDontSee('Dosen Milik Prodi B');
    }

    public function test_dashboard_staf_prodi_hanya_data_prodinya(): void
    {
        $d = $this->dunia();
        $this->jadwalLangsung($d, 'A', 'senin');
        $this->jadwalLangsung($d, 'B', 'selasa');

        $this->sebagai('staff_prodi', $d['staf'])->get(route('dashboard'))->assertOk()
            ->assertViewHas('totalDosen', 1)->assertViewHas('totalMataKuliah', 1)->assertViewHas('totalJadwal', 1)
            ->assertViewHas('dosenPerProdi', fn ($per) => $per->keys()->all() === ['Prodi Uji'] && (int) $per['Prodi Uji'] === 1)
            ->assertSee('Matkul Milik Prodi A')->assertSee('Dosen Milik Prodi A')
            ->assertDontSee('Matkul Milik Prodi B')->assertDontSee('Dosen Milik Prodi B')->assertDontSee('Prodi Lain');
    }

    public function test_manipulasi_request_lintas_prodi_ditolak_di_backend(): void
    {
        $d = $this->dunia();
        $staf = $this->sebagai('staff_prodi', $d['staf']);
        $milikA = $this->jadwalLangsung($d, 'A', 'selasa');

        $staf->post(route('jadwal.store'), $this->payloadJadwal($d, ['id_mata_kuliah' => $d['mkB']->id_mata_kuliah]))->assertSessionHasErrors('id_mata_kuliah');
        $staf->post(route('jadwal.store'), $this->payloadJadwal($d, ['nuptk_nidn' => $d['dosenB']->nuptk_nidn]))->assertSessionHasErrors('nuptk_nidn');
        $staf->post(route('jadwal.store'), $this->payloadJadwal($d, ['id_mata_kuliah' => [$d['mkA']->id_mata_kuliah, $d['mkB']->id_mata_kuliah]]))->assertSessionHasErrors('id_mata_kuliah');
        $staf->post(route('jadwal.store'), $this->payloadJadwal($d, ['id_mata_kuliah' => 999999]))->assertSessionHasErrors('id_mata_kuliah');
        $this->assertSame(1, Jadwal::count());

        $staf->put(route('jadwal.update', $milikA), $this->payloadJadwal($d, ['hari' => 'selasa', 'id_mata_kuliah' => $d['mkB']->id_mata_kuliah]))->assertSessionHasErrors('id_mata_kuliah');
        $staf->put(route('jadwal.update', $milikA), $this->payloadJadwal($d, ['hari' => 'selasa', 'nuptk_nidn' => $d['dosenB']->nuptk_nidn]))->assertSessionHasErrors('nuptk_nidn');
        $this->assertEquals($d['mkA']->id_mata_kuliah, $milikA->fresh()->id_mata_kuliah);
        $this->assertEquals($d['dosenA']->nuptk_nidn, $milikA->fresh()->nuptk_nidn);

        // data prodinya sendiri tetap diterima
        $staf->post(route('jadwal.store'), $this->payloadJadwal($d))->assertSessionHasNoErrors()->assertSessionHas('success');
        $this->assertSame(2, Jadwal::count());
    }

    public function test_laboran_tetap_dapat_memakai_mata_kuliah_dan_dosen_semua_prodi(): void
    {
        $d = $this->dunia();

        $this->sebagai('laboran', $this->laboran())->post(route('jadwal.store'), $this->payloadJadwal($d, [
            'id_mata_kuliah' => $d['mkB']->id_mata_kuliah, 'nuptk_nidn' => $d['dosenB']->nuptk_nidn,
        ]))->assertSessionHasNoErrors()->assertSessionHas('success');
        $this->assertSame(1, Jadwal::count());
    }

    // ---------------------------------------------------------------- import

    public function test_import_mata_kuliah_dibatasi_prodi(): void
    {
        $d = $this->dunia();
        $kolom = ['Kode', 'Mata Kuliah', 'SKS', 'Semester'];

        // Staf Prodi: kolom Prodi tidak ada; data masuk ke prodinya; kode yang sama dengan prodi lain boleh
        $this->assertArrayNotHasKey('Prodi', AkademikImportService::columns('mata-kuliah', $d['a']->id_prodi));
        $staf = $this->sebagai('staff_prodi', $d['staf']);
        $staf->post(route('data-akademik.import', ['tab' => 'mata-kuliah']), ['file' => $this->excel($kolom, [['mk-b1', 'Nama Impor', '3', 'Ganjil'], ['MK-A5', 'Impor A5', '2', 'Genap']])])
            ->assertSessionHas('success');
        $this->assertEquals([$d['a']->id_prodi, $d['a']->id_prodi], MataKuliah::whereIn('kode_mk', ['MK-B1', 'MK-A5'])->where('nama_mk', '!=', 'Matkul Milik Prodi B')->pluck('id_prodi')->all());
        $staf->post(route('data-akademik.import', ['tab' => 'mata-kuliah']), ['file' => $this->excel($kolom, [['MK-A1', 'Lain', '3', 'Ganjil']])])
            ->assertSessionHas('import_report', fn ($r) => $r['error_count'] === 1);

        // Laboran: kolom Prodi wajib; prodi tak dikenal dilaporkan dan tidak ada yang tersimpan
        $this->assertArrayHasKey('Prodi', AkademikImportService::columns('mata-kuliah', null));
        $lab = $this->sebagai('laboran', $this->laboran());
        $lab->post(route('data-akademik.import', ['tab' => 'mata-kuliah']), ['file' => $this->excel($kolom, [['MK-L1', 'Impor L1', '3', 'Ganjil']])])
            ->assertSessionHas('error');
        $sebelum = MataKuliah::count();
        $lab->post(route('data-akademik.import', ['tab' => 'mata-kuliah']), ['file' => $this->excel([...$kolom, 'Prodi'], [['MK-L1', 'Impor L1', '3', 'Ganjil', 'Prodi Lain'], ['MK-L2', 'Impor L2', '3', 'Ganjil', 'Prodi Hantu']])])
            ->assertSessionHas('import_report', fn ($r) => $r['error_count'] === 1 && $r['errors'][0]['row'] === 3);
        $this->assertSame($sebelum, MataKuliah::count());
        $lab->post(route('data-akademik.import', ['tab' => 'mata-kuliah']), ['file' => $this->excel([...$kolom, 'Prodi'], [['MK-L1', 'Impor L1', '3', 'Ganjil', 'Prodi Lain']])])
            ->assertSessionHas('success');
        $this->assertEquals($d['b']->id_prodi, MataKuliah::where('kode_mk', 'MK-L1')->value('id_prodi'));
    }

    // ---------------------------------------------------------------- migrasi data lama

    private function migrasi()
    {
        return require database_path('migrations/2026_12_05_000001_tambah_prodi_pada_mata_kuliahs.php');
    }

    /** Struktur lama (tanpa id_prodi) diisi data lama, lalu migration dijalankan lagi. */
    public function test_migrasi_memetakan_mata_kuliah_lama_tanpa_menebak_dan_menjaga_jadwal(): void
    {
        $this->assertTrue(Schema::hasColumn('mata_kuliahs', 'id_prodi'));
        $migrasi = $this->migrasi();
        $migrasi->down(); // kembali ke struktur lama
        $this->assertFalse(Schema::hasColumn('mata_kuliahs', 'id_prodi'));

        $a = $this->prodi();
        $b = $this->prodi('Prodi Lain');
        $kelasA = Kelas::create(['nama_kelas' => 'KLS-A', 'id_prodi' => $a->id_prodi]);
        $kelasB = Kelas::create(['nama_kelas' => 'KLS-B', 'id_prodi' => $b->id_prodi]);
        $ruang = $this->ruangan();
        $dosen = $this->dosen();
        $baris = fn (string $kode, string $nama) => DB::table('mata_kuliahs')->insertGetId(['kode_mk' => $kode, 'nama_mk' => $nama, 'sks' => 3, 'semester' => 'ganjil'], 'id_mata_kuliah');
        $mkSatu = $baris('SATU', 'Dipakai Satu Prodi');
        $mkBersama = $baris('BERSAMA', 'Dipakai Dua Prodi');
        $mkYatim = $baris('YATIM', 'Tanpa Jadwal');
        $hari = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'];
        $buat = function (int $kelas, ?int $mk, string $h) use ($dosen, $ruang) {
            return Jadwal::create(['id_kelas' => $kelas, 'id_mata_kuliah' => $mk, 'nuptk_nidn' => $dosen->nuptk_nidn, 'id_ruangan' => $ruang->id_ruangan,
                'hari' => $h, 'jam_mulai' => '08:00:00', 'jam_selesai' => '09:00:00', 'status' => 'aktif', 'tahun_akademik' => '2026/2027', 'semester' => 'ganjil']);
        };
        $j1 = $buat($kelasA->id_kelas, $mkSatu, $hari[0]);
        $j2 = $buat($kelasA->id_kelas, $mkBersama, $hari[1]);
        $j3 = $buat($kelasA->id_kelas, $mkBersama, $hari[2]);
        $j4 = $buat($kelasB->id_kelas, $mkBersama, $hari[3]);
        $j5 = $buat($kelasB->id_kelas, null, $hari[4]); // jadwal tanpa mata kuliah tidak disentuh

        $migrasi->up();

        $mk = fn (int $id) => DB::table('mata_kuliahs')->where('id_mata_kuliah', $id)->first();
        // satu prodi: dipetakan pasti
        $this->assertEquals($a->id_prodi, (int) $mk($mkSatu)->id_prodi);
        $this->assertEquals($mkSatu, $j1->fresh()->id_mata_kuliah);
        // dua prodi: baris asli untuk prodi berid terkecil, salinan untuk prodi lain; jadwal mengikuti prodi kelasnya
        $this->assertEquals($a->id_prodi, (int) $mk($mkBersama)->id_prodi);
        $this->assertEquals($mkBersama, $j2->fresh()->id_mata_kuliah);
        $this->assertEquals($mkBersama, $j3->fresh()->id_mata_kuliah);
        $salinan = $j4->fresh()->id_mata_kuliah;
        $this->assertNotSame($mkBersama, $salinan);
        $this->assertEquals($b->id_prodi, (int) $mk($salinan)->id_prodi);
        $this->assertSame(['BERSAMA', 'Dipakai Dua Prodi', 3, 'ganjil'], [$mk($salinan)->kode_mk, $mk($salinan)->nama_mk, (int) $mk($salinan)->sks, $mk($salinan)->semester]);
        // tanpa jadwal: tidak ditebak, dibiarkan NULL, tetap ada
        $this->assertNotNull($mk($mkYatim));
        $this->assertNull($mk($mkYatim)->id_prodi);
        $this->assertNull($j5->fresh()->id_mata_kuliah);

        // tidak ada data hilang dan tidak ada jadwal yang mata kuliahnya berbeda prodi dengan kelasnya
        $this->assertSame(4, DB::table('mata_kuliahs')->count());
        $this->assertSame(5, Jadwal::count());
        $this->assertSame(0, DB::table('jadwals')->join('kelas', 'kelas.id_kelas', '=', 'jadwals.id_kelas')
            ->join('mata_kuliahs', 'mata_kuliahs.id_mata_kuliah', '=', 'jadwals.id_mata_kuliah')
            ->whereColumn('kelas.id_prodi', '!=', 'mata_kuliahs.id_prodi')->count());

        // laporan: kasus selain "satu prodi" tercatat
        $this->assertSame(1, DB::table('mata_kuliah_pemetaan_log')->where('status', 'tanpa_jadwal')->where('id_mata_kuliah', $mkYatim)->count());
        $this->assertSame(2, DB::table('mata_kuliah_pemetaan_log')->where('status', 'dipecah')->where('id_mata_kuliah', $mkBersama)->count());
        $this->assertSame(0, DB::table('mata_kuliah_pemetaan_log')->where('id_mata_kuliah', $mkSatu)->count());

        // kode yang sama kini boleh ada di dua prodi, tetapi tidak dua kali dalam satu prodi
        $this->assertSame(2, DB::table('mata_kuliahs')->where('kode_mk', 'BERSAMA')->count());
        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('mata_kuliahs')->insert(['kode_mk' => 'SATU', 'nama_mk' => 'Ganda', 'sks' => 1, 'semester' => 'ganjil', 'id_prodi' => $a->id_prodi]);
    }

    public function test_rollback_migrasi_menggabungkan_salinan_dan_memulihkan_jadwal(): void
    {
        $migrasi = $this->migrasi();
        $a = $this->prodi();
        $b = $this->prodi('Prodi Lain');
        $migrasi->down();

        $kelasA = Kelas::create(['nama_kelas' => 'KLS-A', 'id_prodi' => $a->id_prodi]);
        $kelasB = Kelas::create(['nama_kelas' => 'KLS-B', 'id_prodi' => $b->id_prodi]);
        $ruang = $this->ruangan();
        $dosen = $this->dosen();
        $mk = DB::table('mata_kuliahs')->insertGetId(['kode_mk' => 'BERSAMA', 'nama_mk' => 'Dipakai Dua Prodi', 'sks' => 3, 'semester' => 'ganjil'], 'id_mata_kuliah');
        foreach ([[$kelasA, 'senin'], [$kelasB, 'selasa']] as [$kelas, $h]) {
            Jadwal::create(['id_kelas' => $kelas->id_kelas, 'id_mata_kuliah' => $mk, 'nuptk_nidn' => $dosen->nuptk_nidn, 'id_ruangan' => $ruang->id_ruangan,
                'hari' => $h, 'jam_mulai' => '08:00:00', 'jam_selesai' => '09:00:00', 'status' => 'aktif', 'tahun_akademik' => '2026/2027', 'semester' => 'ganjil']);
        }
        $migrasi->up();
        $this->assertSame(2, DB::table('mata_kuliahs')->count());

        $migrasi->down();

        $this->assertSame(1, DB::table('mata_kuliahs')->count());
        $this->assertEquals([$mk], Jadwal::pluck('id_mata_kuliah')->unique()->values()->all());
        $this->assertFalse(Schema::hasColumn('mata_kuliahs', 'id_prodi'));
        $this->assertFalse(Schema::hasTable('mata_kuliah_pemetaan_log'));
    }

    public function test_scope_batas_prodi_dan_relasi_prodi(): void
    {
        $d = $this->dunia();

        $this->assertSame('Prodi Uji', $d['mkA']->prodi->nama_prodi);
        $this->assertEquals([$d['mkA']->id_mata_kuliah], MataKuliah::batasProdi($d['a']->id_prodi)->pluck('id_mata_kuliah')->all());
        $this->assertSame(2, MataKuliah::batasProdi(null)->count());
        $this->assertEquals([$d['dosenA']->nuptk_nidn], Dosen::batasProdi($d['a']->id_prodi)->pluck('nuptk_nidn')->all());
    }
}
