<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;


/**
 * @OA\Tag(
 * name="Bookings",
 * description="Endpoints for managing user bookings"
 * )
 */
class BookingController extends Controller
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
     * @OA\Property(property="message", type="string"),
     * @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/BookingResource"))
     * )
     * ),
     * @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $bookings = $user->bookings()->with('classModel.partner')->get();
        return BookingResource::collection($bookings);
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
     * @OA\Property(property="message", type="string"),
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

        return response()->json([
            'message' => 'Booking confirmed successfully.',
            'data' => new BookingResource($booking),
        ], 201); // 201 Created status code
    }
}