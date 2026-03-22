<?php

namespace Database\Factories;

use App\Models\DataType;
use App\Models\Node;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Node>
 */
class NodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'data' => [],
            'order' => 0,
            'data_type_id' => DataType::factory(),
        ];
    }
}
