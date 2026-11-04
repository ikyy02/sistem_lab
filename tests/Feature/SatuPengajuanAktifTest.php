<?php

namespace Tests\Feature;

use App\Models\Peminjaman;
use App\Services\PeminjamanService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

/** Aturan: Mahasiswa dan Dosen hanya boleh punya satu peminjaman aktif (menunggu/disetujui). */
class SatuPengajuanAktifTest extends TestCase
{
    use MembuatData, RefreshDatabase;

    private PeminjamanService $svc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->svc = app(PeminjamanService::class);
        Carbon::setTestNow('2030-01-07 07:00:00'); // Senin
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function ajukan(string $email, ?int $idKatalog = null, string $jenis = 'pribadi'): Peminjaman
    {
        $k = $idKatalog ?? $this->katalog('alat', 10)->id_katalog;

        return $this->svc->ajukan($email, [
            'jenis_peminjaman' => $jenis, 'tanggal_peminjaman' => '2030-01-08 08:00', 'tanggal_rencana_kembali' => '2030-01-08 10:00',
            'detail' => [['id_katalog' => $k, 'jumlah' => 1]], 'ruangan' => [],
        ]);
    }

    private function harusGagal(callable $fn): void
    {
        try {
            $fn();
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('status', $e->errors());

            return;
        }
        $this->fail('ValidationException diharapkan.');
    }

    /** @return array<string,array{0:string}> */
    public static function peran(): array
    {
        return ['mahasiswa' => ['mahasiswa'], 'dosen' => ['dosen']];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('peran')]
    public function test_menunggu_atau_disetujui_menolak_pengajuan_baru(string $role): void
    {
        $email = $this->{$role}()->email;
        $lab = $this->laboran();
        $p = $this->ajukan($email);

        $this->harusGagal(fn () => $this->ajukan($email)); // menunggu
        $this->svc->setujui($p->id_peminjaman, $lab->email);
        $this->harusGagal(fn () => $this->ajukan($email)); // disetujui
        $this->assertSame(1, Peminjaman::where('email_peminjam', $email)->count());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('peran')]
    public function test_ditolak_dibatalkan_atau_selesai_membebaskan_pengguna(string $role): void
    {
        $email = $this->{$role}()->email;
        $lab = $this->laboran();

        $a = $this->ajukan($email);
        $this->svc->tolak($a->id_peminjaman, $lab->email, 'uji');
        $b = $this->ajukan($email); // setelah ditolak
        $this->svc->batalkan($b->id_peminjaman, $email);
        $c = $this->ajukan($email); // setelah dibatalkan
        $c->update(['status' => 'selesai']);
        $d = $this->ajukan($email); // setelah selesai

        $this->assertSame('menunggu', $d->status);
        $this->assertSame(4, Peminjaman::where('email_peminjam', $email)->count());
    }

    public function test_pengguna_lain_tidak_terpengaruh(): void
    {
        $this->ajukan($this->mahasiswa()->email);

        $this->assertSame('menunggu', $this->ajukan($this->mahasiswa()->email)->status);
    }

    public function test_batal_barang_menunggu_berhasil_stok_tetap_dan_disetujui_ditolak(): void
    {
        $k = $this->katalog('alat', 10);
        $lab = $this->laboran();
        $m = $this->mahasiswa()->email;

        $p = $this->ajukan($m, $k->id_katalog);
        $this->assertSame('dibatalkan', $this->svc->batalkan($p->id_peminjaman, $m)->status);
        $this->assertSame(10, $k->fresh()->stok); // stok belum dipotong saat menunggu

        $q = $this->ajukan($m, $k->id_katalog);
        $this->svc->setujui($q->id_peminjaman, $lab->email);
        $this->assertSame(9, $k->fresh()->stok);
        $this->harusGagal(fn () => $this->svc->batalkan($q->id_peminjaman, $m)); // barang saja, sudah disetujui
        $this->assertSame(['disetujui', 9], [$q->fresh()->status, $k->fresh()->stok]);
    }

    public function test_batal_ruangan_menunggu_tidak_dibatasi_h_min_1_tetapi_disetujui_dibatasi(): void
    {
        $r = $this->ruangan();
        $lab = $this->laboran();
        $m = $this->mahasiswa()->email;
        $data = ['jenis_peminjaman' => 'pribadi', 'tanggal_peminjaman' => '2030-01-08 08:00', 'tanggal_rencana_kembali' => '2030-01-08 10:00',
            'detail' => [], 'ruangan' => [['id_ruangan' => $r->id_ruangan]]];

        $p = $this->svc->ajukan($m, $data);
        $this->assertSame('dibatalkan', $this->svc->batalkan($p->id_peminjaman, $m, Carbon::parse('2030-01-08 09:00'))->status); // hari pelaksanaan, masih menunggu

        $q = $this->svc->ajukan($m, $data);
        $this->svc->setujui($q->id_peminjaman, $lab->email);
        $this->harusGagal(fn () => $this->svc->batalkan($q->id_peminjaman, $m, Carbon::parse('2030-01-08 00:00'))); // disetujui: lewat H-1
        $this->assertSame('dibatalkan', $this->svc->batalkan($q->id_peminjaman, $m, Carbon::parse('2030-01-07 23:59'))->status);
    }

    public function test_halaman_ajukan_menampilkan_pemberitahuan_dan_menonaktifkan_form(): void
    {
        $m = $this->mahasiswa();
        $p = $this->ajukan($m->email);
        $sebagai = $this->withSession(['silab_user' => ['role' => 'mahasiswa', 'key' => (string) $m->getKey(), 'nama' => $m->nama, 'email' => $m->email]]);

        $sebagai->get(route('peminjaman.index'))->assertOk()
            ->assertSee('Anda masih memiliki peminjaman yang berjalan')
            ->assertSee(route('peminjaman.show', $p->id_peminjaman), false)
            ->assertSee('<fieldset disabled', false);
        $sebagai->post(route('peminjaman.store'), [
            'jenis_peminjaman' => 'pribadi', 'keterangan' => 'Lagi', 'tanggal_peminjaman' => '2030-01-08 08:00', 'tanggal_rencana_kembali' => '2030-01-08 10:00',
            'detail' => [['id_katalog' => $this->katalog('alat', 5)->id_katalog, 'jumlah' => 1]],
        ])->assertSessionHasErrors('status');
        $this->assertSame(1, Peminjaman::count());
    }
}
