<?php

namespace Database\Factories;

use App\Models\DataType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DataType>
 */
class DataTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'slug' => $this->faker->slug(),
            'icon' => 'heroicon-o-document-text',
        ];
    }
}
