<?php

namespace Tests\Feature;

use App\Models\Akun;
use App\Models\AlatBahan;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\Peminjaman;
use App\Models\PeminjamanDetail;
use App\Models\Ruangan;
use App\Models\Satuan;
use App\Services\AkunService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

class KelolaHttpTest extends TestCase
{
    use MembuatData, RefreshDatabase;

    private function sebagai(string $role, $profil): self
    {
        return $this->withSession(['silab_user' => ['role' => $role, 'key' => (string) $profil->getKey(), 'nama' => $profil->nama, 'email' => $profil->email]]);
    }

    private function admin(): self
    {
        return $this->sebagai('laboran', $this->laboran());
    }

    // ------------------------------------------------------------------ login & role

    public function test_login_memakai_password_apa_adanya_dan_role_wajib_punya_profil(): void
    {
        $m = $this->mahasiswa();
        $m->akun = Akun::find($m->email);
        $this->assertSame('rahasia', Akun::find($m->email)->password);
        $this->post('/login', ['email' => $m->email, 'password' => 'rahasia'])->assertRedirect()->assertSessionHas('silab_user.role', 'mahasiswa');
        $this->post('/login', ['email' => $m->email, 'password' => 'salah'])->assertSessionHasErrors('email');

        $this->akun('dosen', 'yatim@uji.test'); // akun tanpa profil -> tidak boleh login
        $this->post('/login', ['email' => 'yatim@uji.test', 'password' => 'rahasia'])->assertSessionHasErrors('email');
    }

    public function test_hanya_laboran_mengakses_menu_admin(): void
    {
        $this->sebagai('mahasiswa', $this->mahasiswa())->get(route('kelola-user.index'))->assertForbidden();
        $this->sebagai('staff_prodi', $this->staff())->get(route('kelola-katalog.index'))->assertForbidden();
        $this->sebagai('dosen', $this->dosen())->get(route('pengaturan-lab.index'))->assertForbidden();
        $this->sebagai('dosen', $this->dosen())->get(route('katalog'))->assertOk();
        $this->admin()->get(route('kelola-katalog.index'))->assertOk()->assertSee('Kelola Katalog')->assertDontSee('Inventaris');
    }

    public function test_halaman_kelola_user_semua_kategori_tampil(): void
    {
        $this->mahasiswa();
        $this->dosen();
        $this->staff();
        $admin = $this->admin();
        foreach (['mahasiswa', 'dosen', 'staff_prodi', 'laboran'] as $k) {
            $admin->get(route('kelola-user.index', ['kategori' => $k]))->assertOk();
        }
        $admin->get(route('kelola-user.index', ['kategori' => 'mahasiswa', 'search' => 'Mahasiswa', 'sort' => 'nama_prodi']))->assertOk()->assertDontSee('Kelas');
    }

    // ------------------------------------------------------------------ kelola user (akun + profil atomik)

    public function test_tambah_mahasiswa_membuat_akun_dan_profil_sekaligus(): void
    {
        $prodi = $this->prodi();
        $this->admin()->post(route('kelola-user.store', 'mahasiswa'), [
            'nim' => '2210101001', 'nama' => 'Budi', 'id_prodi' => $prodi->id_prodi, 'email' => 'Budi@MHS.politala.ac.id', 'no_whatsapp' => '', 'password' => 'abcd',
        ])->assertSessionHasNoErrors();

        $akun = Akun::find('budi@mhs.politala.ac.id');
        $this->assertSame('mahasiswa', $akun->role);
        $this->assertSame('abcd', $akun->password);
        $m = Mahasiswa::find('2210101001');
        $this->assertSame('budi@mhs.politala.ac.id', $m->email);
        $this->assertNull($m->no_whatsapp);
    }

