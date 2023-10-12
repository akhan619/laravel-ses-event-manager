<?php

namespace Akhan619\LaravelSesEventManager\App\Http\Controllers;

use Akhan619\LaravelSesEventManager\Contracts\BaseControllerContract;
use Akhan619\LaravelSesEventManager\Contracts\EventManagerContract;
use Akhan619\LaravelSesEventManager\Contracts\RouteLoaderContract;
use Aws\Sns\Exception\InvalidSnsMessageException;
use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Psr\Http\Message\ServerRequestInterface;

class BaseController extends Controller implements BaseControllerContract
{
    /**
     * Handle the required controller action.
     *
     * Using the magic __call() method we don't duplicate the same code or bloat our code for
     * all 10 possible controller action like bounce/send/click etc.
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    public function __call($name, $args)
    {
        // Retrive the current PSR request and route loader from the container.
        $request = app()->make(ServerRequestInterface::class);
        $routeLoader = app()->make(RouteLoaderContract::class);

        // Check if the method name is in the list of controller actions that have been
        // created by the RouteLoader class. This validation check is there to ensure that we only
        // process the route actions registered by our package
        if (in_array($name, $routeLoader::$controllerActions, true)) {
            // We now validate and process the message.
            $result = $this->handleRequest($request, Str::studly($name));

            // At this point the sns validator check has passed.
            if ($result instanceof JsonResponse) {
                return $result;
            }

            // Process the event
            app()->make(EventManagerContract::class)->handleEvent($name, $result);

            return response()->json([
                'success' => true,
                'message' => Str::studly($name).' processed.',
            ]);
        } else {
            parent::__call($name, $args);
        }
    }

    /**
     * Validate and process the request.
     *
     * Here we validate that the request is really from SNS. If the check is ok,
     * we check what kind of SNS message it is. It should be either a subscription
     * confirmation or a notification message. We return a JSON if the message is a
     * subscription or if it is NOT a notification. For notifications we return the message body as
     * a std object.
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function handleRequest(ServerRequestInterface $request, string $type): mixed
    {
        // Validate the SNS message. Skipped during testing as message with proper signature cannot be
        // created on our end. We don't have the aws private key to sign our messages.
        $this->validateSns($request);

        $body = request()->getContent();

        $this->logResult($body);

        $body = json_decode($body);

        if ($body === null) {
            Log::error("Failed to parse AWS SES $type request ".json_last_error_msg());

            return response()->json(['success' => false], 422);
        }

        // Check for subscription confirmation message
        if ($this->isSubscriptionConfirmation($body)) {
            // Is confirming subscriptions enabled.
            if (config('laravel-ses-event-manager.confirm_subscription', false)) {
                $subscriptionConfirmed = $this->confirmSubscription($body);

                if ($subscriptionConfirmed) {
                    return response()->json([
                        'success' => true,
                        'message' => "$type subscription confirmed.",
                    ]);
                } else {
                    return response()->json(['success' => false], 422);
                }
            }

            // No, confirming subscriptions is not enabled. Log if logging is enabled and continue as if everything is OK.
            $this->logMessage('Subscription received for ('.$body->TopicArn.') with SubscribeUrl: '.$body->SubscribeURL);

            return response()->json([
                'success' => true,
                'message' => "$type subscription received.",
            ]);
        }

        // Check if the message is a notification
        if ($this->isNotTopicNotification($body)) {
            Log::info("SES Event notification did not match known type. Type Received: {$body->Type}.");

            return response()->json(['success' => false], 422);
        }

        // At this point we have a valid SNS notification message.
        $message = json_decode($body->Message);

        // The SES event notification has a Message property that should be a object when decoded.
        if (!is_object($message)) {
            Log::error('Result message failed to decode: '.json_last_error_msg());

            return response()->json(['success' => false], 422);
        }

        // Handle notificationType field that can be present instead of eventType
        if (!isset($message->eventType)) {
            $message->eventType = $message->notificationType;
        }

        return $message;
    }

    /**
     * Validate SNS requests from AWS.
     */
    protected function validateSns(ServerRequestInterface $request): void
    {
        if (!App::environment('testing')) {
            $message = Message::fromPsrRequest($request);
            $validator = new MessageValidator();

            try {
                $validator->validate($message);
            } catch (InvalidSnsMessageException $e) {
                // Pretend we're not here if the message is invalid
                abort(404, 'Not Found');
            }
        }
    }

    /**
     * Make the call back to AWS to confirm subscription.
     *
     * @return bool
     */
    protected function confirmSubscription(object $body): bool
    {
        // Make a GET request to confirm the subscription.
        $response = Http::get($body->SubscribeURL);

        $this->logResult($response->body());

        // We also put a simple check to make sure the confirmation worked. The subscribe url request will
        // return a 200 status with an xml response on success.
        $xml = simplexml_load_string($response->body());

        if ($response->ok() && $xml !== false && !empty((string) $xml->ConfirmSubscriptionResult->SubscriptionArn)) {
            $this->logMessage('Subscribed to ('.$body->TopicArn.') using GET Request '.$body->SubscribeURL);

            return true;
        } else {
            $this->logMessage('Subscription Attempt Failed for ('.$body->TopicArn.') using GET Request '.$body->SubscribeURL);

            return false;
        }
    }

    /**
     * Check if AWS is trying to confirm subscription.
     *
     * @return bool
     */
    protected function isSubscriptionConfirmation(object $body): bool
    {
        if (isset($body->Type) && ($body->Type === 'SubscriptionConfirmation')) {
            $this->logMessage('Received subscription confirmation: '.$body->TopicArn);

            return true;
        }

        return false;
    }

    /**
     * Check if the message is a topic notification.
     *
     * @return bool
     */
    protected function isTopicNotification(object $body): bool
    {
        if (isset($body->Type) && $body->Type == 'Notification') {
            $this->logMessage('Received topic notification: '.$body->TopicArn);

            return true;
        }

        return false;
    }

    /**
     * Is not a topic notification.
     *
     * @return bool
     */
    protected function isNotTopicNotification(object $body): bool
    {
        return !$this->isTopicNotification($body);
    }

    /**
     * Log message.
     */
    protected function logMessage(string $message): void
    {
        if ($this->debug()) {
            Log::debug($message);
        }
    }

    /**
     * Debug mode on.
     */
    protected function logResult(string $content): void
    {
        if ($this->debug()) {
            Log::debug("REQUEST BODY:\n".$content);
        }
    }

    /**
     * Check if debugging is turned on.
     *
     * @return bool
     */
    protected function debug(): bool
    {
        return config('laravel-ses-event-manager.debug') === true;
    }
}
