<?php

namespace Akhan619\LaravelSesEventManager\Tests\Feature\Implementations;

use Akhan619\LaravelSesEventManager\App\Models\Email;
use Akhan619\LaravelSesEventManager\Tests\FeatureTestCase;
use Illuminate\Routing\Router;

class EventManagerBounceTest extends FeatureTestCase
{
    protected array $tables;
    protected Router $router;
    protected string $routeName;
    protected string $emailTable;
    protected string $bounceTable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->router = $this->app->make(Router::class);
        $this->routeName = $this->app->config->get('laravel-ses-event-manager.named_route_prefix').'.bounces';

        // Import the tables from the migration
        $this->tables = [];
        $this->tables[] = include __DIR__.'/../../../database/migrations/create_emails_table.php.stub';
        $this->tables[] = include __DIR__.'/../../../database/migrations/create_email_bounces_table.php.stub';
        $this->tables[] = include __DIR__.'/../../../database/migrations/add_subject_to_emails_table.php.stub';
        $this->emailTable = config('laravel-ses-event-manager.database_name_prefix').'_emails';
        $this->bounceTable = config('laravel-ses-event-manager.database_name_prefix').'_email_bounces';

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
    public function bouceEventIsSuccessfullySavedWithDsnData()
    {
        $this->assertDatabaseCount($this->bounceTable, 0);
        $this->assertDatabaseCount($this->emailTable, 0);

        $email = Email::factory()->create();

        $this->assertModelExists($email);
        $this->assertDatabaseCount($this->emailTable, 1);
        $this->assertDatabaseHas($this->emailTable, [
            'has_bounce' => false,
        ]);

        $route = $this->router->getRoutes()->getByName($this->routeName);
        $fakeJson = json_decode($this->payloadWithDsn);
        $fakeJson->Message->mail->messageId = $email->message_id;
        $fakeJson->Message = json_encode($fakeJson->Message);

        $this->json(
            'POST',
            "$route->uri",
            (array) $fakeJson
        )
        ->assertJson(['success' => true])
        ->assertStatus(200);

        $this->assertDatabaseCount($this->bounceTable, 1);
        $this->assertDatabaseCount($this->emailTable, 1);
        $this->assertDatabaseHas($this->emailTable, [
            'has_bounce' => true,
        ]);
        $this->assertModelExists($email->bounce);
    }

    /** @test */
    public function bouceEventIsSuccessfullySavedWithoutDsnData()
    {
        $this->assertDatabaseCount($this->bounceTable, 0);
        $this->assertDatabaseCount($this->emailTable, 0);

        $email = Email::factory()->create();

        $this->assertModelExists($email);
        $this->assertDatabaseCount($this->emailTable, 1);
        $this->assertDatabaseHas($this->emailTable, [
            'has_bounce' => false,
        ]);

        $route = $this->router->getRoutes()->getByName($this->routeName);
        $fakeJson = json_decode($this->payloadWithoutDsn);
        $fakeJson->Message->mail->messageId = $email->message_id;
        $fakeJson->Message = json_encode($fakeJson->Message);

        $this->json(
            'POST',
            "$route->uri",
            (array) $fakeJson
        )
        ->assertJson(['success' => true])
        ->assertStatus(200);

        $this->assertDatabaseCount($this->bounceTable, 1);
        $this->assertDatabaseCount($this->emailTable, 1);
        $this->assertDatabaseHas($this->emailTable, [
            'has_bounce' => true,
        ]);
        $this->assertModelExists($email->bounce);
    }

