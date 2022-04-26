# Laravel Ses Event Manager
[![Latest Stable Version](http://poser.pugx.org/akhan619/laravel-ses-event-manager/v?style=flat-square)](https://packagist.org/packages/akhan619/laravel-ses-event-manager)
![Tests](https://github.com/akhan619/laravel-ses-event-manager/actions/workflows/test.yml/badge.svg?branch=main)
[![PHP Version Require](http://poser.pugx.org/akhan619/laravel-ses-event-manager/require/php?style=flat-square)](https://packagist.org/packages/akhan619/laravel-ses-event-manager)
[![Total Downloads](http://poser.pugx.org/akhan619/laravel-ses-event-manager/downloads?style=flat-square)](https://packagist.org/packages/akhan619/laravel-ses-event-manager)
[![StyleCI](https://github.styleci.io/repos/484619978/shield?branch=main)](https://github.styleci.io/repos/484619978?branch=main)
[![License](http://poser.pugx.org/akhan619/laravel-ses-event-manager/license?style=flat-square)](https://packagist.org/packages/akhan619/laravel-ses-event-manager)

Manage incoming `SES` email event notifications over the http/s protocols. This package provides all the set up needed to send normal and queued emails using `SES` and then handle the email event notifications sent by `SES` to http/s webhook. This includes all 10 events as listed by `SES` like `send`, `bounce`, `open`, `click`, etc. Database and eloquent models are provided which store the event data. Don't want to use the models provided by the package or want to handle the event differently. Don't worry! you are covered. Every part of the pacakge is implemented to be modifable by the end user.

## What this package does

As mentioned this package handles the process of:
-  Setting up the webhook routes to receive notifications.
-  Setting up the controllers to confirm incoming subscription confirmations.
-  Setting up the controllers to handle storing the event data to the database.
-  Setting up the database tables.
-  Define the eloquent models to represent the data to be stored.
-  Send/Queue emails to be tracked.

## What this package doesn't do

The package will not do anything beyond what is mentioned above. So, the package won't:
-  Create the AWS resources to actually set up the event notifications like
    - Configuration Sets
    - Event Destinations
    - SNS Topics
    - SNS Subscribers
-  Perform post data-acquisition tasks like stats for emails sent, etc.

If you need to set up the `AWS SES/SNS` resources you may look at the package [Laravel Ses Tracking](https://github.com/akhan619/laravel-ses-tracking). The package provides a simple artisan command for setting up your `AWS` resources. Of course, this is completely optional and you may use any other method to set up the required resources.

> **FULL DISCLOSURE**: I am the package author for the `Laravel Ses Tracking` package. 

# Laravel and PHP Versions

The package is written for Laravel 9 and Php 8 and above.

# Installation

Via Composer

``` bash
composer require akhan619/laravel-ses-event-manager
```
Configuration File

You can publish the configuration file using the following artisan command:

```bash
php artisan vendor:publish --provider="Akhan619\LaravelSesEventManager\LaravelSesEventManagerServiceProvider" --tag="lsem-config"
```

This is will publish a new configuration file called `laravel-ses-event-manager.php` in the `config` folder.

# Migrations

To use the eloquent models provided by the package, you must publish the database migrations of the package using the following artisan command:

```bash
php artisan vendor:publish --provider="Akhan619\LaravelSesEventManager\LaravelSesEventManagerServiceProvider" --tag="lsem-migrations"
```

Don't forget to run the migrations using

```bash
php artisan migrate
```

# Local Development

To test the package during local development you will need a way to actually serve the `Laravel` project so that `AWS` can send the event notifications. You may do so in many ways. One way to do so is by using a tunneling service. I personally used [Expose](https://expose.dev) by `Beyond Code` during development. 

> **FULL DISCLOSURE**: I am NOT sponsored by `Beyond Code`. 

# Requirements

To start using the package, it is assumed that the necessary set up on `AWS` is complete. This means:

- Configuration Set exists
- SNS Topics exist.
- Event Destinations for the events to listen to exist.
- The webhooks (http/s) that will be used, have been subscribed to the SNS Topics.

> **NOTE**: You don't have to confirm the SNS subscription. The package can handle them for you. If you have already completed the above, you can go to the `AWS` console and resend the confirmation notifications and the package will confirm them for you.

# Usage

The package provides the `SesMailer` facade. The facade mimics the Laravel `Mail` facade so can use it just the same. For receiving and storing the notifications successfully, you must use the facade provided by the package. This is so we can track emails by the id provided by `SES`.

## Sending Emails

```php
use Akhan619\LaravelSesEventManager\Facades\SesMailer;

SesMailer::to('john@doe.com')->send($someMailable);

// Or if the recipient in specified in the mailable then,

SesMailer::send($someMailable);
```

## Queueing Emails

To queue emails for now or later, you must add the `QueueForCustomMailer` trait to your mailable. Then you may queue mails as in the framework.

```php
use Akhan619\LaravelSesEventManager\Traits\QueueForCustomMailer;

class TestMailableWithTrait extends Mailable
{
    use Queueable, SerializesModels, QueueForCustomMailer;

    ...
}

// Then, as before
$testMailableWithTrait = new TestMailableWithTrait();

SesMailer::to('john@doe.com')->later(now()->addHours(12), $testMailableWithTrait);
```

## Models

The package uses eloquent models to store the outgoing/incoming email status. All models are specified in the `Akhan619\LaravelSesEventManager\App\Models` namespace under the `src/App/Models` directory. The main model is the `Email` model which provides the following fields:

```php
protected $fillable = [
    'message_id', // The Ses id returned by the call to sendRawEmail
    'email', // The email recipient.
    'name', // Recipient name if any.

    // Boolean fields to show the events that have been received for the email.
    'has_send',
    'has_rendering_failure',
    'has_reject',
    'has_delivery',
    'has_bounce',
    'has_complaint',
    'has_delivery_delay',
    'has_subscription',
    'has_open',
    'has_click'
];
```

For every field above there is a Eloquent relationship which is also defined. The relationships are:

```php
// Has Many
clicks()
opens()

// Has One
bounce()
complaint()
delivery()
send()
reject()
renderingFailure()
deliveryDelay()
subscription()
```

So you may use the above as:
```php
use Akhan619\LaravelSesEventManager\App\Models\Email;

$email = Email::first();

if($mail->has_bounce) {
    echo 'Email has bounced';
}

// Get the bounce data
$bounce = $email->bounce;
```

All the relevant fields present in a given `SES` event are available via the Eloquent model for the event. Please refer to the model for more details. An example of the kind of data for the `EmailBounce` model is given below:

```php
protected $fillable = [
    'message_id',
    'bounce_type',
    'bounce_sub_type',
    'feedback_id',
    'action',
    'status',
    'diagnostic_code',
    'reporting_mta',
    'bounced_at',
];
```

## Custom Handler

If you don't want to use the models provided by the package you may register your own logic for handling the event by registering a `callback` for the given event. You will need to call the `extend` method of a `ModelResolver` instance with your callback in the `boot` method of a `ServiceProvider`. The `callback` will receive two parameters `eventType` and `data`. The possible values and the data they receive are:

| EventType  |  Data |
|---|---|
| MessageSent           | Illuminate\Mail\SentMessage |
| Bounce                | Object [The message field of a SNS notification] |
| Complaint             | Object [The message field of a SNS notification]  |
| Delivery              | Object [The message field of a SNS notification]  |
| Send                  | Object [The message field of a SNS notification]  |
| Reject                | Object [The message field of a SNS notification]  |
| Open                  | Object [The message field of a SNS notification]  |
| Click                 | Object [The message field of a SNS notification]  |
| RenderingFailure      | Object [The message field of a SNS notification]  |
| DeliveryDelay         | Object [The message field of a SNS notification]  |
| Subscription          | Object [The message field of a SNS notification]  |

For example, add the following to the boot method of the `AppServiceProvider` which comes with the default `Laravel` project.

```php
use Akhan619\LaravelSesEventManager\Contracts\ModelResolverContract;

public function boot(ModelResolverContract $resolver)
{
    $resolver->extend('MessageSent', function($event, $data) {
        // Will echo the recipient email address.
        echo current($data->getOriginalMessage()->getTo())->getAddress();
    });
}

// OR

public function boot()
{
    $resolver = app()->make(ModelResolverContract::class);
    $resolver->extend('Bounce', function($event, $data) {
        // The bounce type
        echo $data->bounce->bounceType;
    });
}
```

Refer to the [Laravel](https://laravel.com/api/9.x/Illuminate/Mail/SentMessage.html) docs or the [AWS](https://docs.aws.amazon.com/ses/latest/dg/event-publishing-retrieving-sns-contents.html) docs for more info.

# Configuration

## Debug Mode

You may log debug data to the default logger by setting debug to `true` in the config file.

```php
'debug' => false,
```

## Subscription Confirmation

By default, incoming subscription confirmations are automatically confirmed by the package. If you do not want this behavior, you may disable this by setting the `confirm_subscription` to `false` in the config file.

```php
'confirm_subscription' => true,
```

## Credentials

The credentials to use for `SES` will be picked up from the `.env` file. If you wish to use a different set of credentials, you may specify them in the `ses_options` key in the config file. 

```php
'ses_options' => [
    'key'       => env('AWS_ACCESS_KEY_ID'),
    'secret'    => env('AWS_SECRET_ACCESS_KEY'),
    'region'    => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ...
],
```

## Configuration Set/Options

The configuration set to use when sending emails to track must be specified in the `.env`.

```
CONFIGURATION_SET_NAME=<Enter the name here>
```

If you want, you can also specify it in the config file alongwith any other options under the `ses_options` key in the config file.

```php
'ses_options' => [
    ...
    'options' => [
        'ConfigurationSetName' => env('CONFIGURATION_SET_NAME'),
        <Other options here as key-value pairs>
    ],
],
```

## Events

You may specify the notifications to listen for using the `active_email_events` key in the config file. Set the event to `true` to enable the event.

```php
'active_email_events' => [
    'sends'                 => true,
    'rendering_failures'    => false,
    'rejects'               => false,
    'deliveries'            => true,
    'bounces'               => true,
    'complaints'            => false,
    'delivery_delays'       => true,
    'subscriptions'         => true,
    'opens'                 => false,
    'clicks'                => false,
],
```

## Webhook Routes

You must specify the routes to listen on, so that the controllers and named routes may be generated. These may be specified using the following in the config file:

```php
'named_route_prefix' => 'lsem',

'route_prefix' => 'email/notification',

'routes' => [
    'sends'                     => 'sends',
    'rendering_failures'        => 'rendering-failures',
    'rejects'                   => 'rejects',
    'deliveries'                => 'deliveries',
    'bounces'                   => 'bounces',
    'complaints'                => 'complaints',
    'delivery_delays'           => 'delivery-delays',
    'subscriptions'             => 'subscriptions',
    'opens'                     => 'opens',
    'clicks'                    => 'clicks',
],
```

So for example on a fresh `Laravel` project the route for `delivery_delays` will be generated as:

```php
Route_Key                      Route_Value  
'delivery_delays'           => 'delivery-delays',

URL:
APP_URL/route_prefix/Route_Value

NAME:
named_route_prefix.Route_Key

Example:

URL:
http://localhost/email/notification/delivery-delays

NAME:
lsem.delivery_delays
```

> **NOTE**: Routes are registered only when the corresponding event is enabled.

You may specify any middleware that should be used in the `route_middleware` array.

```php
'route_middleware' => [],
```

## Database

Make sure you have published the migration files and run `php artisan migrate`. By default the tables names are generated as:

```
lsem_****

Example:

lsem_emails
lsem_email_sends
...
```

If for some reason the table names are in conflict with existing tables, you may change the table prefix using the `database_name_prefix` key in the config file.

```php
'database_name_prefix' => 'lsem',
```

## Disable Webhook

If for some reason you don't want the package to listen to incoming notifications, you may disable all the routes by setting `handle_email_events`  to `false` in the config file.

```php
'handle_email_events' => true,
```

This is will disable all the routes that were registered and partially disable the package. 

> **NOTE**: This will not disable the sending of emails. So if you have used the `SesMailer` facade to send emails in your code, that will continue to work.

# Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

# Testing

Run the tests using

``` bash
composer test
```

# Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

# Security

If you discover any security related issues, please email amankhan.mailbox@gmail.com instead of using the issue tracker.

# Credits

- Aman Khan

## License

MIT. Please see the [license file](license.md) for more information.
