<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
class PartnerFactory extends Factory {

    public function definition(): array {

        return [
            'name' => $this->faker->company,
            'type' => $this->faker->randomElement(['gym', 'spa', 'club']),
            'description' => $this->faker->paragraph,
            'city' => 'Lima',
            'address' => $this->faker->streetAddress,
            'latitude' => -12.0464,
            'longitude' => -77.0428,
            'status' => 'approved',
        ];
        
    }
}
