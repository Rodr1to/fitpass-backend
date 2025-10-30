<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 * schema="PartnerResource",
 * title="Partner Resource",
 * description="Represents a partner gym, spa, or club.",
 * @OA\Property(property="partner_id", type="integer", example=1),
 * @OA\Property(property="name", type="string", example="Global Fitness"),
 * @OA\Property(property="type", type="string", enum={"gym", "spa", "club"}, example="gym"),
 * @OA\Property(property="city", type="string", example="Lima"),
 * @OA\Property(property="address", type="string", example="Av. Larco 123"),
 * @OA\Property(
 * property="location",
 * type="object",
 * @OA\Property(property="latitude", type="number", format="float", example=-12.1218),
 * @OA\Property(property="longitude", type="number", format="float", example=-77.0311)
 * ),
 * @OA\Property(property="status", type="string", enum={"pending", "approved", "rejected"}, example="approved")
 * )
 */
class PartnerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'partner_id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'city' => $this->city,
            'address' => $this->address,
            'location' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ],
            'status' => $this->status,
        ];
    }
}
