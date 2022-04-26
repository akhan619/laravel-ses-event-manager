<?php

namespace Akhan619\LaravelSesEventManager\Tests\Feature\Implementations;

use Akhan619\LaravelSesEventManager\App\Models\Email;
use Akhan619\LaravelSesEventManager\Tests\FeatureTestCase;
use Illuminate\Routing\Router;

class EventManagerSubscriptionTest extends FeatureTestCase
{
    protected array $tables;
    protected Router $router;
    protected string $routeName;
    protected string $emailTable;
    protected string $subscriptionTable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->router = $this->app->make(Router::class);
        $this->routeName = $this->app->config->get('laravel-ses-event-manager.named_route_prefix').'.subscriptions';

        // Import the tables from the migration
        $this->tables = [];
        $this->tables[] = include __DIR__.'/../../../database/migrations/create_emails_table.php.stub';
        $this->tables[] = include __DIR__.'/../../../database/migrations/create_email_subscriptions_table.php.stub';
        $this->emailTable = config('laravel-ses-event-manager.database_name_prefix').'_emails';
        $this->subscriptionTable = config('laravel-ses-event-manager.database_name_prefix').'_email_subscriptions';

        foreach ($this->tables as $table) {
            $table->up();
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->tables as $table) {
            $table->down();
        }

        parent::tearDown();
    }

    /** @test */
    public function subscriptionEventIsSuccessfullySaved()
    {
        $this->assertDatabaseCount($this->subscriptionTable, 0);
        $this->assertDatabaseCount($this->emailTable, 0);

        $email = Email::factory()->create();

        $this->assertModelExists($email);
        $this->assertDatabaseCount($this->emailTable, 1);
        $this->assertDatabaseHas($this->emailTable, [
            'has_subscription' => false,
        ]);

        $route = $this->router->getRoutes()->getByName($this->routeName);
        $fakeJson = json_decode($this->payload);
        $fakeJson->Message->mail->messageId = $email->message_id;
        $fakeJson->Message = json_encode($fakeJson->Message);

        $this->json(
            'POST',
            "$route->uri",
            (array) $fakeJson
        )
        ->assertJson(['success' => true])
        ->assertStatus(200);

        $this->assertDatabaseCount($this->subscriptionTable, 1);
        $this->assertDatabaseCount($this->emailTable, 1);
        $this->assertDatabaseHas($this->emailTable, [
            'has_subscription' => true,
        ]);
        $this->assertModelExists($email->subscription);
    }

    protected string $payload = '
    {
        "Type" : "Notification",
        "MessageId" : "22b80b92-fdea-4c2c-8f9d-bdfb0c7bf324",
        "TopicArn" : "arn:aws:sns:us-west-2:123456789012:MyTopic",
        "Subject" : "My First Message",
        "Timestamp" : "2012-05-02T00:54:06.655Z",
        "SignatureVersion" : "1",
        "Signature" : "EXAMPLEw6JRN...",
        "SigningCertURL" : "https://sns.us-west-2.amazonaws.com/SimpleNotificationService-f3ecfb7224c7233fe7bb5f59f96de52f.pem",
        "UnsubscribeURL" : "https://sns.us-west-2.amazonaws.com/?Action=Unsubscribe&SubscriptionArn=arn:aws:sns:us-west-2:123456789012:MyTopic:c9135db0-26c4-47ec-8998-413945fb5a96",
        "Message" : {
            "eventType": "Subscription",
            "mail": {
                "timestamp": "2022-01-12T01:00:14.340Z",
                "source": "monitor@category.sysmon-iad.dohe.com",
                "sourceArn": "arn:aws:ses:us-east-1:777788889999:identity/category.sysmon-iad.dohe.com",
                "sendingAccountId": "777788889999",
                "messageId": "0100017e4bccb684-777bc8de-afa7-4970-92b0-f515137b1497-000000",
                "destination": ["subscription-event-7799@default.sysmon-iad.dohe.com"],
                "headersTruncated": false,
                "headers": [
                    {
                        "name": "Return-Path",
                        "value": "monitor@category.sysmon-iad.dohe.com"
                    },
                    {
                        "name": "From",
                        "value": "monitor@category.sysmon-iad.dohe.com"
                    },
                    {
                        "name": "Reply-To",
                        "value": "monitor@category.sysmon-iad.dohe.com"
                    },
                    {
                        "name": "To",
                        "value": "subscription-event-7799@default.sysmon-iad.dohe.com"
                    },
                    {
                        "name": "Subject",
                        "value": "Bacon System Monitor test OP:SubscriptionEventCanary SEND_TIME:2022-01-12T01:00:14.180Z"
                    },
                    {
                        "name": "MIME-Version",
                        "value": "1.0"
                    },
                    {
                        "name": "Content-Type",
                        "value": "text/html; charset=UTF-8"
                    },
                    {
                        "name": "Content-Transfer-Encoding",
                        "value": "7bit"
                    }
                ],
                "commonHeaders": {
                    "returnPath": "monitor@category.sysmon-iad.dohe.com",
                    "from": ["monitor@category.sysmon-iad.dohe.com"],
                    "replyTo": ["monitor@category.sysmon-iad.dohe.com"],
                    "to": ["subscription-event-7799@default.sysmon-iad.dohe.com"],
                    "messageId": "0100017e4bccb684-777bc8de-afa7-4970-92b0-f515137b1497-000000",
                    "subject": "Bacon System Monitor test OP:SubscriptionEventCanary SEND_TIME:2022-01-12T01:00:14.180Z"
                },
                "tags": {
                    "ses:operation": ["SendEmail"],
                    "ses:configuration-set": ["prod-us-east-1-sesV2-subscription-cardinal-configSet"],
                    "ses:source-ip": ["54.156.777.999"],
                    "ses:from-domain": ["category.sysmon-iad.dohe.com"],
                    "Canary": ["SubscriptionEventCanary"],
                    "ses:caller-identity": ["prod-us-east-1-core-app"],
                    "ExpectedOutcome": ["Subscription"]
                }
            },
            "subscription": {
                "contactList": "SystemMonitor-Canary",
                "timestamp": "2022-01-12T01:00:17.910Z",
                "source": "UnsubscribeHeader",
                "newTopicPreferences": {
                    "unsubscribeAll": true,
                    "topicSubscriptionStatus": [
                        {
                            "topicName": "Canary-Topic",
                            "subscriptionStatus": "OptOut"
                        }
                    ]
                },
                "oldTopicPreferences": {
                    "unsubscribeAll": false,
                    "topicSubscriptionStatus": [
                        {
                            "topicName": "Canary-Topic",
                            "subscriptionStatus": "OptOut"
                        }
                    ]
                }
            }
        }
    }';
}
