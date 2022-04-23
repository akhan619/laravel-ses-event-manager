<?php

namespace Akhan619\LaravelSesEventManager\Database\Factories;

use Akhan619\LaravelSesEventManager\App\Models\EmailSubscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Akhan619\LaravelSesEventManager\App\Models\EmailSubscription>
 */
class EmailSubscriptionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EmailSubscription::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'message_id'                        =>  $this->faker->uuid(),
            'contact_list'                      =>  'SystemMonitor-Canary',
            'new_topic_preferences'             =>  [],
            'old_topic_preferences'             =>  [],
            'notified_at'                       =>  now(),
        ];
    }
}
