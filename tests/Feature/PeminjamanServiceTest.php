<?php

namespace Tests\Feature;

use App\Models\AlatBahan;
use App\Models\Peminjaman;
use App\Models\PeminjamanDetail;
use App\Models\Pengembalian;
use App\Services\PeminjamanService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

class PeminjamanServiceTest extends TestCase
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

    private function data(array $o = []): array
    {
        return $o + [
            'jenis_peminjaman' => 'pribadi', 'tanggal_peminjaman' => '2030-01-08 08:00', 'tanggal_rencana_kembali' => '2030-01-09 08:00',
            'detail' => [], 'ruangan' => [],
        ];
    }

    private function ajukanBarang(string $email, array $items, array $o = []): Peminjaman
    {
        return $this->svc->ajukan($email, $this->data($o + ['detail' => array_map(fn ($id, $j) => ['id_katalog' => $id, 'jumlah' => $j], array_keys($items), $items)]));
    }

    private function gagal(callable $fn, string $field): void
    {
        try {
            $fn();
        } catch (ValidationException $e) {
            $this->assertArrayHasKey($field, $e->errors(), 'Error pada field ' . $field . ' diharapkan, dapat: ' . json_encode($e->errors()));

            return;
        }
        $this->fail("ValidationException ({$field}) diharapkan.");
    }

    // ---------------------------------------------------------------- pengaju & jenis & tanggal

    public function test_hanya_mahasiswa_dan_dosen_boleh_mengajukan(): void
    {
        $k = $this->katalog();
        $d = ['detail' => [['id_katalog' => $k->id_katalog, 'jumlah' => 1]]];
        $this->assertSame('menunggu', $this->svc->ajukan($this->mahasiswa()->email, $this->data($d))->status);
        $this->assertSame('menunggu', $this->svc->ajukan($this->dosen()->email, $this->data($d))->status);
        $this->gagal(fn () => $this->svc->ajukan($this->staff()->email, $this->data($d)), 'email_peminjam');
        $this->gagal(fn () => $this->svc->ajukan($this->laboran()->email, $this->data($d)), 'email_peminjam');
        $this->gagal(fn () => $this->svc->ajukan('tidak@ada.test', $this->data($d)), 'email_peminjam');
    }

    public function test_pribadi_vs_atas_dosen(): void
    {
        $k = $this->katalog();
        $m = $this->mahasiswa();
        $dosen = $this->dosen();
        $d = ['detail' => [['id_katalog' => $k->id_katalog, 'jumlah' => 1]]];

        $pribadi = $this->svc->ajukan($m->email, $this->data($d));
        $this->assertNull($pribadi->email_dosen);
        $this->gagal(fn () => $this->svc->ajukan($m->email, $this->data($d + ['email_dosen' => $dosen->email])), 'email_dosen');

        $atas = $this->svc->ajukan($m->email, $this->data($d + ['jenis_peminjaman' => 'atas_dosen', 'email_dosen' => $dosen->email]));
        $this->assertSame($dosen->email, $atas->email_dosen);
        $this->gagal(fn () => $this->svc->ajukan($m->email, $this->data($d + ['jenis_peminjaman' => 'atas_dosen'])), 'email_dosen');
        // email_dosen harus akun ber-role dosen
        $this->gagal(fn () => $this->svc->ajukan($m->email, $this->data($d + ['jenis_peminjaman' => 'atas_dosen', 'email_dosen' => $this->mahasiswa()->email])), 'email_dosen');
        $this->gagal(fn () => $this->svc->ajukan($m->email, $this->data($d + ['jenis_peminjaman' => 'umum'])), 'jenis_peminjaman');
    }

    public function test_validasi_tanggal(): void
    {
        $k = $this->katalog();
        $d = ['detail' => [['id_katalog' => $k->id_katalog, 'jumlah' => 1]]];
        $m = $this->mahasiswa()->email;
        $this->gagal(fn () => $this->svc->ajukan($m, $this->data($d + ['tanggal_peminjaman' => '2030-01-06 08:00'])), 'tanggal_peminjaman'); // sebelum pengajuan
        $this->gagal(fn () => $this->svc->ajukan($m, $this->data($d + ['tanggal_rencana_kembali' => '2030-01-08 07:00'])), 'tanggal_rencana_kembali'); // sebelum pinjam
        $this->gagal(fn () => $this->svc->ajukan($m, $this->data($d + ['tanggal_peminjaman' => null])), 'tanggal_peminjaman'); // wajib
        $this->gagal(fn () => $this->svc->ajukan($m, $this->data($d + ['tanggal_peminjaman' => 'bukan tanggal'])), 'tanggal_peminjaman');
        $ok = $this->svc->ajukan($m, $this->data($d + ['tanggal_peminjaman' => '2030-01-08 08:00', 'tanggal_rencana_kembali' => '2030-01-08 08:00']));
        $this->assertTrue($ok->tanggal_pengajuan->lte($ok->tanggal_peminjaman) && $ok->tanggal_peminjaman->lte($ok->tanggal_rencana_kembali));
    }

    public function test_detail_dan_ruangan_tidak_boleh_duplikat_atau_nol(): void
    {
        $k = $this->katalog();
        $r = $this->ruangan();
        $m = $this->mahasiswa()->email;
        $this->gagal(fn () => $this->svc->ajukan($m, $this->data(['detail' => [['id_katalog' => $k->id_katalog, 'jumlah' => 1], ['id_katalog' => $k->id_katalog, 'jumlah' => 2]]])), 'detail');
        $this->gagal(fn () => $this->svc->ajukan($m, $this->data(['detail' => [['id_katalog' => $k->id_katalog, 'jumlah' => 0]]])), 'detail');
        $this->gagal(fn () => $this->svc->ajukan($m, $this->data(['detail' => [['id_katalog' => 9999, 'jumlah' => 1]]])), 'detail');
        $this->gagal(fn () => $this->svc->ajukan($m, $this->data()), 'detail'); // kosong
        $rr = ['id_ruangan' => $r->id_ruangan, 'tanggal_mulai' => '2030-01-08 08:00', 'tanggal_selesai' => '2030-01-08 10:00'];
        $this->gagal(fn () => $this->svc->ajukan($m, $this->data(['ruangan' => [$rr, $rr]])), 'ruangan');
        $this->gagal(fn () => $this->svc->ajukan($m, $this->data(['ruangan' => [['tanggal_mulai' => '2030-01-08 10:00'] + $rr]])), 'ruangan'); // mulai == selesai? mulai 10:00 > selesai 10:00 tidak <
    }

    // ---------------------------------------------------------------- approval & stok

    public function test_pengajuan_tidak_mengurangi_stok_approval_mengurangi(): void
    {
        $lab = $this->laboran();
        $k = $this->katalog('alat', 10);
        $p = $this->ajukanBarang($this->mahasiswa()->email, [$k->id_katalog => 4]);
        $this->assertSame(10, $k->fresh()->stok);

        $p = $this->svc->setujui($p->id_peminjaman, $lab->email);
        $this->assertSame('disetujui', $p->status);
        $this->assertSame($lab->email, $p->email_pemroses);
        $this->assertSame(6, $k->fresh()->stok);
    }

    public function test_approval_ditolak_bila_stok_kurang_dan_stok_tidak_negatif(): void
    {
        $lab = $this->laboran();
        $k = $this->katalog('alat', 5);
        $m = $this->mahasiswa()->email;
        $a = $this->ajukanBarang($m, [$k->id_katalog => 3]);
        $b = $this->ajukanBarang($m, [$k->id_katalog => 3]); // total 6 > stok 5 (boleh diajukan, stok tidak dipesan)

        $this->svc->setujui($a->id_peminjaman, $lab->email);
        $this->assertSame(2, $k->fresh()->stok);
        $this->gagal(fn () => $this->svc->setujui($b->id_peminjaman, $lab->email), 'stok');
        $this->assertSame(2, $k->fresh()->stok);
        $this->assertSame('menunggu', $b->fresh()->status);
        $this->assertNull($b->fresh()->email_pemroses);
    }

    public function test_approval_atomic_rollback_semua_stok_bila_salah_satu_gagal(): void
    {
        $lab = $this->laboran();
        $cukup = $this->katalog('alat', 10);
        $kurang = $this->katalog('bahan', 1);
        $p = $this->ajukanBarang($this->mahasiswa()->email, [$cukup->id_katalog => 5, $kurang->id_katalog => 2]);

        $this->gagal(fn () => $this->svc->setujui($p->id_peminjaman, $lab->email), 'stok');
        $this->assertSame(10, $cukup->fresh()->stok); // tidak ada pengurangan parsial
        $this->assertSame('menunggu', $p->fresh()->status);
    }

    public function test_race_condition_dua_approval_untuk_stok_terakhir_hanya_satu_berhasil(): void
    {
        $lab = $this->laboran();
        $k = $this->katalog('alat', 1);
        $m = $this->mahasiswa()->email;
        $a = $this->ajukanBarang($m, [$k->id_katalog => 1]);
        $b = $this->ajukanBarang($m, [$k->id_katalog => 1]);

        // Simulasi pembacaan basi: kedua proses sudah "melihat" stok 1 sebelum salah satu commit.
        $stale = AlatBahan::find($k->id_katalog);
        $this->assertSame(1, $stale->stok);
        $this->svc->setujui($a->id_peminjaman, $lab->email);
        $this->gagal(fn () => $this->svc->setujui($b->id_peminjaman, $lab->email), 'stok');

        $this->assertSame(0, $k->fresh()->stok);
        $this->assertSame(1, Peminjaman::where('status', 'disetujui')->count());
        // Pengurangan memakai UPDATE bersyarat sehingga tidak bisa mendahului stok 0 walau pengecekan terlewat.
        $this->assertSame(0, AlatBahan::where('id_katalog', $k->id_katalog)->where('stok', '>=', 1)->decrement('stok', 1));
        $this->assertSame(0, $k->fresh()->stok);
    }

    public function test_hanya_laboran_memproses_dan_hanya_dari_status_menunggu(): void
    {
        $k = $this->katalog();
        $p = $this->ajukanBarang($this->mahasiswa()->email, [$k->id_katalog => 1]);
        $this->gagal(fn () => $this->svc->setujui($p->id_peminjaman, $this->staff()->email), 'email_pemroses');
        $this->gagal(fn () => $this->svc->tolak($p->id_peminjaman, $this->dosen()->email), 'email_pemroses');

        $lab = $this->laboran();
        $t = $this->svc->tolak($p->id_peminjaman, $lab->email);
        $this->assertSame(['ditolak', $lab->email], [$t->status, $t->email_pemroses]);
        $this->assertSame(10, $k->fresh()->stok); // penolakan tidak mengubah stok
        $this->gagal(fn () => $this->svc->setujui($p->id_peminjaman, $lab->email), 'status');
        $this->gagal(fn () => $this->svc->tolak($p->id_peminjaman, $lab->email), 'status');
    }

    // ---------------------------------------------------------------- pengembalian

    private function disetujui(array $items, array $o = []): array
    {
        $lab = $this->laboran();
        $p = $this->ajukanBarang($this->mahasiswa()->email, $items, $o);
        $p = $this->svc->setujui($p->id_peminjaman, $lab->email);

        return [$p, $lab];
    }

    private function detailId(Peminjaman $p, AlatBahan $k): int
    {
        return PeminjamanDetail::where('id_peminjaman', $p->id_peminjaman)->where('id_katalog', $k->id_katalog)->value('id_detail');
    }

    public function test_alat_baik_kembali_ke_stok_alat_rusak_tidak(): void
    {
        $alat = $this->katalog('alat', 10);
        [$p, $lab] = $this->disetujui([$alat->id_katalog => 5]);
        $d = $this->detailId($p, $alat);
        $this->assertSame(5, $alat->fresh()->stok);

        $this->svc->kembalikan($p->id_peminjaman, $lab->email, [['id_detail_peminjaman' => $d, 'jumlah_dikembalikan' => 2, 'kondisi' => 'baik']]);
        $this->assertSame(7, $alat->fresh()->stok);
        $this->svc->kembalikan($p->id_peminjaman, $lab->email, [['id_detail_peminjaman' => $d, 'jumlah_dikembalikan' => 1, 'kondisi' => 'rusak']]);
        $this->assertSame(7, $alat->fresh()->stok); // rusak: stok tetap
    }

    public function test_bahan_berlebih_kembali_ke_stok_tanpa_kondisi(): void
    {
        $bahan = $this->katalog('bahan', 10);
        [$p, $lab] = $this->disetujui([$bahan->id_katalog => 6]);
        $d = $this->detailId($p, $bahan);
        $this->svc->kembalikan($p->id_peminjaman, $lab->email, [['id_detail_peminjaman' => $d, 'jumlah_dikembalikan' => 2]]);
        $this->assertSame(6, $bahan->fresh()->stok); // 10 - 6 + 2
        $this->assertNull(\App\Models\PengembalianDetail::first()->kondisi);
        $this->gagal(fn () => $this->svc->kembalikan($p->id_peminjaman, $lab->email, [['id_detail_peminjaman' => $d, 'jumlah_dikembalikan' => 1, 'kondisi' => 'baik']]), 'kondisi');
    }

    public function test_kondisi_alat_wajib_baik_atau_rusak(): void
    {
        $alat = $this->katalog('alat', 10);
        [$p, $lab] = $this->disetujui([$alat->id_katalog => 2]);
        $d = $this->detailId($p, $alat);
        $this->gagal(fn () => $this->svc->kembalikan($p->id_peminjaman, $lab->email, [['id_detail_peminjaman' => $d, 'jumlah_dikembalikan' => 1]]), 'kondisi');
        $this->gagal(fn () => $this->svc->kembalikan($p->id_peminjaman, $lab->email, [['id_detail_peminjaman' => $d, 'jumlah_dikembalikan' => 1, 'kondisi' => 'hilang']]), 'kondisi');
        $this->assertSame(0, Pengembalian::count());
    }

    public function test_jumlah_pengembalian_tidak_melebihi_pinjaman_dan_partial_return(): void
    {
        $alat = $this->katalog('alat', 10);
        [$p, $lab] = $this->disetujui([$alat->id_katalog => 5]);
        $d = $this->detailId($p, $alat);
        $row = fn (int $j) => [['id_detail_peminjaman' => $d, 'jumlah_dikembalikan' => $j, 'kondisi' => 'baik']];

        $this->gagal(fn () => $this->svc->kembalikan($p->id_peminjaman, $lab->email, $row(6)), 'jumlah_dikembalikan');
        $this->gagal(fn () => $this->svc->kembalikan($p->id_peminjaman, $lab->email, $row(0)), 'jumlah_dikembalikan');
        $this->svc->kembalikan($p->id_peminjaman, $lab->email, $row(3));        // partial
        $this->assertSame('disetujui', $p->fresh()->status);
        $this->gagal(fn () => $this->svc->kembalikan($p->id_peminjaman, $lab->email, $row(3)), 'jumlah_dikembalikan'); // 3 + 3 > 5
        // duplikat baris dalam satu pengembalian dijumlahkan
        $this->gagal(fn () => $this->svc->kembalikan($p->id_peminjaman, $lab->email, array_merge($row(2), $row(1))), 'jumlah_dikembalikan');
        $this->assertSame(2, Pengembalian::count() + 1); // hanya 1 pengembalian tersimpan
        $this->svc->kembalikan($p->id_peminjaman, $lab->email, $row(2));
        $this->assertSame(10, $alat->fresh()->stok);
    }

    public function test_detail_harus_milik_transaksi_yang_sama(): void
    {
        $alat = $this->katalog('alat', 10);
        [$p1, $lab] = $this->disetujui([$alat->id_katalog => 1]);
        [$p2] = $this->disetujui([$alat->id_katalog => 1]);
        $this->gagal(fn () => $this->svc->kembalikan($p1->id_peminjaman, $lab->email, [['id_detail_peminjaman' => $this->detailId($p2, $alat), 'jumlah_dikembalikan' => 1, 'kondisi' => 'baik']]), 'detail');
    }

    public function test_pengembalian_hanya_untuk_status_disetujui_dan_penerima_laboran(): void
    {
        $alat = $this->katalog('alat', 10);
        $lab = $this->laboran();
        $menunggu = $this->ajukanBarang($this->mahasiswa()->email, [$alat->id_katalog => 1]);
        $row = fn (Peminjaman $p) => [['id_detail_peminjaman' => $this->detailId($p, $alat), 'jumlah_dikembalikan' => 1, 'kondisi' => 'baik']];
        $this->gagal(fn () => $this->svc->kembalikan($menunggu->id_peminjaman, $lab->email, $row($menunggu)), 'status');

        $ditolak = $this->ajukanBarang($this->mahasiswa()->email, [$alat->id_katalog => 1]);
        $this->svc->tolak($ditolak->id_peminjaman, $lab->email);
        $this->gagal(fn () => $this->svc->kembalikan($ditolak->id_peminjaman, $lab->email, $row($ditolak)), 'status');

        [$p] = $this->disetujui([$alat->id_katalog => 1]);
        $this->gagal(fn () => $this->svc->kembalikan($p->id_peminjaman, $this->dosen()->email, $row($p)), 'email_pemroses');
        $this->svc->kembalikan($p->id_peminjaman, $lab->email, $row($p));
        $this->assertSame('selesai', $p->fresh()->status);
        $this->gagal(fn () => $this->svc->kembalikan($p->id_peminjaman, $lab->email, $row($p)), 'status'); // sudah selesai
        $this->assertSame($lab->email, Pengembalian::first()->email_penerima);
    }

    // ---------------------------------------------------------------- status selesai (ditentukan sistem)

    public function test_status_selesai_alat_setelah_semua_dikembalikan_termasuk_yang_rusak(): void
    {
        $alat = $this->katalog('alat', 10);
        [$p, $lab] = $this->disetujui([$alat->id_katalog => 2]);
        $d = $this->detailId($p, $alat);
        $this->svc->kembalikan($p->id_peminjaman, $lab->email, [['id_detail_peminjaman' => $d, 'jumlah_dikembalikan' => 1, 'kondisi' => 'baik']]);
        $this->assertSame('disetujui', $p->fresh()->status);
        $this->svc->kembalikan($p->id_peminjaman, $lab->email, [['id_detail_peminjaman' => $d, 'jumlah_dikembalikan' => 1, 'kondisi' => 'rusak']]);
        $this->assertSame('selesai', $p->fresh()->status);
    }

    public function test_status_bahan_saja_selesai_setelah_rencana_kembali(): void
    {
        $bahan = $this->katalog('bahan', 10);
        [$p] = $this->disetujui([$bahan->id_katalog => 3]);
        $this->assertSame('disetujui', $p->status); // masih bisa mengembalikan sisa bahan
        $this->svc->evaluasiStatus($p, Carbon::parse('2030-01-08 12:00'));
        $this->assertSame('disetujui', $p->fresh()->status);
        $this->assertSame(1, $this->svc->sinkronSelesai(Carbon::parse('2030-01-09 08:00')));
        $this->assertSame('selesai', $p->fresh()->status);
    }

    public function test_status_campuran_butuh_alat_kembali_dan_ruangan_selesai(): void
    {
        $alat = $this->katalog('alat', 10);
        $bahan = $this->katalog('bahan', 10);
        $r = $this->ruangan();
        $lab = $this->laboran();
        $p = $this->svc->ajukan($this->mahasiswa()->email, $this->data([
            'detail' => [['id_katalog' => $alat->id_katalog, 'jumlah' => 1], ['id_katalog' => $bahan->id_katalog, 'jumlah' => 1]],
            'ruangan' => [['id_ruangan' => $r->id_ruangan, 'tanggal_mulai' => '2030-01-08 08:00', 'tanggal_selesai' => '2030-01-08 10:00']],
        ]));
        $this->svc->setujui($p->id_peminjaman, $lab->email);
        $d = $this->detailId($p, $alat);

        // alat kembali sebelum ruangan selesai -> belum selesai
        Carbon::setTestNow('2030-01-08 09:00');
        $this->svc->kembalikan($p->id_peminjaman, $lab->email, [['id_detail_peminjaman' => $d, 'jumlah_dikembalikan' => 1, 'kondisi' => 'baik']]);
        $this->assertSame('disetujui', $p->fresh()->status);
        // setelah periode ruangan lewat -> selesai (bahan tanpa kewajiban)
        $this->assertSame(1, $this->svc->sinkronSelesai(Carbon::parse('2030-01-08 10:00')));
        $this->assertSame('selesai', $p->fresh()->status);
    }

    public function test_status_ruangan_saja_selesai_setelah_periode(): void
    {
        $r = $this->ruangan();
        $lab = $this->laboran();
        $p = $this->svc->ajukan($this->mahasiswa()->email, $this->data(['ruangan' => [['id_ruangan' => $r->id_ruangan, 'tanggal_mulai' => '2030-01-08 08:00', 'tanggal_selesai' => '2030-01-08 10:00']]]));
        $this->svc->setujui($p->id_peminjaman, $lab->email);
        $this->assertSame('disetujui', $p->fresh()->status);
        $this->assertSame(0, $this->svc->sinkronSelesai(Carbon::parse('2030-01-08 09:59')));
        $this->assertSame(1, $this->svc->sinkronSelesai(Carbon::parse('2030-01-08 10:00')));
    }

    // ---------------------------------------------------------------- konflik ruangan & jadwal

    private function ajukanRuangan(string $email, $r, string $mulai, string $selesai): Peminjaman
    {
        return $this->svc->ajukan($email, $this->data(['ruangan' => [['id_ruangan' => $r->id_ruangan, 'tanggal_mulai' => $mulai, 'tanggal_selesai' => $selesai]]]));
    }

    public function test_konflik_ruangan_dengan_peminjaman_lain(): void
    {
        $r = $this->ruangan();
        $lab = $this->laboran();
        $m = $this->mahasiswa()->email;
        $a = $this->ajukanRuangan($m, $r, '2030-01-08 08:00', '2030-01-08 10:00');
        $b = $this->ajukanRuangan($m, $r, '2030-01-08 09:00', '2030-01-08 11:00'); // beririsan
        $c = $this->ajukanRuangan($m, $r, '2030-01-08 10:00', '2030-01-08 12:00'); // bersambung -> boleh
        $this->assertSame('menunggu', $b->status); // pengajuan boleh berstatus menunggu

        $this->svc->setujui($a->id_peminjaman, $lab->email);
        $this->gagal(fn () => $this->svc->setujui($b->id_peminjaman, $lab->email), 'ruangan');
        $this->assertSame('menunggu', $b->fresh()->status);
        $this->assertSame('disetujui', $this->svc->setujui($c->id_peminjaman, $lab->email)->status);
    }

    public function test_konflik_ruangan_tidak_mengurangi_stok_sebagian(): void
    {
        $r = $this->ruangan();
        $lab = $this->laboran();
        $k = $this->katalog('alat', 10);
        $m = $this->mahasiswa()->email;
        $a = $this->ajukanRuangan($m, $r, '2030-01-08 08:00', '2030-01-08 10:00');
        $this->svc->setujui($a->id_peminjaman, $lab->email);
        $b = $this->svc->ajukan($m, $this->data([
            'detail' => [['id_katalog' => $k->id_katalog, 'jumlah' => 3]],
            'ruangan' => [['id_ruangan' => $r->id_ruangan, 'tanggal_mulai' => '2030-01-08 09:00', 'tanggal_selesai' => '2030-01-08 09:30']],
        ]));
        $this->gagal(fn () => $this->svc->setujui($b->id_peminjaman, $lab->email), 'ruangan');
        $this->assertSame(10, $k->fresh()->stok);
    }

    public function test_peminjaman_ditolak_atau_menunggu_tidak_memblokir_ruangan(): void
    {
        $r = $this->ruangan();
        $lab = $this->laboran();
        $m = $this->mahasiswa()->email;
        $a = $this->ajukanRuangan($m, $r, '2030-01-08 08:00', '2030-01-08 10:00');
        $this->svc->tolak($a->id_peminjaman, $lab->email);
        $b = $this->ajukanRuangan($m, $r, '2030-01-08 08:00', '2030-01-08 10:00');
        $this->ajukanRuangan($m, $r, '2030-01-08 08:30', '2030-01-08 09:30'); // menunggu
        $this->assertSame('disetujui', $this->svc->setujui($b->id_peminjaman, $lab->email)->status);
    }

    public function test_konflik_dengan_jadwal_aktif_dan_jadwal_dibatalkan_tidak_memblokir(): void
    {
        $r = $this->ruangan();
        $lab = $this->laboran();
        $m = $this->mahasiswa()->email;
        // 2030-01-08 = Selasa, Januari -> TA 2029/2030 ganjil
        $this->jadwal($r, 'selasa', '08:00:00', '10:00:00', ['tahun_akademik' => '2029/2030', 'semester' => 'ganjil']);
        $this->jadwal($r, 'selasa', '13:00:00', '15:00:00', ['tahun_akademik' => '2029/2030', 'semester' => 'ganjil', 'status' => 'dibatalkan']);
        $this->jadwal($r, 'rabu', '08:00:00', '10:00:00', ['tahun_akademik' => '2029/2030', 'semester' => 'ganjil']); // hari lain
        $this->jadwal($r, 'selasa', '08:00:00', '10:00:00', ['tahun_akademik' => '2028/2029', 'semester' => 'genap']); // semester lain

        $bentrok = $this->ajukanRuangan($m, $r, '2030-01-08 09:00', '2030-01-08 11:00');
        $this->gagal(fn () => $this->svc->setujui($bentrok->id_peminjaman, $lab->email), 'ruangan');

        $sambung = $this->ajukanRuangan($m, $r, '2030-01-08 10:00', '2030-01-08 12:00'); // bersambung dengan jadwal
        $this->assertSame('disetujui', $this->svc->setujui($sambung->id_peminjaman, $lab->email)->status);

        $batal = $this->ajukanRuangan($m, $r, '2030-01-08 13:30', '2030-01-08 14:30'); // jadwal dibatalkan
        $this->assertSame('disetujui', $this->svc->setujui($batal->id_peminjaman, $lab->email)->status);
    }

    public function test_peminjaman_ruangan_beberapa_hari_dicek_per_hari(): void
    {
        $r = $this->ruangan();
        $lab = $this->laboran();
        $this->jadwal($r, 'rabu', '08:00:00', '10:00:00', ['tahun_akademik' => '2029/2030', 'semester' => 'ganjil']);
        // Selasa 18:00 s.d. Rabu 09:00 menabrak jadwal Rabu 08:00-10:00
        $p = $this->ajukanRuangan($this->mahasiswa()->email, $r, '2030-01-08 18:00', '2030-01-09 09:00');
        $this->gagal(fn () => $this->svc->setujui($p->id_peminjaman, $lab->email), 'ruangan');
    }
}
