<?php

namespace App\Http\Controllers\Api\V1;

// BaseApiController for consistent responses
use App\Http\Controllers\Api\V1\BaseApiController;
use Illuminate\Http\Request;
use App\Models\Checkin;
use App\Models\Partner;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Throwable;

// Extend BaseApiController
class CheckinController extends BaseApiController
{
    /**
     * Store a newly created check-in in storage.
     * AUTHENTICATED ROUTE: POST /api/v1/checkin
     */
    public function store(Request $request)
    {
        try {
            // 1. Get the currently authenticated user
            $user = Auth::user();

            // 2. Validate the request - require a partner_id
            $validator = Validator::make($request->all(), [
                'partner_id' => ['required', 'integer', 'exists:partners,id'],
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Failed.', $validator->errors()->toArray(), 422);
            }

            $partnerId = $request->input('partner_id');

            // 3. Find the partner and ensure they are approved
            $partner = Partner::find($partnerId);

            // Double check partner exists (though 'exists' rule helps)
            if (!$partner) {
                 return $this->sendError('Partner not found.', [], 404);
            }
            // IMPORTANT: Ensure user can only check into APPROVED partners
            if ($partner->status !== 'approved') {
                 return $this->sendError('This partner location is currently not available for check-in.', [], 403); // Forbidden
            }

            // 4. (Optional) Add Business Logic Checks Here:
            //    - Does the user's plan allow check-ins?
            //    - Have they exceeded monthly check-in limits?
            //    - Have they checked in too recently at this or another location?
            //    Example:
            //    if (!$user->membershipPlan?->allows_checkin) {
            //        return $this->sendError('Your current plan does not allow check-ins.', [], 403);
            //    }
            //    $recentCheckin = Checkin::where('user_id', $user->id)
            //                            ->where('created_at', '>', now()->subMinutes(5))
            //                            ->exists();
            //    if ($recentCheckin) {
            //        return $this->sendError('You checked in too recently. Please wait a moment.', [], 429); // Too Many Requests
            //    }


            // 5. Create the check-in record
            $checkin = Checkin::create([
                'user_id' => $user->id,
                'partner_id' => $partnerId,
                // 'checkin_time' will default to now() based on migration
            ]);

            // 6. Return success response (consider using an API Resource if needed)
            return $this->sendSuccess($checkin->load('partner'), 'Check-in recorded successfully.', 201); // Load partner details for context

        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to record check-in.');
        }
    }

    // Other methods (index, show, update, destroy) are not typically needed for basic check-ins
    // but could be added later for history viewing or admin adjustments.
}