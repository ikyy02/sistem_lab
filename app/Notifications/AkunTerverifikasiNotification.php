<?php

namespace App\Notifications;

use App\Notifications\Concerns\MengirimEmail;
use App\Support\Role;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AkunTerverifikasiNotification extends Notification implements ShouldQueue
{
    use MengirimEmail, Queueable;

    public function __construct(
        public string $nama,
        public string $role,
        public string $email,
    ) {
        $this->afterCommit = true;
    }

    public function via(object $notifiable): array
    {
        return config('notifikasi.kirim_email_akun', true)
            ? ['mail', 'database']
            : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->siapkanEmail(
            '[SIARKA] Akun Anda telah aktif',
            ['emails.akun-terverifikasi', 'emails.akun-terverifikasi-text'],
            [
                'judul' => 'Akun Anda telah aktif',
                'nama' => $this->nama,
                'role' => Role::LABELS[$this->role],
                'email' => $this->email,
                'tautan' => rtrim((string) config('app.url'), '/').route('login', [], false),
            ],
        );
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'judul' => 'Akun Aktif',
            'pesan' => 'Akun '.$this->email.' ('.Role::LABELS[$this->role].') telah dibuat. Password diberikan terpisah oleh Laboran.',
        ];
    }
}
