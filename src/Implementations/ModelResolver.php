<?php

namespace Akhan619\LaravelSesEventManager\Implementations;

use Akhan619\LaravelSesEventManager\Contracts\ModelResolverContract;
use Closure;

class ModelResolver implements ModelResolverContract
{
    public array $eventCallbacks = [];

    /**
    * Register the given callback for an event.
    *
    */
    public function extend(string $eventType, Closure $callback): void
    {
        $this->eventCallbacks[$eventType] = $callback;
    }

    /**
    * Is a callback registered for the given event
    *
    */
    public function hasCallback(string $eventType): bool
    {
        return isset($this->eventCallbacks[$eventType]);
    }

    /**
    * Call the callback registered for the given event
    *
    */
    public function execute(string $eventType, mixed $data): mixed
    {
        return call_user_func($this->eventCallbacks[$eventType], $eventType, $data);
    }
}