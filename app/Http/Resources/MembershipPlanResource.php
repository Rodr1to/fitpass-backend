<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 * schema="MembershipPlanResource",
 * title="Membership Plan Resource",
 * description="Represents a single membership plan available for users.",
 * @OA\Property(property="id", type="integer", example=1),
 * @OA\Property(property="name", type="string", example="Gold Plan"),
 * @OA\Property(property="price", type="string", example="49.99", description="The price formatted to two decimal places."),
 * @OA\Property(property="features", type="string", example="Access to all gyms, spa included, 1 personal trainer session per month.")
 * )
 */
class MembershipPlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => number_format($this->price, 2), // We format the price to always have 2 decimal places
            'features' => $this->features,
        ];
    }
}