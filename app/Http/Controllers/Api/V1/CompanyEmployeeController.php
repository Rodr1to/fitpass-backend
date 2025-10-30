<?php

namespace App\Http\Controllers\Api\V1;

// --- 1. EXTEND YOUR BASEAPICONTROLLER ---
use App\Http\Controllers\Api\V1\BaseApiController; 
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 * name="Company Admin - Employees",
 * description="Endpoints for HR Admins to manage employees within their company"
 * )
 */
class CompanyEmployeeController extends BaseApiController // <-- 2. EXTEND THE RIGHT CONTROLLER
{
    /**
     * @OA\Get(...)
     */
    public function index(Request $request)
    {
        try {
            $admin = $request->user();

            // --- 3. PRESERVE YOUR BUSINESS LOGIC & ADD PAGINATION ---
            // We keep your logic to exclude the admin from the list,
            // but use paginate() for a proper API response.
            $employees = User::where('company_id', $admin->company_id)
                ->where('id', '!=', $admin->id)
                ->paginate(15);
            
            // --- 4. USE USERRESOURCE AND SENDSUCCESS ---
            // This formats the collection and sends a consistent, wrapped response.
            return $this->sendSuccess(UserResource::collection($employees), 'Employees retrieved successfully.');

        } catch (\Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve employees.');
        }
    }

    /**
     * @OA\Post(...)
     */
    public function store(Request $request)
    {
        try {
            $admin = $request->user();

            // --- 5. USE $request->validate() FOR CLEANER CODE ---
            // This automatically throws an exception on failure, which handleException will catch.
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:'.User::class,
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'role' => 'nullable|in:employee,hr_admin', // Optional: Allow creating other admins
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'company_id' => $admin->company_id, // Assign to the admin's company
                'role' => $validated['role'] ?? 'employee', // Default to 'employee'
                // --- 6. PRESERVE YOUR BUSINESS LOGIC ---
                // Automatically assign the company's default plan (if it has one).
                'membership_plan_id' => $admin->company?->membership_plan_id,
            ]);

            // --- 7. USE USERRESOURCE AND SENDSUCCESS ---
            // This formats the single user model and sends a consistent, wrapped response.
            return $this->sendSuccess(new UserResource($user), 'Employee created successfully.', 201);

        } catch (\Throwable $e) {
            return $this->handleException($e, 'Failed to create employee.');
        }
    }

    /**
     * @OA\Get(...)
     */
    public function show(Request $request, User $user)
    {
        try {
            $this->authorize('view', $user);
            return $this->sendSuccess(new UserResource($user), 'Employee retrieved successfully.');
        } catch (\Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve employee.');
        }
    }

    /**
     * @OA\Put(...)
     */
    public function update(Request $request, User $user)
    {
        try {
            $this->authorize('update', $user);

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|string|email|max:255|unique:users,email,'.$user->id,
                'password' => 'sometimes|required|confirmed|'.Rules\Password::defaults(),
                'membership_plan_id' => 'sometimes|nullable|integer|exists:membership_plans,id',
            ]);
            
            // Only update password if it was provided
            if ($request->filled('password')) {
                $validated['password'] = Hash::make($validated['password']);
            }

            $user->update($validated);

            return $this->sendSuccess(new UserResource($user), 'Employee updated successfully.');
        } catch (\Throwable $e) {
            return $this->handleException($e, 'Failed to update employee.');
        }
    }

    /**
     * @OA\Delete(...)
     */
    public function destroy(User $user)
    {
        try {
            $this->authorize('delete', $user);
            $user->delete();
            
            // --- 8. USE SENDSUCCESS FOR CONSISTENCY (INSTEAD OF 204) ---
            return $this->sendSuccess(null, 'Employee deleted successfully.');
        } catch (\Throwable $e) {
            return $this->handleException($e, 'Failed to delete employee.');
        }
    }
}