    protected string $payloadWithDsn = '
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
            "eventType":"Bounce",
            "bounce":{
                "bounceType":"Permanent",
                "bounceSubType":"General",
                "bouncedRecipients":[
                    {
                        "emailAddress":"recipient@example.com",
                        "action":"failed",
                        "status":"5.1.1",
                        "diagnosticCode":"smtp; 550 5.1.1 user unknown"
                    }
                ],
                "timestamp":"2017-08-05T00:41:02.669Z",
                "feedbackId":"01000157c44f053b-61b59c11-9236-11e6-8f96-7be8aexample-000000",
                "reportingMTA":"dsn; mta.example.com"
            },
            "mail":{
                "timestamp":"2017-08-05T00:40:02.012Z",
                "source":"Sender Name <sender@example.com>",
                "sourceArn":"arn:aws:ses:us-east-1:123456789012:identity/sender@example.com",
                "sendingAccountId":"123456789012",
                "messageId":"EXAMPLE7c191be45-e9aedb9a-02f9-4d12-a87d-dd0099a07f8a-000000",
                "destination":[
                    "recipient@example.com"
                ],
                "headersTruncated":false,
                "headers":[
                    {
                        "name":"From",
                        "value":"Sender Name <sender@example.com>"
                    },
                    {
                        "name":"To",
                        "value":"recipient@example.com"
                    },
                    {
                        "name":"Subject",
                        "value":"Message sent from Amazon SES"
                    },
                    {
                        "name":"MIME-Version",
                        "value":"1.0"
                    },
                    {
                        "name":"Content-Type",
                        "value":"multipart/alternative; boundary=\"----=_Part_7307378_1629847660.1516840721503\""
                    }
                ],
                "commonHeaders":{
                    "from":[
                        "Sender Name <sender@example.com>"
                    ],
                    "to":[
                        "recipient@example.com"
                    ],
                    "messageId":"EXAMPLE7c191be45-e9aedb9a-02f9-4d12-a87d-dd0099a07f8a-000000",
                    "subject":"Message sent from Amazon SES"
                },
                "tags":{
                    "ses:configuration-set":[
                        "ConfigSet"
                    ],
                    "ses:source-ip":[
                        "192.0.2.0"
                    ],
                    "ses:from-domain":[
                        "example.com"
                    ],
                    "ses:caller-identity":[
                        "ses_user"
                    ]
                }
            }
        }
    }';

    protected string $payloadWithoutDsn = '
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
            "eventType":"Bounce",
            "bounce":{
                "bounceType":"Permanent",
                "bounceSubType":"General",
                "bouncedRecipients":[
                    {
                        "emailAddress":"recipient@example.com"
                    }
                ],
                "timestamp":"2017-08-05T00:41:02.669Z",
                "feedbackId":"01000157c44f053b-61b59c11-9236-11e6-8f96-7be8aexample-000000"
            },
            "mail":{
                "timestamp":"2017-08-05T00:40:02.012Z",
                "source":"Sender Name <sender@example.com>",
                "sourceArn":"arn:aws:ses:us-east-1:123456789012:identity/sender@example.com",
                "sendingAccountId":"123456789012",
                "messageId":"EXAMPLE7c191be45-e9aedb9a-02f9-4d12-a87d-dd0099a07f8a-000000",
                "destination":[
                    "recipient@example.com"
                ],
                "headersTruncated":false,
                "headers":[
                    {
                        "name":"From",
                        "value":"Sender Name <sender@example.com>"
                    },
                    {
                        "name":"To",
                        "value":"recipient@example.com"
                    },
                    {
                        "name":"Subject",
                        "value":"Message sent from Amazon SES"
                    },
                    {
                        "name":"MIME-Version",
                        "value":"1.0"
                    },
                    {
                        "name":"Content-Type",
                        "value":"multipart/alternative; boundary=\"----=_Part_7307378_1629847660.1516840721503\""
                    }
                ],
                "commonHeaders":{
                    "from":[
                        "Sender Name <sender@example.com>"
                    ],
                    "to":[
                        "recipient@example.com"
                    ],
                    "messageId":"EXAMPLE7c191be45-e9aedb9a-02f9-4d12-a87d-dd0099a07f8a-000000",
                    "subject":"Message sent from Amazon SES"
                },
                "tags":{
                    "ses:configuration-set":[
                        "ConfigSet"
                    ],
                    "ses:source-ip":[
                        "192.0.2.0"
                    ],
                    "ses:from-domain":[
                        "example.com"
                    ],
                    "ses:caller-identity":[
                        "ses_user"
                    ]
                }
            }
        }
    }';
}
