<x-app-layout title="Activity Feed" :subtitle="$date->format('l, j F Y')">
    <x-ui.date-nav route="feed.index" :date="$date" class="mb-6">
        <span class="font-semibold text-forest">{{ $updates->count() }}</span> update{{ $updates->count() === 1 ? '' : 's' }}
        · <span class="font-semibold text-brand-600">{{ $updates->filter->isCompletion()->count() }}</span> completed
    </x-ui.date-nav>

    @if ($updates->isEmpty())
        <div class="card py-14 text-center text-slate-500">Nothing was updated on this day.</div>
    @else
        <ol class="relative space-y-4 pl-8 sm:pl-12">
            <span class="absolute bottom-3 left-[11px] top-3 w-1 rounded-full bg-slate-200 sm:left-[19px]" aria-hidden="true"></span>

            @foreach ($updates as $u)
                @php
                    $isDone = $u->new_status === 'done';
                    $stale = $u->entry->date <= today()->subDays(\App\Models\DailyEntry::OVERDUE_AFTER_DAYS)->toDateString();
                    $tone = $isDone ? 'brand' : ($stale ? 'overdue' : 'pending');
                    $border = ['brand' => 'border-brand', 'pending' => 'border-pending', 'overdue' => 'border-overdue'][$tone];
                    $dot = ['brand' => 'bg-brand', 'pending' => 'bg-pending', 'overdue' => 'bg-overdue'][$tone];
                    $timeColor = ['brand' => 'text-brand-600', 'pending' => 'text-pending-700', 'overdue' => 'text-overdue-700'][$tone];
                @endphp
                <li class="relative">
                    <span class="absolute -left-[29px] top-6 h-5 w-5 rounded-full border-4 border-canvas sm:-left-[39px] sm:h-6 sm:w-6 {{ $dot }}" aria-hidden="true"></span>
                    <article class="card flex flex-col gap-3 border-l-8 !p-5 sm:flex-row sm:items-start sm:gap-6 {{ $border }}">
                        <div class="shrink-0 sm:w-28">
                            <p class="text-4xl font-extrabold leading-none tracking-tight {{ $timeColor }}">{{ $u->created_at->format('H:i') }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $u->created_at->format('H:i:s') }} GMT</p>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-lg font-bold text-forest">{{ $u->entry->activity->title }}</p>
                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                @if ($u->old_status === $u->new_status)
                                    <span class="pill-muted">Note added</span>
                                @else
                                    <span class="{{ $u->old_status === 'done' ? 'pill-done' : 'pill-pending' }} !opacity-70">{{ ucfirst($u->old_status) }}</span>
                                    <span class="text-slate-500" aria-label="to">&rarr;</span>
                                    <span class="{{ $isDone ? 'pill-done' : ($stale ? 'pill-overdue' : 'pill-pending') }}">{{ ucfirst($u->new_status) }}</span>
                                @endif
                                @if ($u->entry->date !== $date->toDateString())
                                    <span class="pill-muted">for {{ \Carbon\Carbon::parse($u->entry->date)->format('D j M') }}</span>
                                @endif
                            </div>
                            <p class="mt-3 text-sm text-slate-600">
                                @if ($u->remark) “{{ $u->remark }}” @else <span class="text-slate-500">No remark</span> @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-3 sm:w-48 sm:justify-end">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-forest text-sm font-bold text-white">
                                {{ collect(explode(' ', $u->user_name))->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') }}
                            </span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-forest">{{ $u->user_name }}</p>
                                <p class="text-xs text-slate-500">{{ $u->user_staff_id }}</p>
                            </div>
                        </div>
                    </article>
                </li>
            @endforeach
        </ol>
    @endif
</x-app-layout>
