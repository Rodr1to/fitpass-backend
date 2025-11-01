<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseApiController; 
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

/**
 * @OA\Tag(
 *     name="Company Admin - Employees",
 *     description="Endpoints for HR Admins to manage employees within their company"
 * )
 */
class CompanyEmployeeController extends BaseApiController
{
    public function index(Request $request)
    {
        try {
            $admin = $request->user();
            $employees = User::where('company_id', $admin->company_id)
                ->where('id', '!=', $admin->id)
                ->paginate(15);
            
            return $this->sendSuccess(UserResource::collection($employees), 'Employees retrieved successfully.');
        } catch (\Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve employees.');
        }
    }

    public function store(Request $request)
    {
        try {
            $admin = $request->user();
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:'.User::class,
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'role' => 'nullable|in:employee,hr_admin',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'company_id' => $admin->company_id,
                'role' => $validated['role'] ?? 'employee',
                'membership_plan_id' => $admin->company?->membership_plan_id,
            ]);

            return $this->sendSuccess(new UserResource($user), 'Employee created successfully.', 201);
        } catch (\Throwable $e) {
            return $this->handleException($e, 'Failed to create employee.');
        }
    }

    public function show(Request $request, User $user)
    {
        try {
            $this->authorize('view', $user);
            return $this->sendSuccess(new UserResource($user), 'Employee retrieved successfully.');
        } catch (\Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve employee.');
        }
    }

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
            
            if ($request->filled('password')) {
                $validated['password'] = Hash::make($validated['password']);
            }

            $user->update($validated);
            return $this->sendSuccess(new UserResource($user), 'Employee updated successfully.');
        } catch (\Throwable $e) {
            return $this->handleException($e, 'Failed to update employee.');
        }
    }

    public function destroy(User $user)
    {
        try {
            $this->authorize('delete', $user);
            $user->delete();
            return $this->sendSuccess(null, 'Employee deleted successfully.');
        } catch (\Throwable $e) {
            return $this->handleException($e, 'Failed to delete employee.');
        }
    }
}