<?php

namespace App\Services;

use App\Models\DailyEntry;
use App\Models\User;
use Illuminate\Support\Collection;

/** Pending work carried over from earlier days, for shift handover. */
class HandoverService
{
    /** Age buckets: [label, min days, max days (null = open ended)]. */
    public const BUCKETS = [
        ['Yesterday', 1, 1],
        ['2–3 days ago', 2, 3],
        ['4–7 days ago', 4, 7],
        ['More than a week ago', 8, null],
    ];

    /** @return Collection<int, DailyEntry> oldest first */
    public function pending(): Collection
    {
        return DailyEntry::carriedOver()
            ->with(['activity', 'updater'])
            ->orderBy('date')
            ->orderBy('activity_id')
            ->get();
    }

    /**
     * @return Collection<int, array{label: string, overdue: bool, entries: Collection<int, DailyEntry>}>
     */
    public function grouped(?Collection $pending = null): Collection
    {
        $pending ??= $this->pending();

        return collect(self::BUCKETS)
            ->map(fn ($b) => [
                'label' => $b[0],
                'overdue' => $b[1] >= DailyEntry::OVERDUE_AFTER_DAYS,
                'entries' => $pending->filter(function (DailyEntry $e) use ($b) {
                    $days = $e->daysWaiting();

                    return $days >= $b[1] && ($b[2] === null || $days <= $b[2]);
                })->values(),
            ])
            ->filter(fn ($g) => $g['entries']->isNotEmpty())
            ->values();
    }

    /** Plain-text summary suitable for pasting into chat or email. */
    public function summary(User $preparedBy, ?Collection $pending = null): string
    {
        $pending ??= $this->pending();
        $overdue = $pending->filter->isOverdue()->count();

        $lines = [
            'SHIFT HANDOVER · '.now()->format('l, j F Y H:i'),
            'Prepared by: '.$preparedBy->name.' ('.$preparedBy->staff_id.')',
            'Pending from earlier days: '.$pending->count().($overdue ? " ({$overdue} overdue, 2+ days)" : ''),
        ];

        if ($pending->isEmpty()) {
            return implode("\n", [...$lines, '', 'Nothing outstanding. All earlier work is done.']);
        }

        foreach ($this->grouped($pending) as $group) {
            $lines[] = '';
            $lines[] = strtoupper($group['label']).($group['overdue'] ? ' [OVERDUE]' : '');

            foreach ($group['entries'] as $i => $entry) {
                $days = $entry->daysWaiting();
                $lines[] = ($i + 1).'. '.$entry->activity->title;
                $lines[] = '   Pending since '.$entry->dateCarbon()->format('D j M').' ('.$days.' '.($days === 1 ? 'day' : 'days').')';
                $lines[] = '   Last remark: '.($entry->remark ?: 'none');
                $lines[] = '   Last touched by: '.($entry->updater
                    ? $entry->updater->name.' ('.$entry->updater->staff_id.') at '.$entry->last_updated_at->format('j M H:i')
                    : 'nobody yet');
            }
        }

        return implode("\n", $lines);
    }
}
