<x-app-layout title="Activities" subtitle="The daily checks your team tracks">
    <x-slot name="actions">
        <a href="{{ route('admin.activities.create') }}" class="btn-primary">New activity</a>
    </x-slot>

    <div class="card overflow-x-auto !p-2 sm:!p-4">
        <table class="table-clean min-w-[720px]">
            <thead><tr><th>Activity</th><th>Status</th><th>Days tracked</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
                @foreach ($activities as $activity)
                    <tr class="{{ $activity->is_active ? '' : 'opacity-60' }}">
                        <td class="max-w-md">
                            <p class="font-semibold text-forest">{{ $activity->title }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">{{ $activity->description }}</p>
                        </td>
                        <td>@if ($activity->is_active)<span class="pill-done">Active</span>@else<span class="pill-muted">Inactive</span>@endif</td>
                        <td>{{ $activity->entries_count }}</td>
                        <td class="whitespace-nowrap text-right">
                            <a href="{{ route('admin.activities.edit', $activity) }}" class="btn-ghost btn-sm">Edit</a>
                            <form method="POST" action="{{ route('admin.activities.toggle', $activity) }}" class="inline">
                                @csrf @method('PATCH')
                                <button class="btn-ghost btn-sm">{{ $activity->is_active ? 'Deactivate' : 'Activate' }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p class="px-3 pb-2 pt-4 text-xs text-slate-500">Activities are deactivated, never deleted, so past entries and reports stay intact.</p>
    </div>
</x-app-layout>
