<?php

namespace Tests\Feature;

use App\Models\PengaturanOperasional;
use App\Models\Peminjaman;
use App\Models\PeminjamanDetail;
use App\Models\PeminjamanRuangan;
use App\Services\PeminjamanService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

/** Validasi waktu pengajuan: satu hari operasional, jam & hari dari Pengaturan Operasional (default Senin–Jumat 08:00–16:00). */
class PeminjamanWaktuTest extends TestCase
{
    use MembuatData, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2030-01-07 07:00:00'); // Senin; 2030-01-08 Selasa, 12 Sabtu, 13 Minggu
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** @return array<string,array{0:string,1:string,2:list<string>}> field yang harus gagal ([] = lolos) */
    public static function skenario(): array
    {
        return [
            '08-09 lolos' => ['2030-01-08 08:00', '2030-01-08 09:00', []],
            '15-16 lolos' => ['2030-01-08 15:00', '2030-01-08 16:00', []],
            '18-19 di luar jam' => ['2030-01-08 18:00', '2030-01-08 19:00', ['tanggal_peminjaman']],
            '08-17 lewat tutup' => ['2030-01-08 08:00', '2030-01-08 17:00', ['tanggal_rencana_kembali']],
            '18-17 di luar jam dan tidak berurutan' => ['2030-01-08 18:00', '2030-01-08 17:00', ['tanggal_peminjaman', 'tanggal_rencana_kembali']],
            '10-10 selesai tidak lebih besar' => ['2030-01-08 10:00', '2030-01-08 10:00', ['tanggal_rencana_kembali']],
            '15 lintas hari' => ['2030-01-08 15:00', '2030-01-09 09:00', ['tanggal_rencana_kembali']],
            '08-16:01' => ['2030-01-08 08:00', '2030-01-08 16:01', ['tanggal_rencana_kembali']],
            '07:59 sebelum buka' => ['2030-01-08 07:59', '2030-01-08 10:00', ['tanggal_peminjaman']],
            'Sabtu libur' => ['2030-01-12 09:00', '2030-01-12 10:00', ['tanggal_peminjaman']],
            'Minggu libur' => ['2030-01-13 09:00', '2030-01-13 10:00', ['tanggal_peminjaman']],
        ];
    }

    private function payload(string $mulai, string $selesai, string $jenis = 'ruangan'): array
    {
        $item = match ($jenis) {
            'ruangan' => ['ruangan' => [['id_ruangan' => $this->ruangan()->id_ruangan]]],
            'alat' => ['detail' => [['id_katalog' => $this->katalog('alat', 5)->id_katalog, 'jumlah' => 1]]],
            'bahan' => ['detail' => [['id_katalog' => $this->katalog('bahan', 5)->id_katalog, 'jumlah' => 1]]],
            'gabungan' => ['ruangan' => [['id_ruangan' => $this->ruangan()->id_ruangan]], 'detail' => [['id_katalog' => $this->katalog('alat', 5)->id_katalog, 'jumlah' => 1]]],
        };

        return $item + ['jenis_peminjaman' => 'pribadi', 'keterangan' => 'Praktikum', 'tanggal_peminjaman' => $mulai, 'tanggal_rencana_kembali' => $selesai];
    }

    private function tidakAdaData(): void
    {
        $this->assertSame([0, 0, 0], [Peminjaman::count(), PeminjamanDetail::count(), PeminjamanRuangan::count()]);
    }

    private function sebagai(string $role, $profil): self
    {
        return $this->withSession(['silab_user' => ['role' => $role, 'key' => (string) $profil->getKey(), 'nama' => $profil->nama, 'email' => $profil->email]]);
    }

