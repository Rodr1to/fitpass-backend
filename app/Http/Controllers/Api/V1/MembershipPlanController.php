<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MembershipPlanResource;
use App\Models\MembershipPlan;
use Illuminate\Http\Request;

class MembershipPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 1. Get only the 'active' plans from the database.
        $plans = MembershipPlan::where('status', 'active')->get();

        // 2. Pass the data through our API Resource to format it as JSON.
        return MembershipPlanResource::collection($plans);
    }
}