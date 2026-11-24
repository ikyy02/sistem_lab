<?php

namespace Tests\Feature;

use App\Models\AlatBahan;
use App\Models\Dosen;
use App\Models\HariLibur;
use App\Models\Jadwal;
use App\Models\Mahasiswa;
use App\Models\Peminjaman;
use App\Models\PeminjamanDetail;
use App\Models\PeminjamanRuangan;
use App\Models\PengaturanOperasional;
use App\Models\Ruangan;
use App\Services\PeminjamanService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

class PeminjamanTerpaduDanAturanBisnisTest extends TestCase
{
    use MembuatData, RefreshDatabase;

    private PeminjamanService $svc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->svc = app(PeminjamanService::class);
        Carbon::setTestNow('2030-01-07 07:00:00'); // Senin, jam 07:00
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function sebagai(string $role, $profil): self
    {
        return $this->withSession([
            'silab_user' => [
                'role' => $role,
                'key' => (string) $profil->getKey(),
                'nama' => $profil->nama,
                'email' => $profil->email,
            ],
        ]);
    }

    /** 1. Formulir terpadu: Ruangan, Alat, dan Bahan dalam satu transaksi */
    public function test_pengajuan_terpadu_ruangan_alat_dan_bahan_berhasil_disimpan(): void
    {
        $mhs = $this->mahasiswa();
        $r = $this->ruangan();
        $alat = $this->katalog('alat', 10);
        $bahan = $this->katalog('bahan', 20);

        $payload = [
            'jenis_peminjaman' => 'pribadi',
            'keterangan' => 'Praktikum Terpadu',
            'tanggal_peminjaman' => '2030-01-07 08:00',
            'tanggal_rencana_kembali' => '2030-01-07 12:00',
            'ruangan' => [['id_ruangan' => $r->id_ruangan]],
            'detail' => [
                ['id_katalog' => $alat->id_katalog, 'jumlah' => 2],
                ['id_katalog' => $bahan->id_katalog, 'jumlah' => 5],
            ],
        ];

        $p = $this->svc->ajukan($mhs->email, $payload);

        $this->assertSame('menunggu', $p->status);
        $this->assertSame('pribadi', $p->jenis_peminjaman);
        $this->assertCount(1, $p->ruangans);
        $this->assertCount(2, $p->details);
        $this->assertSame(10, $alat->fresh()->stok); // Stok belum berkurang saat diajukan
        $this->assertSame(20, $bahan->fresh()->stok);
    }

