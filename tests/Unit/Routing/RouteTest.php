<?php

namespace Akhan619\LaravelSesEventManager\Tests\Unit\Routing;

use Akhan619\LaravelSesEventManager\Implementations\RouteLoader;
use Akhan619\LaravelSesEventManager\Tests\UnitTestCase;
use Illuminate\Routing\Router;

class RouteTest extends UnitTestCase
{
    protected Router $router;
    protected string $prefix;
    protected array $routeNames;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        // Code before application created.

        parent::setUp();

        // Code after application created.

        $this->router = $this->app->make(Router::class);
        $this->prefix = $this->app->config->get('laravel-ses-event-manager.named_route_prefix');

        $this->routeNames = ($this->app->make(RouteLoader::class))::$routeNames;
    }

    protected function enableRouting($app) 
    {
        $app['config']->set('laravel-ses-event-manager.active_email_events', [
            'sends' => true,
            'rendering_failures' => true,
            'rejects' => false,
            'deliveries' => true,
            'bounces' => true,
            'complaints' => false,
            'delivery_delays' => true,
            'subscriptions' => false,
            'opens' => false,
            'clicks' => false,
        ]);
        
        $app['config']->set('laravel-ses-event-manager.handle_email_events', true);
    }

    protected function disableRouting($app)
    {
        $app['config']->set('laravel-ses-event-manager.handle_email_events', false);
    }

    /**
     * @test
     * @define-env enableRouting
     */
    public function routesAreLoadedWhenHandleEmailEventsIsTrue()
    {
        $this->assertTrue($this->router->has($this->routeNames), "Routes were not loaded when they should be."); 
    }

    /**
     * @test
     * @define-env disableRouting
     */
    public function routesAreNotLoadedWhenHandleEmailEventsIsFalse()
    {
        foreach($this->routeNames as $name) {
            $this->assertFalse($this->router->has($name), "Routes were loaded when they shouldn't have been"); 
        }    
    }
}