    #[DataProvider('skenario')]
    public function test_service_menolak_waktu_tidak_valid_dan_tidak_menyimpan_apa_pun(string $mulai, string $selesai, array $gagal): void
    {
        $svc = app(PeminjamanService::class);
        foreach (['mahasiswa' => $this->mahasiswa(), 'dosen' => $this->dosen()] as $profil) {
            foreach (['ruangan', 'alat', 'bahan', 'gabungan'] as $jenis) {
                $data = $this->payload($mulai, $selesai, $jenis);
                if ($gagal === []) {
                    $this->assertSame('menunggu', $svc->ajukan($profil->email, $data)->status);
                    continue;
                }
                try {
                    $svc->ajukan($profil->email, $data);
                    $this->fail("Seharusnya ditolak: $mulai – $selesai ($jenis)");
                } catch (ValidationException $e) {
                    foreach ($gagal as $field) {
                        $this->assertArrayHasKey($field, $e->errors());
                    }
                }
            }
        }
        if ($gagal !== []) {
            $this->tidakAdaData();
        }
    }

    #[DataProvider('skenario')]
    public function test_http_langsung_mahasiswa_dan_dosen(string $mulai, string $selesai, array $gagal): void
    {
        foreach (['mahasiswa' => $this->mahasiswa(), 'dosen' => $this->dosen()] as $role => $profil) {
            $this->sebagai($role, $profil);
            $res = $this->post(route('peminjaman.store'), $this->payload($mulai, $selesai, 'gabungan'));
            if ($gagal === []) {
                $res->assertSessionHasNoErrors()->assertRedirect(route('peminjaman.riwayat'));
                continue;
            }
            $res->assertSessionHasErrors($gagal)->assertSessionMissing('success');
        }
        if ($gagal !== []) {
            $this->tidakAdaData(); // tidak ada transaksi parsial
        }
        $this->assertSame($gagal === [] ? 2 : 0, Peminjaman::count());
    }

    public function test_jam_18_selesai_17_tidak_menghasilkan_transaksi_baru(): void
    {
        $this->sebagai('mahasiswa', $this->mahasiswa())
            ->post(route('peminjaman.store'), $this->payload('2030-01-08 18:00', '2030-01-08 17:00', 'ruangan'))
            ->assertSessionHasErrors(['tanggal_peminjaman', 'tanggal_rencana_kembali']);
        $this->tidakAdaData();
    }

    public function test_tanggal_kosong_atau_format_salah_ditolak(): void
    {
        $this->sebagai('mahasiswa', $this->mahasiswa());
        foreach ([['', ''], ['bukan tanggal', '2030-01-08 10:00'], ['2030-01-08 09:00', 'xx']] as [$a, $b]) {
            $this->post(route('peminjaman.store'), $this->payload($a, $b))->assertSessionHasErrors();
        }
        $this->tidakAdaData();
    }

    public function test_perubahan_jam_dan_hari_operasional_dipakai_sebagai_aturan(): void
    {
        $m = $this->sebagai('mahasiswa', $this->mahasiswa());
        PengaturanOperasional::ambil()->update(['jam_buka' => '09:00:00', 'jam_tutup' => '12:00:00', 'hari_operasional' => 'selasa']);

        $m->post(route('peminjaman.store'), $this->payload('2030-01-08 08:30', '2030-01-08 10:00'))->assertSessionHasErrors('tanggal_peminjaman');
        $m->post(route('peminjaman.store'), $this->payload('2030-01-08 10:00', '2030-01-08 12:30'))->assertSessionHasErrors('tanggal_rencana_kembali');
        $m->post(route('peminjaman.store'), $this->payload('2030-01-09 10:00', '2030-01-09 11:00'))->assertSessionHasErrors('tanggal_peminjaman'); // Rabu nonaktif
        $this->tidakAdaData();

        $m->post(route('peminjaman.store'), $this->payload('2030-01-08 10:00', '2030-01-08 12:00'))->assertSessionHasNoErrors();
        $this->assertSame(1, Peminjaman::count());
    }

    public function test_form_menampilkan_batas_dan_default_satu_hari_untuk_kedua_role(): void
    {
        foreach (['mahasiswa' => $this->mahasiswa(), 'dosen' => $this->dosen()] as $role => $profil) {
            $this->sebagai($role, $profil)->get(route('peminjaman.index'))->assertOk()
                ->assertSee('value="2030-01-07T08:00"', false) // default: waktu operasional paling awal (hari ini 07:00 -> buka 08:00)
                ->assertSee('value="2030-01-07T09:00"', false) // selesai default +1 jam, tanggal sama
                ->assertSee('min="2030-01-07T08:00"', false);
        }
    }
}
