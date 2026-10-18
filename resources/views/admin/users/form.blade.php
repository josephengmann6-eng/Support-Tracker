<x-app-layout :title="$member->exists ? 'Edit team member' : 'Add team member'">
    @php($self = $member->exists && $member->is(auth()->user()))
    <form method="POST" class="card max-w-3xl"
          action="{{ $member->exists ? route('admin.users.update', $member) : route('admin.users.store') }}">
        @csrf
        @if ($member->exists) @method('PUT') @endif

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <label for="name" class="label">Full name</label>
                <input id="name" name="name" class="field" value="{{ old('name', $member->name) }}" required>
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>
            <div>
                <label for="staff_id" class="label">Staff ID</label>
                <input id="staff_id" name="staff_id" class="field" value="{{ old('staff_id', $member->staff_id) }}" required placeholder="STF-1234">
                <x-input-error :messages="$errors->get('staff_id')" class="mt-2" />
            </div>
            <div>
                <label for="email" class="label">Email</label>
                <input id="email" name="email" type="email" class="field" value="{{ old('email', $member->email) }}" required>
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>
            <div>
                <label for="phone" class="label">Phone</label>
                <input id="phone" name="phone" class="field" value="{{ old('phone', $member->phone) }}">
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>
            <div>
                <label for="department" class="label">Department</label>
                <input id="department" name="department" class="field" value="{{ old('department', $member->department) }}">
                <x-input-error :messages="$errors->get('department')" class="mt-2" />
            </div>
            <div>
                <label for="role" class="label">Role</label>
                <select id="role" name="role" class="field" @disabled($self)>
                    @foreach (['member' => 'Member (update statuses)', 'admin' => 'Admin (manage activities and team)'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('role', $member->role) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @if ($self)<input type="hidden" name="role" value="{{ $member->role }}">@endif
                <x-input-error :messages="$errors->get('role')" class="mt-2" />
            </div>
            <div>
                <label for="password" class="label">Password @if ($member->exists)<span class="normal-case text-slate-500">(leave blank to keep)</span>@endif</label>
                <input id="password" name="password" type="password" class="field" autocomplete="new-password" @required(! $member->exists)>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
            <div>
                <label for="password_confirmation" class="label">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="field" autocomplete="new-password" @required(! $member->exists)>
            </div>
        </div>

        <label class="mt-5 flex items-center gap-3 text-sm font-medium text-slate-700">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="h-5 w-5 rounded border-slate-300 text-brand focus:ring-brand"
                   @checked(old('is_active', $member->is_active)) @disabled($self)>
            Active (can log in)
        </label>
        @if ($self)<input type="hidden" name="is_active" value="1">@endif

        <div class="mt-6 flex gap-2">
            <button class="btn-primary">{{ $member->exists ? 'Save changes' : 'Create account' }}</button>
            <a href="{{ route('admin.users.index') }}" class="btn-ghost">Cancel</a>
        </div>
    </form>
</x-app-layout>
