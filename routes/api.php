<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\MembershipPlanController;
use App\Http\Controllers\Api\V1\PartnerController;
use App\Http\Controllers\Api\V1\BookingController; 
use App\Http\Controllers\Api\V1\CompanyEmployeeController;
use App\Http\Controllers\Api\V1\CompanyInvoiceController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
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

        Route::get('/my-bookings', [BookingController::class, 'index']);
        Route::post('/classes/{classModel}/book', [BookingController::class, 'store']);

        // We protect it with auth:sanctum AND our new 'role' middleware.
        Route::middleware(['role:hr_admin'])
             ->prefix('company') // All routes will be /api/v1/company/...
             ->group(function () {
            
            /*
             * 4. This single line creates all the RESTful routes for managing users
             * within the Company Admin's own company.
             *
             * GET    /api/v1/company/users        -> index()
             * POST   /api/v1/company/users        -> store()
             * GET    /api/v1/company/users/{user} -> show()
             * PUT    /api/v1/company/users/{user} -> update()
             * DELETE /api/v1/company/users/{user} -> destroy()
             */
            Route::apiResource('users', CompanyEmployeeController::class);
            Route::get('invoice/download', [CompanyInvoiceController::class, 'download']);

        }); // End of Company Admin group


        // --- Super Admin Routes ---
        Route::middleware(['role:super_admin'])
             ->prefix('admin') 
             ->group(function () {
            
            Route::apiResource('membership-plans', MembershipPlanController::class)->except(['index']);

        }); // End of Super Admin group
    });
});