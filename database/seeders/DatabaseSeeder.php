<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Partner;
use App\Models\ClassModel;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // seeders for production
        $this->call([
            MembershipPlanSeeder::class, // 1st
            CompanySeeder::class,        // 2nd
            UserSeeder::class,           // 3rd
        ]);

        // checks if app is in prod environment
        // fake data only in non-prod envs
        // B2C logic is fine and can run after
        if (! App::isProduction()) {
            $partners = Partner::factory(20)->create();

            $partners->each(function ($partner) {
                ClassModel::factory(5)->create([
                    'partner_id' => $partner->id,
                ]);
            });
        }
    }
}
