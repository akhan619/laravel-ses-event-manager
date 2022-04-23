<?php

namespace Akhan619\LaravelSesEventManager\Implementations;

use Akhan619\LaravelSesEventManager\App\Models\Email;
use Akhan619\LaravelSesEventManager\Contracts\EventManagerContract;
use Akhan619\LaravelSesEventManager\Contracts\ModelResolverContract;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class EventManager implements EventManagerContract
{
    protected ModelResolverContract $resolver;

    public function __construct(ModelResolverContract $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
    * Handle the event based on the event type.
    *
    * @return void
    */
    public function handleEvent(string $controllerAction, object $message) : void
    {
        $eventTypes = [
            'Bounce', 
            'Complaint', 
            'Delivery', 
            'Send', 
            'Reject', 
            'Open', 
            'Click', 
            'Rendering Failure', 
            'DeliveryDelay', 
            'Subscription'
        ];

        if(in_array($message->eventType, $eventTypes, true)) 
        {
            $this->{'handle' . Str::studly($message->eventType) . 'Event'}($message);
        }
    }

    /**
    * Process a bounce event
    *
    * @return void  
    */
    public function handleBounceEvent(object $message) : void
    {        
        if($this->resolver->hasCallback('Bounce')) {
            $this->resolver->execute('Bounce', $message);
            return;
        }

        try
        {
            DB::transaction(function () use ($message) {
                $email = Email::where('message_id', $message->mail->messageId)->sole();
                $email->bounce()->create([
                    'bounce_type'           =>  $message->bounce->bounceType ?? null,
                    'bounce_sub_type'       =>  $message->bounce->bounceSubType ?? null,
                    'feedback_id'           =>  $message->bounce->feedbackId ?? null,
                    'action'                =>  $message->bounce->bouncedRecipients[0]->action ?? null,
                    'status'                =>  $message->bounce->bouncedRecipients[0]->status ?? null,
                    'diagnostic_code'       =>  $message->bounce->bouncedRecipients[0]->diagnosticCode ?? null,
                    'reporting_mta'         =>  $message->bounce->reportingMTA ?? null,
                    'bounced_at'            =>  $message->bounce->timestamp ? new Carbon($message->bounce->timestamp) : null,
                ]);
                $email->has_bounce = true;
                $email->save();
    
                $this->logMessage('Bounce data was saved successfully.');
            });           

        } catch(\Throwable $th)
        {
            Log::error('Failed to save bounce event data.', [
                'error_object' => $th->__toString()
            ]);
        }
    }   

    /**
    * Process a complaint event
    *
    * @return void  
    */
    public function handleComplaintEvent(object $message) : void
    {
        if($this->resolver->hasCallback('Complaint')) {
            $this->resolver->execute('Complaint', $message);
            return;
        }

        try
        {
            DB::transaction(function () use ($message) {
                $email = Email::where('message_id', $message->mail->messageId)->sole();
                $email->complaint()->create([
                    'feedback_id'                   =>  $message->complaint->feedbackId ?? null,
                    'complaint_sub_type'            =>  $message->complaint->complaintSubType ?? null,
                    'user_agent'                    =>  $message->complaint->userAgent ?? null,
                    'complaint_feedback_type'       =>  $message->complaint->complaintFeedbackType ?? null,
                    'complained_at'                 =>  $message->complaint->timestamp ? new Carbon($message->complaint->timestamp) : null,
                ]);
                $email->has_complaint = true;
                $email->save();
    
                $this->logMessage('Complaint data was saved successfully.');
            });           

        } catch(\Throwable $th)
        {
            Log::error('Failed to save complaint event data.', [
                'error_object' => $th->__toString()
            ]);
        }
    } 

    /**
    * Process a delivery event
    *
    * @return void  
    */
    public function handleDeliveryEvent(object $message) : void
    {
        if($this->resolver->hasCallback('Delivery')) {
            $this->resolver->execute('Delivery', $message);
            return;
        }

        try
        {
            DB::transaction(function () use ($message) {
                $email = Email::where('message_id', $message->mail->messageId)->sole();
                $email->delivery()->create([
                    'delivered_at'  =>  $message->delivery->timestamp ? new Carbon($message->delivery->timestamp) : null,
                ]);
                $email->has_delivery = true;
                $email->save();
    
                $this->logMessage('Delivery data was saved successfully.');
            });           

        } catch(\Throwable $th)
        {
            Log::error('Failed to save delivery event data.', [
                'error_object' => $th->__toString()
            ]);
        }
    }

    /**
    * Process a send event
    *
    * @return void  
    */
    public function handleSendEvent(object $message) : void
    {
        if($this->resolver->hasCallback('Send')) {
            $this->resolver->execute('Send', $message);
            return;
        }

        try
        {
            DB::transaction(function () use ($message) {
                $email = Email::where('message_id', $message->mail->messageId)->sole();
                $email->send()->create([
                    'sent_at'  =>  $message->mail->timestamp ? new Carbon($message->mail->timestamp) : null,
                ]);
                $email->has_send = true;
                $email->save();
    
                $this->logMessage('Send data was saved successfully.');
            });           

        } catch(\Throwable $th)
        {
            Log::error('Failed to save send event data.', [
                'error_object' => $th->__toString()
            ]);
        }
    }

    /**
    * Process a reject event
    *
    * @return void  
    */
    public function handleRejectEvent(object $message) : void
    {
        if($this->resolver->hasCallback('Reject')) {
            $this->resolver->execute('Reject', $message);
            return;
        }

        try
        {
            DB::transaction(function () use ($message) {
                $email = Email::where('message_id', $message->mail->messageId)->sole();
                $email->reject()->create([
                    'reason'                =>  $message->reject->reason ?? null,
                    'rejected_at'           =>  $message->mail->timestamp ? new Carbon($message->mail->timestamp) : null,
                ]);
                $email->has_reject = true;
                $email->save();
    
                $this->logMessage('Reject data was saved successfully.');
            });           

        } catch(\Throwable $th)
        {
            Log::error('Failed to save reject event data.', [
                'error_object' => $th->__toString()
            ]);
        }
    }

    /**
    * Process a open event
    *
    * @return void  
    */
    public function handleOpenEvent(object $message) : void
    {
        if($this->resolver->hasCallback('Open')) {
            $this->resolver->execute('Open', $message);
            return;
        }

        try
        {
            DB::transaction(function () use ($message) {
                $email = Email::where('message_id', $message->mail->messageId)->sole();
                $email->opens()->create([
                    'user_agent'          =>  $message->open->userAgent ?? null,
                    'opened_at'           =>  $message->open->timestamp ? new Carbon($message->open->timestamp) : null,
                ]);
                $email->has_open = true;
                $email->save();
    
                $this->logMessage('Open data was saved successfully.');
            });           

        } catch(\Throwable $th)
        {
            Log::error('Failed to save open event data.', [
                'error_object' => $th->__toString()
            ]);
        }
    }

    /**
    * Process a click event
    *
    * @return void  
    */
    public function handleClickEvent(object $message) : void
    {
        if($this->resolver->hasCallback('Click')) {
            $this->resolver->execute('Click', $message);
            return;
        }

        try
        {
            DB::transaction(function () use ($message) {
                $email = Email::where('message_id', $message->mail->messageId)->sole();
                $email->clicks()->create([
                    'user_agent'        =>  $message->click->userAgent ?? null,
                    'link'              =>  $message->click->link ?? null,
                    'link_tags'         =>  $message->click->linkTags ? (array) $message->click->linkTags : null,
                    'clicked_at'        =>  $message->click->timestamp ? new Carbon($message->click->timestamp) : null,
                ]);
                $email->has_click = true;
                $email->save();
    
                $this->logMessage('Click data was saved successfully.');
            });           

        } catch(\Throwable $th)
        {
            Log::error('Failed to save click event data.', [
                'error_object' => $th->__toString()
            ]);
        }
    }

    /**
    * Process a rendering failure event
    *
    * @return void  
    */
    public function handleRenderingFailureEvent(object $message) : void
    {
        if($this->resolver->hasCallback('RenderingFailure')) {
            $this->resolver->execute('RenderingFailure', $message);
            return;
        }

        try
        {
            DB::transaction(function () use ($message) {
                $email = Email::where('message_id', $message->mail->messageId)->sole();
                $email->renderingFailure()->create([
                    'template_name'         =>  $message->failure->templateName ?? null,
                    'error_message'         =>  $message->failure->errorMessage ?? null,
                    'failed_at'             =>  $message->mail->timestamp ? new Carbon($message->mail->timestamp) : null,
                ]);
                $email->has_rendering_failure = true;
                $email->save();
    
                $this->logMessage('Rendering failure data was saved successfully.');
            });           

        } catch(\Throwable $th)
        {
            Log::error('Failed to save rendering failure event data.', [
                'error_object' => $th->__toString()
            ]);
        }
    }

    /**
    * Process a delivery delay event
    *
    * @return void  
    */
    public function handleDeliveryDelayEvent(object $message) : void
    {
        if($this->resolver->hasCallback('DeliveryDelay')) {
            $this->resolver->execute('DeliveryDelay', $message);
            return;
        }

        try
        {
            DB::transaction(function () use ($message) {
                $email = Email::where('message_id', $message->mail->messageId)->sole();
                $email->deliveryDelay()->create([
                    'delay_type'            =>  $message->deliveryDelay->delayType ?? null,
                    'expiration_time'       =>  $message->deliveryDelay->expirationTime ? new Carbon($message->deliveryDelay->expirationTime) : null,
                    'delayed_at'            =>  $message->deliveryDelay->timestamp ? new Carbon($message->deliveryDelay->timestamp) : null,
                ]);
                $email->has_delivery_delay = true;
                $email->save();
    
                $this->logMessage('Delivery delay data was saved successfully.');
            });           

        } catch(\Throwable $th)
        {
            Log::error('Failed to save delivery delay event data.', [
                'error_object' => $th->__toString()
            ]);
        }
    }

    /**
    * Process a subscription event
    *
    * @return void  
    */
    public function handleSubscriptionEvent(object $message) : void
    {
        if($this->resolver->hasCallback('Subscription')) {
            $this->resolver->execute('Subscription', $message);
            return;
        }

        try
        {
            DB::transaction(function () use ($message) {
                $email = Email::where('message_id', $message->mail->messageId)->sole();
                $email->subscription()->create([
                    'contact_list'                  =>  $message->subscription->contactList ?? null,
                    'new_topic_preferences'         =>  $message->subscription->newTopicPreferences ? json_decode(json_encode($message->subscription->newTopicPreferences), true) : null,
                    'old_topic_preferences'         =>  $message->subscription->oldTopicPreferences ? json_decode(json_encode($message->subscription->oldTopicPreferences), true) : null,
                    'notified_at'                   =>  $message->subscription->timestamp ? new Carbon($message->subscription->timestamp) : null,
                ]);
                $email->has_subscription = true;
                $email->save();
    
                $this->logMessage('Subscription data was saved successfully.');
            });           

        } catch(\Throwable $th)
        {
            Log::error('Failed to save subscription event data.', [
                'error_object' => $th->__toString()
            ]);
        }
    }

    /**
     * Log message
     *
     */
    protected function logMessage(string $message): void
    {
        if ($this->debug()) 
        {
            Log::debug($message);
        }
    } 

    /**
     * Check if debugging is turned on
     *
     * @return bool
     */
    protected function debug(): bool
    {
        return (config('laravel-ses-event-manager.debug') === true);
    }
}