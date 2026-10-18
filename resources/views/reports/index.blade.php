<x-app-layout title="Reports" :subtitle="$filters->from->format('j M Y').($filters->from->equalTo($filters->to) ? '' : ' – '.$filters->to->format('j M Y'))">
    <form method="GET" action="{{ route('reports.index') }}" class="card mb-6 space-y-4"
          x-data="{ preset: @js($filters->preset) }">
        <input type="hidden" name="preset" :value="preset">

        <div class="flex flex-wrap items-center gap-2">
            @foreach (\App\Support\ReportFilters::PRESETS as $key => $label)
                <button type="{{ $key === 'custom' ? 'button' : 'submit' }}"
                        @click="preset = '{{ $key }}'"
                        :class="preset === '{{ $key }}' ? 'bg-forest text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="rounded-full px-4 py-1.5 text-sm font-semibold transition">{{ $label }}</button>
            @endforeach

            <div x-show="preset === 'custom'" x-cloak class="flex flex-wrap items-center gap-2">
                <input type="date" name="from" value="{{ $filters->from->toDateString() }}" max="{{ today()->toDateString() }}" class="field !w-auto" aria-label="From date">
                <span class="text-slate-500">to</span>
                <input type="date" name="to" value="{{ $filters->to->toDateString() }}" max="{{ today()->toDateString() }}" class="field !w-auto" aria-label="To date">
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label for="user_id" class="label">Person</label>
                <select id="user_id" name="user_id" class="field">
                    <option value="">Everyone</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}" @selected($filters->userId === $u->id)>{{ $u->name }} ({{ $u->staff_id }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="activity_id" class="label">Activity</label>
                <select id="activity_id" name="activity_id" class="field">
                    <option value="">All activities</option>
                    @foreach ($activities as $a)
                        <option value="{{ $a->id }}" @selected($filters->activityId === $a->id)>{{ $a->title }}{{ $a->is_active ? '' : ' (inactive)' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="label">Changed to</label>
                <select id="status" name="status" class="field">
                    <option value="">Any status</option>
                    <option value="done" @selected($filters->status === 'done')>Done</option>
                    <option value="pending" @selected($filters->status === 'pending')>Pending</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button class="btn-primary flex-1">Apply filters</button>
                <a href="{{ route('reports.index') }}" class="btn-ghost">Reset</a>
            </div>
        </div>
    </form>

    {{-- Summary counts --}}
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-5">
        <x-ui.stat-card label="Updates" :value="$summary['total']" />
        <x-ui.stat-card label="Marked done" :value="$summary['done']" />
        <x-ui.stat-card label="Marked pending" :value="$summary['pending']" tone="pending" />
        <x-ui.stat-card label="People" :value="$summary['people']" />
        <x-ui.stat-card label="Items touched" :value="$summary['entries']" class="col-span-2 lg:col-span-1" />
    </div>

    <div class="card overflow-x-auto !p-2 sm:!p-4">
        <table class="table-clean min-w-[800px]">
            <thead><tr><th>Date</th><th>Time</th><th>Activity</th><th>Person</th><th>Change</th><th>Remark</th></tr></thead>
            <tbody>
                @forelse ($updates as $u)
                    <tr>
                        <td class="whitespace-nowrap font-medium">{{ $u->created_at->format('D j M Y') }}</td>
                        <td class="whitespace-nowrap font-bold text-forest">{{ $u->created_at->format('H:i') }}</td>
                        <td class="max-w-xs font-semibold text-forest">
                            {{ $u->entry->activity->title }}
                            @if ($u->entry->date !== $u->created_at->toDateString())
                                <span class="block text-xs font-normal text-slate-500">for {{ \Carbon\Carbon::parse($u->entry->date)->format('D j M') }}</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap">{{ $u->user_name }}<span class="block text-xs text-slate-500">{{ $u->user_staff_id }}</span></td>
                        <td class="whitespace-nowrap">
                            @if ($u->old_status === $u->new_status)
                                <span class="pill-muted">Note added</span>
                            @else
                                <span class="{{ $u->old_status === 'done' ? 'pill-done' : 'pill-pending' }} !opacity-70">{{ ucfirst($u->old_status) }}</span>
                                <span class="mx-1 text-slate-500">&rarr;</span>
                                <span class="{{ $u->new_status === 'done' ? 'pill-done' : 'pill-pending' }}">{{ ucfirst($u->new_status) }}</span>
                            @endif
                        </td>
                        <td class="max-w-xs text-slate-600">{{ $u->remark ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-12 text-center text-slate-500">No updates match these filters.</td></tr>
                @endforelse
            </tbody>
        </table>

        @if ($updates->hasPages())
            <div class="border-t border-slate-100 px-3 pt-4">{{ $updates->links('pagination.green') }}</div>
        @endif
    </div>
</x-app-layout>
