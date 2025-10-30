<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use App\Models\User; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use OpenApi\Annotations as OA;


/**
 * @OA\Tag(
 * name="Company Admin - Employees",
 * description="Endpoints for HR Admins to manage employees within their company"
 * )
 */
class CompanyEmployeeController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/v1/company/users",
     * summary="Get a list of employees for the admin's company",
     * tags={"Company Admin - Employees"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="A paginated list of employees.",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string"),
     * @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/UserResource")),
     * @OA\Property(property="links", type="object", description="Pagination links"),
     * @OA\Property(property="meta", type="object", description="Pagination metadata")
     * )
     * ),
     * @OA\Response(response=401, description="Unauthenticated"),
     * @OA\Response(response=403, description="Forbidden")
     * )
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
     * @OA\Post(
     * path="/api/v1/company/users",
     * summary="Create a new employee within the admin's company",
     * tags={"Company Admin - Employees"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"name", "email", "password", "password_confirmation"},
     * @OA\Property(property="name", type="string", example="John Doe"),
     * @OA\Property(property="email", type="string", format="email", example="john.doe@company.com"),
     * @OA\Property(property="password", type="string", format="password", example="password123"),
     * @OA\Property(property="password_confirmation", type="string", format="password", example="password123"),
     * @OA\Property(property="role", type="string", enum={"employee", "hr_admin"}, example="employee", description="Role within the company. Defaults to 'employee'.")
     * )
     * ),
     * @OA\Response(response=201, description="Employee created successfully.", @OA\JsonContent(ref="#/components/schemas/UserResource")),
     * @OA\Response(response=422, description="Validation error")
     * )
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
     * @OA\Get(
     * path="/api/v1/company/users/{id}",
     * summary="Get details of a single employee within the admin's company",
     * tags={"Company Admin - Employees"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=200, description="Employee details.", @OA\JsonContent(ref="#/components/schemas/UserResource")),
     * @OA\Response(response=404, description="Employee not found or not in company")
     * )
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
     * @OA\Put(
     * path="/api/v1/company/users/{id}",
     * summary="Update an existing employee's details",
     * tags={"Company Admin - Employees"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\RequestBody(
     * @OA\JsonContent(
     * @OA\Property(property="name", type="string", example="Johnathan Doe"),
     * @OA\Property(property="email", type="string", format="email", example="john.doe.new@company.com"),
     * @OA\Property(property="role", type="string", enum={"employee", "hr_admin"}),
     * @OA\Property(property="membership_plan_id", type="integer", nullable=true, example=2)
     * )
     * ),
     * @OA\Response(response=200, description="Employee updated successfully.", @OA\JsonContent(ref="#/components/schemas/UserResource")),
     * @OA\Response(response=404, description="Employee not found")
     * )
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
     * @OA\Delete(
     * path="/api/v1/company/users/{id}",
     * summary="Delete an employee from the company",
     * tags={"Company Admin - Employees"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=200, description="Employee deleted successfully"),
     * @OA\Response(response=404, description="Employee not found")
     * )
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
