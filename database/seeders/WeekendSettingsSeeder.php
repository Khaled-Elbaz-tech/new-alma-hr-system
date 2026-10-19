<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WeekendSetting;

class WeekendSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $days = [
            ['day' => 'sunday', 'is_weekend' => false],
            ['day' => 'monday', 'is_weekend' => false],
            ['day' => 'tuesday', 'is_weekend' => false],
            ['day' => 'wednesday', 'is_weekend' => false],
            ['day' => 'thursday', 'is_weekend' => false],
            ['day' => 'friday', 'is_weekend' => true],  // Weekend in Saudi Arabia
            ['day' => 'saturday', 'is_weekend' => true], // Weekend in Saudi Arabia
        ];

        foreach ($days as $day) {
            WeekendSetting::create($day);
        }
    }
}
