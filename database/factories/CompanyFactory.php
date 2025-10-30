<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // --- THIS IS THE FIX ---
            'name' => $this->faker->company(), // Generates a random company name

            'contact_email' => $this->faker->unique()->safeEmail(), // generates random unique email

            'code' => strtoupper(Str::random(8)),
        ];
    }
}
