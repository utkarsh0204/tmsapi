<?php

namespace App\Services;

use App\Models\Task;

class TaskService
{
    public function createTask(array $data): Task
    {
        if (!isset($data['position'])) {
            $data['position'] = Task::where('category_id', $data['category_id'])
                ->max('position') + 1;
        }
        $task = Task::create($data);
        $task->load('category');
        return $task;
    }

    public function updateTask(Task $task, array $data): Task
    {
        $oldCategoryId = $task->category_id;
        $newCategoryId = $data['category_id'] ?? $oldCategoryId;

        if ($newCategoryId !== $oldCategoryId) {
            $this->setCategoryPositionChange($task, $newCategoryId, $data['position'] ?? null);
        } elseif (isset($data['position']) && $data['position'] !== $task->position) {
            $this->setTasksPositionInCategory($task, $data['position']);
        }
        $task->update($data);
        $task->load('category');

        return $task;
    }


    private function setCategoryPositionChange(Task $task, int $newCategoryId, ?int $newPosition = null): void
    {
        $oldCategoryId = $task->category_id;
        $oldPosition = $task->position;
        Task::where('category_id', $oldCategoryId)
            ->where('position', '>', $oldPosition)
            ->decrement('position');

        if (is_null($newPosition)) {
            $newPosition = Task::where('category_id', $newCategoryId)->max('position') + 1;
        } else {
            Task::where('category_id', $newCategoryId)
                ->where('position', '>=', $newPosition)
                ->increment('position');
        }
    }

    public function deleteTask(Task $task): bool
    {
        $categoryId = $task->category_id;
        $deletedPosition = $task->position;
        $deleted = $task->delete();
        if ($deleted) {
            $this->setTasksPositionAfterDeletion($categoryId, $deletedPosition);
        }
        return $deleted;
    }

    private function setTasksPositionInCategory(Task $task, int $newPosition): void
    {
        $oldPosition = $task->position;
        $categoryId = $task->category_id;
        if ($newPosition > $oldPosition) {
            Task::where('category_id', $categoryId)
                ->whereBetween('position', [$oldPosition + 1, $newPosition])
                ->decrement('position');
        } else {
            Task::where('category_id', $categoryId)
                ->whereBetween('position', [$newPosition, $oldPosition - 1])
                ->increment('position');
        }
    }

    private function setTasksPositionAfterDeletion(int $categoryId, int $deletedPosition): void
    {
        Task::where('category_id', $categoryId)
            ->where('position', '>', $deletedPosition)
            ->decrement('position');
    }
}
