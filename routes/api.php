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
    Route::get('/membership-plans', [MembershipPlanController::class, 'index']);

    Route::get('/partners', [PartnerController::class, 'index']);
    Route::get('/partners/{partner}', [PartnerController::class, 'show']);
    Route::get('/partners/{partner}/classes', [PartnerController::class, 'classes']);
});

// This default route is for Sanctum authentication.
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});