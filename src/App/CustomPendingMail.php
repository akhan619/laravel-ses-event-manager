<?php

namespace Akhan619\LaravelSesEventManager\App;

use Illuminate\Mail\PendingMail;
use Illuminate\Contracts\Mail\Mailable as MailableContract;

class CustomPendingMail extends PendingMail
{
    /**
     * Send a new mailable message instance.
     *
     * @param  \Illuminate\Contracts\Mail\Mailable  $mailable
     * @return void
     */
    public function send(MailableContract $mailable)
    {
        return $this->mailer->send($this->fill($mailable));
    }
}