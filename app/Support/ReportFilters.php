<?php

namespace App\Support;

use App\Models\DailyEntry;
use App\Models\EntryUpdate;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/** Parsed, validated filters for the Reports page (also reused by tests). */
class ReportFilters
{
    public const PRESETS = ['today' => 'Today', '7' => '7 days', '30' => '30 days', 'custom' => 'Custom'];

    public function __construct(
        public readonly string $preset,
        public readonly CarbonImmutable $from,
        public readonly CarbonImmutable $to,
        public readonly ?int $userId = null,
        public readonly ?int $activityId = null,
        public readonly ?string $status = null,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $today = CarbonImmutable::today();
        $preset = (string) $request->query('preset', '7');
        $preset = array_key_exists($preset, self::PRESETS) ? $preset : '7';

        [$from, $to] = match ($preset) {
            'today' => [$today, $today],
            '30' => [$today->subDays(29), $today],
            'custom' => [
                DateInput::parse($request->query('from'), $today->subDays(6)),
                DateInput::parse($request->query('to'), $today),
            ],
            default => [$today->subDays(6), $today],
        };

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
        }

        $status = $request->query('status');

        return new self(
            $preset,
            $from,
            $to,
            self::intOrNull($request->query('user_id')),
            self::intOrNull($request->query('activity_id')),
            in_array($status, [DailyEntry::PENDING, DailyEntry::DONE], true) ? $status : null,
        );
    }

    public function query(): Builder
    {
        return EntryUpdate::query()
            ->whereBetween('created_at', [$this->from->startOfDay()->toDateTimeString(), $this->to->endOfDay()->toDateTimeString()])
            ->when($this->userId, fn (Builder $q, int $id) => $q->where('user_id', $id))
            ->when($this->activityId, fn (Builder $q, int $id) => $q->whereHas('entry', fn ($e) => $e->where('activity_id', $id)))
            ->when($this->status, fn (Builder $q, string $s) => $q->where('new_status', $s));
    }

    /** @return array{total: int, done: int, pending: int, people: int, entries: int} */
    public function summary(): array
    {
        $q = $this->query();

        return [
            'total' => (clone $q)->count(),
            'done' => (clone $q)->where('new_status', DailyEntry::DONE)->count(),
            'pending' => (clone $q)->where('new_status', DailyEntry::PENDING)->count(),
            'people' => (clone $q)->distinct()->count('user_id'),
            'entries' => (clone $q)->distinct()->count('daily_entry_id'),
        ];
    }

    /** @return array<string, string|int> query-string form, for pagination/links */
    public function toQuery(): array
    {
        return array_filter([
            'preset' => $this->preset,
            'from' => $this->preset === 'custom' ? $this->from->toDateString() : null,
            'to' => $this->preset === 'custom' ? $this->to->toDateString() : null,
            'user_id' => $this->userId,
            'activity_id' => $this->activityId,
            'status' => $this->status,
        ], fn ($v) => $v !== null);
    }

    private static function intOrNull(mixed $value): ?int
    {
        return is_numeric($value) && (int) $value > 0 ? (int) $value : null;
    }
}
