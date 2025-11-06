<?php

namespace App\Http\Controllers\Api\V1;

// 1. Extend BaseApiController
use App\Http\Controllers\Api\V1\BaseApiController;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use Throwable; // 2. Import Throwable

/**
 * @OA\Tag(
 * name="Bookings",
 * description="Endpoints for managing user bookings"
 * )
 */
class BookingController extends BaseApiController // 3. Extend BaseApiController
{
    /**
     * @OA\Get(
     * path="/api/v1/my-bookings",
     * summary="Get a list of the authenticated user's bookings",
     * tags={"Bookings"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="A list of the user's bookings.",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Bookings retrieved successfully."),
     * @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/BookingResource"))
     * )
     * ),
     * @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request)
    {
        try { // 4. Add try...catch block
            $user = $request->user();
            $bookings = $user->bookings()->with('classModel.partner')->get();
            
            // 5. Use sendSuccess for consistent response
            return $this->sendSuccess(BookingResource::collection($bookings), 'Bookings retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve bookings.');
        }
    }

    /**
     * @OA\Post(
     * path="/api/v1/classes/{classId}/book",
     * summary="Book the authenticated user into a specific class",
     * tags={"Bookings"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="classId",
     * in="path",
     * required=true,
     * description="The ID of the class to book",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=201,
     * description="Booking created successfully.",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Booking confirmed successfully."),
     * @OA\Property(property="data", ref="#/components/schemas/BookingResource")
     * )
     * ),
     * @OA\Response(response=401, description="Unauthenticated"),
     * @OA\Response(response=404, description="Class not found"),
     * @OA\Response(response=409, description="Class is full or already booked")
     * )
     */
    public function store(Request $request, ClassModel $classModel)
    {
        try { // 6. Add try...catch block
            // Use firstOrCreate to prevent a user from double-booking the same class.
            $booking = Booking::firstOrCreate(
                [
                    'user_id' => $request->user()->id,
                    'class_model_id' => $classModel->id,
                ],
                [
                    'status' => 'confirmed'
                ]
            );

            // 7. Use sendSuccess for consistent response
            return $this->sendSuccess(new BookingResource($booking), 'Booking confirmed successfully.', 201);
            
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to book class.');
        }
    }
}