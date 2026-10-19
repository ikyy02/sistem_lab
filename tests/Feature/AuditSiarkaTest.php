<?php

namespace Tests\Feature;

use App\Models\Akun;
use App\Models\Peminjaman;
use App\Services\PeminjamanService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

/** Regresi audit: keamanan login (throttle, sesi), aturan satu hari operasional, dan tombol Selesai. */
class AuditSiarkaTest extends TestCase
{
    use MembuatData, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2030-01-07 07:00:00'); // Senin; operasional default Senin–Jumat 08:00–16:00
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function sebagai(string $role, $profil): self
    {
        return $this->withSession(['silab_user' => ['role' => $role, 'key' => (string) $profil->getKey(), 'nama' => $profil->nama, 'email' => $profil->email]]);
    }

    private function ajukan(string $email, string $mulai, string $selesai, array $o = [])
    {
        return app(PeminjamanService::class)->ajukan($email, $o + [
            'jenis_peminjaman' => 'pribadi', 'tanggal_peminjaman' => $mulai, 'tanggal_rencana_kembali' => $selesai,
            'detail' => [['id_katalog' => $this->katalog('alat', 5)->id_katalog, 'jumlah' => 1]], 'ruangan' => [],
        ]);
    }

    private function harusGagal(callable $fn, string $field): void
    {
        try {
            $fn();
        } catch (ValidationException $e) {
            $this->assertArrayHasKey($field, $e->errors());

            return;
        }
        $this->fail("ValidationException ($field) diharapkan.");
    }

    // ---------------------------------------------------------------- keamanan login

