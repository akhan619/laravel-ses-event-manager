<?php

namespace Akhan619\LaravelSesEventManager\Mocking;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;
use Akhan619\LaravelSesEventManager\LaravelSesEventManagerServiceProvider;
use Akhan619\LaravelSesEventManager\Traits\ModifiesBaseMailable;

class TestMailableWithTrait extends Mailable
{
    use Queueable, SerializesModels, ModifiesBaseMailable;

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
        return $this->view(Str::studly(LaravelSesEventManagerServiceProvider::PREFIX) . '::test');
    }
}