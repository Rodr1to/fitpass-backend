<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * Display a listing of the user's bookings.
     */
    public function index(Request $request)
    {
        //  authenticated user from the request.
        $user = $request->user();

        $bookings = $user->bookings()->with('classModel.partner')->get();

        return BookingResource::collection($bookings);
    }
}