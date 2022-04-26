<?php

namespace Akhan619\LaravelSesEventManager\Tests\Feature\Implementations;

use Akhan619\LaravelSesEventManager\App\Models\Email;
use Akhan619\LaravelSesEventManager\Tests\FeatureTestCase;
use Illuminate\Routing\Router;

class EventManagerSendTest extends FeatureTestCase
{
    protected array $tables;
    protected Router $router;
    protected string $routeName;
    protected string $emailTable;
    protected string $sendTable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->router = $this->app->make(Router::class);
        $this->routeName = $this->app->config->get('laravel-ses-event-manager.named_route_prefix').'.sends';

        // Import the tables from the migration
        $this->tables = [];
        $this->tables[] = include __DIR__.'/../../../database/migrations/create_emails_table.php.stub';
        $this->tables[] = include __DIR__.'/../../../database/migrations/create_email_sends_table.php.stub';
        $this->emailTable = config('laravel-ses-event-manager.database_name_prefix').'_emails';
        $this->sendTable = config('laravel-ses-event-manager.database_name_prefix').'_email_sends';

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
    public function sendEventIsSuccessfullySaved()
    {
        $this->assertDatabaseCount($this->sendTable, 0);
        $this->assertDatabaseCount($this->emailTable, 0);

        $email = Email::factory()->create();

        $this->assertModelExists($email);
        $this->assertDatabaseCount($this->emailTable, 1);
        $this->assertDatabaseHas($this->emailTable, [
            'has_send' => false,
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

        $this->assertDatabaseCount($this->sendTable, 1);
        $this->assertDatabaseCount($this->emailTable, 1);
        $this->assertDatabaseHas($this->emailTable, [
            'has_send' => true,
        ]);
        $this->assertModelExists($email->send);
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
            "eventType": "Send",
            "mail": {
                "timestamp": "2016-10-14T05:02:16.645Z",
                "source": "sender@example.com",
                "sourceArn": "arn:aws:ses:us-east-1:123456789012:identity/sender@example.com",
                "sendingAccountId": "123456789012",
                "messageId": "EXAMPLE7c191be45-e9aedb9a-02f9-4d12-a87d-dd0099a07f8a-000000",
                "destination": [
                    "recipient@example.com"
                ],
                "headersTruncated": false,
                "headers": [
                    {
                        "name": "From",
                        "value": "sender@example.com"
                    },
                    {
                        "name": "To",
                        "value": "recipient@example.com"
                    },
                    {
                        "name": "Subject",
                        "value": "Message sent from Amazon SES"
                    },
                    {
                        "name": "MIME-Version",
                        "value": "1.0"
                    },
                    {
                        "name": "Content-Type",
                        "value": "multipart/mixed;  boundary=\"----=_Part_0_716996660.1476421336341\""
                    },
                    {
                        "name": "X-SES-MESSAGE-TAGS",
                        "value": "myCustomTag1=myCustomTagValue1, myCustomTag2=myCustomTagValue2"
                    }
                ],
                "commonHeaders": {
                    "from": [
                        "sender@example.com"
                    ],
                    "to": [
                        "recipient@example.com"
                    ],
                    "messageId": "EXAMPLE7c191be45-e9aedb9a-02f9-4d12-a87d-dd0099a07f8a-000000",
                    "subject": "Message sent from Amazon SES"
                },
                "tags": {
                    "ses:configuration-set": [
                        "ConfigSet"
                    ],
                    "ses:source-ip": [
                        "192.0.2.0"
                    ],
                    "ses:from-domain": [
                        "example.com"
                    ],      
                    "ses:caller-identity": [
                        "ses_user"
                    ],
                    "myCustomTag1": [
                        "myCustomTagValue1"
                    ],
                    "myCustomTag2": [
                        "myCustomTagValue2"
                    ]      
                }
            },
            "send": {}
        }
    }';
}