    /** 2. Aturan duplikasi ruangan dan katalog ditolak */
    public function test_duplikasi_ruangan_atau_katalog_dalam_satu_transaksi_ditolak(): void
    {
        $mhs = $this->mahasiswa();
        $r = $this->ruangan();
        $alat = $this->katalog('alat', 10);

        $payload = [
            'jenis_peminjaman' => 'pribadi',
            'keterangan' => 'Uji Duplikasi',
            'tanggal_peminjaman' => '2030-01-07 08:00',
            'tanggal_rencana_kembali' => '2030-01-07 10:00',
            'ruangan' => [
                ['id_ruangan' => $r->id_ruangan],
                ['id_ruangan' => $r->id_ruangan],
            ],
            'detail' => [
                ['id_katalog' => $alat->id_katalog, 'jumlah' => 1],
                ['id_katalog' => $alat->id_katalog, 'jumlah' => 2],
            ],
        ];

        try {
            $this->svc->ajukan($mhs->email, $payload);
            $this->fail('Seharusnya melempar ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('ruangan', $e->errors());
            $this->assertArrayHasKey('detail', $e->errors());
        }
    }

    /** 3. Ruangan maksimal 1 dalam satu transaksi */
    public function test_ruangan_lebih_dari_satu_ditolak(): void
    {
        $mhs = $this->mahasiswa();
        $r1 = $this->ruangan('Lab A');
        $r2 = $this->ruangan('Lab B');

        $payload = [
            'jenis_peminjaman' => 'pribadi',
            'keterangan' => 'Dua Ruangan',
            'tanggal_peminjaman' => '2030-01-07 08:00',
            'tanggal_rencana_kembali' => '2030-01-07 10:00',
            'ruangan' => [
                ['id_ruangan' => $r1->id_ruangan],
                ['id_ruangan' => $r2->id_ruangan],
            ],
        ];

        try {
            $this->svc->ajukan($mhs->email, $payload);
            $this->fail('Seharusnya melempar ValidationException karena maksimal 1 ruangan');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('ruangan', $e->errors());
            $this->assertStringContainsString('Maksimal 1 ruangan', $e->errors()['ruangan'][0]);
        }
    }

    /** 4. Aturan Jenis Peminjaman Mahasiswa: Wajib atas_dosen bila durasi > 1 hari operasional */
    public function test_mahasiswa_wajib_atas_dosen_jika_durasi_operasional_lebih_dari_satu_hari(): void
    {
        $mhs = $this->mahasiswa();
        $alat = $this->katalog('alat', 5);

        // Uji coba durasi > 1 hari operasional dengan jenis pribadi
        $payload = [
            'jenis_peminjaman' => 'pribadi',
            'keterangan' => 'Peminjaman Lama',
            'tanggal_peminjaman' => '2030-01-07 08:00', // Senin
            'tanggal_rencana_kembali' => '2030-01-08 12:00', // Selasa (2 hari operasional)
            'detail' => [['id_katalog' => $alat->id_katalog, 'jumlah' => 1]],
        ];

        try {
            $this->svc->ajukan($mhs->email, $payload);
            $this->fail('Seharusnya ditolak jika memilih pribadi untuk peminjaman > 1 hari operasional');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('jenis_peminjaman', $e->errors());
        }
    }

    /** 5. Aturan Jenis Peminjaman Mahasiswa: Wajib atas_dosen jika menyentuh hari nonoperasional (akhir pekan/libur) tanpa ruangan */
    public function test_mahasiswa_wajib_atas_dosen_jika_menyentuh_hari_nonoperasional_tanpa_ruangan(): void
    {
        $mhs = $this->mahasiswa();
        $alat = $this->katalog('alat', 5);

        // Hari Sabtu (2030-01-12) adalah hari nonoperasional laboratorium
        $payload = [
            'jenis_peminjaman' => 'pribadi',
            'keterangan' => 'Bawa alat akhir pekan',
            'tanggal_peminjaman' => '2030-01-11 08:00', // Jumat
            'tanggal_rencana_kembali' => '2030-01-14 10:00', // Senin (menyentuh Sabtu & Minggu)
            'detail' => [['id_katalog' => $alat->id_katalog, 'jumlah' => 1]],
        ];

        try {
            $this->svc->ajukan($mhs->email, $payload);
            $this->fail('Seharusnya melempar ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('jenis_peminjaman', $e->errors());
        }
    }

    /** 6. Dosen yang dipilih harus aktif */
    public function test_dosen_penanggung_jawab_harus_aktif(): void
    {
        $mhs = $this->mahasiswa();
        $dosenNonaktif = $this->dosen(['status' => 'nonaktif']);
        $alat = $this->katalog('alat', 5);

        $payload = [
            'jenis_peminjaman' => 'atas_dosen',
            'email_dosen' => $dosenNonaktif->email,
            'keterangan' => 'Praktikum',
            'tanggal_peminjaman' => '2030-01-07 08:00',
            'tanggal_rencana_kembali' => '2030-01-07 10:00',
            'detail' => [['id_katalog' => $alat->id_katalog, 'jumlah' => 1]],
        ];

        try {
            $this->svc->ajukan($mhs->email, $payload);
            $this->fail('Seharusnya ditolak bila dosen nonaktif');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('email_dosen', $e->errors());
            $this->assertStringContainsString('aktif', $e->errors()['email_dosen'][0]);
        }
    }

    /** 7. Dosen otomatis pembelajaran dan langsung ke Laboran */
    public function test_dosen_otomatis_pembelajaran_dan_langsung_bisa_diproses_laboran(): void
    {
        $dosen = $this->dosen(['status' => 'aktif']);
        $lab = $this->laboran();
        $r = $this->ruangan();

        $payload = [
            'tanggal_peminjaman' => '2030-01-07 08:00',
            'tanggal_rencana_kembali' => '2030-01-07 11:00',
            'ruangan' => [['id_ruangan' => $r->id_ruangan]],
        ];

        $p = $this->svc->ajukan($dosen->email, $payload);
        $this->assertSame(Peminjaman::JENIS_PEMBELAJARAN, $p->jenis_peminjaman);
        $this->assertNull($p->email_dosen);

        // Laboran langsung berwenang memproses peminjaman Pembelajaran Dosen
        $this->assertTrue($p->bisaDiprosesOleh($lab->email, 'laboran'));

        // Laboran menyetujui
        $disetujui = $this->svc->setujui($p->id_peminjaman, $lab->email);
        $this->assertSame('disetujui', $disetujui->status);
        $this->assertSame($lab->email, $disetujui->email_pemroses);
    }

    /** 8. Alur Persetujuan Mandiri vs Atas Dosen: Laboran tidak boleh memproses sebelum Dosen validasi */
    public function test_alur_persetujuan_atas_dosen_dan_penolakan_wajib_alasan(): void
    {
        $mhs = $this->mahasiswa();
        $dosen = $this->dosen(['status' => 'aktif']);
        $lab = $this->laboran();
        $alat = $this->katalog('alat', 10);

        $payload = [
            'jenis_peminjaman' => 'atas_dosen',
            'email_dosen' => $dosen->email,
            'keterangan' => 'Proyek TA',
            'tanggal_peminjaman' => '2030-01-07 08:00',
            'tanggal_rencana_kembali' => '2030-01-07 10:00',
            'detail' => [['id_katalog' => $alat->id_katalog, 'jumlah' => 2]],
        ];

        $p = $this->svc->ajukan($mhs->email, $payload);

        // 1. Laboran TIDAK BISA memproses sebelum Dosen menyetujui
        $this->assertFalse($p->bisaDiprosesOleh($lab->email, 'laboran'));
        try {
            $this->svc->setujui($p->id_peminjaman, $lab->email);
            $this->fail('Laboran seharusnya tidak boleh menyetujui sebelum Dosen validasi');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('email_pemroses', $e->errors());
        }

        // 2. Dosen yang ditunjuk bisa memproses (validasi tahap 1)
        $this->assertTrue($p->bisaDiprosesOleh($dosen->email, 'dosen'));
        $p = $this->svc->setujui($p->id_peminjaman, $dosen->email);

        $this->assertSame('menunggu', $p->status);
        $this->assertNotNull($p->tanggal_persetujuan_dosen);
        $this->assertSame(10, $alat->fresh()->stok); // Stok tetap 10, belum berkurang

        // 3. Sekarang Laboran berwenang memproses (tahap 2 final)
        $this->assertTrue($p->fresh()->bisaDiprosesOleh($lab->email, 'laboran'));
        $final = $this->svc->setujui($p->id_peminjaman, $lab->email);

        $this->assertSame('disetujui', $final->status);
        $this->assertSame(8, $alat->fresh()->stok); // Stok baru berkurang setelah Laboran setujui
    }

    /** 9. Penolakan wajib alasan dan final */
    public function test_penolakan_oleh_dosen_atau_laboran_wajib_alasan_dan_stok_tidak_berubah(): void
    {
        $mhs = $this->mahasiswa();
        $dosen = $this->dosen(['status' => 'aktif']);
        $alat = $this->katalog('alat', 5);

        $p = $this->svc->ajukan($mhs->email, [
            'jenis_peminjaman' => 'atas_dosen',
            'email_dosen' => $dosen->email,
            'keterangan' => 'Proyek',
            'tanggal_peminjaman' => '2030-01-07 08:00',
            'tanggal_rencana_kembali' => '2030-01-07 10:00',
            'detail' => [['id_katalog' => $alat->id_katalog, 'jumlah' => 1]],
        ]);

        // Tolak tanpa alasan harus gagal
        try {
            $this->svc->tolak($p->id_peminjaman, $dosen->email, '');
            $this->fail('Alasan wajib');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('alasan', $e->errors());
        }

        // Tolak dengan alasan
        $ditolak = $this->svc->tolak($p->id_peminjaman, $dosen->email, 'Bukan anggota riset saya');
        $this->assertSame('ditolak', $ditolak->status);
        $this->assertSame('Bukan anggota riset saya', $ditolak->alasan_ditolak);
        $this->assertSame(5, $alat->fresh()->stok); // Stok aman tidak berubah
    }

    /** 10. Pengaju tidak boleh memproses pengajuannya sendiri */
    public function test_dosen_tidak_dapat_memproses_pengajuannya_sendiri(): void
    {
        $dosen = $this->dosen(['status' => 'aktif']);
        $r = $this->ruangan();

        $p = $this->svc->ajukan($dosen->email, [
            'tanggal_peminjaman' => '2030-01-07 08:00',
            'tanggal_rencana_kembali' => '2030-01-07 10:00',
            'ruangan' => [['id_ruangan' => $r->id_ruangan]],
        ]);

        $this->assertFalse($p->bisaDiprosesOleh($dosen->email, 'dosen'));
        try {
            $this->svc->setujui($p->id_peminjaman, $dosen->email);
            $this->fail('Dilarang memproses diri sendiri');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('email_pemroses', $e->errors());
        }
    }
}
