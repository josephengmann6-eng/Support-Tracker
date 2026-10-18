<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    public const ACTIVITIES = [
        ['Daily SMS count vs SMS count from logs', 'Compare the SMS total reported by the gateway dashboard against the count derived from application logs and note any gap.'],
        ['Check failed transactions queue', 'Review the failed transactions queue, retry what can be retried and escalate the rest.'],
        ['Verify overnight batch jobs completed', 'Confirm every scheduled overnight batch job finished successfully and within its window.'],
        ['Review server disk usage', 'Check disk usage on application and database servers; flag anything above 85%.'],
        ['Confirm database backup success', 'Verify last night\'s full database backup completed and the file is restorable.'],
    ];

    public function run(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->first();

        foreach (self::ACTIVITIES as [$title, $description]) {
            $activity = Activity::firstOrNew(['title' => $title]);
            $activity->fill(['description' => $description, 'is_active' => true, 'created_by' => $admin?->id]);

            if (! $activity->exists) {
                // Back-dated so the seeded week of history is valid (entries never pre-date an activity).
                $activity->created_at = now()->subDays(10);
            }

            $activity->save();
        }
    }
}
