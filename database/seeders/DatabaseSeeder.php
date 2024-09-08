<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\ProfileStudent;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call(
            RoleSeeder::class
        );
        $this->call(AdminSeed::class);
        $this->call(ChannelSeeder::class);
        $this->call(ProfitRatiosSeeder::class);
//        User::factory()
//            ->count(100)
//            ->has(ProfileStudent::factory()->count(1), 'profile_student')
//            ->create();
    }
}
