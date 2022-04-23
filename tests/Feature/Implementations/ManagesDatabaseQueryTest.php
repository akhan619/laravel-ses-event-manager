<?php

namespace Akhan619\LaravelSesEventManager\Tests\Feature\Implementations;

use Akhan619\LaravelSesEventManager\Contracts\ManagesDatabaseQueryContract;
use Akhan619\LaravelSesEventManager\Implementations\EventManager;
use Akhan619\LaravelSesEventManager\Implementations\ModelResolver;
use Akhan619\LaravelSesEventManager\Tests\FeatureTestCase;
use \Mockery as m;
use stdClass;

class ManagesDatabaseQueryTest extends FeatureTestCase
{
    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }
    
    /**
     * @test
     */
    public function managesDatabaseQueryContractIsSuccussfullyCalled()
    {
        $eventTypes = [
            'Bounce', 
            'Complaint', 
            'Delivery', 
            'Send', 
            'Reject', 
            'Open', 
            'Click', 
            'RenderingFailure', 
            'DeliveryDelay', 
            'Subscription'
        ];

        $modelResolver = m::mock(ModelResolver::class);
        $eventManager = m::mock(EventManager::class, [$modelResolver])->makePartial();
        $obj = new stdClass();

        $modelResolver->shouldReceive('getModelName')->times(10)->andReturn(TestClass::class);        

        foreach($eventTypes as $type) {
            $eventManager->shouldReceive('passEventDataToUserClass')->once()->with(TestClass::class, $type, $obj);
            $eventManager->{'handle' . $type . 'Event'}($obj);
        }        

        $this->assertTrue(true);
    } 
}

class TestClass implements ManagesDatabaseQueryContract
{
    /**
    * Handle the aws ses event message
    *
    * @return void
    */
    public static function handleSesMessageData(string $eventType, object $object): void
    {

    }
}