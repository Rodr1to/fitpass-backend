<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\ClassModel;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $bookings = $user->bookings()->with('classModel.partner')->get();
        return BookingResource::collection($bookings);
    }

    /**
     * Store a newly created resource in storage.
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