<?php

namespace Tests\Feature;

use App\Models\Akun;
use App\Models\AlatBahan;
use App\Models\Mahasiswa;
use App\Models\Peminjaman;
use App\Notifications\AkunTerverifikasiNotification;
use App\Notifications\PeminjamanDisetujui;
use App\Notifications\PeminjamanDitolak;
use App\Notifications\PeminjamanSelesai;
use App\Notifications\PengajuanBaru;
use App\Notifications\PengajuanDiterima;
use App\Notifications\PengembalianDicatat;
use App\Services\PeminjamanService;
use Illuminate\Contracts\Mail\Factory as MailFactory;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Mockery;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

/**
 * Tes fitur notifikasi tingkat penerimaan: alasan bisnis diuji end-to-end lewat HTTP,
 * isi pesan yang mendalam sudah diuji di NotifikasiAkunTest/NotifikasiPeminjamanTest/NotifikasiPengembalianTest.
 */
class NotifikasiFiturTest extends TestCase
{
    use MembuatData, RefreshDatabase;

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

    private function ajukan(string $email, AlatBahan $k, int $jumlah = 2): Peminjaman
    {
        return app(PeminjamanService::class)->ajukan($email, [
            'jenis_peminjaman' => 'pribadi',
            'tanggal_peminjaman' => $this->hariKerja(9),
            'tanggal_rencana_kembali' => $this->hariKerja(11),
            'keterangan' => 'Uji fitur notifikasi',
            'detail' => [['id_katalog' => $k->id_katalog, 'jumlah' => $jumlah]],
            'ruangan' => [],
        ]);
    }

    public function test_alur_penuh_akun_ajukan_setujui_kembalikan_mengirim_notifikasi_ke_penerima_yang_benar(): void
    {
        Notification::fake();

        $lab = $this->laboran();
        $akunLab = Akun::findOrFail($lab->email);
        $prodi = $this->prodi();

        // 1) Akun baru (dibuat Laboran lewat Kelola Data User) -> AkunTerverifikasiNotification.
        $this->sebagai('laboran', $lab)->post(route('kelola-user.store', 'mahasiswa'), [
            'nim' => '26990001', 'nama' => 'Mahasiswa Uji Notif', 'id_prodi' => $prodi->id_prodi,
            'email' => 'notif.alur@mhs.politala.ac.id', 'no_whatsapp' => '', 'password' => 'rahasia-uji',
        ])->assertSessionHasNoErrors();

        $akunMhs = Akun::findOrFail('notif.alur@mhs.politala.ac.id');
        $mhs = Mahasiswa::findOrFail('26990001');
        Notification::assertSentTo($akunMhs, AkunTerverifikasiNotification::class);

        // 2) Ajukan -> PengajuanDiterima ke peminjam + PengajuanBaru ke penyetuju (Laboran).
        $k = $this->katalog('alat', 10);
        $this->sebagai('mahasiswa', $mhs)->post(route('peminjaman.store'), [
            'jenis_peminjaman' => 'pribadi',
            'no_whatsapp' => '08123456789',
            'tanggal_peminjaman' => $this->hariKerja(9),
            'tanggal_rencana_kembali' => $this->hariKerja(11),
            'keterangan' => 'Uji alur notifikasi',
            'detail' => [['id_katalog' => $k->id_katalog, 'jumlah' => 2]],
            'ruangan' => [],
        ])->assertSessionHasNoErrors();

        $p = Peminjaman::latest('id_peminjaman')->firstOrFail();
        Notification::assertSentTo($akunMhs, PengajuanDiterima::class);
        Notification::assertSentTo($akunLab, PengajuanBaru::class);
        Notification::assertNotSentTo($akunMhs, PengajuanBaru::class);
        $this->assertSame(10, $k->fresh()->stok); // menunggu: stok belum dipotong

        // 3) Setujui -> PeminjamanDisetujui ke peminjam saja; stok berkurang.
        $this->sebagai('laboran', $lab)->post(route('pengajuan.setujui', $p->id_peminjaman))->assertSessionHas('success');

        Notification::assertSentTo($akunMhs, PeminjamanDisetujui::class);
        Notification::assertNotSentTo($akunLab, PeminjamanDisetujui::class);
        $this->assertSame('disetujui', $p->fresh()->status);
        $this->assertSame(8, $k->fresh()->stok);

        // 4) Kembalikan -> PengembalianDicatat ke peminjam; lunas -> PeminjamanSelesai; stok kembali.
        $idDetail = $p->details()->value('id_detail');
        $this->sebagai('laboran', $lab)->post(route('pengembalian.store', $p->id_peminjaman), [
            'jumlah' => [$idDetail => 2], 'kondisi' => [$idDetail => 'baik'],
        ])->assertSessionHas('success');

        Notification::assertSentTo($akunMhs, PengembalianDicatat::class);
        Notification::assertNotSentTo($akunLab, PengembalianDicatat::class);
        Notification::assertSentTo($akunMhs, PeminjamanSelesai::class);
        $this->assertSame('selesai', $p->fresh()->status);
        $this->assertSame(10, $k->fresh()->stok);
    }

