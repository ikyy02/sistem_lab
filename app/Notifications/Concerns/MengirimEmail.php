<?php

namespace App\Notifications\Concerns;

use Illuminate\Notifications\Messages\MailMessage;

trait MengirimEmail
{
    /**
     * @param  array<int, string>  $views
     * @param  array<string, mixed>  $data
     */
    protected function siapkanEmail(string $subjek, array $views, array $data): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($subjek)
            ->view($views, $data);

        $replyTo = config('notifikasi.reply_to');

        if (is_string($replyTo) && $replyTo !== '') {
            $mail->replyTo($replyTo, config('app.name'));
        }

        return $mail;
    }
}
