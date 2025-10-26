<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company; 
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate all incoming data, including the optional company code
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            // Rule: company_code is optional (nullable), must be a string,
            // and must exist in the 'code' column of the 'companies' table
            // where the company's 'status' is 'active'.
            'company_code' => ['nullable', 'string', 'exists:companies,code,status,active'],
        ]);

        $company = null;
        // If a valid company code was provided and validated...
        if (!empty($validatedData['company_code'])) {
            // Find the company again (we know it exists and is active).
            // Use first() as code is unique.
            $company = Company::where('code', $validatedData['company_code'])->first();
        }

        // Create the new user record
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            // Assign the company ID if a company was found, otherwise NULL
            'company_id' => $company?->id,
            // Assign the company's default plan if the company has one, otherwise NULL
            'membership_plan_id' => $company?->membership_plan_id,
            // Set role: 'employee' if joining a company, otherwise default (which is 'employee')
            'role' => $company ? 'employee' : 'employee',
        ]);

        // Fire the Registered event (used for things like sending verification emails)
        event(new Registered($user));

        // Log the user in immediately after registration
        Auth::login($user);

        // Redirect the user to the dashboard
        return redirect(route('dashboard', absolute: false));
    }
}