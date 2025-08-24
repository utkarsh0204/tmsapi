<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    private CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input("per_page", 10);
            $page = $request->input("page", 1);
            $paginator = $this->categoryService->getAllCategoriesWithTasks($page, $perPage);
            return $this->successResponse([
                "page" => $page,
                "per_page" => $perPage,
                "last_page" => $paginator->lastPage(),
                "total" => $paginator->total(),
                "categories" => $paginator->items(),
            ], "Categories Fetched Successfully");
        } catch (\Exception $ex) {
            Log::error('Failed to fetching categories: ' . $ex->__toString());
            return $this->errorResponse("Error In Fetching Categories");
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:128|unique:categories,name',
                'description' => 'nullable|string|max:512',
                'position' => 'nullable|integer|min:0',
            ]);
            $category = $this->categoryService->createCategory($validatedData);
            return $this->successResponse($category, "Category Created Successfully!!", Response::HTTP_CREATED);
        } catch (ValidationException $ex) {
            return $this->errorResponse("validation failed", $ex->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $ex) {
            return $this->errorResponse("Error In Creating Categories");
        }
    }

    public function show(Category $category): JsonResponse
    {
        try {
            $categoryWithTasks = $this->categoryService->getCategoryWithTasks($category->id);
            return $this->successResponse($categoryWithTasks, "Category Found!!");
        } catch (\Exception $ex) {
            Log::error("Error In Fetching Category " . $ex->__toString());
            return $this->errorResponse("Failed to fetch category");
        }
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'name' => 'sometimes|required|string|max:128|unique:categories,name,' . $category->id,
                'description' => 'nullable|string|max:512',
                'position' => 'nullable|integer|min:0',
            ]);
            $updatedCat = $this->categoryService->updateCategory($category, $validatedData);
            return $this->successResponse($updatedCat, "Category Updated Successfully");
        } catch (ValidationException $ex) {
            return $this->errorResponse("validation failed", $ex->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $ex) {
            Log::error("Error In Updating Category: " . $ex->__toString());
            return $this->errorResponse("Error In Updating Category");
        }
    }

    public function destroy(Category $category): JsonResponse
    {
        try {
            $this->categoryService->deleteCategory($category);
            return $this->successResponse(null, "Category Deleted Successfull!!");
        } catch (\Exception $ex) {
            Log::error("Error In Deleting Category: " . $ex->__toString());
            return $this->errorResponse("Error In Deleting Category");
        }
    }
}
