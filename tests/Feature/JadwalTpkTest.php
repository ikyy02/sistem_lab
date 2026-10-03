<?php

namespace Tests\Feature;

use App\Models\Jadwal;
use App\Services\JadwalService;
use App\Services\PeminjamanService;
use App\Services\TpkService;
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
            'id_kelas' => $this->kelas()->id_kelas, 'nuptk_nidn' => $this->dosen()->nuptk_nidn, 'id_ruangan' => $r->id_ruangan,
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

    // ------------------------------------------------------------------ TPK / SAW

    private function pinjam(string $email, $katalog, string $tanggal, string $status = 'disetujui'): void
    {
        $p = \App\Models\Peminjaman::create([
            'email_peminjam' => $email, 'jenis_peminjaman' => 'pribadi', 'tanggal_pengajuan' => '2020-01-01 00:00',
            'tanggal_peminjaman' => $tanggal, 'tanggal_rencana_kembali' => $tanggal, 'status' => $status,
            'email_pemroses' => $status === 'menunggu' ? null : $this->laboran()->email,
        ]);
        \App\Models\PeminjamanDetail::create(['id_peminjaman' => $p->id_peminjaman, 'id_katalog' => $katalog->id_katalog, 'jumlah' => 1]);
    }

    public function test_saw_rumus_cost_dan_benefit_serta_ranking(): void
    {
        $a = $this->katalog('alat', 10, ['nama' => 'A', 'harga' => 100]);
        $b = $this->katalog('alat', 20, ['nama' => 'B', 'harga' => 200]);
        $m = $this->mahasiswa()->email;
        foreach (['2030-03-02', '2030-03-10', '2030-03-20'] as $t) {
            $this->pinjam($m, $a, $t . ' 08:00');
        }
        $this->pinjam($m, $b, '2030-03-05 08:00');
        $this->pinjam($m, $a, '2030-02-28 08:00');                 // bulan lain: tidak dihitung
        $this->pinjam($m, $b, '2030-03-06 08:00', 'menunggu');     // belum terealisasi
        $this->pinjam($m, $b, '2030-03-07 08:00', 'ditolak');      // ditolak

        $hasil = collect(app(TpkService::class)->hitung([0.5, 0.3, 0.2], '2030-03'))->keyBy('nama');
        // A: stok 10 (min 10) R1=1 ; pinjam 3 (max 3) R2=1 ; harga 100 (min 100) R3=1 -> 1.0
        $this->assertEqualsWithDelta(1.0, $hasil['A']['nilai'], 1e-9);
        // B: R1=10/20=0.5 ; R2=1/3 ; R3=100/200=0.5 -> 0.25 + 0.1 + 0.1
        $this->assertEqualsWithDelta(0.5 * 0.5 + 0.3 * (1 / 3) + 0.2 * 0.5, $hasil['B']['nilai'], 1e-9);
        $this->assertSame(3, $hasil['A']['jumlah_peminjaman']);
        $this->assertSame(1, $hasil['B']['jumlah_peminjaman']);
        $this->assertSame([1, 2], [$hasil['A']['ranking'], $hasil['B']['ranking']]);
    }

    public function test_saw_validasi_bobot_dan_tanpa_penyimpanan_tabel(): void
    {
        $svc = app(TpkService::class);
        $this->gagal(fn () => $svc->hitung([0.5, 0.5, 0.5], '2030-03'), 'bobot');
        $this->gagal(fn () => $svc->hitung([1.2, -0.2, 0], '2030-03'), 'bobot');
        $this->gagal(fn () => $svc->hitung([0.5, 0.5], '2030-03'), 'bobot');
        $this->gagal(fn () => $svc->hitung([0.4, 0.3, 0.3], '2030-13'), 'bulan');
        $this->assertSame([], $svc->hitung([0.4, 0.3, 0.3], '2030-03'));
        $this->katalog('alat', 0, ['harga' => 5]); // stok 0 tidak boleh membagi nol
        $this->assertCount(1, $svc->hitung([0.4, 0.3, 0.3], '2030-03'));
    }
}
