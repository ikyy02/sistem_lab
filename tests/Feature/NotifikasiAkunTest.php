<?php

namespace Tests\Feature;

use App\Models\Akun;
use App\Notifications\AkunTerverifikasiNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\MembacaEmail;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

class NotifikasiAkunTest extends TestCase
{
    use MembacaEmail, MembuatData, RefreshDatabase;

    private function sebagai(string $role, $profil): self
    {
        return $this->withSession(['silab_user' => ['role' => $role, 'key' => (string) $profil->getKey(), 'nama' => $profil->nama, 'email' => $profil->email]]);
    }

    public function test_tambah_user_mengirim_notifikasi_akun_terverifikasi(): void
    {
        Notification::fake();
        $password = 'rahasia-1234';

        $this->sebagai('laboran', $this->laboran())
            ->post(route('kelola-user.store', 'laboran'), [
                'id_pegawai' => 'LB9',
                'nama' => 'Lab Baru',
                'email' => 'lab9@uji.test',
                'no_whatsapp' => '',
                'password' => $password,
            ])
            ->assertRedirect(route('kelola-user.index', ['kategori' => 'laboran']))
            ->assertSessionHasNoErrors();

        $akun = Akun::findOrFail('lab9@uji.test');

        Notification::assertSentTo($akun, AkunTerverifikasiNotification::class, function (AkunTerverifikasiNotification $notification) use ($akun, $password) {
            $this->assertSame('Lab Baru', $notification->nama);
            $this->assertSame('laboran', $notification->role);
            $this->assertSame('lab9@uji.test', $notification->email);
            $this->assertInstanceOf(ShouldQueue::class, $notification);
            $this->assertTrue($notification->afterCommit);
            $this->assertSame(['mail', 'database'], $notification->via($akun));

            $mail = $notification->toMail($akun);
            $this->assertStringStartsWith('[SIARKA]', $mail->subject);
            $this->assertSame(
                rtrim((string) config('app.url'), '/').route('login', [], false),
                $mail->viewData['tautan'],
            );

            $isi = $this->teksEmail($mail);
            $this->assertStringContainsString('Lab Baru', $isi);
            $this->assertStringContainsString('Laboran/Admin', $isi);
            $this->assertStringContainsString('lab9@uji.test', $isi);
            $this->assertStringContainsString('Password diberikan terpisah oleh Laboran', $isi);
            $this->assertStringNotContainsString($password, $mail->subject.' '.$this->isiEmail($mail));

            $database = $notification->toDatabase($akun);
            $this->assertArrayHasKey('judul', $database);
            $this->assertArrayHasKey('pesan', $database);
            $this->assertStringNotContainsString($password, $database['pesan']);

            return true;
        });
    }

    public function test_toggle_config_mematikan_email_akun(): void
    {
        config(['notifikasi.kirim_email_akun' => false]);

        $akun = Akun::findOrFail($this->akun('laboran', 'toggle@uji.test')->email);
        $notification = new AkunTerverifikasiNotification('Lab Baru', 'laboran', 'toggle@uji.test');

        $this->assertSame(['database'], $notification->via($akun));
    }
}
