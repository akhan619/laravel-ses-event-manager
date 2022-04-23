<?php

namespace Akhan619\LaravelSesEventManager\Database\Factories;

use Akhan619\LaravelSesEventManager\App\Models\EmailDeliveryDelay;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Akhan619\LaravelSesEventManager\App\Models\EmailDeliveryDelay>
 */
class EmailDeliveryDelayFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EmailDeliveryDelay::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'message_id'            =>  $this->faker->uuid(),
            'delay_type'            =>  'MailboxFull',
            'expiration_time'       =>  now()->addDays(3),
            'delayed_at'            =>  now(),
        ];
    }
}
