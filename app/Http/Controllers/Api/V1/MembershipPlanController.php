<?php

namespace App\Http\Controllers\Api\V1; 

use App\Http\Resources\MembershipPlanResource;
use App\Models\MembershipPlan;
use Illuminate\Http\Request;
use Throwable; 
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 * name="Membership Plans",
 * description="Endpoints for viewing and managing membership plans"
 * )
 */
class MembershipPlanController extends BaseApiController
{
    /**
     * @OA\Get(
     * path="/api/v1/membership-plans",
     * summary="Get a public list of all active membership plans",
     * tags={"Membership Plans"},
     * @OA\Response(response=200, description="List of active plans")
     * )
     */
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

    /**
     * @OA\Post(
     * path="/api/v1/admin/membership-plans",
     * summary="Create a new membership plan",
     * tags={"Super Admin - Plans"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"name", "price", "status"},
     * @OA\Property(property="name", type="string", example="Platinum Plan"),
     * @OA\Property(property="price", type="number", format="float", example=99.99),
     * @OA\Property(property="features", type="string", example="All gyms, All spas, Personal trainer"),
     * @OA\Property(property="status", type="string", enum={"active", "inactive"}, example="active")
     * )
     * ),
     * @OA\Response(response=201, description="Plan created successfully"),
     * @OA\Response(response=401, description="Unauthenticated"),
     * @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function store(Request $request)
    {
        try {
            // 1. Authorize this action (checks policy for 'create')
            $this->authorize('create', MembershipPlan::class);

            // 2. Validate the data --- CORRECTED VALIDATION ---
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255', 'unique:membership_plans'], // Correct table name
                'price' => ['required', 'numeric', 'min:0'],
                'features' => ['nullable', 'string'],
                'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
            ]);
            // --- END OF CORRECTION ---

            if ($validator->fails()) {
                // Use a 422 for validation errors
                return $this->sendError('Validation Failed.', $validator->errors()->toArray(), 422);
            }

            // 3. Create the plan
            $plan = MembershipPlan::create($validator->validated());

            // 4. Return the new plan
            return $this->sendSuccess(new MembershipPlanResource($plan), 'Membership plan created successfully.', 201);
        
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to create membership plan.');
        }
    }

    /**
     * @OA\Get(
     * path="/api/v1/admin/membership-plans/{id}",
     * summary="Get details of a single plan (admin only)",
     * tags={"Super Admin - Plans"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=200, description="Plan details"),
     * @OA\Response(response=404, description="Plan not found")
     * )
     */
    public function show(MembershipPlan $membershipPlan)
    {
        try {
            // 1. Authorize this action (checks policy for 'view')
            $this->authorize('view', $membershipPlan);

            // 2. Return the plan
            return $this->sendSuccess(new MembershipPlanResource($membershipPlan), 'Membership plan retrieved successfully.');

        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve membership plan.');
        }
    }

    /**
     * @OA\Put(
     * path="/api/v1/admin/membership-plans/{id}",
     * summary="Update an existing membership plan",
     * tags={"Super Admin - Plans"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="name", type="string"),
     * @OA\Property(property="price", type="number", format="float"),
     * @OA\Property(property="features", type="string"),
     * @OA\Property(property="status", type="string", enum={"active", "inactive"})
     * )
     * ),
     * @OA\Response(response=200, description="Plan updated successfully")
     * )
     */
    public function update(Request $request, MembershipPlan $membershipPlan)
    {
        try {
            // 1. Authorize this action
            $this->authorize('update', $membershipPlan);

            // 2. Validate the data (This section was already correct)
            $validator = Validator::make($request->all(), [
                'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('membership_plans')->ignore($membershipPlan->id)],
                'price' => ['sometimes', 'required', 'numeric', 'min:0'],
                'features' => ['sometimes', 'nullable', 'string'],
                'status' => ['sometimes', 'required', 'string', Rule::in(['active', 'inactive'])],
            ]);
            
            if ($validator->fails()) {
                return $this->sendError('Validation Failed.', $validator->errors()->toArray(), 422);
            }

            // 3. Update the plan
            $membershipPlan->update($validator->validated());

            // 4. Return the updated plan
            return $this->sendSuccess(new MembershipPlanResource($membershipPlan), 'Membership plan updated successfully.');

        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to update membership plan.');
        }
    }

    /**
     * @OA\Delete(
     * path="/api/v1/admin/membership-plans/{id}",
     * summary="Delete a membership plan",
     * tags={"Super Admin - Plans"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=204, description="Plan deleted successfully")
     * )
     */
    public function destroy(MembershipPlan $membershipPlan)
    {
        try {
            // 1. Authorize this action
            $this->authorize('delete', $membershipPlan);

            // 2. Delete the plan (this will be a soft delete if you updated the model)
            $membershipPlan->delete();

            // 3. Return a 204 No Content response
            return $this->sendSuccess(null, 'Membership plan deleted successfully.', 204);

        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to delete membership plan.');
        }
    }
}