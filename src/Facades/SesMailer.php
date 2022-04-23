<?php

namespace Akhan619\LaravelSesEventManager\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Testing\Fakes\MailFake;

class SesMailer extends Facade
{
    /**
     * Replace the bound instance with a fake.
     *
     * @return \Illuminate\Support\Testing\Fakes\MailFake
     */
    public static function fake()
    {
        // We use the default mail fake provided by laravel since we don't really have
        // any specific functionality in our SesMailer that we need to fake. And the
        // default works since there are no mail drivers associated with faking. 
        static::swap($fake = new MailFake);

        return $fake;
    }

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'SesMailer';
    }
}
