<?php

namespace Tests\Feature;

use App\Models\Akun;
use App\Models\Jadwal;
use App\Models\MataKuliah;
use App\Models\StaffProdi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

/** Halaman khusus Staf Prodi: dashboard, Data Akademik, dan Jadwal Perkuliahan. */
class StaffProdiTest extends TestCase
{
    use MembuatData, RefreshDatabase;

    private StaffProdi $staffProdi;

    protected function setUp(): void
    {
        parent::setUp();
        $this->staffProdi = $this->staff();
    }

    private function sebagai(string $role, StaffProdi|\App\Models\Laboran|\App\Models\Dosen $profil): self
    {
        return $this->withSession(['silab_user' => ['role' => $role, 'key' => (string) $profil->getKey(), 'nama' => $profil->nama, 'email' => $profil->email]]);
    }

    private function sebagaiStaff(): self
    {
        return $this->sebagai('staff_prodi', $this->staffProdi);
    }

    private function payloadJadwal(array $o = []): array
    {
        return $o + [
            'id_mata_kuliah' => MataKuliah::create(['kode_mk' => 'IF-101', 'nama_mk' => 'Basis Data', 'sks' => 3, 'semester' => 'ganjil'])->id_mata_kuliah,
            'id_kelas' => $this->kelas()->id_kelas,
            'nuptk_nidn' => $this->dosen()->nuptk_nidn,
            'id_ruangan' => $this->ruangan()->id_ruangan,
            'hari' => 'senin',
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
            'status' => 'aktif',
            'tahun_akademik' => '2026/2027',
            'semester' => 'ganjil',
        ];
    }

    // ------------------------------------------------------------------ login & menu

    public function test_login_staff_prodi_menuju_dashboard(): void
    {
        $this->post('/login', ['email' => $this->staffProdi->email, 'password' => 'rahasia'])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('silab_user.role', 'staff_prodi');
    }

    public function test_sidebar_staf_prodi_hanya_menu_tugas_staf(): void
    {
        $this->sebagaiStaff()->get(route('dashboard'))->assertOk()
            ->assertSee('Beranda')
            ->assertSee(url('katalog'), false)
            ->assertSee('Data Akademik')
            ->assertSee('Jadwal Perkuliahan')
            ->assertDontSee('Usulan Pengadaan')
            ->assertDontSee('Kelola Katalog')
            ->assertDontSee('Kelola Data User')
            ->assertDontSee('Pengaturan Laboratorium')
            ->assertDontSee('Peminjaman');
    }

    // ------------------------------------------------------------------ hak akses

    public function test_hak_akses_halaman_staf_prodi(): void
    {
        $this->sebagaiStaff()->get(route('data-akademik.index'))->assertOk();
        $this->sebagaiStaff()->get(route('jadwal.index'))->assertOk();
        $this->sebagaiStaff()->get(route('jadwal.create'))->assertOk();
        $this->sebagaiStaff()->get(route('katalog'))->assertOk()->assertDontSee('Kelola Katalog');
        $this->sebagaiStaff()->get(route('kelola-katalog.index'))->assertForbidden();
        $this->sebagaiStaff()->get(route('kelola-user.index'))->assertForbidden();

        $this->sebagai('dosen', $this->dosen())->get(route('jadwal.index'))->assertForbidden();

        $this->flushSession();
        $this->get(route('jadwal.index'))->assertRedirect(route('login'));
    }

    public function test_katalog_staf_prodi_otomatis_terupdate_dari_data_laboran(): void
    {
        // Laboran menambah data katalog
        $katalog = $this->katalog('alat', 10, ['nama' => 'Oscilloscope X1', 'harga' => 1500000]);
        $this->sebagai('laboran', $this->laboran())->get(route('katalog'))
            ->assertOk()->assertSee('Oscilloscope X1');

        // Staf Prodi melihat data yang sama (sumber database yang sama)
        $this->sebagaiStaff()->get(route('katalog'))
            ->assertOk()->assertSee('Oscilloscope X1')->assertSee('1.500.000');

        // Laboran mengubah data -> Staf Prodi langsung melihat versi terbaru tanpa proses apapun
        $this->sebagai('laboran', $this->laboran())->put(
            route('kelola-katalog.update', ['kategori' => 'alat', 'id' => $katalog->id_katalog]),
            ['nama' => 'Oscilloscope X2', 'id_satuan' => $katalog->id_satuan, 'stok' => 3, 'harga' => 2000000, 'id_ruangan' => $katalog->id_ruangan, 'gambar' => null]
        )->assertSessionHas('success');

        $this->sebagaiStaff()->get(route('katalog'))
            ->assertOk()->assertSee('Oscilloscope X2')->assertDontSee('Oscilloscope X1');

        // Laboran menghapus data -> ikut hilang dari katalog Staf Prodi
        $this->sebagai('laboran', $this->laboran())->delete(
            route('kelola-katalog.destroy', ['kategori' => 'alat', 'id' => $katalog->id_katalog])
        )->assertSessionHas('success');

        $this->sebagaiStaff()->get(route('katalog'))
            ->assertOk()->assertDontSee('Oscilloscope X2');
    }

