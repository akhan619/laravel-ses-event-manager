<?php

namespace Akhan619\LaravelSesEventManager\Tests\Unit\Models;

use Akhan619\LaravelSesEventManager\App\Models\Email;
use Akhan619\LaravelSesEventManager\App\Models\EmailComplaint;
use Akhan619\LaravelSesEventManager\Tests\UnitTestCase;
use Illuminate\Support\Facades\Schema;

class EmailComplaintTest extends UnitTestCase
{
    protected array $tables;

    protected function setUp(): void
    {
        parent::setUp();

        // Import the tables from the migration
        $this->tables = [];
        $this->tables[] = include __DIR__.'/../../../database/migrations/create_emails_table.php.stub';
        $this->tables[] = include __DIR__.'/../../../database/migrations/create_email_complaints_table.php.stub';
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
    public function emailComplaintsTableIsCreatedSuccessfully()
    {
        $this->assertTrue(Schema::hasTable(config('laravel-ses-event-manager.database_name_prefix').'_email_complaints'));
    }

    /** @test */
    public function emailComplaintModelCanBeCreatedSuccessfully()
    {
        $email = Email::factory()->complained()->create();
        $emailComplaint = EmailComplaint::factory()->for($email, 'email')->create();

        $this->assertModelExists($email);
        $this->assertModelExists($emailComplaint);
        $this->assertEquals($email->message_id, $emailComplaint->message_id, 'message_ids dont match for the two model instances.');
    }

    /** @test */
    public function modelRelationshipsAreWorking()
    {
        $email = Email::factory()->complained()->create();
        $emailComplaint = EmailComplaint::factory()->for($email, 'email')->create();

        $this->assertEquals($email->message_id, $emailComplaint->email->message_id, 'Belongs To object is different from the Parent object.');
        $this->assertEquals($email->complaint->message_id, $emailComplaint->message_id, 'Has One object is different from the Child object.');
    }
}
