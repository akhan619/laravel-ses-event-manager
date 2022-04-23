<?php

namespace Akhan619\LaravelSesEventManager\Database\Factories;

use Akhan619\LaravelSesEventManager\App\Models\EmailSend;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Akhan619\LaravelSesEventManager\App\Models\EmailSend>
 */
class EmailSendFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EmailSend::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'message_id'    =>  $this->faker->uuid(),
            'sent_at'       =>  now(),
        ];
    }
}
