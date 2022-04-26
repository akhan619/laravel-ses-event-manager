<?php

namespace Akhan619\LaravelSesEventManager\Mocking;

use Akhan619\LaravelSesEventManager\LaravelSesEventManagerServiceProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class TestMailableWithRecipient extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->to('john@doe.com')
        ->view(Str::studly(LaravelSesEventManagerServiceProvider::PREFIX).'::test');
    }
}
