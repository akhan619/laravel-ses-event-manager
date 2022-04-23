<?php

namespace Akhan619\LaravelSesEventManager\Tests\Unit\Models;

use Akhan619\LaravelSesEventManager\App\Models\Email;
use Akhan619\LaravelSesEventManager\App\Models\EmailClick;
use Akhan619\LaravelSesEventManager\Tests\UnitTestCase;
use Illuminate\Support\Facades\Schema;

class EmailClickTest extends UnitTestCase
{
    protected array $tables;

    protected function setUp(): void
    {
        parent::setUp();

        // Import the tables from the migration
        $this->tables = [];    
        $this->tables[] = include __DIR__ . '/../../../database/migrations/create_emails_table.php.stub';
        $this->tables[] = include __DIR__ . '/../../../database/migrations/create_email_clicks_table.php.stub';
        
        foreach($this->tables as $table) 
        {
            $table->up();
        }
    }
    
    protected function tearDown(): void
    {
        foreach($this->tables as $table) 
        {
            $table->down();
        }
        
        parent::tearDown();
    }

    /** @test */
    function emailClicksTableIsCreatedSuccessfully()
    {
        $this->assertTrue(Schema::hasTable(config('laravel-ses-event-manager.database_name_prefix') . '_email_clicks'));
    }

    /** @test */
    function emailClickModelCanBeCreatedSuccessfully()
    {
        $email = Email::factory()->clicked()->create();
        $emailClick = EmailClick::factory()->tagged()->for($email, 'email')->create();
        
        $this->assertModelExists($email);
        $this->assertModelExists($emailClick);
        $this->assertEquals($email->message_id, $emailClick->message_id, 'message_ids dont match for the two model instances.');
    }

    /** @test */
    function modelRelationshipsAreWorking()
    {
        $email = Email::factory()->clicked()->create();
        $emailClick = EmailClick::factory()->for($email, 'email')->create();

        $this->assertEquals($email->message_id, $emailClick->email->message_id, 'Belongs To object is different from the Parent object.');
        $this->assertEquals($email->clicks->first()->message_id, $emailClick->message_id, 'Has One object is different from the Child object.');
    }
}