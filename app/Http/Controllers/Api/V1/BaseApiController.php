<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Throwable; // Import Throwable for exception handling

class BaseApiController extends Controller
{
    /**
     * Send a successful JSON response, handling pagination automatically.
     */
    protected function sendSuccess(mixed $data, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        // If data is already a Resource Collection (often from pagination)
        if ($data instanceof JsonResource && $data->resource instanceof LengthAwarePaginator) {
            return $data->additional($response)->response()->setStatusCode($statusCode);
        }
        // If data is a direct Paginator instance
        if ($data instanceof LengthAwarePaginator) {
             // Wrap in anonymous resource collection to add meta/links
            return JsonResource::collection($data)->additional($response)->response()->setStatusCode($statusCode);
        }
         // If data is a single Resource
        if ($data instanceof JsonResource) {
            return $data->additional($response)->response()->setStatusCode($statusCode);
        }

        // For simple arrays or objects
        $response['data'] = $data;
        return response()->json($response, $statusCode);
    }

    /**
     * Send an error JSON response.
     */
    protected function sendError(string $message, array $errors = [], int $statusCode = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Standardized way to handle exceptions within controllers.
     */
    protected function handleException(Throwable $exception, string $message = 'An error occurred.', int $statusCode = 500): JsonResponse
    {
        // Log the detailed exception for debugging
        \Log::error($exception->getMessage(), [
            'exception' => $exception,
            'trace' => $exception->getTraceAsString()
        ]);

        // For validation exceptions, return the specific errors
        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            return $this->sendError($exception->getMessage(), $exception->errors(), 422);
        }

        // For authorization exceptions
         if ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return $this->sendError($exception->getMessage() ?: 'Forbidden.', [], 403);
        }

        // For general errors in production, return a generic message
        if (app()->isProduction()) {
             return $this->sendError('Server Error.', [], $statusCode);
        }

        // In development, return more details
        return $this->sendError($message, [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ], $statusCode);
    }
}