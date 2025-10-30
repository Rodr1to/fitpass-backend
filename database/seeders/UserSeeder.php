<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User; 
use App\Models\Company; 
use Illuminate\Support\Facades\Hash; 

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Find the company we just created in CompanySeeder
        $company = Company::where('name', 'Test Company Inc.')->first();

        // 2. Create Super Admin (no company)
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@fitpass.com',
            'password' => Hash::make('password'), 
            'role' => 'super_admin', // This one is already in your ENUM
        ]);

        // 3. Create Company Admin (HR)
        if ($company) {
            User::create([
                'name' => 'HR Admin',
                'email' => 'hr@fitpass.com', 
                'password' => Hash::make('password'),
                'role' => 'hr_admin', // <-- THE FIX: Use the role from your ENUM
                'company_id' => $company->id, 
                'membership_plan_id' => $company->membership_plan_id, 
            ]);
        } else {
            $this->command->warn('Test Company Inc. not found. Skipping HR Admin creation.');
        }

        // 4. Create Employee User (no company)
        User::create([
            'name' => 'Employee User',
            'email' => 'employee@fitpass.com',
            'password' => Hash::make('password'),
            'role' => 'employee', // This one is already in your ENUM
        ]);
    }
}