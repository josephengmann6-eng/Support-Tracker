<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\DailyEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyEntry>
 */
class DailyEntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'activity_id' => Activity::factory(),
            'date' => today()->toDateString(),
            'status' => DailyEntry::PENDING,
        ];
    }
}
