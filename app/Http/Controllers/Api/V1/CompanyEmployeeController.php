<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Api\V1\BaseApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Resources\UserResource; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use OpenApi\Annotations as OA;
use Throwable; 

/**
 * @OA\Tag(
 * name="Company Admin - Employees",
 * description="Endpoints for HR Admins to manage employees within their company"
 * )
 */
class CompanyEmployeeController extends BaseApiController // 4. Make sure it extends BaseApiController
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
     * @OA\Property(property="message", type="string", example="Employees retrieved successfully."),
     * @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/UserResource")),
     * @OA\Property(property="links", type="object", 
     * @OA\Property(property="first", type="string", example="http://.../users?page=1"),
     * @OA\Property(property="last", type="string", example="http://.../users?page=5"),
     * @OA\Property(property="prev", type="string", nullable=true, example=null),
     * @OA\Property(property="next", type="string", nullable=true, example="http://.../users?page=2")
     * ),
     * @OA\Property(property="meta", type="object",
     * @OA\Property(property="current_page", type="integer", example=1),
     * @OA\Property(property="last_page", type="integer", example=5),
     * @OA\Property(property="per_page", type="integer", example=15),
     * @OA\Property(property="total", type="integer", example=75)
     * )
     * )
     * ),
     * @OA\Response(response=401, description="Unauthenticated"),
     * @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(Request $request)
    {
        try {
            $admin = $request->user();

            // We use paginate() for a proper and scalable API response
            $employees = User::where('company_id', $admin->company_id)
                ->where('id', '!=', $admin->id)
                ->paginate(15); // You can change 15 to your preferred number

            // We use UserResource and sendSuccess for a consistent response
            return $this->sendSuccess(UserResource::collection($employees), 'Employees retrieved successfully.');

        } catch (Throwable $e) {
            // We use handleException for centralized error handling
            return $this->handleException($e, 'Failed to retrieve employees.');
        }
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
     * @OA\Response(response=201, description="Employee created successfully.", @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Employee created successfully."),
     * @OA\Property(property="data", ref="#/components/schemas/UserResource")
     * )),
     * @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        try {
            $admin = $request->user();

            // We use validate() for cleaner code.
            // It will automatically throw a ValidationException that handleException will catch.
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:' . User::class,
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'role' => 'nullable|in:employee,hr_admin', // Optional: allow creating other admins
            ]);

            $employee = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'company_id' => $admin->company_id, // Assign to the admin's company
                'role' => $validated['role'] ?? 'employee', // Default to 'employee'
                'membership_plan_id' => $admin->company?->membership_plan_id, // Business logic preserved
            ]);

            // We use UserResource and sendSuccess for a consistent response
            return $this->sendSuccess(new UserResource($employee), 'Employee created successfully.', 201);

        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to create employee.');
        }
    }

    /**
     * @OA\Get(
     * path="/api/v1/company/users/{id}",
     * summary="Get details of a single employee within the admin's company",
     * tags={"Company Admin - Employees"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"), description="The ID of the user to retrieve"),
     * @OA\Response(response=200, description="Employee details.", @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Employee retrieved successfully."),
     * @OA\Property(property="data", ref="#/components/schemas/UserResource")
     * )),
     * @OA\Response(response=404, description="Employee not found or not in company")
     * )
     */
    public function show(Request $request, User $user) // 5. Inject Request for authorization
    {
        try {
            // Call the authorization policy
            $this->authorize('view', $user); 
            
            return $this->sendSuccess(new UserResource($user), 'Employee retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve employee.');
        }
    }

    /**
     * @OA\Put(
     * path="/api/v1/company/users/{id}",
     * summary="Update an existing employee's details",
     * tags={"Company Admin - Employees"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"), description="The ID of the user to update"),
     * @OA\RequestBody(
     * @OA\JsonContent(
     * @OA\Property(property="name", type="string", example="Johnathan Doe"),
     * @OA\Property(property="email", type="string", format="email", example="john.doe.new@company.com"),
     * @OA\Property(property="role", type="string", enum={"employee", "hr_admin"}),
     * @OA\Property(property="membership_plan_id", type="integer", nullable=true, example=2)
     * )
     * ),
     * @OA\Response(response=200, description="Employee updated successfully.", @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Employee updated successfully."),
     * @OA\Property(property="data", ref="#/components/schemas/UserResource")
     * )),
     * @OA\Response(response=404, description="Employee not found")
     * )
     */
    public function update(Request $request, User $user)
    {
        try {
            $this->authorize('update', $user);

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
                'password' => 'sometimes|required|confirmed|' . Rules\Password::defaults(),
                'membership_plan_id' => 'sometimes|nullable|integer|exists:membership_plans,id',
            ]);
            
            // Only update password if it was provided
            if ($request->filled('password')) {
                $validated['password'] = Hash::make($validated['password']);
            }

            $user->update($validated);

            return $this->sendSuccess(new UserResource($user), 'Employee updated successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to update employee.');
        }
    }

    /**
     * @OA\Delete(
     * path="/api/v1/company/users/{id}",
     * summary="Delete an employee from the company",
     * tags={"Company Admin - Employees"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"), description="The ID of the user to delete"),
     * @OA\Response(response=200, description="Employee deleted successfully", @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Employee deleted successfully."),
     * @OA\Property(property="data", type="object", nullable=true, example=null)
     * )),
     * @OA\Response(response=404, description="Employee not found")
     * )
     */
    public function destroy(User $user)
    {
        try {
            $this->authorize('delete', $user);
            $user->delete();
            
            // We use sendSuccess for consistency instead of 204
            return $this->sendSuccess(null, 'Employee deleted successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to delete employee.');
        }
    }
}