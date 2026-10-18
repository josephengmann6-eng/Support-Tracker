<x-app-layout title="Dashboard" :subtitle="now()->format('l, j F Y')">
    <x-slot name="actions">
        <a href="{{ route('board.index') }}" class="btn-primary">Open Daily Board</a>
    </x-slot>

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card label="Completion today" :value="$stats['percent'].'%'" :hint="$stats['done'].' of '.$stats['total'].' done'">
            <div class="flex h-12 items-end gap-1" aria-label="Last 7 days completion">
                @foreach ($trend->take(-7) as $day)
                    <span class="w-2 rounded-t {{ $loop->last ? 'bg-brand' : 'bg-brand/40' }}"
                          style="height: {{ max(8, $day['percent'] ?? 0) }}%"
                          title="{{ $day['label'] }}: {{ $day['percent'] === null ? 'no data' : $day['percent'].'%' }}"></span>
                @endforeach
            </div>
        </x-ui.stat-card>

        <x-ui.stat-card label="Pending today" :value="$stats['pending']" tone="pending" hint="Still to be checked today" />

        <x-ui.stat-card label="Carried over" :value="$stats['carried']" :tone="$stats['carried'] ? 'overdue' : 'default'" hint="Pending from earlier days" />

        <x-ui.stat-card label="Updates today" :value="$stats['updates']" hint="Status changes logged" />
    </div>

    {{-- Trend + handover hero --}}
    <div class="mt-5 grid grid-cols-1 gap-5 xl:grid-cols-3">
        <div class="card xl:col-span-2">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-extrabold text-forest">Completion trend</h2>
                <span class="text-xs font-medium text-slate-500">Last 14 days · % done</span>
            </div>
            <div class="relative h-64"><canvas id="trendChart" aria-label="Completion trend, last 14 days" role="img"></canvas></div>
        </div>

        <a href="{{ route('handover.index') }}" class="card group flex flex-col !bg-hero text-white transition hover:shadow-lg">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-extrabold">Handover</h2>
                <span class="text-sm font-semibold text-brand group-hover:underline">Open &rarr;</span>
            </div>
            <p class="mt-4 text-6xl font-extrabold leading-none">{{ $handoverCount }}</p>
            <p class="mt-1 text-sm text-white/60">pending item{{ $handoverCount === 1 ? '' : 's' }} from earlier days</p>

            <div class="mt-5 space-y-2.5">
                @forelse ($oldest as $entry)
                    <div class="rounded-xl bg-white/10 px-3.5 py-2.5">
                        <p class="truncate text-sm font-semibold">{{ $entry->activity->title }}</p>
                        <p class="mt-0.5 text-xs {{ $entry->isOverdue() ? 'font-semibold text-red-300' : 'text-white/60' }}">
                            {{ $entry->daysWaiting() }} {{ $entry->daysWaiting() === 1 ? 'day' : 'days' }} waiting · since {{ $entry->dateCarbon()->format('D j M') }}
                        </p>
                    </div>
                @empty
                    <p class="rounded-xl bg-white/10 px-3.5 py-3 text-sm text-white/80">All clear. Nothing carried over.</p>
                @endforelse
            </div>
        </a>
    </div>

    {{-- Today's activities --}}
    <div class="card mt-5 overflow-x-auto !p-4 sm:!p-6">
        <div class="mb-3 flex items-center justify-between">
            <h2 class="text-lg font-extrabold text-forest">Today's activities</h2>
            <a href="{{ route('board.index') }}" class="text-sm font-semibold text-brand-600 hover:underline">Update on board &rarr;</a>
        </div>
        <table class="table-clean min-w-[680px]">
            <thead><tr><th>Activity</th><th>Status</th><th>Updated by</th><th>Time</th><th>Remark</th></tr></thead>
            <tbody>
                @forelse ($entries as $entry)
                    <tr>
                        <td class="font-semibold text-forest">{{ $entry->activity->title }}</td>
                        <td><x-ui.status :status="$entry->status" /></td>
                        <td>{{ $entry->updater?->name ?? '—' }}@if ($entry->updater)<span class="block text-xs text-slate-500">{{ $entry->updater->staff_id }}</span>@endif</td>
                        <td class="whitespace-nowrap">{{ $entry->last_updated_at?->format('H:i') ?? '—' }}</td>
                        <td class="max-w-xs text-slate-600">{{ $entry->remark ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-10 text-center text-slate-500">No activities were tracked for today.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const trend = @json($trend->values());
            new Chart(document.getElementById('trendChart'), {
                type: 'bar',
                data: {
                    labels: trend.map(d => d.label),
                    datasets: [{
                        data: trend.map(d => d.percent),
                        backgroundColor: '#22C55E',
                        hoverBackgroundColor: '#16A34A',
                        borderRadius: 8,
                        maxBarThickness: 34,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { min: 0, max: 100, ticks: { callback: v => v + '%', stepSize: 25, color: '#64748B' }, grid: { color: '#EEF3EF' }, border: { display: false } },
                        x: { ticks: { color: '#64748B', maxRotation: 0, autoSkip: true }, grid: { display: false }, border: { display: false } },
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => { const d = trend[ctx.dataIndex]; return d.percent === null ? 'No data' : d.percent + '% done (' + d.done + ' of ' + d.total + ')'; },
                            },
                        },
                    },
                },
            });
        });
    </script>
</x-app-layout>
