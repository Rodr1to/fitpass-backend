<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use App\Models\User; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;

class CompanyEmployeeController extends Controller
{
    /**
     * Display a listing of the company's employees.
     *
     * This method is called by the:
     * GET /api/v1/company/users route
     */
    public function index()
    {
        // 1. Get the authenticated user (who we know is a 'company_admin'
        //    thanks to our RoleMiddleware)
        $companyAdmin = Auth::user();

        // 2. Get the company ID from the admin
        $companyId = $companyAdmin->company_id;

        // 3. Find all users who belong to that same company.
        //    We also add a filter to exclude the admin themselves from the list.
        $employees = User::where('company_id', $companyId)
                         ->where('id', '!=', $companyAdmin->id)
                         ->get();

        // 4. Return the list of employees as a JSON response
        return response()->json($employees);
    }

    /**
     * Store a newly created employee in storage.
     *
     * This method is called by the:
     * POST /api/v1/company/users route
     */
    public function store(Request $request)
    {
        // 1. Get the authenticated Company Admin
        $companyAdmin = Auth::user();

        // 2. Validate the incoming data
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // 3. Create the new user and assign them to the admin's company
        $employee = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            
            // --- This is the key logic ---
            'company_id' => $companyAdmin->company_id,
            // Automatically assign the company's default plan (if they have one)
            'membership_plan_id' => $companyAdmin->company?->membership_plan_id, 
            'role' => 'employee', // Default role for new users
        ]);

        // 4. Return the new employee and a 201 (Created) status
        return response()->json($employee, 201);
    }

    /**
     * Display the specified employee.
     *
     * This method is called by the:
     * GET /api/v1/company/users/{user} route
     */
    public function show(User $user)
    {
        // 1. Authorize the action.
        // This will automatically call our UserPolicy's 'view' method.
        // If it returns false, Laravel will automatically send a 403 Forbidden response.
        $this->authorize('view', $user);

        // 2. If authorization passes, return the user.
        return response()->json($user);
    }

    /**
     * Update the specified employee in storage.
     *
     * This method is called by the:
     * PUT /api/v1/company/users/{user} route
     */
    public function update(Request $request, User $user)
    {
        // 1. Authorize the action (checks if admin and user are in the same company)
        $this->authorize('update', $user);

        // 2. Validate the incoming data
        // 'sometimes' means only validate if the field is present
        // We also make sure the email is unique, *except* for the current user's email
        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', 'unique:'.User::class . ',email,' . $user->id],
            'password' => ['sometimes', 'required', 'confirmed', Rules\Password::defaults()],
            'membership_plan_id' => ['sometimes', 'nullable', 'integer', 'exists:membership_plans,id'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        // 3. Update only the fields that were provided
        $user->fill($request->only(['name', 'email', 'membership_plan_id']));

        // Only update password if it was provided
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        // 4. Return the updated user
        return response()->json($user);
    }

    /**
     * Remove the specified employee from storage..
     *
     * This method is called by the:
     * DELETE /api/v1/company/users/{user} route
     */
    public function destroy(User $user)
    {
        // 1. Authorize the action (checks if admin and user are in the same company)
        $this->authorize('delete', $user);

        // 2. Delete the user
        $user->delete();

        // 3. Return a 204 No Content response (standard for successful deletion)
        return response()->json(null, 204);
    }
}
