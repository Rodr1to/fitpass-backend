<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MembershipPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            ['name' => 'Bronze', 'price' => 19.99, 'features' => 'Basic gym access.'],
            ['name' => 'Silver', 'price' => 39.99, 'features' => 'Gym access + 2 classes.'],
            ['name' => 'Gold', 'price' => 59.99, 'features' => 'All access + training.'],
            ['name' => 'Club+', 'price' => 79.99, 'features' => 'All access + sports clubs.'],
            ['name' => 'Digital', 'price' => 9.99, 'features' => 'Online content only.'],
        ];
        foreach ($plans as $plan) {
            \App\Models\MembershipPlan::create($plan);
        }
    }
}
