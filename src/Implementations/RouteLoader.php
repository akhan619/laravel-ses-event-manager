<?php

namespace Akhan619\LaravelSesEventManager\Implementations;

use Akhan619\LaravelSesEventManager\Contracts\BaseControllerContract;
use Akhan619\LaravelSesEventManager\Contracts\RouteLoaderContract;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class RouteLoader implements RouteLoaderContract
{
    public static array $enabledEvents = [];
    public static array $definedRoutes = [];
    public static array $routeNames = [];
    public static array $controllerActions = [];

    /**
    * Parse the config file and register the routes that have their corresponding events enabled.
    *
    * @return void
    */
    public static function create() : void
    {
        // Get the events that we need to listen to for notifications
        self::$enabledEvents = array_filter(config('laravel-ses-event-manager.active_email_events'), function($event) {
            return $event;
        });

        // Filter the route list for the enabled events
        self::$definedRoutes = array_intersect_key(config('laravel-ses-event-manager.routes'), self::$enabledEvents);
        self::$definedRoutes = array_filter(self::$definedRoutes, 'strlen');

        // Clear the routeNames. This is a static variable and if we dont clear it, then during tests it will get the environment
        // state appended again and again.
        self::$routeNames = [];
        
        // Get the implementation for our controller contract.
        $controller = App::make(BaseControllerContract::class);

        // Register the routes with the associated controllers.
        foreach(self::$definedRoutes as $routeKey => $route) 
        {
            // Default will be for example lsem.bounces
            $routeName = config('laravel-ses-event-manager.named_route_prefix') . '.' . $routeKey;
            self::$routeNames[] = $routeName;
            
            // We don't want controller action names to be like delivery_delay, so we camel case it to
            // DeliveryDelay
            $controllerActionName = Str::camel($routeKey);
            self::$controllerActions[] = $controllerActionName;
            
            Route::post($route, [$controller::class, $controllerActionName])->name($routeName);
        }
    }
}