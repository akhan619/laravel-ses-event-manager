<?php

namespace Akhan619\LaravelSesEventManager\Database\Factories;

use Akhan619\LaravelSesEventManager\App\Models\EmailBounce;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Akhan619\LaravelSesEventManager\App\Models\EmailBounce>
 */
class EmailBounceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EmailBounce::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'message_id'         => $this->faker->uuid(),
            'bounce_type'        => $this->faker->randomElement(['Permanent', 'Transient']),
            'bounce_sub_type'    => $this->faker->randomElement(['General', 'NoEmail', 'Suppressed', 'MailboxFull', 'MessageTooLarge']),
            'feedback_id'        => $this->faker->uuid(),
            'bounced_at'         => now(),
        ];
    }

    /**
     * Add dsn data.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withDsn()
    {
        return $this->state(function (array $attributes) {
            return [
                'action'            => 'failed',
                'status'            => '5.1.1',
                'diagnostic_code'   => 'smtp; 550 5.1.1 user unknown',
                'reporting_mta'     => 'dsn; mta.example.com',
            ];
        });
    }
}
