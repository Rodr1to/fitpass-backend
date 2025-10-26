<?php

namespace App\Http\Controllers\Api\V1; 

use App\Http\Resources\MembershipPlanResource;
use App\Models\MembershipPlan;
use Illuminate\Http\Request;
use Throwable; 

class MembershipPlanController extends BaseApiController
{
    public function index()
    {
        try {
            $plans = MembershipPlan::where('status', 'active')->get();
            // Use sendSuccess
            return $this->sendSuccess(MembershipPlanResource::collection($plans), 'Membership plans retrieved successfully.');
        } catch (Throwable $e) {
            // Use handleException for errors
            return $this->handleException($e, 'Failed to retrieve membership plans.');
        }
    }
}