<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

abstract class Controller
{
    public function successResponse(mixed $data = null, string|null $message = null, int $status = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            "status" => "success",
            "message" => $message,
            "data" => $data,
            "timestamp" => Carbon::now()
        ], $status);
    }

    public function errorResponse(string|null $message, mixed $error = null, int $status = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return response()->json([
            "status" => "error",
            "message" => $message,
            "error" => $error,
            "timestamp" => Carbon::now()
        ], $status);
    }
}
