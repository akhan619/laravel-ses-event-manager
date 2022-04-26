<?php

namespace Akhan619\LaravelSesEventManager\Tests\Feature\Controller;

use Akhan619\LaravelSesEventManager\Tests\FeatureTestCase;
use Illuminate\Http\Client\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Http;

class BaseControllerTest extends FeatureTestCase
{
    protected Router $router;
    protected string $routeName;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        // Code before application created.

        parent::setUp();

        // Code after application created.

        $this->router = $this->app->make(Router::class);
        $this->routeName = $this->app->config->get('laravel-ses-event-manager.named_route_prefix').'.bounces';
    }

    protected function disableSubscriptionConfirmation($app)
    {
        $app['config']->set('laravel-ses-event-manager.confirm_subscription', false);
    }

    protected function enableSubscriptionConfirmation($app)
    {
        $app['config']->set('laravel-ses-event-manager.confirm_subscription', true);
    }

    /**
     * @test
     * @define-env enableSubscriptionConfirmation
     */
    public function subscriptionIsConfirmedCorrectlyWhenEnabled(): void
    {
        Http::fake([
            // Stub a JSON response for the fake subscribe url
            'fake-subscribe-url.com' => Http::response($this->exampleSubscriptionConfirmation, 200),
        ]);

        $fakeJson = json_decode($this->exampleSubscriptionResponse);

        $route = $this->router->getRoutes()->getByName($this->routeName);

        $this->json(
            'POST',
            "$route->uri",
            (array) $fakeJson
        )
        ->assertJson(['success' => true])
        ->assertStatus(200);
    }

    /**
     * @test
     * @define-env disableSubscriptionConfirmation
     */
    public function subscriptionIsSkippedCorrectlyWhenDisabled(): void
    {
        Http::fake([
            // Stub a JSON response for the fake subscribe url
            'fake-subscribe-url.com' => Http::response($this->exampleSubscriptionConfirmation, 200),
        ]);

        $fakeJson = json_decode($this->exampleSubscriptionResponse);

        $route = $this->router->getRoutes()->getByName($this->routeName);

        $this->json(
            'POST',
            "$route->uri",
            (array) $fakeJson
        )
        ->assertJson(['success' => true])
        ->assertStatus(200);

        Http::assertNotSent(function (Request $request) {
            return $request->url() === 'fake-subscribe-url.com';
        });
    }

    /**
     * @test
     */
    public function subscriptionFailsWhenGetRequestToSubscribeUrlReturnsNonOkStatus(): void
    {
        Http::fake([
            // Stub a JSON response for the fake subscribe url
            'fake-subscribe-url.com' => Http::response($this->exampleSubscriptionConfirmation, 500),
        ]);

        $fakeJson = json_decode($this->exampleSubscriptionResponse);

        $route = $this->router->getRoutes()->getByName($this->routeName);

        $this->json(
            'POST',
            "$route->uri",
            (array) $fakeJson
        )
        ->assertJson(['success' => false])
        ->assertStatus(422);
    }

    /**
     * @test
     */
    public function returnsCorrectStatusWhenNotificationTypeIsUnknown(): void
    {
        $fakeJson = json_decode($this->exampleUnknownResponseType);

        $route = $this->router->getRoutes()->getByName($this->routeName);

        $this->json(
            'POST',
            "$route->uri",
            (array) $fakeJson
        )
        ->assertJson(['success' => false])
        ->assertStatus(422);
    }

    /**
     * @test
     */
    public function returnsCorrectStatusWhenResponseMessageBodyFailsToParse(): void
    {
        $fakeJson = json_decode($this->examplePoorNotificationResponseMessageBody);

        $route = $this->router->getRoutes()->getByName($this->routeName);

        $this->json(
            'POST',
            "$route->uri",
            (array) $fakeJson
        )
        ->assertJson(['success' => false])
        ->assertStatus(422);
    }

    private $exampleSubscriptionResponse = '{
        "Type" : "SubscriptionConfirmation",
        "MessageId" : "165545c9-2a5c-472c-8df2-7ff2be2b3b1b",
        "Token" : "2336412f37fb687f5d51e6e241d09c805a5a57b30d712f794cc5f6a988666d92768dd60a747ba6f3beb71854e285d6ad02428b09ceece29417f1f02d609c582afbacc99c583a916b9981dd2728f4ae6fdb82efd087cc3b7849e05798d2d2785c03b0879594eeac82c01f235d0e717736",
        "TopicArn" : "arn:aws:sns:us-west-2:123456789012:MyTopic",
        "Message" : "You have chosen to subscribe to the topic arn:aws:sns:us-west-2:123456789012:MyTopic.\nTo confirm the subscription, visit the SubscribeURL included in this message.",
        "SubscribeURL" : "fake-subscribe-url.com",
        "Timestamp" : "2012-04-26T20:45:04.751Z",
        "SignatureVersion" : "1",
        "Signature" : "EXAMPLEpH+DcEwjAPg8O9mY8dReBSwksfg2S7WKQcikcNKWLQjwu6A4VbeS0QHVCkhRS7fUQvi2egU3N858fiTDN6bkkOxYDVrY0Ad8L10Hs3zH81mtnPk5uvvolIC1CXGu43obcgFxeL3khZl8IKvO61GWB6jI9b5+gLPoBc1Q=",
        "SigningCertURL" : "https://sns.us-west-2.amazonaws.com/SimpleNotificationService-f3ecfb7224c7233fe7bb5f59f96de52f.pem"
    }';

    private $exampleSubscriptionConfirmation = '
    <ConfirmSubscriptionResponse xmlns="http://sns.amazonaws.com/doc/2010-03-31/">
        <ConfirmSubscriptionResult>
            <SubscriptionArn>arn:aws:sns:us-west-2:123456789012:MyTopic:2bcfbf39-05c3-41de-beaa-fcfcc21c8f55</SubscriptionArn>
        </ConfirmSubscriptionResult>
        <ResponseMetadata>
            <RequestId>075ecce8-8dac-11e1-bf80-f781d96e9307</RequestId>
        </ResponseMetadata>
    </ConfirmSubscriptionResponse>';

    private $examplePoorNotificationResponseMessageBody = '{
        "Type" : "Notification",
        "MessageId" : "22b80b92-fdea-4c2c-8f9d-bdfb0c7bf324",
        "TopicArn" : "arn:aws:sns:us-west-2:123456789012:MyTopic",
        "Subject" : "My First Message",
        "Message" : "Will Fail",
        "Timestamp" : "2012-05-02T00:54:06.655Z",
        "SignatureVersion" : "1",
        "Signature" : "EXAMPLEw6JRN...",
        "SigningCertURL" : "https://sns.us-west-2.amazonaws.com/SimpleNotificationService-f3ecfb7224c7233fe7bb5f59f96de52f.pem",
        "UnsubscribeURL" : "https://sns.us-west-2.amazonaws.com/?Action=Unsubscribe&SubscriptionArn=arn:aws:sns:us-west-2:123456789012:MyTopic:c9135db0-26c4-47ec-8998-413945fb5a96"
    }';

    private $exampleUnknownResponseType = '{
        "Type" : "Unknown",
        "MessageId" : "22b80b92-fdea-4c2c-8f9d-bdfb0c7bf324",
        "TopicArn" : "arn:aws:sns:us-west-2:123456789012:MyTopic",
        "Subject" : "My First Message",
        "Message" : "Will Fail",
        "Timestamp" : "2012-05-02T00:54:06.655Z",
        "SignatureVersion" : "1",
        "Signature" : "EXAMPLEw6JRN...",
        "SigningCertURL" : "https://sns.us-west-2.amazonaws.com/SimpleNotificationService-f3ecfb7224c7233fe7bb5f59f96de52f.pem",
        "UnsubscribeURL" : "https://sns.us-west-2.amazonaws.com/?Action=Unsubscribe&SubscriptionArn=arn:aws:sns:us-west-2:123456789012:MyTopic:c9135db0-26c4-47ec-8998-413945fb5a96"
    }';
}
