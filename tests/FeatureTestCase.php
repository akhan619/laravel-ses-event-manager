<?php

namespace Akhan619\LaravelSesEventManager\Tests;

use Akhan619\LaravelSesEventManager\LaravelSesEventManagerServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class FeatureTestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        // Code before application created.

        parent::setUp();

        // Code after application created.
        $this->withoutExceptionHandling();
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelSesEventManagerServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.debug', false);    
            
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
            'foreign_key_constraints' => true,
        ]);

        $app['config']->set('laravel-ses-event-manager.active_email_events', [
            'sends' => true,
            'rendering_failures' => true,
            'rejects' => true,
            'deliveries' => true,
            'bounces' => true,
            'complaints' => true,
            'delivery_delays' => true,
            'subscriptions' => true,
            'opens' => true,
            'clicks' => true,
        ]);
    }
}