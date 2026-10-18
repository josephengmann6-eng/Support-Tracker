<x-app-layout title="Team" subtitle="Accounts are created here; there is no public registration">
    <x-slot name="actions">
        <a href="{{ route('admin.users.create') }}" class="btn-primary">Add team member</a>
    </x-slot>

    @error('toggle')
        <div class="mb-4 rounded-xl bg-overdue-100 px-4 py-3 text-sm font-medium text-overdue-700" role="alert">{{ $message }}</div>
    @enderror

    <div class="card overflow-x-auto !p-2 sm:!p-4">
        <table class="table-clean min-w-[820px]">
            <thead><tr><th>Member</th><th>Staff ID</th><th>Department</th><th>Role</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
                @foreach ($users as $u)
                    <tr class="{{ $u->is_active ? '' : 'opacity-60' }}">
                        <td>
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-forest text-xs font-bold text-white">{{ $u->initials() }}</span>
                                <div>
                                    <p class="font-semibold text-forest">{{ $u->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $u->email }}@if ($u->phone) · {{ $u->phone }}@endif</p>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap font-medium">{{ $u->staff_id }}</td>
                        <td>{{ $u->department ?? '—' }}</td>
                        <td>@if ($u->isAdmin())<span class="pill bg-forest text-white">Admin</span>@else<span class="pill-muted">Member</span>@endif</td>
                        <td>@if ($u->is_active)<span class="pill-done">Active</span>@else<span class="pill-muted">Inactive</span>@endif</td>
                        <td class="whitespace-nowrap text-right">
                            <a href="{{ route('admin.users.edit', $u) }}" class="btn-ghost btn-sm">Edit</a>
                            @unless ($u->is(auth()->user()))
                                <form method="POST" action="{{ route('admin.users.toggle', $u) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <button class="btn-ghost btn-sm">{{ $u->is_active ? 'Deactivate' : 'Activate' }}</button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>
