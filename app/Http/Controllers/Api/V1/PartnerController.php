<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClassModelResource;
use App\Http\Resources\PartnerResource;
use App\Models\Partner;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function index(Request $request)
    {
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

    public function show(Partner $partner)
    {

        return new PartnerResource($partner);
    }


    public function classes(Partner $partner)
    {
        $classes = $partner->classes()->where('start_time', '>=', now())->get();
        return ClassModelResource::collection($classes);
    }
}