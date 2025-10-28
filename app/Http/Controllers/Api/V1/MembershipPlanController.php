<?php

namespace App\Http\Controllers\Api\V1; 

use App\Http\Resources\MembershipPlanResource;
use App\Models\MembershipPlan;
use Illuminate\Http\Request;
use Throwable; 
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MembershipPlanController extends BaseApiController
{
    /**
     * Display a listing of the resource.
     * PUBLIC ROUTE: GET /api/v1/membership-plans
     * (Your existing, correct method)
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
     * Store a newly created resource in storage.
     * ADMIN ROUTE: POST /api/v1/admin/membership-plans
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
     * Display the specified resource.
     * ADMIN ROUTE: GET /api/v1/admin/membership-plans/{plan}
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
     * Update the specified resource in storage.
     * ADMIN ROUTE: PUT /api/v1/admin/membership-plans/{plan}
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
     * Remove the specified resource from storage.
     * ADMIN ROUTE: DELETE /api/v1/admin/membership-plans/{plan}
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