<?php

namespace Akhan619\LaravelSesEventManager;

use Akhan619\LaravelSesEventManager\App\Http\Controllers\BaseController;
use Akhan619\LaravelSesEventManager\Contracts\BaseControllerContract;
use Akhan619\LaravelSesEventManager\Contracts\EventManagerContract;
use Akhan619\LaravelSesEventManager\Contracts\ModelResolverContract;
use Akhan619\LaravelSesEventManager\Contracts\RouteLoaderContract;
use Akhan619\LaravelSesEventManager\Implementations\EventManager;
use Akhan619\LaravelSesEventManager\Implementations\ModelResolver;
use Akhan619\LaravelSesEventManager\Implementations\RouteLoader;
use Akhan619\LaravelSesEventManager\Implementations\SesMailer;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class LaravelSesEventManagerServiceProvider extends ServiceProvider
{
    const PREFIX = 'lsem';

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/Mocking/Views', Str::studly(self::PREFIX));
        $this->registerRoutes();

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-ses-event-manager.php', 'laravel-ses-event-manager');

        $this->app->singleton('SesMailer', function ($app) {
            return new SesMailer();
        });

        $this->app->singleton(BaseControllerContract::class, function ($app) {
            return new BaseController();
        });

        $this->app->singleton(RouteLoaderContract::class, function ($app) {
            return new RouteLoader();
        });

        $this->app->singleton(ModelResolverContract::class, function ($app) {
            return new ModelResolver();
        });

        $this->app->singleton(EventManagerContract::class, function ($app) {
            return new EventManager($app->make(ModelResolverContract::class));
        });
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/laravel-ses-event-manager.php' => config_path('laravel-ses-event-manager.php'),
        ], self::PREFIX.'-config');

        // Publishing the migration files.
        $this->publishes([
            __DIR__.'/../database/migrations/create_emails_table.php.stub'                   => database_path('migrations/'.date('Y_m_d_His', now()->timestamp).'_create_'.config('laravel-ses-event-manager.database_name_prefix').'_emails_table.php'),
            __DIR__.'/../database/migrations/create_email_clicks_table.php.stub'             => database_path('migrations/'.date('Y_m_d_His', now()->addSeconds(1)->timestamp).'_create_'.config('laravel-ses-event-manager.database_name_prefix').'_email_clicks_table.php'),
            __DIR__.'/../database/migrations/create_email_opens_table.php.stub'              => database_path('migrations/'.date('Y_m_d_His', now()->addSeconds(2)->timestamp).'_create_'.config('laravel-ses-event-manager.database_name_prefix').'_email_opens_table.php'),
            __DIR__.'/../database/migrations/create_email_bounces_table.php.stub'            => database_path('migrations/'.date('Y_m_d_His', now()->addSeconds(3)->timestamp).'_create_'.config('laravel-ses-event-manager.database_name_prefix').'_email_bounces_table.php'),
            __DIR__.'/../database/migrations/create_email_complaints_table.php.stub'         => database_path('migrations/'.date('Y_m_d_His', now()->addSeconds(4)->timestamp).'_create_'.config('laravel-ses-event-manager.database_name_prefix').'_email_complaints_table.php'),
            __DIR__.'/../database/migrations/create_email_deliveries_table.php.stub'         => database_path('migrations/'.date('Y_m_d_His', now()->addSeconds(5)->timestamp).'_create_'.config('laravel-ses-event-manager.database_name_prefix').'_email_deliveries_table.php'),
            __DIR__.'/../database/migrations/create_email_sends_table.php.stub'              => database_path('migrations/'.date('Y_m_d_His', now()->addSeconds(6)->timestamp).'_create_'.config('laravel-ses-event-manager.database_name_prefix').'_email_sends_table.php'),
            __DIR__.'/../database/migrations/create_email_rejects_table.php.stub'            => database_path('migrations/'.date('Y_m_d_His', now()->addSeconds(7)->timestamp).'_create_'.config('laravel-ses-event-manager.database_name_prefix').'_email_rejects_table.php'),
            __DIR__.'/../database/migrations/create_email_rendering_failures_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', now()->addSeconds(8)->timestamp).'_create_'.config('laravel-ses-event-manager.database_name_prefix').'_email_rendering_failures_table.php'),
            __DIR__.'/../database/migrations/create_email_delivery_delays_table.php.stub'    => database_path('migrations/'.date('Y_m_d_His', now()->addSeconds(9)->timestamp).'_create_'.config('laravel-ses-event-manager.database_name_prefix').'_email_delivery_delays_table.php'),
            __DIR__.'/../database/migrations/create_email_subscriptions_table.php.stub'      => database_path('migrations/'.date('Y_m_d_His', now()->addSeconds(10)->timestamp).'_create_'.config('laravel-ses-event-manager.database_name_prefix').'_email_subscriptions_table.php'),
            __DIR__.'/../database/migrations/add_subject_to_emails_table.php.stub'           => database_path('migrations/'.date('Y_m_d_His', now()->addSeconds(11)->timestamp).'_create_'.config('laravel-ses-event-manager.database_name_prefix').'_add_subject_to_emails_table.php'),
        ], self::PREFIX.'-migrations');
    }

    protected function registerRoutes(): void
    {
        // Check if handling events is enabled, then register the routes.
        if (config('laravel-ses-event-manager.handle_email_events', false)) {
            Route::group($this->routeConfiguration(), function () {
                $this->app->make(RouteLoaderContract::class)::create();
            });
        }
    }

    protected function routeConfiguration(): array
    {
        return [
            'prefix'     => config('laravel-ses-event-manager.route_prefix'),
            'middleware' => config('laravel-ses-event-manager.route_middleware'),
        ];
    }
}
