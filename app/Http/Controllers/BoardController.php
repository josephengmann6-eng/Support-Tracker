<?php

namespace App\Http\Controllers;

use App\Services\ActivityUpdateService;
use App\Support\DateInput;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BoardController extends Controller
{
    public function index(Request $request, ActivityUpdateService $service): View
    {
        $date = DateInput::day($request);

        return view('board.index', [
            'date' => $date,
            'entries' => $service->entriesFor($date),
        ]);
    }
}
