<?php

namespace Akhan619\LaravelSesEventManager\Contracts;

interface EventManagerContract {

    /**
    * Process a given event
    *
    * @return void  
    */
    public function handleEvent(string $controllerAction, object $message) : void;

    /**
    * Process a bounce event
    *
    * @return void  
    */
    public function handleBounceEvent(object $message) : void;

    /**
    * Process a complaint event
    *
    * @return void  
    */
    public function handleComplaintEvent(object $message) : void;

    /**
    * Process a delivery event
    *
    * @return void  
    */
    public function handleDeliveryEvent(object $message) : void;

    /**
    * Process a send event
    *
    * @return void  
    */
    public function handleSendEvent(object $message) : void;

    /**
    * Process a reject event
    *
    * @return void  
    */
    public function handleRejectEvent(object $message) : void;

    /**
    * Process a open event
    *
    * @return void  
    */
    public function handleOpenEvent(object $message) : void;

    /**
    * Process a click event
    *
    * @return void  
    */
    public function handleClickEvent(object $message) : void;

    /**
    * Process a rendering failure event
    *
    * @return void  
    */
    public function handleRenderingFailureEvent(object $message) : void;

    /**
    * Process a delivery delay event
    *
    * @return void  
    */
    public function handleDeliveryDelayEvent(object $message) : void;

    /**
    * Process a subscription event
    *
    * @return void  
    */
    public function handleSubscriptionEvent(object $message) : void;
    
}