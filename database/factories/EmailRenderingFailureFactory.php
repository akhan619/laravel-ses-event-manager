<?php

namespace Akhan619\LaravelSesEventManager\Database\Factories;

use Akhan619\LaravelSesEventManager\App\Models\EmailRenderingFailure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Akhan619\LaravelSesEventManager\App\Models\EmailRenderingFailure>
 */
class EmailRenderingFailureFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EmailRenderingFailure::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'message_id'            =>  $this->faker->uuid(),
            'template_name'         =>  'MyTemplate',
            'error_message'         =>  "Attribute 'attributeName' is not present in the rendering data.",
            'failed_at'             =>  now(),
        ];
    }
}
