<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 * schema="CheckinResource",
 * title="Check-in Resource",
 * description="Represents a single user check-in event.",
 * @OA\Property(property="checkin_id", type="integer", example=1),
 * @OA\Property(property="checkin_time", type="string", format="date-time", example="2025-10-30T15:00:00.000000Z"),
 * @OA\Property(property="user", ref="#/components/schemas/UserResource"),
 * @OA\Property(property="partner", ref="#/components/schemas/PartnerResource")
 * )
 */
class CheckinResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'checkin_id' => $this->id,
            'checkin_time' => $this->created_at,
            
            // Conditionally include the user and partner details if they were loaded
            'user' => new UserResource($this->whenLoaded('user')),
            'partner' => new PartnerResource($this->whenLoaded('partner')),
        ];
    }
}