<?php

namespace App\Http\Controllers;

use App\Services\ActivityUpdateService;
use App\Services\DashboardStats;
use App\Services\HandoverService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(ActivityUpdateService $entries, DashboardStats $stats, HandoverService $handover): View
    {
        $today = $entries->entriesFor(today());
        $pending = $handover->pending();

        return view('dashboard', [
            'entries' => $today,
            'stats' => $stats->summary($today),
            'trend' => $stats->trend(14),
            'handoverCount' => $pending->count(),
            'oldest' => $pending->take(3),
        ]);
    }
}
