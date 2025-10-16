<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@fitpass.com',
            'password' => bcrypt('password'), // Use bcrypt() helper
            'role' => 'super_admin',
        ]);
        \App\Models\User::create([
            'name' => 'HR Admin',
            'email' => 'hr@fitpass.com',
            'password' => bcrypt('password'),
            'role' => 'hr_admin',
        ]);
        \App\Models\User::create([
            'name' => 'Employee User',
            'email' => 'employee@fitpass.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
        ]);
    }
}
