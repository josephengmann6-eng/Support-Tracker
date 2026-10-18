<x-app-layout :title="$activity->exists ? 'Edit activity' : 'New activity'">
    <form method="POST" class="card max-w-2xl space-y-5"
          action="{{ $activity->exists ? route('admin.activities.update', $activity) : route('admin.activities.store') }}">
        @csrf
        @if ($activity->exists) @method('PUT') @endif

        <div>
            <label for="title" class="label">Title</label>
            <input id="title" name="title" class="field" value="{{ old('title', $activity->title) }}" required maxlength="255">
            <x-input-error :messages="$errors->get('title')" class="mt-2" />
        </div>
        <div>
            <label for="description" class="label">Description</label>
            <textarea id="description" name="description" rows="4" class="field" maxlength="2000">{{ old('description', $activity->description) }}</textarea>
            <x-input-error :messages="$errors->get('description')" class="mt-2" />
        </div>
        <label class="flex items-center gap-3 text-sm font-medium text-slate-700">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="h-5 w-5 rounded border-slate-300 text-brand focus:ring-brand" @checked(old('is_active', $activity->is_active))>
            Active (shown on the Daily Board)
        </label>

        <div class="flex gap-2">
            <button class="btn-primary">{{ $activity->exists ? 'Save changes' : 'Create activity' }}</button>
            <a href="{{ route('admin.activities.index') }}" class="btn-ghost">Cancel</a>
        </div>
    </form>
</x-app-layout>
