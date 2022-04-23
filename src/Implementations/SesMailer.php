<?php

namespace Akhan619\LaravelSesEventManager\Implementations;

use Akhan619\LaravelSesEventManager\Contracts\SesMailerContract;
use Illuminate\Mail\Transport\SesTransport;
use Aws\Ses\SesClient;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Mail\SentMessage;
use Akhan619\LaravelSesEventManager\App\Models\Email;
use Akhan619\LaravelSesEventManager\App\CustomMailer;
use Akhan619\LaravelSesEventManager\App\CustomPendingMail;
use Akhan619\LaravelSesEventManager\LaravelSesEventManagerServiceProvider;

class SesMailer implements SesMailerContract
{
    protected ?CustomMailer $mailer = null;
    protected mixed $result = null;

    /**
    * Initialize our custom mailer. 
    *
    * When the first call is made to SesMailer, we cache a local copy
    * of a CustomMailer instance customized for our use.
    *
    */
    protected function createCustomMailer(): CustomMailer
    {
        $config = [
            'transport' => 'ses',
        ];

        $mailer = new CustomMailer(
            LaravelSesEventManagerServiceProvider::PREFIX . '-mailer',
            app()->make('view'),
            $this->createSesTransport($config),
            app()->make('events')
        );

        if (app()->bound('queue')) {
            $mailer->setQueue(app()->make('queue'));
        }

        foreach (['from', 'reply_to', 'to', 'return_path'] as $type) {
            $this->setGlobalAddress($mailer, $config, $type);
        }

        return $mailer;
    }

    /**
    * Process the calls on the SesMailer instance.
    *
    * Our SesMailer class is basically a wrapper around a CustomMailer instance customized for our use case.
    * By creating a wrapper instead of extending from the CustomMailer/Mailer class, we achieve a few things:
    * 1. Isolation: Our CustomMailer instance configured to use the ses transport is configured with options defined
    *    in OUR config file. So the developer is free to use the default laravel mailer with ses with any options they wish with 
    *    options defined in the config/service.php file.
    * 2. Seperation: Our SesMailer can be easily initialized in the register method of the service provider. If we simply extended the
    *    CustomMailer/Mailer class, then the Mail Manager code below would need to be run in the Service Provider since the CustomMailer/Mailer
    *    constructor would need certain paramters. The service provider would be bloated and basically lead to ugly code.
    *
    * @return  mixed
    * @throws  \Exception
    */
    public function __call($method, $parameters)
    {
        $this->setResultCache($this->routeCalls($method, $parameters));

        // Check if we are done with sending the message
        if($this->isMessageSent($this->getResultCache())) {

            $this->createEmailRecord($this->getResultCache());
        }        

        return $this->getResultCache() instanceof CustomPendingMail ? $this : $this->getResultCache();
    }

    /**
    * Get the custom mailer instance
    *
    * @return 
    */
    public function getCustomMailer()
    {
        return $this->mailer ?? $this->createCustomMailer();
    }

    /**
    * Get the current result cache
    *
    * @return 
    */
    public function getResultCache()
    {
        return $this->result;
    }

    /**
    * Set the current result cache
    *
    * @return 
    */
    public function setResultCache(mixed $result)
    {
        $this->result = $result;
    }

    /**
    * Route method calls to the underlying instance.
    *
    * Check if we have CustomPendingMail instance. If yes, route the call to the instance otherwise pass it the mailer.
    * We are basically going to manually chain the CustomPendingMail method calls as we don't want to loose control of 
    * execution flow to the CustomPendingMail instance. If that had happened then the check for the SentMessage would never be triggered.
    * @return mixed
    */
    public function routeCalls($method, $parameters): mixed
    {
        return $this->getResultCache() instanceof CustomPendingMail ? 
            $this->getResultCache()->{$method}(...$parameters) : 
            $this->getCustomMailer()->{$method}(...$parameters);
    }

    /**
    * Check if we are done with sending the email
    *
    * @return bool
    */
    public function isMessageSent($result): bool
    {
        return $result instanceof SentMessage;
    }

    /**
    * Save the email in the databse
    *
    * @return void
    */
    public function createEmailRecord($result): void
    {
        Email::create([
            'message_id'        =>  $result->getOriginalMessage()->getHeaders()->get('X-SES-Message-ID')->getValue(),
            'email'             =>  current($result->getOriginalMessage()->getTo())->getAddress()
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Illuminate\Mail\Manager class function definitions - START
    |--------------------------------------------------------------------------
    |
    |	Some of the protected methods are replicated here to set up our mailer instance.
    |
    */

    protected function setGlobalAddress(CustomMailer $mailer, array $config, string $type) : void
    {
        $address = Arr::get($config, $type, config('mail.'.$type));

        if (is_array($address) && isset($address['address'])) {
            $mailer->{'always'.Str::studly($type)}($address['address'], $address['name']);
        }
    }

    public function createSesTransport(array $config) : SesTransport
    {
        $config = array_merge(
            config('laravel-ses-event-manager.ses_options', []),
            ['version' => 'latest', 'service' => 'email'],
            $config
        );

        $config = Arr::except($config, ['transport']);

        return new SesTransport(
            new SesClient($this->addSesCredentials($config)),
            $config['options'] ?? []
        );
    }

    protected function addSesCredentials(array $config) : array
    {
        if (! empty($config['key']) && ! empty($config['secret'])) {
            $config['credentials'] = Arr::only($config, ['key', 'secret', 'token']);
        }

        return $config;
    }

    /*
    |--------------------------------------------------------------------------
    | Illuminate\Mail\Manager class function definitions - END
    |--------------------------------------------------------------------------
    */
}