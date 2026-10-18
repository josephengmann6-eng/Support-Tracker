<?php

use App\Models\Activity;
use App\Models\DailyEntry;
use App\Models\EntryUpdate;
use App\Models\User;
use App\Services\ActivityUpdateService;
use Illuminate\Support\Carbon;

beforeEach(function () {
    Carbon::setTestNow('2026-10-17 10:30:00');
    $this->activity = Activity::factory()->create(['title' => 'Check backups']);
    $this->entry = DailyEntry::factory()->create(['activity_id' => $this->activity->id, 'date' => '2026-10-17']);
});

afterEach(fn () => Carbon::setTestNow());

test('a member can update a status and an audit row is written with a snapshot and server timestamp', function () {
    $member = User::factory()->create(['name' => 'Kofi Owusu', 'staff_id' => 'STF-1042']);

    $this->actingAs($member)
        ->post(route('board.update', $this->entry), ['status' => 'done', 'remark' => 'All good'])
        ->assertRedirect(route('board.index', ['date' => '2026-10-17']));

    $this->entry->refresh();
    expect($this->entry->status)->toBe('done')
        ->and($this->entry->remark)->toBe('All good')
        ->and($this->entry->last_updated_by)->toBe($member->id);

    $log = EntryUpdate::sole();
    expect($log->daily_entry_id)->toBe($this->entry->id)
        ->and($log->user_id)->toBe($member->id)
        ->and($log->user_name)->toBe('Kofi Owusu')
        ->and($log->user_staff_id)->toBe('STF-1042')
        ->and($log->old_status)->toBe('pending')
        ->and($log->new_status)->toBe('done')
        ->and($log->remark)->toBe('All good')
        ->and($log->created_at->toDateTimeString())->toBe('2026-10-17 10:30:00');
});

test('the client cannot choose the timestamp', function () {
    $member = User::factory()->create();

    $this->actingAs($member)->post(route('board.update', $this->entry), [
        'status' => 'done', 'created_at' => '2001-01-01 00:00:00', 'last_updated_at' => '2001-01-01 00:00:00',
    ]);

    expect(EntryUpdate::sole()->created_at->toDateTimeString())->toBe('2026-10-17 10:30:00');
});

test('profile edits never rewrite history snapshots', function () {
    $member = User::factory()->create(['name' => 'Old Name', 'staff_id' => 'STF-0001']);
    app(ActivityUpdateService::class)->update($this->entry, $member, 'done');

    $member->update(['name' => 'New Name', 'staff_id' => 'STF-9999']);

    $log = EntryUpdate::sole();
    expect($log->user_name)->toBe('Old Name')->and($log->user_staff_id)->toBe('STF-0001');
});

test('marking pending requires a remark', function () {
    $this->entry->update(['status' => 'done']);

    $this->actingAs(User::factory()->create())
        ->post(route('board.update', $this->entry), ['status' => 'pending'])
        ->assertSessionHasErrors('remark');

    expect(EntryUpdate::count())->toBe(0);
});

test('every change is appended and the latest sets the current status', function () {
    $member = User::factory()->create();
    $service = app(ActivityUpdateService::class);

    $service->update($this->entry, $member, 'pending', 'Waiting on vendor');
    $service->update($this->entry, $member, 'done', 'Vendor replied');
    $service->update($this->entry, $member, 'pending', 'Regressed');

    expect(EntryUpdate::count())->toBe(3)
        ->and($this->entry->fresh()->status)->toBe('pending')
        ->and($this->entry->fresh()->remark)->toBe('Regressed');
});

test('audit rows are append-only', function () {
    app(ActivityUpdateService::class)->update($this->entry, User::factory()->create(), 'done');
    $log = EntryUpdate::sole();

    expect(fn () => $log->update(['remark' => 'tampered']))->toThrow(LogicException::class);
    expect(fn () => $log->delete())->toThrow(LogicException::class);
    expect(fn () => DB::table('entry_updates')->update(['remark' => 'tampered']))->toThrow(Exception::class);
    expect(fn () => DB::table('entry_updates')->delete())->toThrow(Exception::class);
});

test('daily entries are created lazily, once per activity and date', function () {
    $this->entry->delete();
    $service = app(ActivityUpdateService::class);

    expect(DailyEntry::count())->toBe(0);
    $service->entriesFor(today());
    $service->entriesFor(today());

    expect(DailyEntry::count())->toBe(1);
});

test('nothing is created for future dates or before an activity existed', function () {
    $this->entry->delete();
    $service = app(ActivityUpdateService::class);

    $service->entriesFor(today()->addDay());
    $service->entriesFor(today()->subDays(60));

    expect(DailyEntry::count())->toBe(0);
});

test('inactive activities cannot be updated', function () {
    $this->activity->update(['is_active' => false]);

    $this->actingAs(User::factory()->create())
        ->post(route('board.update', $this->entry), ['status' => 'done'])
        ->assertForbidden();
});

test('a member can add a note to a done item without changing status', function () {
    $this->entry->update(['status' => 'done']);
    $member = User::factory()->create();

    $this->actingAs($member)
        ->post(route('board.update', $this->entry), ['status' => 'done', 'remark' => 'Checked again, still healthy'])
        ->assertRedirect(route('board.index', ['date' => '2026-10-17']));

    $this->entry->refresh();
    expect($this->entry->status)->toBe('done')
        ->and($this->entry->remark)->toBe('Checked again, still healthy');

    $log = EntryUpdate::sole();
    expect($log->old_status)->toBe('done')
        ->and($log->new_status)->toBe('done')
        ->and($log->remark)->toBe('Checked again, still healthy');
});

test('feed and reports display note added when status is unchanged', function () {
    $member = User::factory()->create();
    $service = app(ActivityUpdateService::class);
    $service->update($this->entry, $member, 'pending', 'Initial remark');
    $service->update($this->entry, $member, 'pending', 'Follow-up note');

    $this->actingAs($member)->get(route('feed.index'))
        ->assertOk()
        ->assertSee('Note added');

    $this->actingAs($member)->get(route('reports.index'))
        ->assertOk()
        ->assertSee('Note added');
});

test('dashboard renders empty state when there are no activities', function () {
    $this->entry->delete();
    $this->activity->delete();
    $member = User::factory()->create();

    $this->actingAs($member)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('No activities were tracked for today.');
});

test('handover links to update on board', function () {
    $member = User::factory()->create();
    DailyEntry::factory()->create([
        'activity_id' => $this->activity->id,
        'date' => '2026-10-15',
        'status' => 'pending',
    ]);

    $this->actingAs($member)->get(route('handover.index'))
        ->assertOk()
        ->assertSee('Update on board')
        ->assertDontSee('>Open<', false);
});

test('board renders mobile stacked cards and table layout', function () {
    $member = User::factory()->create();

    $this->actingAs($member)->get(route('board.index'))
        ->assertOk()
        ->assertSee('sm:hidden', false)
        ->assertSee('hidden sm:block', false)
        ->assertSee('Save remark');
});


