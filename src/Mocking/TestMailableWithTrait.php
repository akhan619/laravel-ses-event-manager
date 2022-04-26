<?php

namespace Akhan619\LaravelSesEventManager\Mocking;

use Akhan619\LaravelSesEventManager\LaravelSesEventManagerServiceProvider;
use Akhan619\LaravelSesEventManager\Traits\QueueForCustomMailer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class TestMailableWithTrait extends Mailable
{
    use Queueable;
    use SerializesModels;
    use QueueForCustomMailer;

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
        return $this->view(Str::studly(LaravelSesEventManagerServiceProvider::PREFIX).'::test');
    }
}
