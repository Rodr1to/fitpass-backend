<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Throwable;
use OpenApi\Annotations as OA;


/**
 * @OA\Info(
 * version="1.0.0",
 * title="FitPass HOPn API Documentation",
 * description="API Documentation for the FitPass HOPn Platform",
 * @OA\Contact(
 * email="your-email@example.com"
 * )
 * )
 *
 * @OA\Server(
 * url=L5_SWAGGER_CONST_HOST,
 * description="Main API Server"
 * )
 *
 * @OA\SecurityScheme(
 * securityScheme="bearerAuth",
 * type="http",
 * scheme="bearer",
 * bearerFormat="JWT",
 * description="Enter token in format (Bearer <token>)"
 * )
 */
class BaseApiController extends Controller
{
    /**
     * Send a successful JSON response with a guaranteed consistent structure.
     */
    protected function sendSuccess(mixed $data, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        // Case 1: The data is a paginated resource collection.
        // This is the most complex case, and we handle it explicitly.
        if ($data instanceof \Illuminate\Http\Resources\Json\ResourceCollection && $data->resource instanceof LengthAwarePaginator) {
            
            // We extract the paginated data and merge it into our consistent response structure.
            $paginatedData = $data->response()->getData(true);

            $responsePayload = [
                'success' => true,
                'message' => $message,
                'data'    => $paginatedData['data'],
                'links'   => $paginatedData['links'],
                'meta'    => $paginatedData['meta'],
            ];

            return response()->json($responsePayload, $statusCode);
        }

        // Case 2: For ALL other data types (single items, regular arrays, null),
        // we manually wrap them in a "data" key to ensure consistency. This is the fix.
        $responsePayload = [
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ];

        return response()->json($responsePayload, $statusCode);
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
        Log::error($exception->getMessage(), [
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);

        if ($exception instanceof ValidationException) {
            return $this->sendError($exception->getMessage(), $exception->errors(), 422);
        }

        if ($exception instanceof AuthorizationException) {
            return $this->sendError($exception->getMessage() ?: 'Forbidden.', [], 403);
        }

        if (app()->isProduction()) {
            return $this->sendError('An internal server error occurred.', [], $statusCode);
        }

        return $this->sendError($message, [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ], $statusCode);
    }
}