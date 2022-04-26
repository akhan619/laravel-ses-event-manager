<?php

namespace Akhan619\LaravelSesEventManager\Database\Factories;

use Akhan619\LaravelSesEventManager\App\Models\EmailClick;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Akhan619\LaravelSesEventManager\App\Models\EmailClick>
 */
class EmailClickFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EmailClick::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'message_id'    => $this->faker->uuid(),
            'user_agent'    => $this->faker->userAgent(),
            'link'          => $this->faker->url().'/'.$this->faker->slug(),
            'clicked_at'    => now(),
        ];
    }

    /**
     * Add tags to the link.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function tagged()
    {
        return $this->state(function (array $attributes) {
            return [
                'link_tags' => [
                    $this->faker->word() => $this->faker->word(),
                ],
            ];
        });
    }
}
