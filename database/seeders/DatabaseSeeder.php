<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // delete the example User::factory() code and replace it
        // with a call to the specific seeders we created.
        $this->call([
            MembershipPlanSeeder::class,
            UserSeeder::class,
        ]);
    }
}
