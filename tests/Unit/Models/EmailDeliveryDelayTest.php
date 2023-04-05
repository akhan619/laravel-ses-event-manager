<?php

namespace Akhan619\LaravelSesEventManager\Tests\Unit\Models;

use Akhan619\LaravelSesEventManager\App\Models\Email;
use Akhan619\LaravelSesEventManager\App\Models\EmailDeliveryDelay;
use Akhan619\LaravelSesEventManager\Tests\UnitTestCase;
use Illuminate\Support\Facades\Schema;

class EmailDeliveryDelayTest extends UnitTestCase
{
    protected array $tables;

    protected function setUp(): void
    {
        parent::setUp();

        // Import the tables from the migration
        $this->tables = [];
        $this->tables[] = include __DIR__.'/../../../database/migrations/create_emails_table.php.stub';
        $this->tables[] = include __DIR__.'/../../../database/migrations/create_email_delivery_delays_table.php.stub';
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
    public function emailDeliveryDelaysTableIsCreatedSuccessfully()
    {
        $this->assertTrue(Schema::hasTable(config('laravel-ses-event-manager.database_name_prefix').'_email_delivery_delays'));
    }

    /** @test */
    public function emailDeliveryDelayModelCanBeCreatedSuccessfully()
    {
        $email = Email::factory()->delayed()->create();
        $emailDeliveryDelay = EmailDeliveryDelay::factory()->for($email, 'email')->create();

        $this->assertModelExists($email);
        $this->assertModelExists($emailDeliveryDelay);
        $this->assertEquals($email->message_id, $emailDeliveryDelay->message_id, 'message_ids dont match for the two model instances.');
    }

    /** @test */
    public function modelRelationshipsAreWorking()
    {
        $email = Email::factory()->delayed()->create();
        $emailDeliveryDelay = EmailDeliveryDelay::factory()->for($email, 'email')->create();

        $this->assertEquals($email->message_id, $emailDeliveryDelay->email->message_id, 'Belongs To object is different from the Parent object.');
        $this->assertEquals($email->deliveryDelay->message_id, $emailDeliveryDelay->message_id, 'Has One object is different from the Child object.');
    }
}
