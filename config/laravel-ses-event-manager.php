<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Debug mode
    |--------------------------------------------------------------------------
    |
    |	Is logging enabled?
    |	Type: bool
    |
    */
    'debug' => false,
    
    /*
    |--------------------------------------------------------------------------
    | Enable Message Handling
    |--------------------------------------------------------------------------
    |
    |	Should email event messages be handled by the package
    |	Type: bool
    |
    */
    'handle_email_events' => true,
    
    /*
    |--------------------------------------------------------------------------
    | Confirm Subscription
    |--------------------------------------------------------------------------
    |
    |	Should confirm subscription messages be automatically confirmed.
    |   If no, then the subscription post request will be logged using the default logger.
    |   This means that you will find the request dump typically in the storage/logs/laravel.log file.
    |	Type: bool
    |
    */
    'confirm_subscription' => true,

    /*
    |--------------------------------------------------------------------------
    | SES Events
    |--------------------------------------------------------------------------
    |
    |   Enable the SES events you wish to receive notifications for via SNS. 
    |   A corresponding route must be present in the routes sections below.
    |	Type: array
    |
    */
    'active_email_events' => [
        'sends' => true,
        'rendering_failures' => false,
        'rejects' => false,
        'deliveries' => true,
        'bounces' => true,
        'complaints' => false,
        'delivery_delays' => true,
        'subscriptions' => true,
        'opens' => false,
        'clicks' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Named Route prefix
    |--------------------------------------------------------------------------
    |
    |   Define the route name prefix for the named routes. Set this to something else if named 
    |   routes are clashing with your app.
    |	Type: string
    |
    */
    'named_route_prefix' => 'lsem',

    /*
    |--------------------------------------------------------------------------
    | Route prefix
    |--------------------------------------------------------------------------
    |
    |   Define the route prefix for the http/s endpoint to use. A leading or trailing '/' is not required.
    |   Set to null if no prefix is required.
    |	Type: string|null
    |
    */
    'route_prefix' => 'email/notification',

    /*
    |--------------------------------------------------------------------------
    | Route Middleware
    |--------------------------------------------------------------------------
    |
    |   Define the route middleware for the http/s endpoint to use. For 3rd-party 
    |   calls like Aws here, should be usually left blank.
    |	Type: array
    |
    */
    'route_middleware' => [],

    /*
    |--------------------------------------------------------------------------
    | Route names
    |--------------------------------------------------------------------------
    |
    |   Define the route names to use when listening for SNS notifications. These will be 
    |   automatically setup for use. The general syntax is:
    |       APP_URL/prefix/route
    |   Example based on defaults:
    |       http://localhost/email/notification/sends
    |
    |   Note: The routes will be registered only if they are enabled under the 'active' key.
    |	Type: array
    |
    */
    'routes' => [
        'sends' => 'sends',
        'rendering_failures' => 'rendering-failures',
        'rejects' => 'rejects',
        'deliveries' => 'deliveries',
        'bounces' => 'bounces',
        'complaints' => 'complaints',
        'delivery_delays' => 'delivery-delays',
        'subscriptions' => 'subscriptions',
        'opens' => 'opens',
        'clicks' => 'clicks',
    ],

    /*
    |--------------------------------------------------------------------------
    | SES Options
    |--------------------------------------------------------------------------
    |
    |	The set of ses options to use. 
    |
    */
    'ses_options' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        'options' => [
            'ConfigurationSetName' => env('CONFIGURATION_SET_NAME'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Database name prefix
    |--------------------------------------------------------------------------
    |
    |   Define the database name prefix for the database tables. Set this to something else if database 
    |   names are clashing with your app.
    |	Type: string
    |
    */
    'database_name_prefix' => 'lsem',
];