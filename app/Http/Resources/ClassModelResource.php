<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 * schema="ClassModelResource",
 * title="Class Model Resource",
 * description="Represents a single class or session.",
 * @OA\Property(property="class_id", type="integer", example=101),
 * @OA\Property(property="name", type="string", example="Morning Yoga"),
 * @OA\Property(property="description", type="string", example="A refreshing yoga session to start your day."),
 * @OA\Property(property="start_time", type="string", format="date-time", example="2025-11-05T09:00:00.000000Z"),
 * @OA\Property(property="end_time", type="string", format="date-time", example="2025-11-05T10:00:00.000000Z"),
 * @OA\Property(property="capacity", type="integer", example=20)
 * )
 */
class ClassModelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'class_id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'capacity' => $this->capacity,
        ];
    }
}