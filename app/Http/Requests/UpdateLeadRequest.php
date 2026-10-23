<?php

namespace App\Http\Requests;

use App\Enums\LeadStatus;
use App\Rules\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('lead')) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => Str::lower(trim((string) $this->input('email'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('leads', 'email')->ignore($this->route('lead')),
            ],
            'phone' => ['required', 'string', 'max:30', new PhoneNumber],
            'status' => ['required', Rule::enum(LeadStatus::class)],
        ];
    }
}
