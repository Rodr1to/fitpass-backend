<?php

namespace App\Http\Controllers\Api\V1; 

use App\Http\Resources\PartnerResource;
use App\Http\Resources\ClassModelResource; 
use App\Models\Partner;
use Illuminate\Http\Request;
use Throwable; 
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 * name="Partners",
 * description="Endpoints for viewing and managing partners (gyms, spas, etc.)"
 * )
 */
class PartnerController extends BaseApiController
{
    /**
     * @OA\Get(
     * path="/api/v1/partners",
     * summary="Get a public list of approved partners",
     * tags={"Partners"},
     * @OA\Parameter(name="city", in="query", required=false, @OA\Schema(type="string", example="Lima")),
     * @OA\Parameter(name="type", in="query", required=false, @OA\Schema(type="string", enum={"gym", "spa", "club"})),
     * @OA\Response(response=200, description="List of approved partners")
     * )
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
     * @OA\Get(
     * path="/api/v1/partners/{id}",
     * summary="Get details for a single partner",
     * tags={"Partners"},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=200, description="Partner details"),
     * @OA\Response(response=404, description="Partner not found")
     * )
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
     * @OA\Get(
     * path="/api/v1/partners/{id}/classes",
     * summary="Get a list of all classes offered by a single partner",
     * tags={"Partners"},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=200, description="List of classes"),
     * @OA\Response(response=404, description="Partner not found")
     * )
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
     * @OA\Post(
     * path="/api/v1/admin/partners",
     * summary="Create a new partner",
     * tags={"Super Admin - Partners"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"name", "type", "city", "address", "latitude", "longitude", "contact_email"},
     * @OA\Property(property="name", type="string", example="FitZone Gym"),
     * @OA\Property(property="type", type="string", enum={"gym", "spa", "club"}),
     * @OA\Property(property="description", type="string", nullable=true),
     * @OA\Property(property="city", type="string", example="Lima"),
     * @OA\Property(property="address", type="string"),
     * @OA\Property(property="latitude", type="number", format="float"),
     * @OA\Property(property="longitude", type="number", format="float"),
     * @OA\Property(property="contact_email", type="string", format="email")
     * )
     * ),
     * @OA\Response(response=201, description="Partner created successfully")
     * )
     */
    public function store(Request $request)
    {
        try {
            // 1. Authorize this action (checks PartnerPolicy for 'create')
            $this->authorize('create', Partner::class);

            // 2. Validate the data (including phone_number)
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255', 'unique:partners'],
                'address' => ['required', 'string', 'max:255'],
                'city' => ['required', 'string', 'max:100'],
                'type' => ['required', 'string', Rule::in(['gym', 'spa', 'club'])], // Adjust types as needed
                'description' => ['nullable', 'string'],
                'status' => ['required', 'string', Rule::in(['pending', 'approved', 'rejected'])], 
                'latitude' => ['nullable', 'numeric', 'between:-90,90'],
                'longitude' => ['nullable', 'numeric', 'between:-180,180'],
                'phone_number' => ['nullable', 'string', 'max:20'], // Correctly included
                'cover_image_url' => ['nullable', 'string', 'url', 'max:255'], // Added basic URL validation
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
     * @OA\Put(
     * path="/api/v1/admin/partners/{id}",
     * summary="Update an existing partner",
     * tags={"Super Admin - Partners"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="name", type="string"),
     * @OA\Property(property="status", type="string", enum={"pending", "approved", "rejected"})
     * )
     * ),
     * @OA\Response(response=200, description="Partner updated successfully")
     * )
     */
    public function update(Request $request, Partner $partner)
    {
        try {
            // 1. Authorize this action
            $this->authorize('update', $partner);

            // 2. Validate the data (including phone_number)
            $validator = Validator::make($request->all(), [
                'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('partners')->ignore($partner->id)],
                'address' => ['sometimes', 'required', 'string', 'max:255'],
                'city' => ['sometimes', 'required', 'string', 'max:100'],
                'type' => ['sometimes', 'required', 'string', Rule::in(['gym', 'spa', 'club'])],
                'description' => ['sometimes', 'nullable', 'string'],
                'status' => ['sometimes', 'required', 'string', Rule::in(['pending', 'approved', 'rejected'])],
                'latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
                'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
                'phone_number' => ['sometimes', 'nullable', 'string', 'max:20'], // Correctly included
                'cover_image_url' => ['sometimes', 'nullable', 'string', 'url', 'max:255'], // Added basic URL validation
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
     * @OA\Delete(
     * path="/api/v1/admin/partners/{id}",
     * summary="Delete a partner",
     * tags={"Super Admin - Partners"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=204, description="Partner deleted successfully")
     * )
     */
    public function destroy(Partner $partner)
    {
        try {
            // 1. Authorize this action
            $this->authorize('delete', $partner);

            // 2. Delete the partner (Uses SoftDeletes if enabled in model)
            $partner->delete();

            // 3. Return a 204 No Content response
            return $this->sendSuccess(null, 'Partner deleted successfully.', 204);

        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to delete partner.');
        }
    }
}