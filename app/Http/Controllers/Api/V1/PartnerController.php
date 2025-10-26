<?php

namespace App\Http\Controllers\Api\V1; // Correct namespace

use App\Http\Resources\PartnerResource;
use App\Http\Resources\ClassModelResource; // Import ClassModelResource
use App\Models\Partner;
use Illuminate\Http\Request;
use Throwable; // Import Throwable

// Extend BaseApiController
class PartnerController extends BaseApiController
{
    public function index(Request $request)
    {
        try {
            $query = Partner::query()->where('status', 'approved');

            if ($request->filled('city')) {
                $query->where('city', $request->city);
            }
            if ($request->filled('type')) {
                $query->whereRaw('LOWER(type) LIKE ?', ['%' . strtolower($request->type) . '%']);
            }

            $partners = $query->paginate(15);

            // Use sendSuccess (handles pagination automatically)
            return $this->sendSuccess(PartnerResource::collection($partners), 'Partners retrieved successfully.');

        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve partners.');
        }
    }

    public function show(Partner $partner)
    {
         // Ensure only approved partners are publicly visible
         if ($partner->status !== 'approved') {
            return $this->sendError('Partner not found or not available.', [], 404);
        }
        // Use sendSuccess
        return $this->sendSuccess(new PartnerResource($partner), 'Partner details retrieved successfully.');
    }

     public function classes(Partner $partner)
     {
         try {
            // Ensure only approved partners' classes are shown
            if ($partner->status !== 'approved') {
                return $this->sendError('Partner not found or not available.', [], 404);
            }

            $classes = $partner->classes()
                                ->where('start_time', '>=', now()) // Only future classes
                                ->orderBy('start_time', 'asc')
                                ->get();

            // Use sendSuccess
            return $this->sendSuccess(ClassModelResource::collection($classes), 'Partner classes retrieved successfully.');

         } catch (Throwable $e) {
             return $this->handleException($e, 'Failed to retrieve partner classes.');
         }
     }
}