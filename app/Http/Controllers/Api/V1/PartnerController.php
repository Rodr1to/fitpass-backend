<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PartnerResource;
use App\Models\Partner;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function index(Request $request)
    {
        // Start the query, only getting partners with 'approved' status.
        $query = Partner::query()->where('status', 'approved');

        // Check if a 'city' filter was provided in the URL, e.g., ?city=Lima
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        // Check if a 'type' filter was provided, e.g., ?type=gym
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Get the results, but paginate them to 15 per page.
        $partners = $query->paginate(15);

        // Pass the data through our API Resource to format it.
        return PartnerResource::collection($partners);
    }
}