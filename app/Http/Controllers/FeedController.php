<?php

namespace App\Http\Controllers;

use App\Models\EntryUpdate;
use App\Support\DateInput;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedController extends Controller
{
    public function index(Request $request): View
    {
        $date = DateInput::day($request);

        $updates = EntryUpdate::with('entry.activity')
            ->whereBetween('created_at', [$date->startOfDay()->toDateTimeString(), $date->endOfDay()->toDateTimeString()])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return view('feed.index', ['date' => $date, 'updates' => $updates]);
    }
}
