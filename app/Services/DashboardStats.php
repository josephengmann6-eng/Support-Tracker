<?php

namespace App\Services;

use App\Models\DailyEntry;
use App\Models\EntryUpdate;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class DashboardStats
{
    /**
     * Percentage done per day for the last `$days` days (oldest first).
     * Days with no entries have a null percentage rather than a misleading 0.
     *
     * @return Collection<int, array{date: string, label: string, total: int, done: int, percent: int|null}>
     */
    public function trend(int $days = 14): Collection
    {
        $from = CarbonImmutable::today()->subDays($days - 1);

        $rows = DailyEntry::query()
            ->selectRaw("date, count(*) as total, sum(case when status = 'done' then 1 else 0 end) as done")
            ->where('date', '>=', $from->toDateString())
            ->where('date', '<=', today()->toDateString())
            ->whereHas('activity', fn ($q) => $q->active())
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        return collect(range(0, $days - 1))->map(function (int $i) use ($from, $rows) {
            $day = $from->addDays($i);
            $row = $rows->get($day->toDateString());
            $total = (int) ($row->total ?? 0);
            $done = (int) ($row->done ?? 0);

            return [
                'date' => $day->toDateString(),
                'label' => $day->format('D j'),
                'total' => $total,
                'done' => $done,
                'percent' => $total ? (int) round($done / $total * 100) : null,
            ];
        });
    }

    /**
     * @param  Collection<int, DailyEntry>  $todayEntries
     * @return array{percent: int, done: int, total: int, pending: int, carried: int, updates: int}
     */
    public function summary(Collection $todayEntries): array
    {
        $total = $todayEntries->count();
        $done = $todayEntries->where('status', DailyEntry::DONE)->count();

        return [
            'percent' => $total ? (int) round($done / $total * 100) : 0,
            'done' => $done,
            'total' => $total,
            'pending' => $total - $done,
            'carried' => DailyEntry::carriedOver()->count(),
            'updates' => EntryUpdate::where('created_at', '>=', today()->startOfDay()->toDateTimeString())->count(),
        ];
    }
}
