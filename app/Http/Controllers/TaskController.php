<?php

namespace App\Http\Controllers;

use App\Enums\TaskPriority;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TaskController extends Controller
{
    private TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:128',
                'description' => 'nullable|string|max:512',
                'category_id' => 'required|exists:categories,id',
                'priority' => ['required', Rule::enum(TaskPriority::class)],
                'position' => 'nullable|integer|min:0',
                'completion_date' => 'nullable|date|after_or_equal:today',
                'status' => 'nullable|boolean',
            ]);
            $task = $this->taskService->createTask($validatedData);
            return $this->successResponse($task, "Task Created Successfully!!", Response::HTTP_CREATED);
        } catch (ValidationException $ex) {
            return $this->errorResponse("validation failed", $ex->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $ex) {
            Log::error("Error In Creating Task: " . $ex->__toString());
            return $this->errorResponse("Error In Creating Task");
        }
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'title' => 'sometimes|required|string|max:128',
                'description' => 'nullable|string|max:512',
                'category_id' => 'sometimes|required|exists:categories,id',
                'priority' => ['required', Rule::enum(TaskPriority::class)],
                'position' => 'nullable|integer|min:0',
                'completion_date' => 'nullable|date',
                'status' => 'nullable|boolean',
            ]);
            $updatedTask = $this->taskService->updateTask($task, $validatedData);
            return $this->successResponse($updatedTask, "Task Updated Successfully!!");
        } catch (ValidationException $ex) {
            return $this->errorResponse("validation failed", $ex->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $ex) {
            Log::error("Error In Updating Task: " . $ex->__toString());
            return $this->errorResponse("Error In Updating Task");
        }
    }

    public function destroy(Task $task): JsonResponse
    {
        try {
            $this->taskService->deleteTask($task);
            return $this->successResponse(null, "Task Deleted Successfully!!");
        } catch (\Exception $ex) {
            Log::error("Error In Deleting Task: " . $ex->__toString());
            return $this->errorResponse("Error In Updating Task");
        }
    }
}
