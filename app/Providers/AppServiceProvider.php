<?php

namespace App\Providers;

use App\Services\CategoryService;
use App\Services\TaskService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->singleton(CategoryService::class, function ($app) {
            return new CategoryService();
        });
        $this->app->singleton(TaskService::class, function ($app) {
            return new TaskService();
        });
    }
}
