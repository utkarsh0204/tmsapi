<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, false),
            'description' => $this->faker->sentence(),
        ];
    }

    public function todo(): static
    {
        return $this->state(fn(array $attr) => [
            'name' => 'To Do',
            'description' => 'Task to be started',
            'position' => 0,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn(array $attr) => [
            'name' => 'In Progress',
            'description' => 'Tasks currently in progresss',
            'position' => 1,
        ]);
    }

    public function done(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'Done',
            'description' => 'Completed tasks',
            'position' => 2,
        ]);
    }
}
