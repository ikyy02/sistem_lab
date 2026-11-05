<?php

namespace Tests\Feature;

use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\Peminjaman;
use App\Models\PeminjamanDetail;
use App\Services\PeminjamanService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

/**
 * Dashboard Mahasiswa: isolasi antar mahasiswa, ketiadaan relasi kelas,
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

    // ------------------------------------------------------------------ isolasi antar mahasiswa

    public function test_mahasiswa_a_tidak_melihat_data_mahasiswa_b(): void
    {
        $dosen = $this->dosen();

        $a = $this->mahasiswa();
        $b = $this->mahasiswa();

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

        $this->sebagai('mahasiswa', $a)->get(route('dashboard'))->assertOk()
            ->assertSee('Barang Milik A')
            ->assertDontSee('Alasan penolakan khusus milik B')
            ->assertDontSee('Barang Milik B');

        $this->sebagai('mahasiswa', $b)->get(route('dashboard'))->assertOk()
            ->assertSee('Alasan penolakan khusus milik B')
            ->assertSee('Barang Milik B')
            ->assertDontSee('Barang Milik A');
    }

    // ------------------------------------------------------------------ tanpa relasi kelas

    public function test_dashboard_mahasiswa_tidak_bergantung_pada_kelas_atau_jadwal_kuliah(): void
    {
        $m = $this->mahasiswa(); // tanpa kelas apa pun
        $kelas = $this->kelas();
        $this->jadwal($this->ruangan('Ruang Jadwal A'), 'rabu', '13:00', '14:40', ['id_kelas' => $kelas->id_kelas]);

        $this->sebagai('mahasiswa', $m)->get(route('dashboard'))->assertOk()
            ->assertSee('Ajukan Peminjaman')
            ->assertDontSee('Belum ada kelas')
            ->assertDontSee('Jadwal kuliah')
            ->assertDontSee('Ruang Jadwal A');
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
}
