<?php

namespace Tests\Feature;

use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\KelasMahasiswa;
use App\Models\Mahasiswa;
use App\Models\Peminjaman;
use App\Models\PeminjamanDetail;
use App\Services\PeminjamanService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

/**
 * Dashboard Mahasiswa: migrasi kelas_mahasiswas, isolasi antar mahasiswa, jadwal kuliah,
 * peringatan terlambat/penolakan, status laboratorium, dan cabang Dosen/Laboran yang tidak berubah.
 */
class DashboardMahasiswaTest extends TestCase
{
    use MembuatData, RefreshDatabase;

    /** Rabu, 07 Oktober 2026 10:00 — agar "hari ini" dan sisa minggu ini deterministik. */
    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-07 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function sebagai(string $role, $profil): self
    {
        return $this->withSession(['silab_user' => [
            'role' => $role, 'key' => (string) $profil->getKey(), 'nama' => $profil->nama, 'email' => $profil->email,
        ]]);
    }

    private function peminjaman($m, array $o = []): Peminjaman
    {
        return Peminjaman::create($o + [
            'email_peminjam' => $m->email, 'jenis_peminjaman' => 'pribadi',
            'tanggal_pengajuan' => now()->subDays(2), 'tanggal_peminjaman' => now()->addDay(),
            'tanggal_rencana_kembali' => now()->addDays(3), 'status' => 'menunggu',
        ]);
    }

    /** @return array{0:string,1:string} */
    private function periode(): array
    {
        return PeminjamanService::periodeAkademik(now());
    }

    // ------------------------------------------------------------------ migrasi

    public function test_migrasi_tabel_dan_kolom_ada(): void
    {
        $this->assertTrue(Schema::hasTable('kelas_mahasiswas'));
        $this->assertTrue(Schema::hasColumns('kelas_mahasiswas', [
            'id_kelas_mahasiswa', 'nim', 'id_kelas', 'tahun_akademik', 'semester',
        ]));
        $this->assertFalse(Schema::hasColumn('mahasiswas', 'kelas')); // tetap dilarang
    }

    public function test_migrasi_down_dapat_dijalankan_dan_up_membuat_ulang(): void
    {
        $migration = require database_path('migrations/2026_11_17_000001_create_kelas_mahasiswas_table.php');
        try {
            $migration->down();
            $this->assertFalse(Schema::hasTable('kelas_mahasiswas'));
        } finally {
            $migration->up();
        }
        $this->assertTrue(Schema::hasTable('kelas_mahasiswas'));
    }

    public function test_unique_periode_menolak_duplikat(): void
    {
        $m = $this->mahasiswa();
        $k = $this->kelas();
        [$tahun, $semester] = $this->periode();
        KelasMahasiswa::create(['nim' => $m->nim, 'id_kelas' => $k->id_kelas, 'tahun_akademik' => $tahun, 'semester' => $semester]);

        $this->assertThrowsQuery(fn () => KelasMahasiswa::create([
            'nim' => $m->nim, 'id_kelas' => $this->kelas()->id_kelas, 'tahun_akademik' => $tahun, 'semester' => $semester,
        ])); // satu mahasiswa hanya satu kelas per semester
        KelasMahasiswa::create(['nim' => $m->nim, 'id_kelas' => $k->id_kelas, 'tahun_akademik' => $tahun, 'semester' => 'genap']); // semester lain boleh
        $this->assertSame(2, KelasMahasiswa::where('nim', $m->nim)->count());
    }

    public function test_fk_menolak_mahasiswa_dan_kelas_tanpa_rujukan(): void
    {
        $m = $this->mahasiswa();
        $k = $this->kelas();
        [$tahun, $semester] = $this->periode();

        $this->assertThrowsQuery(fn () => KelasMahasiswa::create([
            'nim' => '9999999999', 'id_kelas' => $k->id_kelas, 'tahun_akademik' => $tahun, 'semester' => $semester,
        ]));
        $this->assertThrowsQuery(fn () => KelasMahasiswa::create([
            'nim' => $m->nim, 'id_kelas' => 999999, 'tahun_akademik' => $tahun, 'semester' => $semester,
        ]));
        $this->assertSame(0, KelasMahasiswa::count());
    }

    public function test_mahasiswa_dan_kelas_terhubung_tidak_bisa_dihapus(): void
    {
        $m = $this->mahasiswa();
        $k = $this->kelas();
        [$tahun, $semester] = $this->periode();
        KelasMahasiswa::create(['nim' => $m->nim, 'id_kelas' => $k->id_kelas, 'tahun_akademik' => $tahun, 'semester' => $semester]);

        $this->assertThrowsQuery(fn () => Mahasiswa::whereKey($m->nim)->delete());
        $this->assertThrowsQuery(fn () => Kelas::whereKey($k->id_kelas)->delete());
        $this->assertSame(1, KelasMahasiswa::count());
    }

