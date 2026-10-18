<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use App\Support\ReportFilters;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $filters = ReportFilters::fromRequest($request);

        return view('reports.index', [
            'filters' => $filters,
            'summary' => $filters->summary(),
            'updates' => $filters->query()
                ->with('entry.activity')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->paginate(20)
                ->appends($filters->toQuery()),
            'users' => User::orderBy('name')->get(['id', 'name', 'staff_id']),
            'activities' => Activity::orderBy('title')->get(['id', 'title', 'is_active']),
        ]);
    }
}
