<?php

namespace Akhan619\LaravelSesEventManager\Mocking;

use Akhan619\LaravelSesEventManager\LaravelSesEventManagerServiceProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class TestQueuedMailable extends Mailable implements ShouldQueue
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
        return $this->view(Str::studly(LaravelSesEventManagerServiceProvider::PREFIX).'::test');
    }
}
