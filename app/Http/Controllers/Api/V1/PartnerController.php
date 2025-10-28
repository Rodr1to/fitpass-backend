<?php

namespace App\Http\Controllers\Api\V1; 

use App\Http\Resources\PartnerResource;
use App\Http\Resources\ClassModelResource; 
use App\Models\Partner;
use Illuminate\Http\Request;
use Throwable; 
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

// Extend BaseApiController
class PartnerController extends BaseApiController
{
    /**
     * Display a listing of the resource.
     * PUBLIC ROUTE: GET /api/v1/partners
     */
    public function index(Request $request)
    {
        try {
            $query = Partner::query()->where('status', 'approved');

            if ($request->filled('city')) {
                $query->where('city', $request->city);
            }
            if ($request->filled('type')) {
                // Using WHERE clause for exact match or LIKE for partial
                // Using LOWER ensures case-insensitivity
                 $query->whereRaw('LOWER(type) LIKE ?', ['%' . strtolower($request->type) . '%']);
            }

            // Using paginate from your code
            $partners = $query->latest()->paginate(15); // Added latest() for consistent ordering

            // Use sendSuccess (handles pagination automatically)
            return $this->sendSuccess(PartnerResource::collection($partners), 'Partners retrieved successfully.');

        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve partners.');
        }
    }

    /**
     * Display the specified resource.
     * PUBLIC ROUTE: GET /api/v1/partners/{partner}
     */
    public function show(Partner $partner)
    {
         // Ensure only approved partners are publicly visible
         if ($partner->status !== 'approved') {
             return $this->sendError('Partner not found or not available.', [], 404);
         }
         // Use sendSuccess
         return $this->sendSuccess(new PartnerResource($partner), 'Partner details retrieved successfully.');
    }

    /**
     * Display classes for a specific partner.
     * PUBLIC ROUTE: GET /api/v1/partners/{partner}/classes
     */
     public function classes(Partner $partner)
     {
         try {
             // Ensure only approved partners' classes are shown
             if ($partner->status !== 'approved') {
                 return $this->sendError('Partner not found or not available.', [], 404);
             }

             // Query for future classes, ordered
             $classes = $partner->classes() 
                              ->where('start_time', '>=', now()) 
                              ->orderBy('start_time', 'asc')
                              ->get();

             // Use sendSuccess
             return $this->sendSuccess(ClassModelResource::collection($classes), 'Partner classes retrieved successfully.');

         } catch (Throwable $e) {
              return $this->handleException($e, 'Failed to retrieve partner classes.');
         }
     }

    // --- NEW ADMIN METHODS START HERE ---

    /**
     * Store a newly created resource in storage.
     * ADMIN ROUTE: POST /api/v1/admin/partners
     */
    public function store(Request $request)
    {
        try {
            // 1. Authorize this action (checks PartnerPolicy for 'create')
            $this->authorize('create', Partner::class);

            // 2. Validate the data (using fields from your partners table migration)
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255', 'unique:partners'],
                'address' => ['required', 'string', 'max:255'],
                'city' => ['required', 'string', 'max:100'],
                'type' => ['required', 'string', Rule::in(['gym', 'spa', 'club'])], // Adjust types as needed
                'description' => ['nullable', 'string'],
                'status' => ['required', 'string', Rule::in(['pending', 'approved', 'rejected'])], 
                'latitude' => ['nullable', 'numeric', 'between:-90,90'],
                'longitude' => ['nullable', 'numeric', 'between:-180,180'],
                'phone_number' => ['nullable', 'string', 'max:20'],
                // Add validation for 'cover_image_url' if needed
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Failed.', $validator->errors()->toArray(), 422);
            }

            // 3. Create the partner
            $partner = Partner::create($validator->validated());

            // 4. Return the new partner using your resource
            return $this->sendSuccess(new PartnerResource($partner), 'Partner created successfully.', 201);
        
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to create partner.');
        }
    }


    /**
     * Update the specified resource in storage.
     * ADMIN ROUTE: PUT /api/v1/admin/partners/{partner}
     */
    public function update(Request $request, Partner $partner)
    {
        try {
            // 1. Authorize this action
            $this->authorize('update', $partner);

            // 2. Validate the data
            $validator = Validator::make($request->all(), [
                'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('partners')->ignore($partner->id)],
                'address' => ['sometimes', 'required', 'string', 'max:255'],
                'city' => ['sometimes', 'required', 'string', 'max:100'],
                'type' => ['sometimes', 'required', 'string', Rule::in(['gym', 'spa', 'club'])],
                'description' => ['sometimes', 'nullable', 'string'],
                'status' => ['sometimes', 'required', 'string', Rule::in(['pending', 'approved', 'rejected'])],
                'latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
                'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
                'phone_number' => ['sometimes', 'nullable', 'string', 'max:20'],
            ]);
            
            if ($validator->fails()) {
                return $this->sendError('Validation Failed.', $validator->errors()->toArray(), 422);
            }

            // 3. Update the partner
            $partner->update($validator->validated());

            // 4. Return the updated partner using your resource
            return $this->sendSuccess(new PartnerResource($partner), 'Partner updated successfully.');

        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to update partner.');
        }
    }

    /**
     * Remove the specified resource from storage.
     * ADMIN ROUTE: DELETE /api/v1/admin/partners/{partner}
     */
    public function destroy(Partner $partner)
    {
        try {
            // 1. Authorize this action
            $this->authorize('delete', $partner);

            // 2. Delete the partner (Consider adding SoftDeletes to Partner model if needed)
            $partner->delete();

            // 3. Return a 204 No Content response
            return $this->sendSuccess(null, 'Partner deleted successfully.', 204);

        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to delete partner.');
        }
    }
}