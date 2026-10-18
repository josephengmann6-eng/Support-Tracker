@props(['status', 'overdue' => false])
@if ($status === 'done')
    <span {{ $attributes->merge(['class' => 'pill-done']) }}><span class="h-1.5 w-1.5 rounded-full bg-brand"></span> Done</span>
@elseif ($overdue)
    <span {{ $attributes->merge(['class' => 'pill-overdue']) }}><span class="h-1.5 w-1.5 rounded-full bg-overdue"></span> Overdue</span>
@else
    <span {{ $attributes->merge(['class' => 'pill-pending']) }}><span class="h-1.5 w-1.5 rounded-full bg-pending"></span> Pending</span>
@endif
