<?php

namespace Akhan619\LaravelSesEventManager\Tests\Unit\Implementations;

use Akhan619\LaravelSesEventManager\Contracts\ModelResolverContract;
use Akhan619\LaravelSesEventManager\Tests\UnitTestCase;

class ModelResolverTest extends UnitTestCase
{
    /**
     * @test
     */
    public function modelResolverCanRegisterCallback()
    {
        $resolver = app()->make(ModelResolverContract::class);
        $resolver->extend('TestEvent', function ($event, $data) {
        });

        $this->assertTrue($resolver->hasCallback('TestEvent'));
        $this->assertFalse($resolver->hasCallback('NonExistentEvent'));
    }

    /**
     * @test
     */
    public function modelResolverCanExecuteRegisteredCallback()
    {
        $resolver = app()->make(ModelResolverContract::class);
        $resolver->extend('TestEvent', function ($event, $data) {
            return 'TestPassed';
        });

        $this->assertEquals($resolver->execute('TestEvent', []), 'TestPassed');
        $this->assertNotEquals($resolver->execute('TestEvent', []), 'TestFailed');
    }
}
