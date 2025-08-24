<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryService
{
    public function getAllCategoriesWithTasks($page = 1, $perPage = 10): LengthAwarePaginator
    {
        return Category::with("tasks")
            ->orderBy("position")
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function getCategoryWithTasks(int $id): Category
    {
        return Category::with("tasks")
            ->findOrFail($id);
    }

    public function createCategory(array $data): Category
    {
        if (!isset($data['position'])) {
            $data['position'] = Category::max('position') + 1;
        }

        $category = Category::create($data);
        $category->load('tasks');

        return $category;
    }

    public function updateCategory(Category $category, array $data): Category
    {
        if (isset($data['position']) && $data['position'] !== $category->position) {
            $this->setCategoryPosition($category, $data['position']);
        }
        $category->update($data);
        $category->load('tasks');

        return $category;
    }

    public function deleteCategory(Category $category): bool
    {
        $deletedPosition = $category->position;
        $deleted = $category->delete();
        if ($deleted) {
            $this->setCategoryPositionAfterDeletion($deletedPosition);
        }
        return $deleted;
    }

    private function setCategoryPosition(Category $category, int $newPosition): void
    {
        $oldPosition = $category->position;
        if ($newPosition > $oldPosition) {
            Category::whereBetween('position', [$oldPosition + 1, $newPosition])
                ->decrement('position');
        } else {
            Category::whereBetween('position', [$newPosition, $oldPosition - 1])
                ->increment('position');
        }
    }

    private function setCategoryPositionAfterDeletion(int $deletedPosition): void
    {
        Category::where('position', '>', $deletedPosition)
            ->decrement('position');
    }
}
