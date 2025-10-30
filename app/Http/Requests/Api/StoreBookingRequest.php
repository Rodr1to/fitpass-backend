<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Basic check: user must be logged in. More specific checks
     * (like plan allows booking) can be added via Policies.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     * For booking, the class ID comes from the route parameter.
     * The request body might be empty or contain optional fields.
     */
    public function rules(): array
    {
        return [
            // Example: Add validation if a request body field is expected
            // 'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}