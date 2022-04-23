<?php

namespace Akhan619\LaravelSesEventManager\Contracts;

interface ModelResolverContract {
    
    /**
    * Return the FQN model name
    *
    */
    public function getModelName(string $type): string;

}