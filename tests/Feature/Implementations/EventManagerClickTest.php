<?php

namespace Akhan619\LaravelSesEventManager\Tests\Feature\Implementations;

use Akhan619\LaravelSesEventManager\App\Models\Email;
use Akhan619\LaravelSesEventManager\Tests\FeatureTestCase;
use Illuminate\Routing\Router;

class EventManagerClickTest extends FeatureTestCase
{
    protected array $tables;
    protected Router $router;
    protected string $routeName;
    protected string $emailTable;
    protected string $clickTable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->router = $this->app->make(Router::class);
        $this->routeName = $this->app->config->get('laravel-ses-event-manager.named_route_prefix').'.clicks';

        // Import the tables from the migration
        $this->tables = [];
        $this->tables[] = include __DIR__.'/../../../database/migrations/create_emails_table.php.stub';
        $this->tables[] = include __DIR__.'/../../../database/migrations/create_email_clicks_table.php.stub';
        $this->tables[] = include __DIR__.'/../../../database/migrations/add_subject_to_emails_table.php.stub';
        $this->emailTable = config('laravel-ses-event-manager.database_name_prefix').'_emails';
        $this->clickTable = config('laravel-ses-event-manager.database_name_prefix').'_email_clicks';

        foreach ($this->tables as $table) {
            $table->up();
        }
    }

    protected function tearDown(): void
    {
        foreach (array_reverse($this->tables) as $table) {
            $table->down();
        }

        parent::tearDown();
    }

    /** @test */
    public function clickEventIsSuccessfullySaved()
    {
        $this->assertDatabaseCount($this->clickTable, 0);
        $this->assertDatabaseCount($this->emailTable, 0);

        $email = Email::factory()->create();

        $this->assertModelExists($email);
        $this->assertDatabaseCount($this->emailTable, 1);
        $this->assertDatabaseHas($this->emailTable, [
            'has_click' => false,
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

        $this->assertDatabaseCount($this->clickTable, 1);
        $this->assertDatabaseCount($this->emailTable, 1);
        $this->assertDatabaseHas($this->emailTable, [
            'has_click' => true,
        ]);
        $this->assertModelExists($email->clicks->first());
    }

    /** @test */
    public function multipleClickEventsAreSuccessfullySaved()
    {
        $this->assertDatabaseCount($this->clickTable, 0);
        $this->assertDatabaseCount($this->emailTable, 0);

        $email = Email::factory()->create();

        $this->assertModelExists($email);
        $this->assertDatabaseCount($this->emailTable, 1);
        $this->assertDatabaseHas($this->emailTable, [
            'has_click' => false,
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

        $this->assertDatabaseCount($this->clickTable, 1);
        $this->assertDatabaseCount($this->emailTable, 1);
        $this->assertDatabaseHas($this->emailTable, [
            'has_click' => true,
        ]);
        $this->assertModelExists($email->clicks->first());

        $this->json(
            'POST',
            "$route->uri",
            (array) $fakeJson
        )
        ->assertJson(['success' => true])
        ->assertStatus(200);

        $this->assertDatabaseCount($this->clickTable, 2);
        $this->assertDatabaseCount($this->emailTable, 1);
        $this->assertDatabaseHas($this->emailTable, [
            'has_click' => true,
        ]);

        $email->refresh();
        $this->assertModelExists($email->clicks->skip(1)->first());
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
            "eventType": "Click",
            "click": {
                "ipAddress": "192.0.2.1",
                "link": "http://docs.aws.amazon.com/ses/latest/DeveloperGuide/send-email-smtp.html",
                "linkTags": {
                    "samplekey0": [
                        "samplevalue0"
                    ],
                    "samplekey1": [
                        "samplevalue1"
                    ]
                },
                "timestamp": "2017-08-09T23:51:25.570Z",
                "userAgent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.90 Safari/537.36"
            },
            "mail": {
                "commonHeaders": {
                    "from": [
                        "sender@example.com"
                    ],
                    "messageId": "EXAMPLE7c191be45-e9aedb9a-02f9-4d12-a87d-dd0099a07f8a-000000",
                    "subject": "Message sent from Amazon SES",
                    "to": [
                        "recipient@example.com"
                    ]
                },
                "destination": [
                    "recipient@example.com"
                ],
                "headers": [
                    {
                        "name": "X-SES-CONFIGURATION-SET",
                        "value": "ConfigSet"
                    },
                    {
                        "name":"X-SES-MESSAGE-TAGS",
                        "value":"myCustomTag1=myCustomValue1, myCustomTag2=myCustomValue2"
                    },
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
                        "value": "multipart/alternative; boundary=\"XBoundary\""
                    },
                    {
                        "name": "Message-ID",
                        "value": "EXAMPLE7c191be45-e9aedb9a-02f9-4d12-a87d-dd0099a07f8a-000000"
                    }
                ],
                "headersTruncated": false,
                "messageId": "EXAMPLE7c191be45-e9aedb9a-02f9-4d12-a87d-dd0099a07f8a-000000",
                "sendingAccountId": "123456789012",
                "source": "sender@example.com",
                "tags": {
                    "myCustomTag1":[
                        "myCustomValue1"
                    ],
                    "myCustomTag2":[
                        "myCustomValue2"
                    ],
                    "ses:caller-identity": [
                        "ses_user"
                    ],
                    "ses:configuration-set": [
                        "ConfigSet"
                    ],
                    "ses:from-domain": [
                        "example.com"
                    ],
                    "ses:source-ip": [
                        "192.0.2.0"
                    ]
                },
                "timestamp": "2017-08-09T23:50:05.795Z"
            }
        }
    }';
}
