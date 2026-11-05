<?php

namespace Tests\Feature;

use App\Models\Akun;
use App\Models\AlatBahan;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\PengembalianDetail;
use App\Notifications\PeminjamanSelesai;
use App\Notifications\PeminjamanTerlambat;
use App\Notifications\PengembalianDicatat;
use App\Notifications\PengingatPengembalian;
use App\Services\PeminjamanService;
use App\Services\PengingatPeminjamanService;
use App\Services\SinkronSelesaiService;
use App\Support\Notif;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\MembacaEmail;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

class NotifikasiPengembalianTest extends TestCase
{
    use MembacaEmail, MembuatData, RefreshDatabase;

    private function sebagai(string $role, $profil): self
    {
        return $this->withSession(['silab_user' => ['role' => $role, 'key' => (string) $profil->getKey(), 'nama' => $profil->nama, 'email' => $profil->email]]);
    }

    private function hariKerja(int $jam): string
    {
        $t = now()->addDay()->startOfDay();
        while ($t->isWeekend()) {
            $t->addDay();
        }

        return $t->setTime($jam, 0)->format('Y-m-d H:i:s');
    }

    private function ajukan(string $email, array $o = []): Peminjaman
    {
        return app(PeminjamanService::class)->ajukan($email, $o + [
            'jenis_peminjaman' => 'pribadi',
            'tanggal_peminjaman' => $this->hariKerja(9),
            'tanggal_rencana_kembali' => $this->hariKerja(11),
            'keterangan' => 'Praktikum jaringan',
            'detail' => [],
            'ruangan' => [],
        ]);
    }

    private function disetujui(string $email, AlatBahan $katalog, string $rencana, int $jumlah = 2): Peminjaman
    {
        $p = Peminjaman::create([
            'email_peminjam' => $email, 'jenis_peminjaman' => 'pribadi',
            'tanggal_pengajuan' => now(), 'tanggal_peminjaman' => now()->subDay(),
            'tanggal_rencana_kembali' => $rencana, 'status' => 'disetujui',
        ]);
        $p->details()->create(['id_katalog' => $katalog->id_katalog, 'jumlah' => $jumlah]);

        return $p;
    }

    private function isi($mail): string
    {
        return $this->teksEmail($mail);
    }

    public function test_pengembalian_mengirim_rincian_tabel_sisa_dan_blok_peringatan_rusak(): void
    {
        Notification::fake();
        $m = $this->mahasiswa();
        $lab = $this->laboran();
        $k = $this->katalog('alat', 10);

        $p = $this->ajukan($m->email, ['detail' => [['id_katalog' => $k->id_katalog, 'jumlah' => 3]]]);
        app(PeminjamanService::class)->setujui($p->id_peminjaman, $lab->email);
        $idDetail = $p->details()->value('id_detail');
        $akunM = Akun::findOrFail($m->email);
        $akunLab = Akun::findOrFail($lab->email);

        // pengembalian sebagian: tabel kondisi, sisa 1, tanpa peringatan, belum selesai
        $this->sebagai('laboran', $lab)->post(route('pengembalian.store', $p->id_peminjaman), [
            'jumlah' => [$idDetail => 2], 'kondisi' => [$idDetail => 'baik'], 'keterangan' => 'Dikembalikan awal',
        ])->assertSessionHasNoErrors();

        Notification::assertSentTo($akunM, PengembalianDicatat::class, function (PengembalianDicatat $n) use ($akunM, $m, $lab, $k, $p) {
            $this->assertInstanceOf(ShouldQueue::class, $n);
            $this->assertTrue($n->afterCommit);
            $this->assertSame(['mail', 'database'], $n->via($akunM));

            $mail = $n->toMail($akunM);
            $isi = $this->isi($mail);
            $this->assertStringContainsString('Barang yang dikembalikan', $isi);
            $this->assertStringContainsString($k->nama.': 2 (Baik)', $isi);
            $this->assertStringContainsString($k->nama.': 1', $isi);
            $this->assertStringNotContainsString('kondisi rusak', $isi);
            $this->assertStringContainsString('Nama penerima: '.$lab->nama, $isi);
            $this->assertStringContainsString($m->nama, $isi);
            $this->assertSame(
                rtrim((string) config('app.url'), '/').route('peminjaman.show', $p->id_peminjaman, false),
                $mail->viewData['tautan'],
            );

            $pesan = $n->toDatabase($akunM)['pesan'];
            $this->assertStringContainsString('2 barang baik', $pesan);
            $this->assertStringContainsString('1 belum kembali', $pesan);

            return true;
        });
        Notification::assertNotSentTo($akunM, PeminjamanSelesai::class);
        Notification::assertNotSentTo($akunLab, PengembalianDicatat::class);
        $this->assertSame('disetujui', $p->fresh()->status);

        // sisa dikembalikan rusak -> blok peringatan + PeminjamanSelesai tepat sekali
        $this->sebagai('laboran', $lab)->post(route('pengembalian.store', $p->id_peminjaman), [
            'jumlah' => [$idDetail => 1], 'kondisi' => [$idDetail => 'rusak'],
        ])->assertSessionHasNoErrors();

        $this->assertSame('selesai', $p->fresh()->status);
        $terkirim = Notification::sent($akunM, PengembalianDicatat::class);
        $this->assertCount(2, $terkirim);
        $terakhir = $terkirim->last();
        $isi = $this->isi($terakhir->toMail($akunM));
        $this->assertStringContainsString($k->nama.': 1 (Rusak)', $isi);
        $this->assertStringContainsString('kondisi rusak', $isi);
        $this->assertStringContainsString('Alat terkait: '.$k->nama.' (1)', $isi);
        $this->assertStringContainsString('Semua barang sudah dikembalikan.', $isi);
        $this->assertStringContainsString('1 rusak', $terakhir->toDatabase($akunM)['pesan']);
        Notification::assertSentToTimes($akunM, PeminjamanSelesai::class, 1);

        // sudah selesai: percobaan pengembalian lagi ditolak service, jumlah notifikasi tidak bertambah
        $this->sebagai('laboran', $lab)->post(route('pengembalian.store', $p->id_peminjaman), [
            'jumlah' => [$idDetail => 1], 'kondisi' => [$idDetail => 'baik'],
        ])->assertSessionHasErrors('status');
        Notification::assertSentToTimes($akunM, PeminjamanSelesai::class, 1);
    }