    // ------------------------------------------------------------------ dashboard

    public function test_dashboard_staf_prodi_menghitung_data_dari_database(): void
    {
        $this->sebagaiStaff()->post(route('jadwal.store'), $this->payloadJadwal())
            ->assertSessionHas('success');

        $this->sebagaiStaff()->get(route('dashboard'))->assertOk()
            ->assertViewHas('totalDosen', 1)
            ->assertViewHas('totalMataKuliah', 1)
            ->assertViewHas('totalJadwal', 1)
            ->assertDontSee('Total Mahasiswa')
            ->assertSee('Total Dosen')
            ->assertSee('Dashboard Staf Prodi')
            ->assertSee('Basis Data');
    }

    public function test_dashboard_staf_prodi_tanpa_data_tampil_keadaan_kosong(): void
    {
        $this->sebagaiStaff()->get(route('dashboard'))->assertOk()
            ->assertViewHas('totalJadwal', 0)
            ->assertDontSee('Total Mahasiswa')
            ->assertDontSee('Data Mahasiswa')
            ->assertSee('Belum ada data jadwal perkuliahan.');
    }

    // ------------------------------------------------------------------ data akademik

    public function test_crud_mata_kuliah_dari_halaman_data_akademik(): void
    {
        $payload = ['kode_mk' => 'IF-101', 'nama_mk' => ' Basis Data ', 'sks' => 3, 'semester' => 'ganjil'];

        $this->sebagaiStaff()->post(route('data-akademik.store', ['tab' => 'mata-kuliah']), $payload)
            ->assertRedirect()->assertSessionHas('success');
        $mk = MataKuliah::firstOrFail();
        $this->assertSame('IF-101', $mk->kode_mk);
        $this->assertSame('Basis Data', $mk->nama_mk);

        $this->sebagaiStaff()->post(route('data-akademik.store', ['tab' => 'mata-kuliah']), $payload)
            ->assertSessionHasErrors('kode_mk');
        $this->sebagaiStaff()->post(route('data-akademik.store', ['tab' => 'mata-kuliah']), [
            'kode_mk' => 'IF-102', 'nama_mk' => 'Basis Data', 'sks' => 0, 'semester' => 'semester-x',
        ])->assertSessionHasErrors(['nama_mk', 'sks', 'semester']);
        $this->assertSame(1, MataKuliah::count());

        $this->sebagaiStaff()->put(route('data-akademik.update', ['tab' => 'mata-kuliah', 'id' => $mk->id_mata_kuliah]), [
            'kode_mk' => 'IF-101', 'nama_mk' => 'Basis Data Lanjut', 'sks' => 4, 'semester' => 'genap',
        ])->assertSessionHas('success');
        $this->assertSame('Basis Data Lanjut', $mk->fresh()->nama_mk);

        $this->sebagaiStaff()->delete(route('data-akademik.destroy', ['tab' => 'mata-kuliah', 'id' => $mk->id_mata_kuliah]))
            ->assertSessionHas('success');
        $this->assertNull(MataKuliah::find($mk->id_mata_kuliah));
    }

    public function test_crud_kelas_dan_tab_semester(): void
    {
        $this->sebagaiStaff()->get(route('data-akademik.index', ['tab' => 'semester']))->assertOk()
            ->assertSee('Nilai tetap sistem')
            ->assertSee('Ganjil');

        $this->sebagaiStaff()->post(route('data-akademik.store', ['tab' => 'kelas']), [
            'nama_kelas' => '9Q', 'id_prodi' => $this->prodi()->id_prodi,
        ])->assertSessionHas('success');
        $kelas = \App\Models\Kelas::where('nama_kelas', '9Q')->firstOrFail();

        $this->sebagaiStaff()->post(route('data-akademik.store', ['tab' => 'kelas']), [
            'nama_kelas' => '9Q', 'id_prodi' => $kelas->id_prodi,
        ])->assertSessionHasErrors('nama_kelas');

        $this->sebagaiStaff()->put(route('data-akademik.update', ['tab' => 'kelas', 'id' => $kelas->id_kelas]), [
            'nama_kelas' => '9R', 'id_prodi' => $kelas->id_prodi,
        ])->assertSessionHas('success');
        $this->assertSame('9R', $kelas->fresh()->nama_kelas);

        $this->sebagaiStaff()->delete(route('data-akademik.destroy', ['tab' => 'kelas', 'id' => $kelas->id_kelas]))
            ->assertSessionHas('success');
        $this->assertNull(\App\Models\Kelas::find($kelas->id_kelas));
    }

