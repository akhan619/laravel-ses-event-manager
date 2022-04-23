<?php

namespace Akhan619\LaravelSesEventManager\Contracts;

interface ManagesDatabaseQueryContract {
    
    /**
    * Handle the aws ses event message
    *
    * @return void
    */
    public static function handleSesMessageData(string $eventType, object $object): void;

}