    public function test_selesaikan_mengirim_peminjaman_selesai_sekali(): void
    {
        Notification::fake();
        $m = $this->mahasiswa();
        $lab = $this->laboran();
        $r = $this->ruangan();

        $p = app(PeminjamanService::class)->ajukan($m->email, [
            'jenis_peminjaman' => 'pribadi',
            'tanggal_peminjaman' => '2030-01-08 08:00',
            'tanggal_rencana_kembali' => '2030-01-08 10:00',
            'keterangan' => 'Praktikum jaringan',
            'detail' => [],
            'ruangan' => [['id_ruangan' => $r->id_ruangan]],
        ]);
        app(PeminjamanService::class)->setujui($p->id_peminjaman, $lab->email);

        Carbon::setTestNow('2030-01-08 10:01');
        try {
            $akunM = Akun::findOrFail($m->email);
            $this->sebagai('mahasiswa', $m)->post(route('peminjaman.selesai', $p->id_peminjaman))->assertSessionHas('success');
            $this->assertSame('selesai', $p->fresh()->status);

            Notification::assertSentTo($akunM, PeminjamanSelesai::class, function (PeminjamanSelesai $n) use ($akunM, $p) {
                $mail = $n->toMail($akunM);
                $this->assertSame('[SIARKA] Peminjaman #'.$p->id_peminjaman.' selesai', $mail->subject);
                $this->assertStringContainsString('selesai', $this->isi($mail));
                $this->assertSame(
                    rtrim((string) config('app.url'), '/').route('peminjaman.show', $p->id_peminjaman, false),
                    $mail->viewData['tautan'],
                );

                return true;
            });

            // klik ulang ditolak service (status bukan disetujui lagi): tidak ada kiriman kedua
            $this->sebagai('mahasiswa', $m)->post(route('peminjaman.selesai', $p->id_peminjaman))->assertSessionHasErrors('status');
            Notification::assertSentToTimes($akunM, PeminjamanSelesai::class, 1);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_sinkron_selesai_mengirim_notifikasi_sekali_per_transaksi(): void
    {
        Notification::fake();
        Carbon::setTestNow('2030-01-10 08:00:00');
        try {
            $m = $this->mahasiswa();
            $lab = $this->laboran();
            $bahan = $this->katalog('bahan', 10);
            $p = $this->ajukan($m->email, [
                'tanggal_peminjaman' => '2030-01-10 08:00',
                'tanggal_rencana_kembali' => '2030-01-10 10:00',
                'detail' => [['id_katalog' => $bahan->id_katalog, 'jumlah' => 2]],
            ]);
            app(PeminjamanService::class)->setujui($p->id_peminjaman, $lab->email);

            Carbon::setTestNow('2030-01-12 08:00:00');
            $akunM = Akun::findOrFail($m->email);
            $svc = app(SinkronSelesaiService::class);

            $this->assertSame(1, $svc->jalankan());
            $this->assertSame('selesai', $p->fresh()->status);
            Notification::assertSentToTimes($akunM, PeminjamanSelesai::class, 1);

            // evaluasi berikutnya: status sudah selesai -> tidak ada kiriman baru
            $this->assertSame(0, $svc->jalankan());
            Notification::assertSentToTimes($akunM, PeminjamanSelesai::class, 1);

            // penjaga cache juga mencegah duplikat dari jalur lain (mis. balapan dengan controller)
            Notif::peminjamanSelesai($p->fresh());
            Notification::assertSentToTimes($akunM, PeminjamanSelesai::class, 1);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_pengingat_harian_h1_dan_terlambat_masing_masing_sekali(): void
    {
        Notification::fake();
        Carbon::setTestNow('2030-01-10 07:00:00');
        try {
            $m1 = $this->mahasiswa();
            $m2 = $this->mahasiswa();
            $m3 = $this->mahasiswa();
            $m4 = $this->mahasiswa();
            $lab = $this->laboran();
            $k = $this->katalog('alat', 10);

            $this->disetujui($m1->email, $k, '2030-01-11 10:00');          // jatuh tempo besok
            $this->disetujui($m2->email, $k, '2030-01-09 10:00');          // sudah lewat
            $this->disetujui($m3->email, $k, '2030-01-12 10:00');          // masih dua hari lagi
            $lunas = $this->disetujui($m4->email, $k, '2030-01-11 10:00'); // besok tetapi alat sudah kembali
            $pg = Pengembalian::create(['id_peminjaman' => $lunas->id_peminjaman, 'tanggal_pengembalian' => now(), 'email_penerima' => $lab->email]);
            PengembalianDetail::create([
                'id_pengembalian' => $pg->id_pengembalian,
                'id_detail_peminjaman' => $lunas->details()->value('id_detail'),
                'jumlah_dikembalikan' => 2, 'kondisi' => 'baik',
            ]);

            $svc = app(PengingatPeminjamanService::class);
            $this->assertSame(2, $svc->kirim());

            $a1 = Akun::findOrFail($m1->email);
            $a2 = Akun::findOrFail($m2->email);
            $a4 = Akun::findOrFail($m4->email);

            Notification::assertSentTo($a1, PengingatPengembalian::class, function (PengingatPengembalian $n) use ($a1, $k) {
                $isi = $this->isi($n->toMail($a1));
                $this->assertStringContainsString('besok', $isi);
                $this->assertStringContainsString('11 Januari 2030 pukul 10.00 WITA', $isi);
                $this->assertStringContainsString($k->nama, $isi);

                return true;
            });
            Notification::assertSentTo($a2, PeminjamanTerlambat::class, function (PeminjamanTerlambat $n) use ($a2) {
                $mail = $n->toMail($a2);
                $isi = $this->isi($mail);
                $this->assertStringContainsString('terlambat dikembalikan', $mail->subject);
                $this->assertStringContainsString('melewati tanggal rencana pengembalian', $isi);
                $this->assertStringContainsString('9 Januari 2030 pukul 10.00 WITA', $isi);

                return true;
            });
            Notification::assertNotSentTo(Akun::findOrFail($m3->email), PengingatPengembalian::class);
            Notification::assertNotSentTo(Akun::findOrFail($m3->email), PeminjamanTerlambat::class);
            Notification::assertNotSentTo($a4, PengingatPengembalian::class);
            Notification::assertNotSentTo($a4, PeminjamanTerlambat::class);

            // dijalankan ulang di hari yang sama: dijaga cache per (id_peminjaman, jenis, tanggal rencana)
            $this->assertSame(0, $svc->kirim());
            Notification::assertSentToTimes($a1, PengingatPengembalian::class, 1);
            Notification::assertSentToTimes($a2, PeminjamanTerlambat::class, 1);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_jadwal_pengingat_dan_sinkron_terdaftar(): void
    {
        $this->artisan('schedule:list')->assertSuccessful();

        $deskripsi = collect(app(Schedule::class)->events())->pluck('description');
        $this->assertTrue($deskripsi->contains('pengingat-peminjaman'));
        $this->assertTrue($deskripsi->contains('peminjaman-sinkron-selesai'));
    }
}