    // ------------------------------------------------------------------ data akademik: dosen

    public function test_tab_dosen_staff_hanya_baca_dan_tanpa_email(): void
    {
        $dosen = $this->dosen(['nama' => 'Dosen Cari Uji']);

        $this->sebagaiStaff()->get(route('data-akademik.index', ['tab' => 'dosen']))->assertOk()
            ->assertSee('Dosen Cari Uji')
            ->assertDontSee($dosen->email)
            ->assertDontSee('Tambah Dosen')
            ->assertDontSee('dosenModal')
            ->assertDontSee('pe-4">Aksi', false)
            ->assertDontSee('data-akademik/dosen/', false);

        $this->sebagaiStaff()->get(route('data-akademik.index', ['tab' => 'dosen', 'search' => 'Cari Uji']))->assertOk()
            ->assertSee('Dosen Cari Uji');
        // email tidak ikut dicari
        $this->sebagaiStaff()->get(route('data-akademik.index', ['tab' => 'dosen', 'search' => $dosen->email]))->assertOk()
            ->assertDontSee('Dosen Cari Uji');
    }

    public function test_staff_tidak_bisa_tambah_ubah_hapus_dosen(): void
    {
        $prodi = $this->prodi();
        $dosen = $this->dosen(['nuptk_nidn' => 'D00000123']);
        $namaAsli = $dosen->nama;
        $payload = ['nuptk_nidn' => 'D00000999', 'nama' => 'Dosen Baru', 'id_prodi' => $prodi->id_prodi,
            'email' => 'dosenbaru@uji.test', 'no_whatsapp' => '081234567890', 'status' => 'aktif', 'password' => 'abcd'];

        $this->sebagaiStaff()->post(route('data-akademik.store', ['tab' => 'dosen']), $payload)->assertForbidden();
        $this->sebagaiStaff()->put(route('data-akademik.update', ['tab' => 'dosen', 'id' => $dosen->getKey()]), $payload)->assertForbidden();
        $this->sebagaiStaff()->delete(route('data-akademik.destroy', ['tab' => 'dosen', 'id' => $dosen->getKey()]))->assertForbidden();

        $this->assertNull(\App\Models\Dosen::find('D00000999'));
        $this->assertNull(Akun::find('dosenbaru@uji.test'));
        $this->assertSame($namaAsli, $dosen->fresh()->nama);
        $this->assertNotNull(Akun::find($dosen->email));
    }

    public function test_relasi_dosen_pada_jadwal_tetap_berfungsi(): void
    {
        $dosen = $this->dosen();
        $this->sebagaiStaff()->post(route('jadwal.store'), $this->payloadJadwal(['nuptk_nidn' => $dosen->nuptk_nidn]))
            ->assertSessionHas('success');

        $this->assertSame($dosen->nuptk_nidn, Jadwal::first()->nuptk_nidn);
        $this->sebagaiStaff()->get(route('jadwal.index'))->assertOk();
        $this->sebagaiStaff()->delete(route('data-akademik.destroy', ['tab' => 'dosen', 'id' => $dosen->getKey()]))->assertForbidden();
        $this->assertNotNull(\App\Models\Dosen::find($dosen->getKey()));
    }

    // ------------------------------------------------------------------ data akademik: import

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

