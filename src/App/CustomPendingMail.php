<?php

namespace Akhan619\LaravelSesEventManager\App;

use Illuminate\Contracts\Mail\Mailable as MailableContract;
use Illuminate\Mail\PendingMail;

class CustomPendingMail extends PendingMail
{
    /**
     * Send a new mailable message instance.
     *
     * @param \Illuminate\Contracts\Mail\Mailable $mailable
     *
     * @return void
     */
    public function send(MailableContract $mailable)
    {
        return $this->mailer->send($this->fill($mailable));
    }
}
