<?php

namespace Tests\Feature;

use App\Models\Akun;
use App\Models\Peminjaman;
use App\Notifications\PeminjamanDibatalkan;
use App\Notifications\PeminjamanDisetujui;
use App\Notifications\PeminjamanDitolak;
use App\Notifications\PengajuanBaru;
use App\Notifications\PengajuanDiterima;
use App\Services\PeminjamanService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\MembacaEmail;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

class NotifikasiPeminjamanTest extends TestCase
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

    public function test_pengajuan_pribadi_mengirim_notifikasi_ke_peminjam_dan_semua_laboran(): void
    {
        Notification::fake();
        $m = $this->mahasiswa();
        $lab1 = $this->laboran();
        $lab2 = $this->laboran();
        $dosen = $this->dosen();
        $k = $this->katalog('alat', 5);
        $r = $this->ruangan();

        $this->sebagai('mahasiswa', $m)->post(route('peminjaman.store'), [
            'jenis_peminjaman' => 'pribadi',
            'no_whatsapp' => '08123456789',
            'tanggal_peminjaman' => $this->hariKerja(9),
            'tanggal_rencana_kembali' => $this->hariKerja(11),
            'keterangan' => 'Praktikum jaringan',
            'detail' => [['id_katalog' => $k->id_katalog, 'jumlah' => 2]],
            'ruangan' => [['id_ruangan' => $r->id_ruangan]],
        ])->assertRedirect(route('peminjaman.riwayat'))->assertSessionHasNoErrors();

        $p = Peminjaman::latest('id_peminjaman')->firstOrFail();
        $akunPeminjam = Akun::findOrFail($m->email);

        Notification::assertSentTo($akunPeminjam, PengajuanDiterima::class, function (PengajuanDiterima $n) use ($p, $akunPeminjam, $m, $k, $r) {
            $this->assertSame($p->id_peminjaman, $n->peminjaman->id_peminjaman);
            $this->assertInstanceOf(ShouldQueue::class, $n);
            $this->assertTrue($n->afterCommit);
            $this->assertSame(['mail', 'database'], $n->via($akunPeminjam));

            $mail = $n->toMail($akunPeminjam);
            $isi = $this->teksEmail($mail);
            $this->assertStringContainsString('#'.$p->id_peminjaman, $isi);
            $this->assertStringContainsString($m->nama, $isi);
            $this->assertStringContainsString('Peminjaman Mandiri', $isi);
            $this->assertStringContainsString($r->nama_ruangan, $isi);
            $this->assertStringContainsString($k->nama.' (Alat) x 2', $isi);
            $this->assertStringContainsString('Menunggu Persetujuan', $isi);
            $this->assertSame(
                rtrim((string) config('app.url'), '/').route('peminjaman.show', $p->id_peminjaman, false),
                $mail->viewData['tautan'],
            );

            $database = $n->toDatabase($akunPeminjam);
            $this->assertStringContainsString('#'.$p->id_peminjaman, $database['pesan']);

            return true;
        });

        Notification::assertSentTo(Akun::findOrFail($lab1->email), PengajuanBaru::class);
        Notification::assertSentTo(Akun::findOrFail($lab2->email), PengajuanBaru::class);
        Notification::assertNotSentTo(Akun::findOrFail($dosen->email), PengajuanBaru::class);
        Notification::assertNotSentTo(Akun::findOrFail($lab1->email), PengajuanDiterima::class);
    }

    public function test_pengajuan_atas_dosen_mengirim_notifikasi_ke_dosen_penanggung_jawab(): void
    {
        Notification::fake();
        $m = $this->mahasiswa();
        $dosen = $this->dosen();
        $lab = $this->laboran();
        $k = $this->katalog('alat', 5);

        $this->sebagai('mahasiswa', $m)->post(route('peminjaman.store'), [
            'jenis_peminjaman' => 'atas_dosen',
            'email_dosen' => $dosen->email,
            'no_whatsapp' => '08123456789',
            'tanggal_peminjaman' => $this->hariKerja(9),
            'tanggal_rencana_kembali' => $this->hariKerja(11),
            'keterangan' => 'Praktikum jaringan',
            'detail' => [['id_katalog' => $k->id_katalog, 'jumlah' => 1]],
        ])->assertSessionHasNoErrors();

        Notification::assertSentTo(Akun::findOrFail($dosen->email), PengajuanBaru::class, function (PengajuanBaru $n) use ($dosen) {
            $mail = $n->toMail(Akun::findOrFail($dosen->email));
            $this->assertStringContainsString('Peminjaman dengan Penanggung Jawab Dosen', $this->teksEmail($mail));

            return true;
        });
        Notification::assertSentTo(Akun::findOrFail($m->email), PengajuanDiterima::class);
        Notification::assertNotSentTo(Akun::findOrFail($lab->email), PengajuanBaru::class);
    }

    public function test_pengajuan_dosen_pembelajaran_mengirim_notifikasi_ke_semua_laboran(): void
    {
        Notification::fake();
        Carbon::setTestNow('2030-01-07 09:00:00'); // Senin
        try {
            $dosen = $this->dosen();
            $lab = $this->laboran();
            $k = $this->katalog('alat', 5);

            $this->sebagai('dosen', $dosen)->post(route('peminjaman.store'), [
                'no_whatsapp' => '08123456789',
                'tanggal_peminjaman' => '2030-01-08 09:00',
                'tanggal_rencana_kembali' => '2030-01-08 11:00',
                'detail' => [['id_katalog' => $k->id_katalog, 'jumlah' => 1]],
            ])->assertSessionHasNoErrors();

            $p = Peminjaman::latest('id_peminjaman')->firstOrFail();
            $this->assertSame(['pembelajaran', null], [$p->jenis_peminjaman, $p->email_dosen]);

            Notification::assertSentTo(Akun::findOrFail($dosen->email), PengajuanDiterima::class);
            Notification::assertSentTo(Akun::findOrFail($lab->email), PengajuanBaru::class);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_setujui_mengirim_notifikasi_ke_peminjam(): void
    {
        Notification::fake();
        $m = $this->mahasiswa();
        $lab = $this->laboran();
        $k = $this->katalog('alat', 10);

        $p = $this->ajukan($m->email, ['detail' => [['id_katalog' => $k->id_katalog, 'jumlah' => 2]]]);

        $this->sebagai('laboran', $lab)->post(route('pengajuan.setujui', $p->id_peminjaman))->assertSessionHas('success');

        Notification::assertSentTo(Akun::findOrFail($m->email), PeminjamanDisetujui::class, function (PeminjamanDisetujui $n) use ($m, $p) {
            $mail = $n->toMail(Akun::findOrFail($m->email));
            $isi = $this->teksEmail($mail);
            $this->assertStringContainsString('#'.$p->id_peminjaman, $isi);
            $this->assertStringContainsString('Disetujui', $isi);
            $this->assertSame(
                rtrim((string) config('app.url'), '/').route('peminjaman.show', $p->id_peminjaman, false),
                $mail->viewData['tautan'],
            );

            return true;
        });
    }

    public function test_tolak_mengirim_notifikasi_alasan_ke_peminjam(): void
    {
        Notification::fake();
        $m = $this->mahasiswa();
        $lab = $this->laboran();
        $k = $this->katalog('alat', 10);

        $p = $this->ajukan($m->email, ['detail' => [['id_katalog' => $k->id_katalog, 'jumlah' => 3]]]);

        $this->sebagai('laboran', $lab)
            ->post(route('pengajuan.tolak', $p->id_peminjaman), ['alasan' => 'Dipakai praktikum lain'])
            ->assertSessionHas('success');

        Notification::assertSentTo(Akun::findOrFail($m->email), PeminjamanDitolak::class, function (PeminjamanDitolak $n) use ($m, $p) {
            $akun = Akun::findOrFail($m->email);
            $mail = $n->toMail($akun);
            $isi = $this->teksEmail($mail);
            $this->assertStringContainsString('Ditolak', $isi);
            $this->assertStringContainsString('Dipakai praktikum lain', $isi);
            $this->assertStringContainsString('Dipakai praktikum lain', $n->toDatabase($akun)['pesan']);
            $this->assertSame(
                rtrim((string) config('app.url'), '/').route('peminjaman.show', $p->id_peminjaman, false),
                $mail->viewData['tautan'],
            );

            return true;
        });
    }

    public function test_batalkan_mengirim_notifikasi_ke_penyetuju(): void
    {
        Notification::fake();
        $m = $this->mahasiswa();
        $dosen = $this->dosen();
        $lab = $this->laboran();

        $r1 = $this->ruangan();
        $pribadi = $this->ajukan($m->email, ['ruangan' => [['id_ruangan' => $r1->id_ruangan]]]);
        $this->sebagai('mahasiswa', $m)->post(route('peminjaman.batal', $pribadi->id_peminjaman))->assertSessionHas('success');

        $r2 = $this->ruangan();
        $atasDosen = $this->ajukan($m->email, [
            'jenis_peminjaman' => 'atas_dosen',
            'email_dosen' => $dosen->email,
            'ruangan' => [['id_ruangan' => $r2->id_ruangan]],
        ]);
        $this->sebagai('mahasiswa', $m)->post(route('peminjaman.batal', $atasDosen->id_peminjaman))->assertSessionHas('success');

        Notification::assertSentTo(Akun::findOrFail($lab->email), PeminjamanDibatalkan::class);
        Notification::assertSentTo(Akun::findOrFail($dosen->email), PeminjamanDibatalkan::class);
        Notification::assertNotSentTo(Akun::findOrFail($m->email), PeminjamanDibatalkan::class);
    }
}
