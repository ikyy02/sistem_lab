<?php

namespace Tests\Feature;

use App\Models\Jadwal;
use App\Models\PeriodeJadwal;
use App\Services\JadwalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

/** Periode berlaku jadwal: periode baru mengarsipkan periode lama; jadwal arsip hanya-baca. */
class PeriodeJadwalTest extends TestCase
{
    use MembuatData, RefreshDatabase;

    private function sebagaiStaff(): self
    {
        $s = $this->staff();

        return $this->withSession(['silab_user' => ['role' => 'staff_prodi', 'key' => (string) $s->getKey(), 'nama' => $s->nama, 'email' => $s->email]]);
    }

    public function test_periode_baru_mengarsipkan_periode_lama_tanpa_menimpa_jadwal(): void
    {
        $lama = PeriodeJadwal::create(['nama' => 'Ganjil 2026/2027 - Paruh 1', 'tanggal_mulai' => '2026-08-17', 'tanggal_berakhir' => '2026-10-31', 'status' => 'berjalan']);
        $j = $this->jadwal($this->ruangan(), 'senin', '08:00', '10:00', ['id_periode' => $lama->id_periode]);

        $this->sebagaiStaff()->post(route('jadwal.periode.store'), [
            'nama' => 'Ganjil 2026/2027 - Paruh 2', 'tanggal_mulai' => '2026-11-02', 'tanggal_berakhir' => '2027-01-31',
        ])->assertRedirect(route('jadwal.periode.index'));

        $this->assertSame('arsip', $lama->fresh()->status);
        $this->assertSame(1, PeriodeJadwal::where('status', 'berjalan')->count());
        $this->assertSame($lama->id_periode, $j->fresh()->id_periode); // jadwal lama tetap pada periodenya
        $this->assertTrue($j->fresh()->terarsip());
    }

    public function test_periode_baru_tidak_boleh_tumpang_tindih(): void
    {
        PeriodeJadwal::create(['nama' => 'P1', 'tanggal_mulai' => '2026-08-17', 'tanggal_berakhir' => '2026-10-31', 'status' => 'berjalan']);
        $this->sebagaiStaff()->post(route('jadwal.periode.store'), ['nama' => 'P2', 'tanggal_mulai' => '2026-10-31', 'tanggal_berakhir' => '2027-01-31'])
            ->assertSessionHasErrors('tanggal_mulai');
        $this->assertSame(1, PeriodeJadwal::count());
    }

    public function test_jadwal_arsip_tidak_dapat_diubah_atau_dihapus(): void
    {
        $arsip = PeriodeJadwal::create(['nama' => 'Lama', 'tanggal_mulai' => '2026-02-01', 'tanggal_berakhir' => '2026-07-31', 'status' => 'arsip']);
        $j = $this->jadwal($this->ruangan(), 'selasa', '08:00', '10:00', ['id_periode' => $arsip->id_periode]);

        $this->sebagaiStaff();
        $this->get(route('jadwal.show', $j))->assertOk();
        $this->get(route('jadwal.edit', $j))->assertForbidden();
        $this->delete(route('jadwal.destroy', $j))->assertForbidden();
        $this->assertNotNull(Jadwal::find($j->id_jadwal));

        $this->expectException(ValidationException::class);
        app(JadwalService::class)->simpan([
            'id_kelas' => $j->id_kelas, 'nuptk_nidn' => $j->nuptk_nidn, 'id_ruangan' => $j->id_ruangan, 'hari' => 'rabu',
            'jam_mulai' => '08:00', 'jam_selesai' => '10:00', 'status' => 'aktif', 'tahun_akademik' => '2026/2027', 'semester' => 'ganjil',
        ], $j);
    }

    public function test_jadwal_baru_masuk_periode_berjalan_dan_arsip_tidak_memblokir_ruangan(): void
    {
        $arsip = PeriodeJadwal::create(['nama' => 'Lama', 'tanggal_mulai' => '2026-08-17', 'tanggal_berakhir' => '2026-10-31', 'status' => 'arsip']);
        $baru = PeriodeJadwal::create(['nama' => 'Baru', 'tanggal_mulai' => '2026-11-02', 'tanggal_berakhir' => '2027-01-31', 'status' => 'berjalan']);
        $r = $this->ruangan();
        $this->jadwal($r, 'senin', '08:00', '10:00', ['id_periode' => $arsip->id_periode]);

        // jam yang sama pada ruangan yang sama di periode berjalan tidak dianggap bentrok dengan arsip
        $j = app(JadwalService::class)->simpan([
            'id_kelas' => $this->kelas()->id_kelas, 'nuptk_nidn' => $this->dosen()->nuptk_nidn, 'id_ruangan' => $r->id_ruangan, 'hari' => 'senin',
            'jam_mulai' => '08:00', 'jam_selesai' => '10:00', 'status' => 'aktif', 'tahun_akademik' => '2026/2027', 'semester' => 'ganjil',
        ]);
        $this->assertSame($baru->id_periode, $j->id_periode);
    }
}
