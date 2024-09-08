<?php

namespace Database\Seeders;

use App\Models\ProfitRatio;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProfitRatiosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProfitRatio::create([
            'type' => 'private lesson',
            'value' => 1.0,
        ]);

        ProfitRatio::create([
            'type' => 'video call',
            'value' => 1.0,
        ]);

        ProfitRatio::create([
            'type' => 'file',
            'value' => 1.0,
        ]);

        ProfitRatio::create([
            'type' => 'ads',
            'value' => 1.0,
        ]);
    }
}
