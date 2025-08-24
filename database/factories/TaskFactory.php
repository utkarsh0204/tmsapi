<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'category_id' => Category::factory(),
            'priority' => $this->faker->randomElement(TaskPriority::getList()),
            'status' => $this->faker->boolean(25),
            'completion_date' => $this->faker->optional(0.5)->dateTimeBetween('now', '+30 days'),
        ];
    }
}