    public function test_tolak_mengirim_notifikasi_ke_peminjam_saja_dan_stok_tidak_berubah(): void
    {
        Notification::fake();

        $m = $this->mahasiswa();
        $lab = $this->laboran();
        $akunMhs = Akun::findOrFail($m->email);
        $akunLab = Akun::findOrFail($lab->email);
        $k = $this->katalog('alat', 10);

        $p = $this->ajukan($m->email, $k, 2);

        $this->sebagai('laboran', $lab)
            ->post(route('pengajuan.tolak', $p->id_peminjaman), ['alasan' => 'Dipakai praktikum lain'])
            ->assertSessionHas('success');

        Notification::assertSentTo($akunMhs, PeminjamanDitolak::class);
        Notification::assertNotSentTo($akunLab, PeminjamanDitolak::class);
        Notification::assertNotSentTo($akunMhs, PeminjamanDisetujui::class);
        $this->assertSame('ditolak', $p->fresh()->status);
        $this->assertSame(10, $k->fresh()->stok);
    }

    public function test_error_email_tidak_menggagalkan_transaksi_stok(): void
    {
        $m = $this->mahasiswa();
        $lab = $this->laboran();
        $k = $this->katalog('alat', 10);

        $p = Peminjaman::create([
            'email_peminjam' => $m->email, 'jenis_peminjaman' => 'pribadi',
            'tanggal_pengajuan' => now(), 'tanggal_peminjaman' => now()->addDay(),
            'tanggal_rencana_kembali' => now()->addDays(2), 'status' => 'menunggu',
        ]);
        $p->details()->create(['id_katalog' => $k->id_katalog, 'jumlah' => 2]);

        // Server SMTP "mati": setiap usaha kirim email melempar error.
        $mailer = Mockery::mock(Mailer::class);
        $mailer->shouldReceive('send')->andThrow(new \RuntimeException('SMTP uji dimatikan'));
        $manager = Mockery::mock(MailFactory::class);
        $manager->shouldReceive('mailer')->andReturn($mailer);
        $this->app->instance('mail.manager', $manager);

        Log::spy();

        $this->sebagai('laboran', $lab)
            ->post(route('pengajuan.setujui', $p->id_peminjaman))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        // Transaksi stok tetap commit walau pengiriman notifikasi gagal.
        $this->assertSame('disetujui', $p->fresh()->status);
        $this->assertSame(8, $k->fresh()->stok);

        Log::shouldHaveReceived('warning')->withArgs(
            fn ($pesan) => is_string($pesan) && str_contains($pesan, 'Notif gagal dikirim ke ' . $m->email)
        )->atLeast()->once();
    }
}
