<?php

namespace Tests\Feature;

use App\Models\Akun;
use App\Models\Jadwal;
use App\Models\MataKuliah;
use App\Models\StaffProdi;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            ->assertDontSee('Kelola Data Master')
            ->assertDontSee('Pengaturan Operasional')
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

        $this->sebagai('laboran', $this->laboran())->get(route('data-akademik.index'))->assertForbidden();
        $this->sebagai('laboran', $this->laboran())->get(route('jadwal.index'))->assertForbidden();
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

    public function test_tab_dosen_tampil_dan_crud_dari_halaman_data_akademik(): void
    {
        $prodi = $this->prodi();

        $this->sebagaiStaff()->get(route('data-akademik.index', ['tab' => 'dosen']))->assertOk()
            ->assertSee('Dosen')
            ->assertSee('Belum ada data dosen.');

        $this->sebagaiStaff()->post(route('data-akademik.store', ['tab' => 'dosen']), [
            'nuptk_nidn' => 'D00000123', 'nama' => ' Dosen Baru ', 'id_prodi' => $prodi->id_prodi,
            'email' => 'DosenBaru@Uji.Test', 'no_whatsapp' => '081234567890', 'status' => 'aktif', 'password' => 'abcd',
        ])->assertRedirect()->assertSessionHas('success');

        $dosen = \App\Models\Dosen::where('nuptk_nidn', 'D00000123')->firstOrFail();
        $this->assertSame('Dosen Baru', $dosen->nama);
        $this->assertSame('dosenbaru@uji.test', $dosen->email);
        $this->assertSame('dosen', Akun::find($dosen->email)->role);

        // NUPTK/NIDN ganda ditolak
        $this->sebagaiStaff()->post(route('data-akademik.store', ['tab' => 'dosen']), [
            'nuptk_nidn' => 'D00000123', 'nama' => 'Dosen Lain', 'id_prodi' => $prodi->id_prodi,
            'email' => 'lain@uji.test', 'no_whatsapp' => null, 'status' => 'nonaktif', 'password' => 'abcd',
        ])->assertSessionHasErrors('nuptk_nidn');

        $this->sebagaiStaff()->put(route('data-akademik.update', ['tab' => 'dosen', 'id' => $dosen->getKey()]), [
            'nuptk_nidn' => 'D00000123', 'nama' => 'Dosen Baru Lagi', 'id_prodi' => $prodi->id_prodi,
            'email' => 'dosenbaru@uji.test', 'no_whatsapp' => '081234567891', 'status' => 'nonaktif',
        ])->assertSessionHas('success');
        $this->assertSame('Dosen Baru Lagi', $dosen->fresh()->nama);
        $this->assertSame('nonaktif', $dosen->fresh()->status);

        $this->sebagaiStaff()->delete(route('data-akademik.destroy', ['tab' => 'dosen', 'id' => $dosen->getKey()]))
            ->assertSessionHas('success');
        $this->assertNull(\App\Models\Dosen::find('D00000123'));
        $this->assertNull(Akun::find('dosenbaru@uji.test'));
    }

    public function test_dosen_masih_dipakai_jadwal_tidak_bisa_dihapus(): void
    {
        $dosen = $this->dosen();
        $this->sebagaiStaff()->post(route('jadwal.store'), $this->payloadJadwal(['nuptk_nidn' => $dosen->nuptk_nidn]));

        $this->sebagaiStaff()->delete(route('data-akademik.destroy', ['tab' => 'dosen', 'id' => $dosen->getKey()]))
            ->assertSessionHas('error');
        $this->assertNotNull(\App\Models\Dosen::find($dosen->getKey()));
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