    public function test_import_mata_kuliah_dan_kelas_dari_data_akademik(): void
    {
        $prodi = $this->prodi('D3 Uji');

        $this->sebagaiStaff()->get(route('data-akademik.index'))->assertOk()->assertSee('importModal');
        $this->sebagaiStaff()->get(route('data-akademik.template', ['tab' => 'kelas']))->assertOk();

        $this->sebagaiStaff()->post(route('data-akademik.import', ['tab' => 'mata-kuliah']), ['file' => $this->excel(
            ['Kode', 'Mata Kuliah', 'SKS', 'Semester'],
            [['if-101', 'Basis Data', '3', 'Ganjil'], ['IF-102', 'Jaringan', '2', 'genap']],
        )])->assertSessionHas('success');
        $this->assertSame('IF-101', MataKuliah::where('nama_mk', 'Basis Data')->value('kode_mk'));
        $this->assertSame(2, MataKuliah::count());

        // satu baris salah (kode sudah ada, SKS 0) -> tidak ada yang tersimpan
        $this->sebagaiStaff()->post(route('data-akademik.import', ['tab' => 'mata-kuliah']), ['file' => $this->excel(
            ['Kode', 'Mata Kuliah', 'SKS', 'Semester'],
            [['IF-103', 'Algoritma', '3', 'Ganjil'], ['IF-101', 'Lain', '0', 'Ganjil']],
        )])->assertSessionHas('import_report', fn ($r) => $r['error_count'] === 1 && $r['errors'][0]['row'] === 3);
        $this->assertSame(2, MataKuliah::count());

        $this->sebagaiStaff()->post(route('data-akademik.import', ['tab' => 'kelas']), ['file' => $this->excel(
            ['Nama Kelas', 'Prodi'],
            [['1A', 'D3 Uji'], ['1B', 'D3 Uji']],
        )])->assertSessionHas('success');
        $this->assertSame(2, \App\Models\Kelas::where('id_prodi', $prodi->id_prodi)->whereIn('nama_kelas', ['1A', '1B'])->count());

        // tab dosen tidak punya import
        $this->sebagaiStaff()->post(route('data-akademik.import', ['tab' => 'dosen']), ['file' => $this->excel(['Nama'], [['x']])])->assertNotFound();
    }

    // ------------------------------------------------------------------ jadwal

    public function test_tambah_ubah_hapus_jadwal_dan_deteksi_bentrok(): void
    {
        $payload = $this->payloadJadwal();

        $this->sebagaiStaff()->post(route('jadwal.store'), $payload)
            ->assertSessionHas('success');
        $this->assertSame(1, Jadwal::count());

        // ruangan + hari + semester sama, waktu beririsan -> bentrok
        $this->sebagaiStaff()->post(route('jadwal.store'), array_merge($payload, [
            'nuptk_nidn' => $this->dosen()->nuptk_nidn,
            'jam_mulai' => '09:00', 'jam_selesai' => '11:00',
        ]))->assertSessionHasErrors('jam_mulai');
        $this->assertSame(1, Jadwal::count());

        // bertemu tepat di batas (10:00) -> bukan bentrok
        $this->sebagaiStaff()->post(route('jadwal.store'), array_merge($payload, [
            'nuptk_nidn' => $this->dosen()->nuptk_nidn,
            'jam_mulai' => '10:00', 'jam_selesai' => '12:00',
        ]))->assertSessionHas('success');
        $this->assertSame(2, Jadwal::count());

        $jadwal = Jadwal::orderBy('id_jadwal')->first();
        $this->sebagaiStaff()->get(route('jadwal.edit', $jadwal))->assertOk()->assertSee('Basis Data');
        $this->sebagaiStaff()->put(route('jadwal.update', $jadwal), array_merge($payload, ['jam_mulai' => '08:30']))
            ->assertSessionHas('success');
        $this->assertSame('08:30:00', $jadwal->fresh()->jam_mulai);

        $this->sebagaiStaff()->get(route('jadwal.show', $jadwal))->assertOk()->assertSee('Basis Data');
        $this->sebagaiStaff()->delete(route('jadwal.destroy', $jadwal))->assertSessionHas('success');
        $this->assertSame(1, Jadwal::count());
    }

    public function test_daftar_jadwal_bisa_dicari_dan_difilter(): void
    {
        $payload = $this->payloadJadwal();
        $this->sebagaiStaff()->post(route('jadwal.store'), $payload);

        $this->sebagaiStaff()->get(route('jadwal.index', ['search' => 'Basis']))->assertOk()->assertSee('Basis Data');
        $this->sebagaiStaff()->get(route('jadwal.index', ['search' => 'Tidak Ada']))->assertOk()
            ->assertSee('Tidak ada jadwal');
        $this->sebagaiStaff()->get(route('jadwal.index', ['hari' => 'selasa']))->assertOk()
            ->assertDontSee('Basis Data');
        $this->sebagaiStaff()->get(route('jadwal.index', ['hari' => 'senin', 'status' => 'aktif']))->assertOk()
            ->assertSee('Basis Data');
        $this->sebagaiStaff()->get(route('jadwal.index', ['per_page' => 999]))->assertOk();
    }
}
