<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ActivityRequest;
use App\Models\Activity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Activities are never deleted, only deactivated, so history stays intact. */
class ActivityController extends Controller
{
    public function index(): View
    {
        return view('admin.activities.index', [
            'activities' => Activity::withCount('entries')->orderByDesc('is_active')->orderBy('title')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.activities.form', ['activity' => new Activity(['is_active' => true])]);
    }

    public function store(ActivityRequest $request): RedirectResponse
    {
        Activity::create([
            ...$request->safe()->only(['title', 'description']),
            'is_active' => $request->boolean('is_active'),
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.activities.index')->with('status', 'Activity created.');
    }

    public function edit(Activity $activity): View
    {
        return view('admin.activities.form', ['activity' => $activity]);
    }

    public function update(ActivityRequest $request, Activity $activity): RedirectResponse
    {
        $activity->update([
            ...$request->safe()->only(['title', 'description']),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.activities.index')->with('status', 'Activity updated.');
    }

    public function toggle(Request $request, Activity $activity): RedirectResponse
    {
        $activity->update(['is_active' => ! $activity->is_active]);

        return back()->with('status', $activity->title.($activity->is_active ? ' activated.' : ' deactivated.'));
    }
}
