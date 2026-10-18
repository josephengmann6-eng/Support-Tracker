@props(['route', 'date'])
<div {{ $attributes->merge(['class' => 'card flex flex-wrap items-center gap-3 !p-4']) }}>
    <a href="{{ route($route, ['date' => $date->subDay()->toDateString()]) }}" class="btn-ghost btn-sm focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2" aria-label="Previous day">&larr;</a>
    <form method="GET" action="{{ route($route) }}" class="flex items-center gap-2">
        <input type="date" name="date" value="{{ $date->toDateString() }}" max="{{ today()->toDateString() }}" class="field !w-auto focus:ring-2 focus:ring-brand focus:ring-offset-2" onchange="this.form.submit()" aria-label="Pick a date">
    </form>
    @if ($date->isToday())
        <span class="btn btn-sm pointer-events-none bg-brand-100 text-[#166534]">Today</span>
        <span class="btn-ghost btn-sm pointer-events-none opacity-40" aria-hidden="true">&rarr;</span>
    @else
        <a href="{{ route($route, ['date' => $date->addDay()->toDateString()]) }}" class="btn-ghost btn-sm focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2" aria-label="Next day">&rarr;</a>
    @endif
    <div class="ml-auto text-sm text-slate-500">{{ $slot }}</div>
</div>
