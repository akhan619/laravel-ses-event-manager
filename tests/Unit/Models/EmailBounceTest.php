<?php

namespace Akhan619\LaravelSesEventManager\Tests\Unit\Models;

use Akhan619\LaravelSesEventManager\App\Models\Email;
use Akhan619\LaravelSesEventManager\App\Models\EmailBounce;
use Akhan619\LaravelSesEventManager\Tests\UnitTestCase;
use Illuminate\Support\Facades\Schema;

class EmailBounceTest extends UnitTestCase
{
    protected array $tables;

    protected function setUp(): void
    {
        parent::setUp();

        // Import the tables from the migration
        $this->tables = [];
        $this->tables[] = include __DIR__.'/../../../database/migrations/create_emails_table.php.stub';
        $this->tables[] = include __DIR__.'/../../../database/migrations/create_email_bounces_table.php.stub';

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
    public function emailBouncesTableIsCreatedSuccessfully()
    {
        $this->assertTrue(Schema::hasTable(config('laravel-ses-event-manager.database_name_prefix').'_email_bounces'));
    }

    /** @test */
    public function emailBounceModelCanBeCreatedSuccessfully()
    {
        $email = Email::factory()->bounced()->create();
        $emailBounce = EmailBounce::factory()->for($email, 'email')->create();

        $this->assertModelExists($email);
        $this->assertModelExists($emailBounce);
        $this->assertEquals($email->message_id, $emailBounce->message_id, 'message_ids dont match for the two model instances.');
    }

    /** @test */
    public function modelRelationshipsAreWorking()
    {
        $email = Email::factory()->bounced()->create();
        $emailBounce = EmailBounce::factory()->for($email, 'email')->create();

        $this->assertEquals($email->message_id, $emailBounce->email->message_id, 'Belongs To object is different from the Parent object.');
        $this->assertEquals($email->bounce->message_id, $emailBounce->message_id, 'Has One object is different from the Child object.');
    }
}
