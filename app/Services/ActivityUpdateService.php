<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\DailyEntry;
use App\Models\EntryUpdate;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ActivityUpdateService
{
    /**
     * Lazily create the day's entries (no cron). Only past/present days, and only for
     * activities that already existed on that day, so browsing history never invents work.
     *
     * @return Collection<int, DailyEntry>
     */
    public function entriesFor(CarbonInterface $date): Collection
    {
        $day = $date->toDateString();

        if ($day <= today()->toDateString()) {
            Activity::active()->orderBy('id')->get()
                ->filter(fn (Activity $a) => $a->created_at->toDateString() <= $day)
                ->each(fn (Activity $a) => DailyEntry::firstOrCreate(
                    ['activity_id' => $a->id, 'date' => $day],
                    ['status' => DailyEntry::PENDING],
                ));
        }

        return DailyEntry::with(['activity', 'updater'])
            ->where('date', $day)
            ->whereHas('activity', fn ($q) => $q->active())
            ->get()
            ->sortBy('activity_id')
            ->values();
    }

    /**
     * Change an entry's status: update the entry and append an audit row atomically.
     * The timestamp is server-side; `$at` exists only so seeders can backfill history.
     */
    public function update(
        DailyEntry $entry,
        User $user,
        string $newStatus,
        ?string $remark = null,
        ?CarbonInterface $at = null,
    ): EntryUpdate {
        return DB::transaction(function () use ($entry, $user, $newStatus, $remark, $at) {
            $entry = DailyEntry::query()->lockForUpdate()->findOrFail($entry->id);
            $at ??= now();
            $remark = filled($remark) ? trim($remark) : null;

            $log = EntryUpdate::create([
                'daily_entry_id' => $entry->id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_staff_id' => $user->staff_id,
                'old_status' => $entry->status,
                'new_status' => $newStatus,
                'remark' => $remark,
                'created_at' => $at,
            ]);

            $entry->update([
                'status' => $newStatus,
                'remark' => $remark,
                'last_updated_by' => $user->id,
                'last_updated_at' => $at,
            ]);

            return $log;
        });
    }
}