    public function test_email_unik_lintas_role_dan_validasi_field(): void
    {
        $dosen = $this->dosen();
        $prodi = $this->prodi();
        $base = ['nuptk_nidn' => 'D999', 'nama' => 'Dosen Baru', 'id_prodi' => $prodi->id_prodi, 'email' => $dosen->email, 'password' => 'abcd'];
        $admin = $this->admin();
        $admin->post(route('kelola-user.store', 'dosen'), $base)->assertSessionHasErrors('email');
        $admin->post(route('kelola-user.store', 'staff_prodi'), ['id_pegawai' => 'P1', 'nama' => 'S', 'id_prodi' => $prodi->id_prodi, 'email' => $dosen->email, 'password' => 'abcd'])->assertSessionHasErrors('email');
        $admin->post(route('kelola-user.store', 'dosen'), ['email' => str_repeat('a', 45) . '@x.test'] + $base)->assertSessionHasErrors('email'); // > 50
        $admin->post(route('kelola-user.store', 'dosen'), ['id_prodi' => 99999, 'email' => 'ok@uji.test'] + $base)->assertSessionHasErrors('id_prodi');
        $admin->post(route('kelola-user.store', 'dosen'), ['password' => ''] + ['email' => 'ok2@uji.test'] + $base)->assertSessionHasErrors('password');
        $this->assertSame(0, Akun::where('email', 'like', 'ok%')->count());
        $this->assertNull(\App\Models\Dosen::find('D999'));
    }

