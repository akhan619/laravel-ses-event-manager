<?php

namespace Akhan619\LaravelSesEventManager\Implementations;

use Akhan619\LaravelSesEventManager\Contracts\ModelResolverContract;

class ModelResolver implements ModelResolverContract
{
    /**
    * Return the FQN model name
    *
    */
    public function getModelName(string $type): string
    {
        return config('laravel-ses-event-manager.resolved_models.' . $type);
    }
}