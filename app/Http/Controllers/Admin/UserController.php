<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Team management. Users are deactivated, never deleted (history references them). */
class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', ['users' => User::orderByDesc('is_active')->orderBy('name')->get()]);
    }

    public function create(): View
    {
        return view('admin.users.form', ['member' => new User(['role' => User::ROLE_MEMBER, 'is_active' => true])]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        User::create([
            ...$request->safe()->except(['is_active', 'password_confirmation']),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.users.index')->with('status', 'Team member created.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', ['member' => $user]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = $request->safe()->except(['is_active', 'password', 'password_confirmation']);
        $data['is_active'] = $request->boolean('is_active');

        if ($user->is($request->user())) {
            // Admins cannot lock themselves out or demote themselves.
            $data['role'] = $user->role;
            $data['is_active'] = true;
        }

        if ($request->filled('password')) {
            $data['password'] = $request->validated('password');
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('status', $user->name.' updated.');
    }

    public function toggle(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['toggle' => 'You cannot deactivate your own account.']);
        }

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('status', $user->name.($user->is_active ? ' activated.' : ' deactivated.'));
    }
}
