<?php

namespace App\Http\Controllers\Api\V1\Company;

use App\Http\Controllers\Api\V1\BaseApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Checkin;
use App\Models\Booking;
use OpenApi\Annotations as OA;

class StatsController extends BaseApiController
{
    /**
     * @OA\Get(
     * path="/api/v1/company/stats/usage-summary",
     * summary="Get usage summary for the admin's company",
     * tags={"Company Admin - Analytics"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="start_date", in="query", required=false, @OA\Schema(type="string", format="date", example="2025-10-01")),
     * @OA\Parameter(name="end_date", in="query", required=false, @OA\Schema(type="string", format="date", example="2025-10-31")),
     * @OA\Response(response=200, description="Successful operation"),
     * @OA\Response(response=401, description="Unauthenticated"),
     * @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function usageSummary(Request $request)
    {
        try {
            $request->validate([
                'start_date' => 'nullable|date|before_or_equal:end_date',
                'end_date' => 'nullable|date',
            ]);

            $admin = $request->user();
            $companyId = $admin->company_id;

            // Base query for users in the company
            $usersQuery = User::where('company_id', $companyId);

            // Base queries for activities, filtered by the company's users
            $checkinsQuery = Checkin::whereIn('user_id', $usersQuery->pluck('id'));
            $bookingsQuery = Booking::whereIn('user_id', $usersQuery->pluck('id'));

            // Apply date filters if provided
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $checkinsQuery->whereBetween('created_at', [$request->start_date, $request->end_date]);
                $bookingsQuery->whereBetween('created_at', [$request->start_date, $request->end_date]);
            }

            $stats = [
                'total_active_employees' => $usersQuery->count(),
                'total_checkins' => $checkinsQuery->count(),
                'total_bookings' => $bookingsQuery->count(),
                'breakdown_by_partner_type' => Checkin::query()
                    ->join('partners', 'checkins.partner_id', '=', 'partners.id')
                    ->whereIn('checkins.user_id', User::where('company_id', $companyId)->pluck('id'))
                    ->when($request->filled('start_date') && $request->filled('end_date'), function ($query) use ($request) {
                        $query->whereBetween('checkins.created_at', [$request->start_date, $request->end_date]);
                    })
                    ->select('partners.type', DB::raw('count(*) as count'))
                    ->groupBy('partners.type')
                    ->get()
                    ->pluck('count', 'type'),
            ];

            return $this->sendSuccess($stats, 'Usage summary retrieved successfully.');
        } catch (\Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve usage summary.');
        }
    }

    /**
     * @OA\Get(
     * path="/api/v1/company/stats/checkins-by-partner",
     * summary="Get check-in counts per partner for the admin's company",
     * tags={"Company Admin - Analytics"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(response=200, description="Successful operation"),
     * @OA\Response(response=401, description="Unauthenticated"),
     * @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function checkinsByPartner(Request $request)
    {
        try {
            $admin = $request->user();
            $companyId = $admin->company_id;

            $checkinsByPartner = DB::table('checkins')
                ->join('users', 'checkins.user_id', '=', 'users.id')
                ->join('partners', 'checkins.partner_id', '=', 'partners.id')
                ->where('users.company_id', $companyId)
                ->select('partners.name', 'partners.type', 'partners.city', DB::raw('COUNT(checkins.id) as checkin_count'))
                ->groupBy('partners.id', 'partners.name', 'partners.type', 'partners.city')
                ->orderByDesc('checkin_count')
                ->limit(20) // Limit to top 20 for performance
                ->get();

            return $this->sendSuccess($checkinsByPartner, 'Check-ins by partner retrieved successfully.');
        } catch (\Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve check-ins by partner.');
        }
    }
}