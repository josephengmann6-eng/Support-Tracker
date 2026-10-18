<x-guest-layout>
    <h1 class="text-xl font-extrabold text-forest">Welcome back</h1>
    <p class="mb-6 mt-1 text-sm text-slate-500">Sign in with your work email. Accounts are created by an administrator.</p>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <div>
            <label for="email" class="label">Email</label>
            <input id="email" name="email" type="email" class="field" value="{{ old('email') }}" required autofocus autocomplete="username">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>
        <div>
            <label for="password" class="label">Password</label>
            <input id="password" name="password" type="password" class="field" required autocomplete="current-password">
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>
        <label class="inline-flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" class="rounded border-slate-300 text-brand focus:ring-brand"> Remember me
        </label>
        <button class="btn-primary w-full">Log in</button>
    </form>
</x-guest-layout>