    public function test_laboran_tanpa_prodi_dan_whatsapp_boleh_kosong(): void
    {
        $this->admin()->post(route('kelola-user.store', 'laboran'), ['id_pegawai' => 'LB9', 'nama' => 'Lab Baru', 'email' => 'lab9@uji.test', 'no_whatsapp' => '', 'password' => 'abcd'])->assertSessionHasNoErrors();
        $this->assertSame('laboran', Akun::find('lab9@uji.test')->role);
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('laborans', 'id_prodi'));
    }

    public function test_pembuatan_akun_atomik_gagal_profil_membatalkan_akun(): void
    {
        $prodi = $this->prodi();
        try {
            app(AkunService::class)->buat('mahasiswa', 'atom@uji.test', 'abcd', ['nim' => 'A1', 'nama' => 'A', 'id_prodi' => 999999]); // FK prodi gagal
            $this->fail('Harus gagal');
        } catch (\Illuminate\Database\QueryException) {
        }
        $this->assertNull(Akun::find('atom@uji.test'));
    }

    public function test_ubah_email_dan_password_dan_hapus_akun_yang_masih_dipakai_ditolak(): void
    {
        $m = $this->mahasiswa(['email' => $this->akun('mahasiswa', 'lama@mhs.politala.ac.id')->email]);
        $admin = $this->admin();
        $admin->put(route('kelola-user.update', ['kategori' => 'mahasiswa', 'key' => $m->nim]), [
            'nim' => $m->nim, 'nama' => 'Nama Baru', 'id_prodi' => $m->id_prodi, 'email' => 'baru@mhs.politala.ac.id', 'password' => 'passbaru',
        ])->assertSessionHasNoErrors();
        $this->assertNull(Akun::find('lama@mhs.politala.ac.id'));
        $this->assertSame('passbaru', Akun::find('baru@mhs.politala.ac.id')->password);
        $this->assertSame('baru@mhs.politala.ac.id', $m->fresh()->email); // ikut berubah (ON UPDATE CASCADE)

        $k = $this->katalog();
        $p = Peminjaman::create(['email_peminjam' => 'baru@mhs.politala.ac.id', 'jenis_peminjaman' => 'pribadi', 'tanggal_pengajuan' => now(), 'tanggal_peminjaman' => now(), 'tanggal_rencana_kembali' => now(), 'status' => 'menunggu']);
        PeminjamanDetail::create(['id_peminjaman' => $p->id_peminjaman, 'id_katalog' => $k->id_katalog, 'jumlah' => 1]);
        $admin->delete(route('kelola-user.destroy', ['kategori' => 'mahasiswa', 'key' => $m->nim]))->assertSessionHas('error');
        $this->assertNotNull(Mahasiswa::find($m->nim));
        $this->assertNotNull(Akun::find('baru@mhs.politala.ac.id'));
        $this->assertSame(1, Peminjaman::count()); // histori utuh

        $bebas = $this->mahasiswa();
        $admin->delete(route('kelola-user.destroy', ['kategori' => 'mahasiswa', 'key' => $bebas->nim]))->assertSessionHas('success');
        $this->assertNull(Akun::find($bebas->email));
    }

    // ------------------------------------------------------------------ kelola katalog

    private function katalogPost(array $o = []): array
    {
        return $o + [
            'nama' => 'Kabel UTP', 'id_satuan' => $this->satuan('meter')->id_satuan, 'stok' => 100, 'harga' => '491.80',
            'id_ruangan' => $this->ruangan()->id_ruangan, 'keterangan' => '', 'gambar' => UploadedFile::fake()->image('a.jpg'),
        ];
    }

    public function test_tambah_katalog_valid_dan_aturan_wajib(): void
    {
        $admin = $this->admin();
        $admin->post(route('kelola-katalog.store', 'bahan'), $this->katalogPost())->assertSessionHasNoErrors();
        $k = AlatBahan::where('nama', 'Kabel UTP')->first();
        $this->assertSame(['bahan', 100, '491.80'], [$k->jenis, $k->stok, $k->harga]);
        $this->assertNotSame('', $k->gambar);
        @unlink(public_path(AlatBahan::GAMBAR_DIR . '/' . $k->gambar));

        $admin->post(route('kelola-katalog.store', 'alat'), $this->katalogPost(['gambar' => null]))->assertSessionHasErrors(['gambar', 'nama']); // gambar wajib; nama duplikat
        $admin->post(route('kelola-katalog.store', 'alat'), $this->katalogPost(['nama' => 'X1', 'gambar' => null]))->assertSessionHasErrors('gambar');
        $admin->post(route('kelola-katalog.store', 'alat'), $this->katalogPost(['nama' => 'X2', 'harga' => 0]))->assertSessionHasErrors('harga');
        $admin->post(route('kelola-katalog.store', 'alat'), $this->katalogPost(['nama' => 'X3', 'harga' => -5]))->assertSessionHasErrors('harga');
        $admin->post(route('kelola-katalog.store', 'alat'), $this->katalogPost(['nama' => 'X4', 'stok' => -1]))->assertSessionHasErrors('stok');
        $admin->post(route('kelola-katalog.store', 'alat'), $this->katalogPost(['nama' => 'X5', 'id_ruangan' => '']))->assertSessionHasErrors('id_ruangan');
        $admin->post(route('kelola-katalog.store', 'alat'), $this->katalogPost(['nama' => str_repeat('n', 51)]))->assertSessionHasErrors('nama');
        $this->assertSame(1, AlatBahan::count());
    }

    public function test_nama_katalog_unik_global_antara_alat_dan_bahan(): void
    {
        $this->katalog('alat', 1, ['nama' => 'Solder']);
        $this->admin()->post(route('kelola-katalog.store', 'bahan'), $this->katalogPost(['nama' => 'Solder']))->assertSessionHasErrors('nama');
    }

    public function test_edit_katalog_tanpa_ganti_gambar_dan_jenis_tidak_berubah_setelah_dipinjam(): void
    {
        $k = $this->katalog('alat', 5, ['nama' => 'Tang']);
        $this->admin()->put(route('kelola-katalog.update', ['kategori' => 'alat', 'id' => $k->id_katalog]), $this->katalogPost(['nama' => 'Tang', 'gambar' => null, 'stok' => 9]))->assertSessionHasNoErrors();
        $this->assertSame([9, 'x.jpg'], [$k->fresh()->stok, $k->fresh()->gambar]);

        $p = Peminjaman::create(['email_peminjam' => $this->mahasiswa()->email, 'jenis_peminjaman' => 'pribadi', 'tanggal_pengajuan' => now(), 'tanggal_peminjaman' => now(), 'tanggal_rencana_kembali' => now(), 'status' => 'menunggu']);
        PeminjamanDetail::create(['id_peminjaman' => $p->id_peminjaman, 'id_katalog' => $k->id_katalog, 'jumlah' => 1]);
        $this->expectException(\DomainException::class);
        $k->fresh()->update(['jenis' => 'bahan']);
    }

    public function test_jenis_bebas_diubah_sebelum_pernah_dipinjam(): void
    {
        $k = $this->katalog('alat');
        $k->update(['jenis' => 'bahan']);
        $this->assertSame('bahan', $k->fresh()->jenis);
    }

    public function test_hapus_katalog_dan_ruangan_yang_dipakai_ditolak(): void
    {
        $k = $this->katalog('alat');
        $admin = $this->admin();
        $admin->delete(route('kelola-katalog.destroy', ['kategori' => 'ruangan', 'id' => $k->id_ruangan]))->assertSessionHas('error');
        $this->assertNotNull(Ruangan::find($k->id_ruangan));

        $p = Peminjaman::create(['email_peminjam' => $this->mahasiswa()->email, 'jenis_peminjaman' => 'pribadi', 'tanggal_pengajuan' => now(), 'tanggal_peminjaman' => now(), 'tanggal_rencana_kembali' => now(), 'status' => 'menunggu']);
        PeminjamanDetail::create(['id_peminjaman' => $p->id_peminjaman, 'id_katalog' => $k->id_katalog, 'jumlah' => 1]);
        $admin->delete(route('kelola-katalog.destroy', ['kategori' => 'alat', 'id' => $k->id_katalog]))->assertSessionHas('error');
        $this->assertNotNull(AlatBahan::find($k->id_katalog));

        $bebas = $this->katalog('bahan');
        $admin->delete(route('kelola-katalog.destroy', ['kategori' => 'bahan', 'id' => $bebas->id_katalog]))->assertSessionHas('success');
    }

    public function test_ruangan_crud_unik_dan_tab_tampil(): void
    {
        $admin = $this->admin();
        $admin->post(route('kelola-katalog.store', 'ruangan'), ['nama_ruangan' => 'Lab Jaringan', 'keterangan' => 'Lt 2'])->assertSessionHasNoErrors();
        $admin->post(route('kelola-katalog.store', 'ruangan'), ['nama_ruangan' => 'Lab Jaringan'])->assertSessionHasErrors('nama_ruangan');
        foreach (['alat', 'bahan', 'ruangan'] as $tab) {
            $admin->get(route('kelola-katalog.index', ['kategori' => $tab]))->assertOk();
            $admin->get(route('katalog', ['jenis' => $tab]))->assertOk();
        }
        $admin->get(route('kelola-katalog.index', ['kategori' => 'ruangan']))->assertSee('Lab Jaringan');
    }

    public function test_katalog_tampil_harga_per_satuan(): void
    {
        $this->katalog('bahan', 3, ['nama' => 'Kabel UTP', 'harga' => '491.80', 'id_satuan' => $this->satuan('meter')->id_satuan]);
        $this->admin()->get(route('kelola-katalog.index', ['kategori' => 'bahan']))->assertSee('491,80');
        $this->admin()->get(route('katalog'))->assertSee('491,80')->assertSee('meter');
    }

    // ------------------------------------------------------------------ master

    public function test_pengaturan_laboratorium_satuan_dan_tab_operasional(): void
    {
        $admin = $this->admin();
        $admin->post(route('pengaturan-lab.satuan.store'), ['nama' => 'liter'])->assertSessionHasNoErrors();
        $admin->post(route('pengaturan-lab.satuan.store'), ['nama' => 'liter'])->assertSessionHasErrors('nama');
        foreach (['satuan', 'operasional', 'kelas'] as $tab) { // tab tak dikenal (mis. kelas) jatuh ke Satuan
            $admin->get(route('pengaturan-lab.index', ['tab' => $tab]))->assertOk()->assertDontSee('Kelas');
        }
        $admin->put(route('pengaturan-lab.operasional.update'), ['jam_buka' => '09:00', 'jam_tutup' => '15:00', 'hari' => ['senin', 'selasa']])->assertSessionHasNoErrors();
        $this->assertSame('09:00:00', \App\Models\PengaturanOperasional::ambil()->jam_buka);
        $admin->put(route('pengaturan-lab.operasional.update'), ['jam_buka' => '15:00', 'jam_tutup' => '09:00', 'hari' => ['senin']])->assertSessionHasErrors('jam_tutup');
    }

    public function test_kelas_hanya_dikelola_di_data_akademik_dan_url_lama_hilang(): void
    {
        $prodi = $this->prodi();
        $admin = $this->admin();
        $admin->post(route('data-akademik.store', 'kelas'), ['nama_kelas' => '1A', 'id_prodi' => $prodi->id_prodi])->assertSessionHasNoErrors();
        $this->assertSame(1, Kelas::where('nama_kelas', '1A')->count());
        $admin->get('/master-data')->assertNotFound();
        $admin->get('/pengaturan-operasional')->assertNotFound();
        $admin->post('/pengaturan-lab/kelas', ['nama' => '2A'])->assertNotFound();
    }

    public function test_tab_dosen_data_akademik_tidak_untuk_laboran_tetapi_untuk_staf(): void
    {
        $dosen = $this->dosen();
        $admin = $this->admin();
        $admin->get(route('data-akademik.index'))->assertOk()->assertDontSee('tab=dosen', false);
        $admin->get(route('data-akademik.index', ['tab' => 'dosen']))->assertOk()->assertDontSee($dosen->nama);
        $admin->post(route('data-akademik.store', 'dosen'), [])->assertForbidden();
        $admin->put(route('data-akademik.update', ['tab' => 'dosen', 'id' => $dosen->getKey()]), [])->assertForbidden();
        $admin->delete(route('data-akademik.destroy', ['tab' => 'dosen', 'id' => $dosen->getKey()]))->assertForbidden();
        $this->assertDatabaseHas('dosens', ['nuptk_nidn' => $dosen->nuptk_nidn]);

        $staf = $this->sebagai('staff_prodi', $this->staff());
        $staf->get(route('data-akademik.index'))->assertOk()->assertSee('tab=dosen', false);
        $staf->get(route('data-akademik.index', ['tab' => 'dosen']))->assertOk()->assertSee($dosen->nama);
    }

    public function test_laboran_mengelola_status_dosen_lewat_kelola_data_user(): void
    {
        $dosen = $this->dosen();
        $admin = $this->admin();
        $admin->put(route('kelola-user.update', ['kategori' => 'dosen', 'key' => $dosen->getKey()]), [
            'nuptk_nidn' => $dosen->nuptk_nidn, 'nama' => $dosen->nama, 'id_prodi' => $dosen->id_prodi, 'email' => $dosen->email, 'no_whatsapp' => '', 'status' => 'nonaktif',
        ])->assertSessionHasNoErrors();
        $this->assertSame('nonaktif', $dosen->fresh()->status);
        $admin->put(route('kelola-user.update', ['kategori' => 'dosen', 'key' => $dosen->getKey()]), [
            'nuptk_nidn' => $dosen->nuptk_nidn, 'nama' => $dosen->nama, 'id_prodi' => $dosen->id_prodi, 'email' => $dosen->email, 'status' => 'lain',
        ])->assertSessionHasErrors('status');
    }

    public function test_master_yang_dipakai_tidak_dapat_dihapus(): void
    {
        $k = $this->katalog();
        $admin = $this->admin();
        $admin->delete(route('pengaturan-lab.satuan.destroy', ['id' => $k->id_satuan]))->assertSessionHas('error');
        $this->assertNotNull(Satuan::find($k->id_satuan));

        $kelas = $this->kelas();
        $this->jadwal($this->ruangan(), 'senin', '08:00:00', '09:00:00', ['id_kelas' => $kelas->id_kelas]);
        $admin->delete(route('data-akademik.destroy', ['tab' => 'kelas', 'id' => $kelas->id_kelas]))->assertSessionHas('error');
        $this->assertNotNull(Kelas::find($kelas->id_kelas));
    }
}
