<x-app-layout title="Daily Board" :subtitle="$date->format('l, j F Y')">
    <x-slot name="actions">
        @unless ($date->isToday())
            <a href="{{ route('board.index') }}" class="btn-primary">Go to today</a>
        @endunless
    </x-slot>

@php
    $failedEntry = $errors->any() && old('entry_id') ? $entries->firstWhere('id', (int) old('entry_id')) : null;
@endphp

    <div x-data="{
            open: {{ $errors->any() ? 'true' : 'false' }},
            id: {{ $failedEntry ? $failedEntry->id : 'null' }},
            status: @js(old('status', 'pending')),
            title: @js($failedEntry ? $failedEntry->activity->title : ''),
            remark: @js(old('remark', '')),
            previousRemark: @js($failedEntry ? ($failedEntry->remark ?? '') : ''),
            action: @js($failedEntry ? route('board.update', $failedEntry) : ''),
            isCurrentDone: {{ $failedEntry && $failedEntry->status === 'done' ? 'true' : 'false' }},
            ask(entry, status, title, url, remark, isCurrentDone = false) {
                this.id = entry; this.status = status; this.title = title; this.action = url;
                this.previousRemark = remark || '';
                this.remark = '';
                this.isCurrentDone = isCurrentDone;
                this.open = true; this.$nextTick(() => this.$refs.remark.focus());
            }
        }" @keydown.escape.window="open = false">

        {{-- Date navigation --}}
        <x-ui.date-nav route="board.index" :date="$date" class="mb-6">
            <span class="font-semibold text-brand-600">{{ $entries->where('status', 'done')->count() }}</span> of {{ $entries->count() }} done
        </x-ui.date-nav>

        {{-- Mobile stacked cards (below sm) --}}
        <div class="space-y-3 sm:hidden">
            @forelse ($entries as $entry)
                <div class="card !p-4 flex flex-col gap-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <h3 class="font-bold text-forest text-base leading-snug">{{ $entry->activity->title }}</h3>
                            @if ($entry->activity->description)
                                <p class="mt-0.5 line-clamp-2 text-xs text-slate-500">{{ $entry->activity->description }}</p>
                            @endif
                        </div>
                        <x-ui.status :status="$entry->status" :overdue="$entry->isOverdue()" class="shrink-0" />
                    </div>

                    @if ($entry->remark)
                        <div class="rounded-xl border border-slate-100 bg-slate-50 p-2.5 text-xs text-slate-700">
                            <span class="mb-0.5 block text-[11px] font-semibold uppercase tracking-wider text-slate-500">Remark</span>
                            <p class="whitespace-pre-wrap">{{ $entry->remark }}</p>
                        </div>
                    @endif

                    <div class="flex items-center justify-between border-t border-slate-100 pt-2.5 text-xs text-slate-500">
                        <div>
                            @if ($entry->last_updated_at)
                                <span>Updated by <strong class="font-semibold text-slate-700">{{ $entry->updater?->name }}</strong></span>
                                <span class="block text-[11px] text-slate-500">{{ $entry->updater?->staff_id }} · {{ $entry->last_updated_at->format('H:i') }}</span>
                            @else
                                <span>Not touched yet</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <button type="button" class="btn-primary btn-sm flex-1 py-2 text-center"
                            @click="ask({{ $entry->id }}, 'done', @js($entry->activity->title), @js(route('board.update', $entry)), @js($entry->remark ?? ''), {{ $entry->status === 'done' ? 'true' : 'false' }})">
                            {{ $entry->status === 'done' ? 'Add note' : 'Done' }}
                        </button>
                        <button type="button" class="btn-amber btn-sm flex-1 py-2 text-center"
                            @click="ask({{ $entry->id }}, 'pending', @js($entry->activity->title), @js(route('board.update', $entry)), @js($entry->remark ?? ''), false)">
                            Pending
                        </button>
                    </div>
                </div>
            @empty
                <div class="card py-10 text-center text-sm text-slate-500">No activities were tracked on this day.</div>
            @endforelse
        </div>

        {{-- Desktop/tablet table (sm and above) --}}
        <div class="hidden sm:block card overflow-x-auto !p-2 sm:!p-4">
            <table class="table-clean min-w-[760px]">
                <thead>
                    <tr>
                        <th>Activity</th><th>Status</th><th>Remark</th><th>Last update</th><th class="text-right">Update</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($entries as $entry)
                        <tr>
                            <td class="max-w-xs">
                                <p class="font-semibold text-forest">{{ $entry->activity->title }}</p>
                                <p class="mt-0.5 line-clamp-2 text-xs text-slate-500">{{ $entry->activity->description }}</p>
                            </td>
                            <td><x-ui.status :status="$entry->status" :overdue="$entry->isOverdue()" /></td>
                            <td class="max-w-xs text-slate-600">{{ $entry->remark ?? '—' }}</td>
                            <td class="whitespace-nowrap">
                                @if ($entry->last_updated_at)
                                    <p class="font-medium text-slate-700">{{ $entry->updater?->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $entry->updater?->staff_id }} · {{ $entry->last_updated_at->format('H:i') }}</p>
                                @else
                                    <span class="text-slate-500">Not touched yet</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap text-right">
                                <button type="button" class="btn-primary btn-sm"
                                    @click="ask({{ $entry->id }}, 'done', @js($entry->activity->title), @js(route('board.update', $entry)), @js($entry->remark ?? ''), {{ $entry->status === 'done' ? 'true' : 'false' }})">{{ $entry->status === 'done' ? 'Add note' : 'Done' }}</button>
                                <button type="button" class="btn-amber btn-sm"
                                    @click="ask({{ $entry->id }}, 'pending', @js($entry->activity->title), @js(route('board.update', $entry)), @js($entry->remark ?? ''), false)">Pending</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-10 text-center text-slate-500">No activities were tracked on this day.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Remark modal --}}
        <div x-show="open" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="absolute inset-0 bg-forest/60" @click="open = false"></div>
            <form method="POST" :action="action" class="card relative w-full max-w-md" x-transition>
                @csrf
                <input type="hidden" name="entry_id" :value="id">
                <input type="hidden" name="status" :value="status">
                @if ($errors->any())
                    <div class="mb-4 rounded-xl bg-overdue-100 px-4 py-3 text-sm font-medium text-overdue-700" role="alert">
                        @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                    </div>
                @endif
                <h2 class="text-lg font-extrabold text-forest" x-text="status === 'done' ? (isCurrentDone ? 'Add note' : 'Mark as done') : 'Mark as pending'"></h2>
                <p class="mb-4 mt-1 text-sm text-slate-500" x-text="title"></p>
                <template x-if="previousRemark">
                    <div class="mb-4 rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs">
                        <span class="mb-1 block font-semibold text-slate-500">Previous remark</span>
                        <p class="whitespace-pre-wrap text-slate-700" x-text="previousRemark"></p>
                    </div>
                </template>
                <label for="remark" class="label">
                    Remark <span x-show="status === 'pending' || isCurrentDone" class="normal-case text-overdue">(required)</span>
                    <span x-show="status === 'done' && !isCurrentDone" class="normal-case text-slate-500">(optional)</span>
                </label>
                <textarea id="remark" name="remark" x-ref="remark" x-model="remark" rows="4" maxlength="1000" class="field"
                          :required="status === 'pending' || isCurrentDone" placeholder="What did you find? What is still blocking?"></textarea>
                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" class="btn-ghost" @click="open = false">Cancel</button>
                    <button class="btn" :class="status === 'done' ? 'bg-brand text-forest hover:bg-brand-600 hover:text-white' : 'bg-pending text-amber-950 hover:bg-amber-400 hover:text-amber-950'"
                            x-text="status === 'done' ? (isCurrentDone ? 'Save note' : 'Mark as done') : 'Save remark'"></button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
