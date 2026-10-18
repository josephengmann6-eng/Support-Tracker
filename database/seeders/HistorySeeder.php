<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\DailyEntry;
use App\Models\User;
use App\Services\ActivityUpdateService;
use Illuminate\Database\Seeder;

/**
 * Seven days of realistic history (through the real service, back-dated) so the
 * dashboard chart, feed and handover are populated on first run.
 */
class HistorySeeder extends Seeder
{
    /** [days ago, activity index] => remark. These stay pending and show up in the handover. */
    private const STUCK = [
        '1:1' => 'Fourteen items stuck in the queue; waiting on the payment gateway to reconcile.',
        '3:2' => 'NIGHTLY_RECON failed at step 3. DBA is looking into the lock timeout.',
        '5:3' => 'app-02 at 91% disk. Log cleanup requested from Infrastructure.',
    ];

    private const PENDING_NOTES = [
        'Waiting on the vendor portal to come back up.',
        'Numbers do not match yet, rechecking against the raw logs.',
        'Started late because of a production incident, will finish after lunch.',
    ];

    private const DONE_NOTES = [null, null, 'All good.', 'Checked and matches.', 'Resolved, no action needed.'];

    public function run(ActivityUpdateService $service): void
    {
        if (DailyEntry::exists()) {
            return; // Idempotent: never layer fake history over real data.
        }

        mt_srand(2026);

        $members = User::where('role', User::ROLE_MEMBER)->orderBy('id')->get();
        $activities = Activity::active()->orderBy('id')->get()->values();

        for ($daysAgo = 7; $daysAgo >= 1; $daysAgo--) {
            $date = today()->subDays($daysAgo);
            $entries = $service->entriesFor($date)->keyBy('activity_id');

            foreach ($activities as $i => $activity) {
                $entry = $entries[$activity->id];
                $user = $members[mt_rand(0, $members->count() - 1)];
                $stuck = self::STUCK["$daysAgo:$i"] ?? null;

                if ($stuck) {
                    $service->update($entry, $user, DailyEntry::PENDING, $stuck, $date->copy()->setTime(15, mt_rand(10, 55)));

                    continue;
                }

                if (mt_rand(1, 100) <= 35) {
                    $service->update($entry, $user, DailyEntry::PENDING, self::PENDING_NOTES[mt_rand(0, 2)], $date->copy()->setTime(8, mt_rand(30, 59)));
                }

                $finisher = $members[mt_rand(0, $members->count() - 1)];
                $service->update($entry, $finisher, DailyEntry::DONE, self::DONE_NOTES[mt_rand(0, 4)], $date->copy()->setTime(mt_rand(9, 15), mt_rand(0, 59)));
            }
        }

        // Today: a few items worked, the rest still waiting.
        $today = $service->entriesFor(today())->keyBy('activity_id');
        $startOfDay = today()->addMinute();
        $ago = fn (int $minutes) => now()->subMinutes($minutes)->max($startOfDay);

        $service->update($today[$activities[0]->id], $members[0], DailyEntry::DONE, 'Gateway 18,402 vs logs 18,402.', $ago(180));
        $service->update($today[$activities[4]->id], $members[1], DailyEntry::DONE, null, $ago(95));
        $service->update($today[$activities[1]->id], $members[2], DailyEntry::PENDING, 'Six failed items under investigation.', $ago(40));
    }
}
