<?php

namespace Akhan619\LaravelSesEventManager\Database\Factories;

use Akhan619\LaravelSesEventManager\App\Models\EmailReject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Akhan619\LaravelSesEventManager\App\Models\EmailReject>
 */
class EmailRejectFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EmailReject::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'message_id'        =>  $this->faker->uuid(),
            'reason'            =>  'Bad content',
            'rejected_at'       =>  now(),
        ];
    }
}
