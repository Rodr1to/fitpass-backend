<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA; 

/**
 * @OA\Schema(
 * schema="UserResource",
 * title="User Resource",
 * description="User model representation",
 * @OA\Property(property="id", type="integer", example=1),
 * @OA\Property(property="name", type="string", example="HR Admin"),
 * @OA\Property(property="email", type="string", format="email", example="hr@fitpass.com"),
 * @OA\Property(property="role", type="string", enum={"employee", "hr_admin", "super_admin"}, example="hr_admin"),
 * @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true, example="null"),
 * @OA\Property(property="company_id", type="integer", nullable=true, example=1),
 * @OA\Property(property="membership_plan_id", type="integer", nullable=true, example=3),
 * @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-26T11:04:40.000000Z"),
 * @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-26T11:04:40.000000Z")
 * )
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // This structure matches the @OA\Schema above
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'email_verified_at' => $this->email_verified_at,
            'company_id' => $this->company_id,
            'membership_plan_id' => $this->membership_plan_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}