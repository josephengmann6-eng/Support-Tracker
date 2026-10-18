<?php

use App\Models\Activity;
use App\Models\DailyEntry;
use App\Models\EntryUpdate;
use App\Models\User;
use App\Support\ReportFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

beforeEach(function () {
    Carbon::setTestNow('2026-10-17 12:00:00');
    $this->user = User::factory()->create(['name' => 'Ama', 'staff_id' => 'STF-1']);
    $this->other = User::factory()->create(['name' => 'Kofi', 'staff_id' => 'STF-2']);
    $this->a1 = Activity::factory()->create(['title' => 'A1']);
    $this->a2 = Activity::factory()->create(['title' => 'A2']);

    // One update per hand-picked moment, including both edges of the day.
    $this->log = function (Activity $a, User $u, string $at, string $new = 'done') {
        $entry = DailyEntry::firstOrCreate(['activity_id' => $a->id, 'date' => substr($at, 0, 10)]);

        return EntryUpdate::create([
            'daily_entry_id' => $entry->id, 'user_id' => $u->id, 'user_name' => $u->name, 'user_staff_id' => $u->staff_id,
            'old_status' => 'pending', 'new_status' => $new, 'remark' => $new === 'pending' ? 'blocked' : null, 'created_at' => $at,
        ]);
    };
});

afterEach(fn () => Carbon::setTestNow());

function filtersFor(array $query): ReportFilters
{
    return ReportFilters::fromRequest(Request::create('/reports', 'GET', $query));
}

function ids(ReportFilters $f): array
{
    return $f->query()->orderBy('id')->pluck('id')->all();
}

test('presets resolve to the correct inclusive date ranges', function () {
    expect(filtersFor(['preset' => 'today']))
        ->from->toDateString()->toBe('2026-10-17')
        ->to->toDateString()->toBe('2026-10-17');

    expect(filtersFor(['preset' => '7']))
        ->from->toDateString()->toBe('2026-10-11')
        ->to->toDateString()->toBe('2026-10-17');

    expect(filtersFor(['preset' => '30']))
        ->from->toDateString()->toBe('2026-09-18')
        ->to->toDateString()->toBe('2026-10-17');

    expect(filtersFor([]))->preset->toBe('7');
    expect(filtersFor(['preset' => 'nonsense']))->preset->toBe('7');
});

test('today includes midnight and 23:59:59 but nothing from neighbouring days', function () {
    $before = ($this->log)($this->a1, $this->user, '2026-10-16 23:59:59');
    $start = ($this->log)($this->a1, $this->user, '2026-10-17 00:00:00');
    $end = ($this->log)($this->a2, $this->user, '2026-10-17 23:59:59');
    $after = ($this->log)($this->a2, $this->user, '2026-10-18 00:00:00');

    expect(ids(filtersFor(['preset' => 'today'])))->toBe([$start->id, $end->id])
        ->and(ids(filtersFor(['preset' => 'today'])))->not->toContain($before->id, $after->id);
});

test('7 days and 30 days cut off at the right day', function () {
    $d6 = ($this->log)($this->a1, $this->user, '2026-10-11 08:00:00'); // 6 days ago: in 7-day window
    $d7 = ($this->log)($this->a1, $this->user, '2026-10-10 08:00:00'); // 7 days ago: out
    $d29 = ($this->log)($this->a2, $this->user, '2026-09-18 08:00:00'); // 29 days ago: in 30-day window
    $d30 = ($this->log)($this->a2, $this->user, '2026-09-17 08:00:00'); // 30 days ago: out

    expect(ids(filtersFor(['preset' => '7'])))->toBe([$d6->id])
        ->and(ids(filtersFor(['preset' => '30'])))->toBe([$d6->id, $d7->id, $d29->id]);
});

test('custom ranges are inclusive, and swapped dates are corrected', function () {
    $in1 = ($this->log)($this->a1, $this->user, '2026-10-12 00:00:00');
    $in2 = ($this->log)($this->a1, $this->user, '2026-10-14 23:59:59');
    ($this->log)($this->a1, $this->user, '2026-10-11 23:59:59');
    ($this->log)($this->a1, $this->user, '2026-10-15 00:00:00');

    $custom = ['preset' => 'custom', 'from' => '2026-10-12', 'to' => '2026-10-14'];
    expect(ids(filtersFor($custom)))->toBe([$in1->id, $in2->id]);
    expect(ids(filtersFor(['from' => '2026-10-14', 'to' => '2026-10-12'] + $custom)))->toBe([$in1->id, $in2->id]);
});

test('invalid custom dates fall back instead of erroring', function () {
    $f = filtersFor(['preset' => 'custom', 'from' => 'not-a-date', 'to' => '2026-02-30']);

    expect($f->to->toDateString())->toBe('2026-10-17')
        ->and($f->from->toDateString())->toBe('2026-10-11');
});

test('user, activity and status filters combine with the date range', function () {
    $a = ($this->log)($this->a1, $this->user, '2026-10-17 09:00:00', 'done');
    $b = ($this->log)($this->a1, $this->other, '2026-10-17 10:00:00', 'pending');
    $c = ($this->log)($this->a2, $this->user, '2026-10-17 11:00:00', 'pending');
    $old = ($this->log)($this->a1, $this->user, '2026-09-20 11:00:00', 'done'); // inside the 30-day window

    expect(ids(filtersFor(['preset' => 'today', 'user_id' => $this->user->id])))->toBe([$a->id, $c->id])
        ->and(ids(filtersFor(['preset' => 'today', 'activity_id' => $this->a1->id])))->toBe([$a->id, $b->id])
        ->and(ids(filtersFor(['preset' => 'today', 'status' => 'pending'])))->toBe([$b->id, $c->id])
        ->and(ids(filtersFor(['preset' => 'today', 'user_id' => $this->other->id, 'activity_id' => $this->a1->id, 'status' => 'pending'])))->toBe([$b->id])
        ->and(ids(filtersFor(['preset' => '30', 'user_id' => $this->user->id, 'status' => 'done'])))->toBe([$a->id, $old->id]);
});

test('summary counts match the filtered rows', function () {
    ($this->log)($this->a1, $this->user, '2026-10-17 09:00:00', 'done');
    ($this->log)($this->a1, $this->other, '2026-10-17 10:00:00', 'pending');
    ($this->log)($this->a2, $this->user, '2026-10-17 11:00:00', 'done');
    ($this->log)($this->a2, $this->user, '2026-09-01 11:00:00', 'done'); // outside range

    expect(filtersFor(['preset' => 'today'])->summary())->toBe([
        'total' => 3, 'done' => 2, 'pending' => 1, 'people' => 2, 'entries' => 2,
    ]);
});

test('the reports page renders filtered, paginated results for a member', function () {
    foreach (range(1, 25) as $i) {
        ($this->log)($this->a1, $this->user, '2026-10-17 09:'.str_pad((string) $i, 2, '0', STR_PAD_LEFT).':00');
    }
    ($this->log)($this->a2, $this->other, '2026-10-17 12:00:00', 'pending');

    $this->actingAs($this->user)->get(route('reports.index', ['preset' => 'today', 'user_id' => $this->user->id]))
        ->assertOk()
        ->assertSee('Ama')
        ->assertDontSee('Kofi (STF-2)</td>', false)
        ->assertSee('page=2');

    $this->actingAs($this->user)->get(route('reports.index', ['preset' => 'today', 'status' => 'pending']))
        ->assertOk()->assertSee('blocked');
});
