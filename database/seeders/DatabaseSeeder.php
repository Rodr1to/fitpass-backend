<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Partner;
use App\Models\ClassModel;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // call existing seeders for plans and users
        $this->call([
            MembershipPlanSeeder::class,
            UserSeeder::class,
        ]);

        // create 20 fake partners
        $partners = Partner::factory(20)->create();

        // loop through each of those partners and create 5 classes for each one
        $partners->each(function ($partner) {
            ClassModel::factory(5)->create([
                'partner_id' => $partner->id,
            ]);
        });
    }
}