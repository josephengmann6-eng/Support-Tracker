<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} · {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{ nav: false }" @keydown.escape.window="nav = false">
    @php
        $user = auth()->user();
        $links = [
            ['Dashboard', 'dashboard', 'dashboard*', 'M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10'],
            ['Daily Board', 'board.index', 'board*', 'M9 5h10M9 12h10M9 19h10M4 5h.01M4 12h.01M4 19h.01'],
            ['Activity Feed', 'feed.index', 'feed*', 'M12 8v4l3 2M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['Handover', 'handover.index', 'handover*', 'M8 7h12M8 7l3-3M8 7l3 3M16 17H4M16 17l-3-3M16 17l-3 3'],
            ['Reports', 'reports.index', 'reports*', 'M4 20V10M10 20V4M16 20v-8M22 20H2'],
        ];
        $adminLinks = [
            ['Activities', 'admin.activities.index', 'admin/activities*', 'M9 11l3 3L22 4M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11'],
            ['Team', 'admin.users.index', 'admin/users*', 'M17 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M10 10a4 4 0 100-8 4 4 0 000 8zM21 20v-2a4 4 0 00-3-3.9'],
        ];
    @endphp

    {{-- Mobile top bar --}}
    <header class="sticky top-0 z-30 flex items-center justify-between bg-forest px-4 py-3 text-white lg:hidden">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-bold rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 focus:ring-offset-forest"><img src="{{ asset('images/logo.svg') }}" alt="SupportTracker" class="h-7 w-auto"> {{ config('app.name') }}</a>
        <button @click="nav = !nav" class="rounded-lg p-2 hover:bg-forest-700 focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 focus:ring-offset-forest" aria-label="Toggle menu">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
    </header>
    <div x-show="nav" x-cloak @click="nav = false" class="fixed inset-0 z-40 bg-black/40 lg:hidden"></div>

    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col bg-forest p-5 text-white transition-transform lg:translate-x-0"
           :class="{ '!translate-x-0': nav }">
        <a href="{{ route('dashboard') }}" class="mb-8 flex items-center gap-3 px-2 text-lg font-extrabold tracking-tight rounded-xl focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 focus:ring-offset-forest">
            <img src="{{ asset('images/logo.svg') }}" alt="SupportTracker" class="h-8 w-auto"> {{ config('app.name') }}
        </a>

        <nav class="flex-1 space-y-1 overflow-y-auto">
            @foreach ($links as [$label, $route, $pattern, $icon])
                @if (Route::has($route))
                    <x-ui.nav-item :href="route($route)" :active="request()->is($pattern)" :icon="$icon">{{ $label }}</x-ui.nav-item>
                @endif
            @endforeach

            @if ($user->isAdmin())
                <p class="px-3 pb-1 pt-6 text-[11px] font-semibold uppercase tracking-widest text-white/40">Admin</p>
                @foreach ($adminLinks as [$label, $route, $pattern, $icon])
                    @if (Route::has($route))
                        <x-ui.nav-item :href="route($route)" :active="request()->is($pattern)" :icon="$icon">{{ $label }}</x-ui.nav-item>
                    @endif
                @endforeach
            @endif
        </nav>

        <div class="mt-4 rounded-2xl bg-forest-700 p-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('profile.edit') }}" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand text-sm font-bold text-forest focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 focus:ring-offset-forest" title="Account settings">{{ $user->initials() }}</a>
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold">{{ $user->name }}</p>
                    <p class="truncate text-xs text-white/60">{{ $user->staff_id }} · {{ ucfirst($user->role) }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button class="w-full rounded-lg bg-white/10 px-3 py-1.5 text-xs font-semibold hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 focus:ring-offset-forest">Log out</button>
            </form>
        </div>
    </aside>

    {{-- Main --}}
    <main class="px-4 py-6 lg:ml-64 lg:px-10 lg:py-8">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-forest lg:text-3xl">{{ $title ?? 'Dashboard' }}</h1>
                @isset($subtitle)<p class="mt-0.5 text-sm text-slate-500">{{ $subtitle }}</p>@endisset
            </div>
            <div class="flex items-center gap-3">
                @php($overdueCount = $overdueCount ?? 0)
                <a href="{{ Route::has('handover.index') ? route('handover.index') : '#' }}" class="relative rounded-full bg-white p-2.5 shadow-soft hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2" title="{{ $overdueCount }} overdue item(s)">
                    <svg class="h-5 w-5 text-forest" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 11-6 0"/></svg>
                    @if ($overdueCount > 0)
                        <span class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-overdue px-1 text-[10px] font-bold text-white">{{ $overdueCount }}</span>
                    @endif
                </a>
                {{ $actions ?? '' }}
            </div>
        </div>

        @if (session('status'))
            <div class="mb-4 rounded-xl bg-brand-100 px-4 py-3 text-sm font-medium text-[#166534]" role="status">{{ session('status') }}</div>
        @endif

        {{ $slot }}
    </main>
</body>
</html>
