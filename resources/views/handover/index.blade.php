<x-app-layout title="Handover" subtitle="Pending work carried over from earlier days">
    <x-slot name="actions">
        <button type="button" class="btn-primary" x-data="{ copied: false }"
                @click="
                    const text = @js($summary);
                    const done = () => { copied = true; setTimeout(() => copied = false, 2000) };
                    if (navigator.clipboard && window.isSecureContext) { navigator.clipboard.writeText(text).then(done) }
                    else { const t = document.createElement('textarea'); t.value = text; document.body.appendChild(t); t.select(); document.execCommand('copy'); t.remove(); done() }">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V6a2 2 0 012-2h9"/></svg>
            <span x-text="copied ? 'Copied!' : 'Copy handover summary'">Copy handover summary</span>
        </button>
    </x-slot>

    <div class="card mb-6 flex flex-wrap items-center gap-6 !bg-hero text-white">
        <div>
            <p class="text-sm text-white/60">Pending from earlier days</p>
            <p class="text-5xl font-extrabold leading-tight">{{ $pending->count() }}</p>
        </div>
        <div>
            <p class="text-sm text-white/60">Overdue (2+ days)</p>
            <p class="text-5xl font-extrabold leading-tight {{ $pending->filter->isOverdue()->count() ? 'text-red-300' : '' }}">{{ $pending->filter->isOverdue()->count() }}</p>
        </div>
    </div>

    @forelse ($groups as $group)
        <section class="card mb-6 overflow-x-auto !p-2 sm:!p-4">
            <h2 class="flex items-center gap-2 px-3 py-2 text-base font-extrabold {{ $group['overdue'] ? 'text-overdue-700' : 'text-forest' }}">
                {{ $group['label'] }}
                <span class="{{ $group['overdue'] ? 'pill-overdue' : 'pill-pending' }}">{{ $group['entries']->count() }}</span>
            </h2>
            <table class="table-clean min-w-[820px]">
                <thead>
                    <tr><th>Activity</th><th>First pending</th><th>Days waiting</th><th>Last remark</th><th>Last touched by</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach ($group['entries'] as $entry)
                        @php($days = $entry->daysWaiting())
                        <tr>
                            <td class="max-w-xs font-semibold text-forest">{{ $entry->activity->title }}</td>
                            <td class="whitespace-nowrap">{{ $entry->dateCarbon()->format('D j M Y') }}</td>
                            <td>
                                <span class="{{ $entry->isOverdue() ? 'pill-overdue' : 'pill-pending' }}">{{ $days }} {{ $days === 1 ? 'day' : 'days' }}</span>
                            </td>
                            <td class="max-w-xs text-slate-600">{{ $entry->remark ?? '—' }}</td>
                            <td class="whitespace-nowrap">
                                @if ($entry->updater)
                                    <p class="font-medium text-slate-700">{{ $entry->updater->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $entry->updater->staff_id }} · {{ $entry->last_updated_at->format('j M H:i') }}</p>
                                @else
                                    <span class="text-slate-500">Nobody yet</span>
                                @endif
                            </td>
                            <td class="text-right"><a href="{{ route('board.index', ['date' => $entry->date]) }}" class="btn-ghost btn-sm whitespace-nowrap">Update on board</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    @empty
        <div class="card py-14 text-center">
            <p class="text-2xl font-extrabold text-forest">All clear</p>
            <p class="mt-1 text-slate-500">Nothing is outstanding from earlier days.</p>
        </div>
    @endforelse
</x-app-layout>
