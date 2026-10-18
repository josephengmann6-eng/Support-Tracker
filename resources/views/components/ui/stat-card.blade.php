@props(['label', 'value', 'hint' => null, 'tone' => 'default'])
@php
    $valueColor = ['default' => 'text-forest', 'pending' => 'text-pending-700', 'overdue' => 'text-overdue'][$tone] ?? 'text-forest';
@endphp
<div {{ $attributes->merge(['class' => 'card flex flex-col justify-between']) }}>
    <p class="card-title">{{ $label }}</p>
    <div class="mt-3 flex items-end justify-between gap-3">
        <div>
            <p class="text-4xl font-extrabold leading-none tracking-tight {{ $valueColor }}">{{ $value }}</p>
            @if ($hint)<p class="mt-2 text-xs text-slate-500">{{ $hint }}</p>@endif
        </div>
        {{ $slot }}
    </div>
</div>
