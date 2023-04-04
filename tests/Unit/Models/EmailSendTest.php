<?php

namespace Akhan619\LaravelSesEventManager\Tests\Unit\Models;

use Akhan619\LaravelSesEventManager\App\Models\Email;
use Akhan619\LaravelSesEventManager\App\Models\EmailSend;
use Akhan619\LaravelSesEventManager\Tests\UnitTestCase;
use Illuminate\Support\Facades\Schema;

class EmailSendTest extends UnitTestCase
{
    protected array $tables;

    protected function setUp(): void
    {
        parent::setUp();

        // Import the tables from the migration
        $this->tables = [];
        $this->tables[] = include __DIR__.'/../../../database/migrations/create_emails_table.php.stub';
        $this->tables[] = include __DIR__.'/../../../database/migrations/create_email_sends_table.php.stub';
        $this->tables[] = include __DIR__.'/../../../database/migrations/add_subject_to_emails_table.php.stub';

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
    public function emailSendsTableIsCreatedSuccessfully()
    {
        $this->assertTrue(Schema::hasTable(config('laravel-ses-event-manager.database_name_prefix').'_email_sends'));
    }

    /** @test */
    public function emailSendModelCanBeCreatedSuccessfully()
    {
        $email = Email::factory()->sent()->create();
        $emailSend = EmailSend::factory()->for($email, 'email')->create();

        $this->assertModelExists($email);
        $this->assertModelExists($emailSend);
        $this->assertEquals($email->message_id, $emailSend->message_id, 'message_ids dont match for the two model instances.');
    }

    /** @test */
    public function modelRelationshipsAreWorking()
    {
        $email = Email::factory()->sent()->create();
        $emailSend = EmailSend::factory()->for($email, 'email')->create();

        $this->assertEquals($email->message_id, $emailSend->email->message_id, 'Belongs To object is different from the Parent object.');
        $this->assertEquals($email->send->message_id, $emailSend->message_id, 'Has One object is different from the Child object.');
    }
}
