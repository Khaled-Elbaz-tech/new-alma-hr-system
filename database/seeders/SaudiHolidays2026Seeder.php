<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Holiday;

/**
 * Saudi public holidays 2026.
 * Eid dates are astronomical estimates — moon sighting may shift them
 * by a day or two. HR should confirm/adjust in Admin > Holidays.
 * Idempotent: safe to re-run (matches on name + year).
 */
class SaudiHolidays2026Seeder extends Seeder
{
    public function run(): void
    {
        $holidays = [
            [
                'name' => 'Founding Day',
                'start_date' => '2026-02-22',
                'end_date' => '2026-02-22',
                'year' => 2026,
                'is_recurring' => true,
                'description' => 'Saudi Founding Day (fixed date).',
            ],
            [
                'name' => 'Eid Al-Fitr Holiday',
                'start_date' => '2026-03-20',
                'end_date' => '2026-03-22',
                'year' => 2026,
                'is_recurring' => false,
                'description' => 'ESTIMATED — depends on moon sighting. HR to confirm exact dates.',
            ],
            [
                'name' => 'Eid Al-Adha Holiday',
                'start_date' => '2026-05-26',
                'end_date' => '2026-05-29',
                'year' => 2026,
                'is_recurring' => false,
                'description' => 'ESTIMATED incl. Arafat Day — depends on moon sighting. HR to confirm exact dates.',
            ],
            [
                'name' => 'Saudi National Day',
                'start_date' => '2026-09-23',
                'year' => 2026,
                'end_date' => '2026-09-23',
                'is_recurring' => true,
                'description' => 'Saudi National Day (fixed date).',
            ],
        ];

        foreach ($holidays as $holiday) {
            Holiday::updateOrCreate(
                ['name' => $holiday['name'], 'year' => $holiday['year']],
                $holiday
            );
        }
    }
}
