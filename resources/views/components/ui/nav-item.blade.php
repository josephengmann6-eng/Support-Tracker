@props(['href', 'active' => false, 'icon'])
<a href="{{ $href }}"
   @if ($active) aria-current="page" @endif
   {{ $attributes->class([
       'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 focus:ring-offset-forest',
       'bg-forest-600 text-white' => $active,
       'text-white/70 hover:bg-forest-700 hover:text-white' => ! $active,
   ]) }}>
    <svg class="h-5 w-5 shrink-0 {{ $active ? 'text-brand' : '' }}" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="{{ $icon }}"/></svg>
    {{ $slot }}
</a>
