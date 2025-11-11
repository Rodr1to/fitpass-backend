<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\UserResource;
use OpenApi\Annotations as OA; 
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;

// Extend BaseApiController
class AuthController extends BaseApiController
{
    /**
     * @OA\Post(
     * path="/api/v1/register",
     * summary="Register a new user",
     * tags={"Authentication"},
     * @OA\RequestBody(
     * required=true,
     * description="User registration details",
     * @OA\JsonContent(
     * required={"name", "email", "password", "password_confirmation"},
     * @OA\Property(property="name", type="string", example="John Doe"),
     * @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     * @OA\Property(property="password", type="string", format="password", example="password123"),
     * @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="User registered successfully.",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="User registered successfully."),
     * @OA\Property(property="data", type="object",
     * @OA\Property(property="token", type="string", example="2|Abc...xyz"),
     * @OA\Property(property="user", ref="#/components/schemas/UserResource")
     * )
     * )
     * ),
     * @OA\Response(response=422, description="Validation error")
     * )
     */
    public function register(Request $request)
    {
        try {
            // 1. Validate the incoming data
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'device_name' => 'required|string', // Device name is needed to create the token
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Failed.', $validator->errors()->toArray(), 422);
            }
            
            // 2. Create the new user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'employee', // Default role for public registration
            ]);

            // 3. Create the API token
            $token = $user->createToken($request->device_name)->plainTextToken;

            // 4. Return the new user and token
            return $this->sendSuccess([
                'token' => $token,
                'user' => new UserResource($user)
            ], 'User registered successfully.', 201); // 201 = Created

        } catch (Throwable $e) {
            return $this->handleException($e, 'Registration failed due to a server error.');
        }
    }

    /** // 
     * @OA\Post(
     * path="/api/v1/login",
     * summary="Authenticate user and return API token",
     * tags={"Authentication"},
     * @OA\RequestBody(
     * required=true,
     * description="User credentials",
     * @OA\JsonContent(
     * required={"email","password","device_name"},
     * @OA\Property(property="email", type="string", format="email", example="hr@fitpass.com"),
     * @OA\Property(property="password", type="string", format="password", example="password"),
     * @OA\Property(property="device_name", type="string", example="Chrome Browser", description="Identifier for the client device/application")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Login successful",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Login successful."),
     * @OA\Property(property="data", type="object",
     * @OA\Property(property="token", type="string", example="1|Abcdefghijklmnopqrstuvwxyz123456"),
     * @OA\Property(property="user", ref="#/components/schemas/UserResource")
     * )
     * )
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation error or invalid credentials"
     * ),
     * @OA\Response(
     * response=500,
     * description="Server error"
     * )
     * )
     */
    public function login(Request $request)
    {
        try {
            // 1. Validate the request data
            // Frontend MUST send 'device_name' to name the token
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'email' => 'required|string|email',
                'password' => 'required|string',
                'device_name' => 'required|string',
            ]);

             if ($validator->fails()) {
                 return $this->sendError('Validation Failed.', $validator->errors()->toArray(), 422);
             }

            // 2. Attempt to find the user
            $user = User::where('email', $request->email)->first();

            // 3. Check password and create token
            if (! $user || ! Hash::check($request->password, $user->password)) {
                // Use Laravel's standard validation exception for failed login
                throw ValidationException::withMessages([
                    'email' => [__('auth.failed')], // Standard Laravel auth failed message
                ]);
            }

            // 4. (Optional Checks): Add checks here if needed
            // e.g., if ($user->status !== 'active') { throw new \Exception('Account inactive.'); }

            // 5. Create the API token
            $token = $user->createToken($request->device_name)->plainTextToken;

            // 6. Return the token and user info (using UserResource for consistency)
            return $this->sendSuccess([
                'token' => $token,
                'user' => new UserResource($user) // Use API Resource
            ], 'Login successful.');

        } catch (ValidationException $e) {
            // Return validation errors specifically
            return $this->sendError('Login failed.', $e->errors(), 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Login failed due to server error.');
        }
    }

    /** // 
     * @OA\Post(
     * path="/api/v1/logout",
     * summary="Logout user (invalidate current token)",
     * tags={"Authentication"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Successfully logged out",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Successfully logged out."),
     * @OA\Property(property="data", type="object", nullable=true, example=null)
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated"
     * )
     * )
     */
    public function logout(Request $request)
    {
        try {
            // Get the authenticated user via the token
            $user = $request->user();

            // Revoke the specific token that was used to make this request
            $user->currentAccessToken()->delete();

            return $this->sendSuccess(null, 'Successfully logged out.');

        } catch (Throwable $e) {
            // Use handleException for consistent error handling
            return $this->handleException($e, 'Logout failed.');
        }
    }
}
