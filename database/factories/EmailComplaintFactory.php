<?php

namespace Akhan619\LaravelSesEventManager\Database\Factories;

use Akhan619\LaravelSesEventManager\App\Models\EmailComplaint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Akhan619\LaravelSesEventManager\App\Models\EmailComplaint>
 */
class EmailComplaintFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EmailComplaint::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'message_id'        => $this->faker->uuid(),
            'feedback_id'       => $this->faker->uuid(),
            'complained_at'     => now(),
        ];
    }

    /**
     * Add feedback report data.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withFeedbackReport()
    {
        return $this->state(function (array $attributes) {
            return [
                'user_agent'                    => $this->faker->userAgent(),
                'complaint_feedback_type'       => 'abuse',
            ];
        });
    }
}
