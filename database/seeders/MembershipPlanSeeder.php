<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MembershipPlan; // Make sure this is imported

class MembershipPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            ['name' => 'Bronze', 'price' => 19.99, 'features' => 'Basic gym access.', 'status' => 'active'],
            ['name' => 'Silver', 'price' => 39.99, 'features' => 'Gym access + 2 classes.', 'status' => 'active'],
            ['name' => 'Gold', 'price' => 59.99, 'features' => 'All access + training.', 'status' => 'active'],
            ['name' => 'Club+', 'price' => 79.99, 'features' => 'All access + sports clubs.', 'status' => 'active'],
            ['name' => 'Digital', 'price' => 9.99, 'features' => 'Online content only.', 'status' => 'active'],
        ];
        
        foreach ($plans as $plan) {
            // Use firstOrCreate to prevent duplicate errors
            MembershipPlan::firstOrCreate(['name' => $plan['name']], $plan);
        }
    }
}