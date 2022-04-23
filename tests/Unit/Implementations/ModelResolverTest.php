<?php

namespace Akhan619\LaravelSesEventManager\Tests\Unit\Implementations;

use Akhan619\LaravelSesEventManager\App\Models\Email;
use Akhan619\LaravelSesEventManager\App\Models\EmailDeliveryDelay;
use Akhan619\LaravelSesEventManager\Contracts\ModelResolverContract;
use Akhan619\LaravelSesEventManager\Tests\UnitTestCase;

class ModelResolverTest extends UnitTestCase
{
    protected function setOptions($app)
    {
        $app['config']->set('laravel-ses-event-manager.resolved_models.emails', \Akhan619\LaravelSesEventManager\App\Models\Email::class);
        $app['config']->set('laravel-ses-event-manager.resolved_models.delivery_delays', EmailDeliveryDelay::class);
    }
    
    /**
     * @test
     * @define-env setOptions
     */
    public function modelResolverReturnTheRightModel()
    {
        $resolver = app()->make(ModelResolverContract::class);

        $this->assertEquals($resolver->getModelName('emails'), Email::class);
        $this->assertEquals($resolver->getModelName('delivery_delays'), EmailDeliveryDelay::class);
    } 
}