    // ------------------------------------------------------------------ isolasi antar mahasiswa

    public function test_mahasiswa_a_tidak_melihat_data_mahasiswa_b(): void
    {
        [$tahun, $semester] = $this->periode();
        $dosen = $this->dosen();

        $a = $this->mahasiswa();
        $b = $this->mahasiswa();
        $kelasA = $this->kelas();
        $kelasB = $this->kelas();
        KelasMahasiswa::create(['nim' => $a->nim, 'id_kelas' => $kelasA->id_kelas, 'tahun_akademik' => $tahun, 'semester' => $semester]);
        KelasMahasiswa::create(['nim' => $b->nim, 'id_kelas' => $kelasB->id_kelas, 'tahun_akademik' => $tahun, 'semester' => $semester]);

        // peminjaman milik masing-masing (ringkasan memakai nama katalog)
        $kA = $this->katalog('alat', 5, ['nama' => 'Barang Milik A']);
        $kB = $this->katalog('alat', 5, ['nama' => 'Barang Milik B']);
        $pinjamA = $this->peminjaman($a, ['keterangan' => 'Isolasi milik A']);
        PeminjamanDetail::create(['id_peminjaman' => $pinjamA->id_peminjaman, 'id_katalog' => $kA->id_katalog, 'jumlah' => 1]);
        $pinjamB = $this->peminjaman($b, ['keterangan' => 'Isolasi milik B']);
        PeminjamanDetail::create(['id_peminjaman' => $pinjamB->id_peminjaman, 'id_katalog' => $kB->id_katalog, 'jumlah' => 2]);
        Peminjaman::create(['email_peminjam' => $b->email, 'jenis_peminjaman' => 'pribadi',
            'tanggal_pengajuan' => now()->subDays(2), 'tanggal_peminjaman' => now()->addDay(),
            'tanggal_rencana_kembali' => now()->addDays(3), 'status' => 'ditolak',
            'alasan_ditolak' => 'Alasan penolakan khusus milik B', 'tanggal_persetujuan' => now()->subDay(),
            'email_pemroses' => $dosen->email]);

        // jadwal kelas masing-masing
        $this->jadwal($this->ruangan('Ruang Jadwal A'), 'rabu', '08:00', '09:40', ['id_kelas' => $kelasA->id_kelas]);
        $this->jadwal($this->ruangan('Ruang Jadwal B'), 'rabu', '10:00', '11:40', ['id_kelas' => $kelasB->id_kelas]);

        $this->sebagai('mahasiswa', $a)->get(route('dashboard'))->assertOk()
            ->assertSee('Barang Milik A')
            ->assertSee('Ruang Jadwal A')
            ->assertDontSee('Alasan penolakan khusus milik B')
            ->assertDontSee('Barang Milik B')
            ->assertDontSee('Ruang Jadwal B');

        $this->sebagai('mahasiswa', $b)->get(route('dashboard'))->assertOk()
            ->assertSee('Alasan penolakan khusus milik B')
            ->assertSee('Barang Milik B')
            ->assertSee('Ruang Jadwal B')
            ->assertDontSee('Barang Milik A')
            ->assertDontSee('Ruang Jadwal A');
    }

    // ------------------------------------------------------------------ jadwal kuliah

    public function test_jadwal_hanya_kelas_sendiri_status_aktif_dan_periode_berjalan(): void
    {
        [$tahun, $semester] = $this->periode();
        $m = $this->mahasiswa();
        $kelas = $this->kelas();
        KelasMahasiswa::create(['nim' => $m->nim, 'id_kelas' => $kelas->id_kelas, 'tahun_akademik' => $tahun, 'semester' => $semester]);

        $this->jadwal($this->ruangan('Ruang Jadwal A'), 'rabu', '13:00', '14:40', ['id_kelas' => $kelas->id_kelas]); // hari ini, aktif
        $this->jadwal($this->ruangan('Ruang Dibatalkan'), 'rabu', '08:00', '09:00', ['id_kelas' => $kelas->id_kelas, 'status' => 'dibatalkan']);
        $this->jadwal($this->ruangan('Ruang Kelas Lain'), 'rabu', '10:00', '11:00', ['id_kelas' => $this->kelas()->id_kelas]);
        $this->jadwal($this->ruangan('Ruang Beda Periode'), 'rabu', '15:00', '16:00', [
            'id_kelas' => $kelas->id_kelas, 'tahun_akademik' => '2025/2026', 'semester' => 'genap',
        ]);
        $this->jadwal($this->ruangan('Ruang Luar Minggu'), 'selasa', '08:00', '09:00', ['id_kelas' => $kelas->id_kelas]); // di luar rabu..minggu

        $this->sebagai('mahasiswa', $m)->get(route('dashboard'))->assertOk()
            ->assertSee('Ruang Jadwal A')
            ->assertSee('Hari ini')
            ->assertDontSee('Ruang Dibatalkan')
            ->assertDontSee('Ruang Kelas Lain')
            ->assertDontSee('Ruang Beda Periode')
            ->assertDontSee('Ruang Luar Minggu');
    }

