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
        // ... (your existing index method is perfect, leave it as is)
        $query = Partner::query()->where('status', 'approved');
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }
        if ($request->filled('type')) {
            $query->where('type', 'like', '%' . $request->type . '%');
        }
        $partners = $query->paginate(15);
        return PartnerResource::collection($partners);
    }

    /**
     * Display the specified resource.
     */
    public function show(Partner $partner) // 👈 FILL IN THIS METHOD
    {
        // Because of route model binding, Laravel automatically gives us the correct Partner.
        // We just need to pass it through our API Resource to format it.
        return new PartnerResource($partner);
    }
}