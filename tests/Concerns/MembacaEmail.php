<?php

namespace Tests\Concerns;

use Illuminate\Notifications\Messages\MailMessage;

trait MembacaEmail
{
    protected function htmlEmail(MailMessage $mail): string
    {
        $views = array_values((array) $mail->view);

        return view($views[0], $mail->viewData)->render();
    }

    protected function teksEmail(MailMessage $mail): string
    {
        $views = array_values((array) $mail->view);

        return view($views[1], $mail->viewData)->render();
    }

    protected function isiEmail(MailMessage $mail): string
    {
        return $this->htmlEmail($mail)."\n".$this->teksEmail($mail);
    }
}
