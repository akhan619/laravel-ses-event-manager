<?php

namespace Akhan619\LaravelSesEventManager\Database\Factories;

use Akhan619\LaravelSesEventManager\App\Models\Email;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Akhan619\LaravelSesEventManager\App\Models\Email>
 */
class EmailFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Email::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'message_id'    => $this->faker->uuid(),
            'email'         => $this->faker->unique()->safeEmail(),
            'name'          => $this->faker->name(),
        ];
    }

    /**
     * Indicate that the email was sent.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function sent()
    {
        return $this->state(function (array $attributes) {
            return [
                'has_send'       => true,
            ];
        });
    }

    /**
     * Indicate that the email was delivered.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function delivered()
    {
        return $this->state(function (array $attributes) {
            return [
                'has_send'       => true,
                'has_delivery'   => true,
            ];
        });
    }

    /**
     * Indicate that the email was opened.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function opened()
    {
        return $this->state(function (array $attributes) {
            return [
                'has_send'          => true,
                'has_delivery'      => true,
                'has_open'          => true,
            ];
        });
    }

    /**
     * Indicate that the email was clicked.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function clicked()
    {
        return $this->state(function (array $attributes) {
            return [
                'has_send'          => true,
                'has_delivery'      => true,
                'has_open'          => true,
                'has_click'         => true,
            ];
        });
    }

    /**
     * Indicate that the email failed to render.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function renderingFailed()
    {
        return $this->state(function (array $attributes) {
            return [
                'has_send'                  => true,
                'has_rendering_failure'     => true,
            ];
        });
    }

    /**
     * Indicate that the email was rejected.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function rejected()
    {
        return $this->state(function (array $attributes) {
            return [
                'has_reject'                  => true,
            ];
        });
    }

    /**
     * Indicate that the email was bounced.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function bounced()
    {
        return $this->state(function (array $attributes) {
            return [
                'has_send'          => true,
                'has_bounce'        => true,
            ];
        });
    }

    /**
     * Indicate that the email was complained against.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function complained()
    {
        return $this->state(function (array $attributes) {
            return [
                'has_send'              => true,
                'has_delivery'          => true,
                'has_complaint'         => true,
            ];
        });
    }

    /**
     * Indicate that the email was delayed.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function delayed()
    {
        return $this->state(function (array $attributes) {
            return [
                'has_send'                    => true,
                'has_delivery_delay'          => true,
            ];
        });
    }

    /**
     * Indicate that the email was subscribed.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function subscribed()
    {
        return $this->state(function (array $attributes) {
            return [
                'has_send'                  => true,
                'has_delivery'              => true,
                'has_subscription'          => true,
            ];
        });
    }
}
