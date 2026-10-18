<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateEntryStatusRequest;
use App\Models\DailyEntry;
use App\Services\ActivityUpdateService;
use Illuminate\Http\RedirectResponse;

class EntryStatusController extends Controller
{
    public function update(UpdateEntryStatusRequest $request, DailyEntry $entry, ActivityUpdateService $service): RedirectResponse
    {
        $service->update($entry, $request->user(), $request->validated('status'), $request->validated('remark'));

        return redirect()
            ->route('board.index', ['date' => $entry->date])
            ->with('status', $entry->activity->title.' marked '.$request->validated('status').'.');
    }
}
