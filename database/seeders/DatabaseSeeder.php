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
        // We MUST call them in this order for relationships to link
        $this->call([
            MembershipPlanSeeder::class, // 1st
            CompanySeeder::class,          // 2nd (This is the new one)
            UserSeeder::class,           // 3rd
        ]);

        // This B2C logic is fine and can run after
        $partners = Partner::factory(20)->create();

        $partners->each(function ($partner) {
            ClassModel::factory(5)->create([
                'partner_id' => $partner->id,
            ]);
        });
    }
}