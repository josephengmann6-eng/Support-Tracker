<?php

namespace App\Http\Controllers;

use App\Services\HandoverService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HandoverController extends Controller
{
    public function index(Request $request, HandoverService $handover): View
    {
        $pending = $handover->pending();

        return view('handover.index', [
            'pending' => $pending,
            'groups' => $handover->grouped($pending),
            'summary' => $handover->summary($request->user(), $pending),
        ]);
    }
}
