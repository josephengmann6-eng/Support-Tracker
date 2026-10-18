<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign in · {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="flex min-h-screen flex-col items-center justify-center px-4">
        <div class="mb-6 flex items-center gap-3 text-2xl font-extrabold tracking-tight text-forest">
            <x-ui.logo class="h-11 w-11" /> {{ config('app.name') }}
        </div>
        <div class="card w-full max-w-md !p-8">{{ $slot }}</div>
        <p class="mt-6 text-xs text-slate-500">Shift handover for application support teams</p>
    </div>
</body>
</html>
