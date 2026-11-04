<?php

namespace Tests\Feature;

use App\Models\Jadwal;
use App\Services\JadwalService;
use App\Services\PeminjamanService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

class JadwalTpkTest extends TestCase
{
    use MembuatData, RefreshDatabase;

    private function payload($r, array $o = []): array
    {
        return $o + [
            'id_mata_kuliah' => $this->mataKuliah()->id_mata_kuliah, 'id_kelas' => $this->kelas()->id_kelas, 'nuptk_nidn' => $this->dosen()->nuptk_nidn, 'id_ruangan' => $r->id_ruangan,
            'hari' => 'senin', 'jam_mulai' => '08:00', 'jam_selesai' => '10:00', 'status' => 'aktif',
            'tahun_akademik' => '2026/2027', 'semester' => 'ganjil',
        ];
    }

    private function gagal(callable $fn, string $field): void
    {
        try {
            $fn();
        } catch (ValidationException $e) {
            $this->assertArrayHasKey($field, $e->errors());

            return;
        }
        $this->fail("ValidationException ({$field}) diharapkan.");
    }

    public function test_jadwal_jam_mulai_harus_sebelum_selesai(): void
    {
        $r = $this->ruangan();
        $svc = app(JadwalService::class);
        $this->gagal(fn () => $svc->simpan($this->payload($r, ['jam_mulai' => '10:00', 'jam_selesai' => '10:00'])), 'jam_selesai');
        $this->gagal(fn () => $svc->simpan($this->payload($r, ['jam_mulai' => '11:00', 'jam_selesai' => '10:00'])), 'jam_selesai');
        $this->gagal(fn () => $svc->simpan($this->payload($r, ['tahun_akademik' => '2026/2028'])), 'tahun_akademik');
        $this->gagal(fn () => $svc->simpan($this->payload($r, ['hari' => 'minggu'])), 'hari');
        $this->assertSame(0, Jadwal::count());
    }

    public function test_konflik_jadwal_aktif_pada_ruangan_hari_tahun_semester_sama(): void
    {
        $r = $this->ruangan();
        $svc = app(JadwalService::class);
        $svc->simpan($this->payload($r));
        $this->gagal(fn () => $svc->simpan($this->payload($r, ['jam_mulai' => '09:00', 'jam_selesai' => '11:00'])), 'jam_mulai');
        $this->gagal(fn () => $svc->simpan($this->payload($r, ['jam_mulai' => '07:00', 'jam_selesai' => '08:30'])), 'jam_mulai');
        $this->gagal(fn () => $svc->simpan($this->payload($r, ['jam_mulai' => '08:30', 'jam_selesai' => '09:00'])), 'jam_mulai'); // di dalam

        // bersambung, hari lain, ruangan lain, semester lain, tahun lain: boleh
        $svc->simpan($this->payload($r, ['jam_mulai' => '10:00', 'jam_selesai' => '12:00']));
        $svc->simpan($this->payload($r, ['hari' => 'selasa']));
        $svc->simpan($this->payload($this->ruangan()));
        $svc->simpan($this->payload($r, ['semester' => 'genap']));
        $svc->simpan($this->payload($r, ['tahun_akademik' => '2027/2028']));
        $this->assertSame(6, Jadwal::count());
    }

    public function test_jadwal_dibatalkan_tidak_memblokir_dan_pengaktifan_ulang_dicek(): void
    {
        $r = $this->ruangan();
        $svc = app(JadwalService::class);
        $batal = $svc->simpan($this->payload($r, ['status' => 'dibatalkan']));
        $aktif = $svc->simpan($this->payload($r)); // tidak terblokir oleh yang dibatalkan
        $this->gagal(fn () => $svc->simpan($this->payload($r, ['status' => 'aktif']), $batal), 'jam_mulai'); // diaktifkan kembali -> bentrok
        $svc->simpan($this->payload($r, ['jam_mulai' => '09:00', 'jam_selesai' => '09:30', 'status' => 'dibatalkan']), $batal);
        $svc->simpan($this->payload($r, ['jam_mulai' => '08:00', 'jam_selesai' => '10:00']), $aktif); // edit diri sendiri tidak bentrok dengan dirinya
        $this->assertSame('dibatalkan', $batal->fresh()->status);
    }

    public function test_periode_akademik(): void
    {
        $this->assertSame(['2026/2027', 'ganjil'], PeminjamanService::periodeAkademik(Carbon::parse('2026-09-15')));
        $this->assertSame(['2025/2026', 'ganjil'], PeminjamanService::periodeAkademik(Carbon::parse('2026-01-15')));
        $this->assertSame(['2025/2026', 'genap'], PeminjamanService::periodeAkademik(Carbon::parse('2026-03-15')));
    }
}
