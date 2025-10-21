<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Partner; // Import the Partner model

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClassModel>
 */
class ClassModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = $this->faker->dateTimeBetween('+1 day', '+1 week');
        $endTime = (clone $startTime)->modify('+1 hour');

        return [
            'partner_id' => Partner::factory(), // Associate with a Partner
            'name' => $this->faker->randomElement(['Morning Yoga', 'HIIT Blast', 'Spin Cycle', 'Advanced CrossFit', 'Zumba Party']),
            'description' => $this->faker->paragraph(3),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'capacity' => $this->faker->numberBetween(10, 30),
        ];
    }
}
