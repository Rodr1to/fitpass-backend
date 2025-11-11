<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\MembershipPlanController;
use App\Http\Controllers\Api\V1\PartnerController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\CompanyEmployeeController;
use App\Http\Controllers\Api\V1\CompanyInvoiceController;
use App\Http\Controllers\Api\V1\CheckinController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\Company\StatsController as CompanyStatsController;
use App\Http\Controllers\Api\V1\Admin\StatsController as AdminStatsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
*/

// API Version 1 Group
Route::prefix('v1')->group(function () {

    // --- Public Routes ---
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/membership-plans', [MembershipPlanController::class, 'index']);
    Route::get('/partners', [PartnerController::class, 'index']);
    Route::get('/partners/{partner}', [PartnerController::class, 'show']);
    Route::get('/partners/{partner}/classes', [PartnerController::class, 'classes']);
    Route::post('/login', [AuthController::class, 'login']);


    // --- Authenticated Routes ---
    Route::middleware('auth:sanctum')->group(function () {

        /**
         * @OA\Get(
         * path="/api/v1/user",
         * summary="Get the authenticated user's details",
         * tags={"Authentication"},
         * security={{"bearerAuth":{}}},
         * @OA\Response(
         * response=200,
         * description="User details returned successfully.",
         * @OA\JsonContent(
         * @OA\Property(property="success", type="boolean", example=true),
         * @OA\Property(property="message", type="string", example="User details retrieved successfully."),
         * @OA\Property(property="data", ref="#/components/schemas/UserResource")
         * )
         * ),
         * @OA\Response(response=401, description="Unauthenticated")
         * )
         */
        Route::get('/user', function (Request $request) {
            // Return as a Resource for a consistent JSON response
            return (new \App\Http\Resources\UserResource($request->user()))
                ->additional([
                    'success' => true,
                    'message' => 'User details retrieved successfully.'
                ]);
        });

        // Logout Route
        Route::post('/logout', [AuthController::class, 'logout']);

        // Booking Routes
        Route::get('/my-bookings', [BookingController::class, 'index']);
        Route::post('/classes/{classModel}/book', [BookingController::class, 'store']);

        // Checkin Routes
        Route::post('/checkin', [CheckinController::class, 'store']);

        // --- Company Admin Routes ---
        Route::middleware(['role:hr_admin'])
            ->prefix('company') // All routes will be /api/v1/company/...
            ->name('company.') // Add route names for clarity
            ->group(function () {
            
            Route::apiResource('users', CompanyEmployeeController::class);
            
            /**
             * @OA\Get(
             * path="/api/v1/company/invoice/download",
             * summary="[Company Admin] Download a PDF invoice",
             * tags={"Company Admin - Invoicing"},
             * security={{"bearerAuth":{}}},
             * @OA\Response(
             * response=200,
             * description="PDF file download.",
             * @OA\MediaType(mediaType="application/pdf")
             * ),
             * @OA\Response(response=401, description="Unauthenticated"),
             * @OA\Response(response=403, description="Forbidden")
             * )
             */
            Route::get('invoice/download', [CompanyInvoiceController::class, 'download']);

            // --- Company Analytics Routes ---
            /**
             * @OA\Get(
             * path="/api/v1/company/stats/usage-summary",
             * summary="[Company Admin] Get a usage summary for their company",
             * tags={"Company Admin - Analytics"},
             * security={{"bearerAuth":{}}},
             * @OA\Response(response=200, description="Usage summary data"),
             * @OA\Response(response=403, description="Forbidden")
             * )
             */
            Route::get('stats/usage-summary', [CompanyStatsController::class, 'usageSummary']);

            /**
             * @OA\Get(
             * path="/api/v1/company/stats/checkins-by-partner",
             * summary="[Company Admin] Get check-in statistics by partner",
             * tags={"Company Admin - Analytics"},
             * security={{"bearerAuth":{}}},
             * @OA\Response(response=200, description="Partner check-in data"),
             * @OA\Response(response=403, description="Forbidden")
             * )
             */
            Route::get('stats/checkins-by-partner', [CompanyStatsController::class, 'checkinsByPartner']);

        }); // End of Company Admin group


        // --- Super Admin Routes ---
        Route::middleware(['role:super_admin'])
            ->prefix('admin') // All routes will be /api/v1/admin/...
            ->name('admin.') // Add route names for clarity
            ->group(function () {
            
            Route::apiResource('membership-plans', MembershipPlanController::class)->except(['index']);
            Route::apiResource('partners', PartnerController::class)->except(['index', 'show']);

            // --- Super Admin Analytics Routes ---
            /**
             * @OA\Get(
             * path="/api/v1/admin/stats/platform-overview",
             * summary="[Super Admin] Get a high-level platform overview",
             * tags={"Super Admin - Analytics"},
             * security={{"bearerAuth":{}}},
             * @OA\Response(response=200, description="Platform overview data"),
             * @OA\Response(response=403, description="Forbidden")
             * )
             */
            Route::get('stats/platform-overview', [AdminStatsController::class, 'platformOverview']);

            /**
             * @OA\Get(
             * path="/api/v1/admin/stats/company-activity",
             * summary="[Super Admin] Get usage summaries for all companies",
             * tags={"Super Admin - Analytics"},
             * security={{"bearerAuth":{}}},
             * @OA\Response(response=200, description="Company activity data"),
             * @OA\Response(response=403, description="Forbidden")
             * )
             */
            Route::get('stats/company-activity', [AdminStatsController::class, 'companyActivity']);

            /**
             * @OA\Get(
             * path="/api/v1/admin/stats/partner-performance",
             * summary="[Super Admin] Get performance data for all partners",
             * tags={"Super Admin - Analytics"},
             * security={{"bearerAuth":{}}},
             * @OA\Response(response=200, description="Partner performance data"),
             * @OA\Response(response=403, description="Forbidden")
             * )
             */
            Route::get('stats/partner-performance', [AdminStatsController::class, 'partnerPerformance']);

        }); // End of Super Admin group
    }); // End of auth:sanctum group
});