<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $todoCategory = Category::factory()->todo()->create();
        $inProgressCategory = Category::factory()->inProgress()->create();
        $doneCategory = Category::factory()->done()->create();

        Task::factory()->count(5)->create(['category_id' => $todoCategory->id]);
        Task::factory()->count(3)->create(['category_id' => $inProgressCategory->id]);
        Task::factory()->count(7)->create([
            'category_id' => $doneCategory->id,
            'status' => true,
        ]);
    }
}
