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
        $company = Company::where('contact_email', 'billing@testcompany.com')->first();

        // Super Admin
        User::firstOrCreate(
            ['email' => 'superadmin@fitpass.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
            ]
        );

        // HR Admin
        if ($company) {
            User::firstOrCreate(
                ['email' => 'hr@fitpass.com'],
                [
                    'name' => 'HR Admin',
                    'password' => Hash::make('password'),
                    'role' => 'hr_admin',
                    'company_id' => $company->id,
                    'membership_plan_id' => $company->membership_plan_id,
                ]
            );
        } else {
            $this->command->warn('Test Company Inc. not found. Skipping HR Admin creation.');
        }

        // Employee User
        User::firstOrCreate(
            ['email' => 'employee@fitpass.com'],
            [
                'name' => 'Employee User',
                'password' => Hash::make('password'),
                'role' => 'employee',
            ]
        );
    }
}