    public function test_login_dibatasi_setelah_lima_percobaan_gagal(): void
    {
        $m = $this->mahasiswa();
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $m->email, 'password' => 'salah'])->assertSessionHasErrors('email');
        }
        $this->post('/login', ['email' => $m->email, 'password' => 'rahasia'])->assertSessionHasErrors('email')->assertSessionMissing('silab_user');
    }

    public function test_sesi_tidak_berlaku_bila_akun_dihapus_atau_role_berubah(): void
    {
        $m = $this->mahasiswa();
        $this->sebagai('mahasiswa', $m)->get(route('dashboard'))->assertOk();
        Akun::where('email', $m->email)->update(['role' => 'dosen']);
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    // ---------------------------------------------------------------- satu hari operasional

    public function test_semua_role_dan_jenis_sumber_daya_dibatasi_satu_hari_operasional(): void
    {
        $m = $this->mahasiswa()->email;
        $d = $this->dosen()->email;

        $this->assertSame('menunggu', $this->ajukan($m, '2030-01-08 08:00', '2030-01-08 16:00')->status); // tepat jam tutup boleh
        $this->harusGagal(fn () => $this->ajukan($m, '2030-01-08 08:00', '2030-01-08 16:01'), 'tanggal_rencana_kembali'); // lewat 1 menit
        $this->harusGagal(fn () => $this->ajukan($m, '2030-01-08 15:00', '2030-01-09 09:00'), 'tanggal_rencana_kembali'); // lintas hari
        $this->harusGagal(fn () => $this->ajukan($m, '2030-01-08 07:59', '2030-01-08 10:00'), 'tanggal_peminjaman'); // sebelum buka
        $this->harusGagal(fn () => $this->ajukan($m, '2030-01-12 08:00', '2030-01-12 10:00'), 'tanggal_peminjaman'); // Sabtu: bukan hari operasional
        $this->harusGagal(fn () => $this->ajukan($d, '2030-01-08 09:00', '2030-01-09 09:00'), 'tanggal_rencana_kembali');

        $r = ['ruangan' => [['id_ruangan' => $this->ruangan()->id_ruangan]], 'detail' => []];
        $this->harusGagal(fn () => $this->ajukan($m, '2030-01-08 09:00', '2030-01-09 09:00', $r), 'tanggal_rencana_kembali'); // ruangan saja
        $gabung = $r + [];
        $gabung['detail'] = [['id_katalog' => $this->katalog('bahan', 5)->id_katalog, 'jumlah' => 1]];
        $this->harusGagal(fn () => $this->ajukan($m, '2030-01-08 09:00', '2030-01-09 09:00', $gabung), 'tanggal_rencana_kembali'); // gabungan
        $this->assertSame('menunggu', $this->ajukan($m, '2030-01-08 09:00', '2030-01-08 11:00', $gabung)->status);
    }

    // ---------------------------------------------------------------- tombol Selesai (tanpa syarat waktu)

    private function ruanganDisetujui($peminjam, string $mulai, string $selesai, string $jenis = 'pribadi', array $o = []): Peminjaman
    {
        $p = Peminjaman::create($o + [
            'email_peminjam' => $peminjam->email, 'jenis_peminjaman' => $jenis, 'tanggal_pengajuan' => now()->subDay(),
            'tanggal_peminjaman' => $mulai, 'tanggal_rencana_kembali' => $selesai, 'status' => 'disetujui',
        ]);
        $p->ruangans()->create(['id_ruangan' => $this->ruangan()->id_ruangan, 'tanggal_mulai' => $mulai, 'tanggal_selesai' => $selesai]);

        return $p;
    }

    public function test_ruangan_saja_selesai_kapan_saja_baik_kegiatan_belum_mulai_maupun_sudah_lewat_dan_tanggal_aneh(): void
    {
        $m = $this->mahasiswa();
        $depan = $this->ruanganDisetujui($m, '2030-01-09 08:00', '2030-01-09 15:00'); // sekarang 2030-01-07 07:00
        $aneh = $this->ruanganDisetujui($m, '2030-01-07 08:00', '2030-01-07 15:00');
        $aneh->ruangans()->update(['tanggal_selesai' => '2031-09-10 05:00']); // data ruangan tidak selaras dengan peminjaman
        $lewat = $this->ruanganDisetujui($m, '2030-01-03 08:00', '2030-01-03 15:00');

        $this->sebagai('mahasiswa', $m)->get(route('peminjaman.show', $depan->id_peminjaman))
            ->assertOk()->assertDontSee('setelah kegiatan berakhir', false)->assertSee('kapan saja', false);
        foreach ([$depan, $aneh, $lewat] as $p) {
            $this->post(route('peminjaman.selesai', $p->id_peminjaman))->assertSessionHasNoErrors();
            $this->assertSame('selesai', $p->fresh()->status);
        }
    }

    public function test_selesai_ditolak_untuk_pengguna_tidak_berwenang_dan_status_selain_disetujui_dan_ulang_tidak_mengubah_apa_pun(): void
    {
        $m = $this->mahasiswa();
        $p = $this->ruanganDisetujui($m, '2030-01-09 08:00', '2030-01-09 15:00');
        $this->sebagai('mahasiswa', $this->mahasiswa())->post(route('peminjaman.selesai', $p->id_peminjaman))->assertSessionHasErrors('status');
        $this->sebagai('dosen', $this->dosen())->post(route('peminjaman.selesai', $p->id_peminjaman))->assertSessionHasErrors('status');
        $this->assertSame('disetujui', $p->fresh()->status);

        $this->sebagai('mahasiswa', $m)->post(route('peminjaman.selesai', $p->id_peminjaman))->assertSessionHasNoErrors();
        $this->post(route('peminjaman.selesai', $p->id_peminjaman))->assertSessionHasErrors('status'); // sudah selesai
        $this->assertSame('selesai', $p->fresh()->status);

        foreach (['menunggu', 'ditolak', 'dibatalkan'] as $status) {
            $q = $this->ruanganDisetujui($m, '2030-01-09 08:00', '2030-01-09 15:00', 'pribadi', ['status' => $status]);
            $this->post(route('peminjaman.selesai', $q->id_peminjaman))->assertSessionHasErrors('status');
            $this->assertSame($status, $q->fresh()->status);
        }
    }

    public function test_gabungan_ruangan_dan_alat_hanya_laboran_dan_menunggu_pengembalian_alat_bukan_waktu(): void
    {
        $m = $this->mahasiswa();
        $lab = $this->laboran();
        $alat = $this->katalog('alat', 10);
        $p = $this->ruanganDisetujui($m, '2030-01-09 08:00', '2030-01-09 15:00'); // kegiatan masih di masa depan
        $d = $p->details()->create(['id_katalog' => $alat->id_katalog, 'jumlah' => 2]);

        $this->sebagai('mahasiswa', $m)->post(route('peminjaman.selesai', $p->id_peminjaman))->assertSessionHasErrors('status'); // bukan Laboran
        $this->sebagai('laboran', $lab)->post(route('peminjaman.selesai', $p->id_peminjaman))->assertSessionHasErrors('status'); // alat belum kembali
        $this->assertSame('disetujui', $p->fresh()->status);

        app(PeminjamanService::class)->kembalikan($p->id_peminjaman, $lab->email, [['id_detail_peminjaman' => $d->id_detail, 'jumlah_dikembalikan' => 2, 'kondisi' => 'baik']]);
        $stok = $alat->fresh()->stok;
        $this->post(route('peminjaman.selesai', $p->id_peminjaman))->assertSessionHasNoErrors();
        $this->assertSame('selesai', $p->fresh()->status);
        $this->post(route('peminjaman.selesai', $p->id_peminjaman))->assertSessionHasErrors('status');
        $this->assertSame($stok, $alat->fresh()->stok); // klik ulang tidak mengubah stok
    }
}
