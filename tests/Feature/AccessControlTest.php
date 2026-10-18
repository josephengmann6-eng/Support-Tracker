<?php

use App\Models\Activity;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

dataset('admin routes', [
    'activities index' => ['get', 'admin.activities.index'],
    'activities create' => ['get', 'admin.activities.create'],
    'users index' => ['get', 'admin.users.index'],
    'users create' => ['get', 'admin.users.create'],
]);

test('a member cannot open admin routes', function (string $method, string $route) {
    $this->actingAs(User::factory()->create())->{$method}(route($route))->assertForbidden();
})->with('admin routes');

test('a member cannot create, change or toggle activities and users', function () {
    $member = User::factory()->create();
    $activity = Activity::factory()->create();
    $other = User::factory()->create();

    $this->actingAs($member);
    $this->post(route('admin.activities.store'), ['title' => 'Sneaky'])->assertForbidden();
    $this->put(route('admin.activities.update', $activity), ['title' => 'Renamed'])->assertForbidden();
    $this->patch(route('admin.activities.toggle', $activity))->assertForbidden();
    $this->post(route('admin.users.store'), ['name' => 'X'])->assertForbidden();
    $this->patch(route('admin.users.toggle', $other))->assertForbidden();

    expect($activity->fresh()->is_active)->toBeTrue()->and($other->fresh()->is_active)->toBeTrue();
});

test('guests are redirected to login', function () {
    $this->get(route('board.index'))->assertRedirect(route('login'));
    $this->get(route('admin.users.index'))->assertRedirect(route('login'));
});

test('members do not see admin links but admins do', function () {
    $this->actingAs(User::factory()->create())->get(route('dashboard'))->assertOk()->assertDontSee('admin/users');
    $this->actingAs(User::factory()->admin()->create())->get(route('dashboard'))->assertOk()->assertSee('admin/users');
});

test('there is no public registration', function () {
    $this->get('/register')->assertNotFound();
    $this->post('/register', ['name' => 'A', 'email' => 'a@b.test', 'password' => 'password', 'password_confirmation' => 'password'])->assertStatus(404);
});

test('an inactive user cannot log in', function () {
    $user = User::factory()->inactive()->create();

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('a deactivated user with a live session is signed out', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $user->update(['is_active' => false]);

    $this->get(route('dashboard'))->assertRedirect(route('login'));
    $this->assertGuest();
});

test('login is throttled after repeated failures', function () {
    $user = User::factory()->create();

    foreach (range(1, 5) as $i) {
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong']);
    }

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])
        ->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('an admin can create a team member and an activity', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Efua Quaye', 'staff_id' => 'STF-2001', 'email' => 'efua@example.test', 'phone' => '0241112222',
        'department' => 'Support', 'role' => 'member', 'is_active' => '1',
        'password' => 'a-strong-password', 'password_confirmation' => 'a-strong-password',
    ])->assertRedirect(route('admin.users.index'));

    $created = User::where('staff_id', 'STF-2001')->firstOrFail();
    expect(Hash::check('a-strong-password', $created->password))->toBeTrue()->and($created->role)->toBe('member');

    $this->post(route('admin.activities.store'), ['title' => 'Check certs', 'description' => 'TLS expiry', 'is_active' => '1'])
        ->assertRedirect(route('admin.activities.index'));
    expect(Activity::where('title', 'Check certs')->firstOrFail()->created_by)->toBe($admin->id);
});

test('an admin can render every admin page', function () {
    $admin = User::factory()->admin()->create();
    $activity = Activity::factory()->create();

    $this->actingAs($admin);
    $this->get(route('admin.activities.index'))->assertOk();
    $this->get(route('admin.activities.create'))->assertOk();
    $this->get(route('admin.activities.edit', $activity))->assertOk();
    $this->get(route('admin.users.index'))->assertOk();
    $this->get(route('admin.users.create'))->assertOk();
    $this->get(route('admin.users.edit', $admin))->assertOk();
});

test('activities and users are deactivated, never deleted', function () {
    $admin = User::factory()->admin()->create();
    $activity = Activity::factory()->create();
    $member = User::factory()->create();

    $this->actingAs($admin);
    $this->patch(route('admin.activities.toggle', $activity));
    $this->patch(route('admin.users.toggle', $member));

    expect($activity->fresh()->is_active)->toBeFalse()->and($member->fresh()->is_active)->toBeFalse();
    $this->delete('/admin/activities/'.$activity->id)->assertStatus(405);
    $this->delete('/admin/users/'.$member->id)->assertStatus(405);
});

test('an admin cannot deactivate or demote themselves', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->patch(route('admin.users.toggle', $admin))->assertSessionHasErrors('toggle');
    $this->put(route('admin.users.update', $admin), [
        'name' => $admin->name, 'staff_id' => $admin->staff_id, 'email' => $admin->email, 'role' => 'member', 'is_active' => '0',
    ]);

    $admin->refresh();
    expect($admin->role)->toBe('admin')->and($admin->is_active)->toBeTrue();
});

test('active sidebar link has aria-current page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('aria-current="page"', false);
});