    public function test_mahasiswa_tanpa_kelas_mendapat_state_kosong(): void
    {
        $m = $this->mahasiswa();
        $this->sebagai('mahasiswa', $m)->get(route('dashboard'))->assertOk()->assertSee('Belum ada kelas');
        $this->assertSame(0, KelasMahasiswa::where('nim', $m->nim)->count());
    }

    // ------------------------------------------------------------------ ringkasan, peringatan, pembaruan

    public function test_dashboard_menampilkan_ringkasan_dan_peringatan_terlambat_serta_alasan_ditolak(): void
    {
        $m = $this->mahasiswa();
        $dosen = $this->dosen();
        $k = $this->katalog('alat', 10);

        $terlambat = $this->peminjaman($m, [
            'tanggal_pengajuan' => now()->subDays(9), 'tanggal_peminjaman' => now()->subDays(8),
            'tanggal_rencana_kembali' => now()->subDay(), 'status' => 'disetujui',
            'email_pemroses' => $dosen->email, 'tanggal_persetujuan' => now()->subDays(8),
        ]);
        PeminjamanDetail::create(['id_peminjaman' => $terlambat->id_peminjaman, 'id_katalog' => $k->id_katalog, 'jumlah' => 1]);

        $ditolak = $this->peminjaman($m, [
            'tanggal_pengajuan' => now()->subDays(3), 'tanggal_peminjaman' => now()->addDays(3),
            'tanggal_rencana_kembali' => now()->addDays(4), 'status' => 'ditolak',
            'email_pemroses' => $dosen->email, 'tanggal_persetujuan' => now()->subDay(),
            'alasan_ditolak' => 'Barang dipakai ujian nasional',
        ]);

        $this->sebagai('mahasiswa', $m)->get(route('dashboard'))->assertOk()
            ->assertSee('Total riwayat Peminjaman')
            ->assertSee('Ajukan Peminjaman')
            ->assertSee('peminjaman terlambat')          // banner peringatan
            ->assertSee('Harus dikembalikan')
            ->assertSee('Barang dipakai ujian nasional') // alasan tampil di Pembaruan terbaru
            ->assertSee('Pembaruan terbaru');
        $this->assertSame('ditolak', $ditolak->fresh()->status);
        $this->assertSame('Barang dipakai ujian nasional', $ditolak->fresh()->alasan_ditolak);
    }

    public function test_status_laboratorium_tutup_pada_hari_tidak_operasional(): void
    {
        Carbon::setTestNow('2026-10-11 10:00:00'); // Minggu: bukan hari operasional

        $m = $this->mahasiswa();
        $this->sebagai('mahasiswa', $m)->get(route('dashboard'))->assertOk()
            ->assertSee('Laboratorium Tutup')
            ->assertSee('Laboratorium tidak beroperasi pada hari ini.');
    }

    public function test_status_laboratorium_buka_pada_jam_operasional(): void
    {
        $m = $this->mahasiswa();
        $this->sebagai('mahasiswa', $m)->get(route('dashboard'))->assertOk()->assertSee('Buka');
    }

    // ------------------------------------------------------------------ cabang lain tidak berubah

    public function test_dashboard_dosen_dan_laboran_tetap_sama(): void
    {
        $k = $this->katalog('alat', 5);
        $m = $this->mahasiswa();
        $p = Peminjaman::create(['email_peminjam' => $m->email, 'jenis_peminjaman' => 'pribadi',
            'tanggal_pengajuan' => now(), 'tanggal_peminjaman' => now()->addDay(),
            'tanggal_rencana_kembali' => now()->addDays(2), 'status' => 'menunggu']);
        PeminjamanDetail::create(['id_peminjaman' => $p->id_peminjaman, 'id_katalog' => $k->id_katalog, 'jumlah' => 1]);

        $this->sebagai('laboran', $this->laboran())->get(route('dashboard'))->assertOk()
            ->assertSee('Pengajuan menunggu persetujuan')
            ->assertSee('Peminjaman sedang berlangsung')
            ->assertDontSee('Belum ada kelas'); // cabang mahasiswa tidak dipakai

        app(PeminjamanService::class)->setujui($p->id_peminjaman, $this->laboran()->email);
        $this->sebagai('dosen', $this->dosen())->get(route('dashboard'))->assertOk()
            ->assertSee('Pengajuan terbaru')
            ->assertDontSee('Belum ada kelas');
    }

    private function assertThrowsQuery(callable $fn): void
    {
        try {
            $fn();
        } catch (QueryException) {
            $this->addToAssertionCount(1);

            return;
        }
        $this->fail('QueryException diharapkan (constraint database).');
    }
}
