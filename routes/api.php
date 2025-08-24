<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::apiResource('categories', CategoryController::class);
Route::apiResource('tasks', TaskController::class);
