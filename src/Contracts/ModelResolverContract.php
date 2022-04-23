<?php

namespace Akhan619\LaravelSesEventManager\Contracts;

use Closure;

interface ModelResolverContract {
    
    /**
    * Register the given callback for an event.
    *
    */
    public function extend(string $eventType, Closure $callback): void;

    /**
    * Is a callback registered for the given event
    *
    */
    public function hasCallback(string $eventType): bool;

    /**
    * Call the callback registered for the given event
    *
    */
    public function execute(string $eventType, mixed $data): mixed;

}