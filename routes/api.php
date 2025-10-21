<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\MembershipPlanController;
use App\Http\Controllers\Api\V1\PartnerController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// API Version 1 Group
Route::prefix('v1')->group(function () {
    // --- Public Routes ---
    Route::get('/membership-plans', [MembershipPlanController::class, 'index']);
    Route::get('/partners', [PartnerController::class, 'index']);
    Route::get('/partners/{partner}', [PartnerController::class, 'show']);
    Route::get('/partners/{partner}/classes', [PartnerController::class, 'classes']);

    // --- Authenticated Routes ---
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        // We will add the booking endpoints inside this group.
    });
});