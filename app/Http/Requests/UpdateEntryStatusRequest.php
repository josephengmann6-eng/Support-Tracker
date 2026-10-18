<?php

namespace App\Http\Requests;

use App\Models\DailyEntry;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEntryStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Any active signed-in user may update statuses, but not for deactivated activities.
        $entry = $this->route('entry');

        return $this->user() !== null && $entry->activity->is_active;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([DailyEntry::PENDING, DailyEntry::DONE])],
            'remark' => [
                Rule::requiredIf(fn () => $this->input('status') === DailyEntry::PENDING),
                'nullable', 'string', 'max:1000',
            ],
        ];
    }

    public function messages(): array
    {
        return ['remark.required' => 'A remark is required when marking an activity as pending.'];
    }

    public function after(): array
    {
        return [function ($validator) {
            $entry = $this->route('entry');

            if ($this->input('status') === $entry->status && ! filled($this->input('remark'))) {
                $validator->errors()->add('status', 'This activity is already '.$entry->status.'; add a remark to log a note.');
            }
        }];
    }
}
