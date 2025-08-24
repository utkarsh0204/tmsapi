<?php

use Carbon\Carbon;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (NotFoundHttpException $ex, $request) {
            if ($request->is("api/*")) {
                Log::info("Resource Not Found: " . $ex->__toString());
                return response()->json([
                    "status" => "error",
                    "message" => "Resource Not Found!!",
                    "error" => null,
                    "timestamp" => Carbon::now()
                ], Response::HTTP_NOT_FOUND);
            }
        });

        $exceptions->render(function (MethodNotAllowedHttpException $ex, $request) {
            if ($request->is("api/*")) {
                Log::info("Resource Not Found: " . $ex->__toString());
                return response()->json([
                    "status" => "error",
                    "message" => $ex->getMessage(),
                    "error" => null,
                    "timestamp" => Carbon::now()
                ], Response::HTTP_METHOD_NOT_ALLOWED);
            }
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is("api/*")) {
                Log::info("Some Error Occured: " . $e->__toString());
                return response()->json([
                    "status" => "error",
                    "message" => "Some Error Occured",
                    "error" => null,
                    "timestamp" => Carbon::now()
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        });
    })->create();
