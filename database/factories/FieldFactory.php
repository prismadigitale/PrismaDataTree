<?php

namespace Database\Factories;

use App\Models\Field;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Field>
 */
class FieldFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'label' => $this->faker->word(),
            'type' => 'text',
            'options' => [],
            'validation_rules' => 'nullable',
        ];
    }
}
