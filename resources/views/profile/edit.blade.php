<x-app-layout title="Account">
    <div class="card max-w-xl">
        <div class="mb-6 flex items-center gap-4">
            <span class="flex h-14 w-14 items-center justify-center rounded-full bg-brand text-lg font-bold text-forest">{{ $user->initials() }}</span>
            <div>
                <p class="text-lg font-bold text-forest">{{ $user->name }}</p>
                <p class="text-sm text-slate-500">{{ $user->staff_id }} · {{ $user->email }} · {{ $user->department }}</p>
            </div>
        </div>
        @include('profile.partials.update-password-form')
    </div>
</x-app-layout>
