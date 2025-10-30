<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\BaseApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Company;
use App\Models\Partner;
use App\Models\Checkin;
use App\Models\Booking;
use OpenApi\Annotations as OA;

class StatsController extends BaseApiController
{
    /**
     * @OA\Get(
     * path="/api/v1/admin/stats/platform-overview",
     * summary="Get a high-level overview of the entire platform",
     * tags={"Super Admin - Analytics"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(response=200, description="Successful operation"),
     * @OA\Response(response=401, description="Unauthenticated"),
     * @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function platformOverview(Request $request)
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'total_companies' => Company::count(),
                'total_approved_partners' => Partner::where('status', 'approved')->count(),
                'total_checkins_last_30_days' => Checkin::where('created_at', '>=', now()->subDays(30))->count(),
                'total_bookings_last_30_days' => Booking::where('created_at', '>=', now()->subDays(30))->count(),
            ];

            return $this->sendSuccess($stats, 'Platform overview retrieved successfully.');
        } catch (\Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve platform overview.');
        }
    }

    /**
     * @OA\Get(
     * path="/api/v1/admin/stats/company-activity",
     * summary="Get a summary of activity for each company",
     * tags={"Super Admin - Analytics"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(response=200, description="Successful operation"),
     * @OA\Response(response=401, description="Unauthenticated"),
     * @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function companyActivity(Request $request)
    {
        try {
            $companyActivity = Company::withCount(['users', 'checkins', 'bookings'])
                ->orderByDesc('checkins_count')
                ->get();

            return $this->sendSuccess($companyActivity, 'Company activity retrieved successfully.');
        } catch (\Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve company activity.');
        }
    }

    /**
     * @OA\Get(
     * path="/api/v1/admin/stats/partner-performance",
     * summary="Get performance stats for all partners",
     * tags={"Super Admin - Analytics"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(response=200, description="Successful operation"),
     * @OA\Response(response=401, description="Unauthenticated"),
     * @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function partnerPerformance(Request $request)
    {
        try {
            $partnerPerformance = Partner::where('status', 'approved')
                ->withCount(['checkins', 'classes'])
                ->orderByDesc('checkins_count')
                ->get();

            return $this->sendSuccess($partnerPerformance, 'Partner performance retrieved successfully.');
        } catch (\Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve partner performance.');
        }
    }
}