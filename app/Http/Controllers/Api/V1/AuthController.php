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

// Extend BaseApiController
class AuthController extends BaseApiController
{
    /**
     * Handle an incoming authentication request (API Login).
     * PUBLIC ROUTE: POST /api/v1/login
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
                'user' => new $user // Use API Resource
            ], 'Login successful.');

        } catch (ValidationException $e) {
            // Return validation errors specifically
            return $this->sendError('Login failed.', $e->errors(), 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Login failed due to server error.');
        }
    }

    /**
     * Log the user out (Invalidate the token).
     * AUTHENTICATED ROUTE: POST /api/v1/logout
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
