<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'key' => ['required', 'string', Rule::in(['hero_content', 'contact_information', 'homepage_seo', 'services', 'skills'])],
            'group' => ['nullable', 'string', 'max:100'],
            'value' => ['required', 'array'],
            'value.items' => [
                Rule::requiredIf(fn (): bool => in_array($this->input('key'), ['services', 'skills'], true)),
                'array',
            ],
            'value.items.*.title' => [
                Rule::requiredIf(fn (): bool => $this->input('key') === 'services'),
                'string',
                'max:255',
            ],
            'value.items.*.description' => [
                Rule::requiredIf(fn (): bool => $this->input('key') === 'services'),
                'string',
                'max:5000',
            ],
            'value.items.*.icon' => [
                'nullable',
                'string',
                'max:100',
            ],
            'value.items.*.sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'value.items.*.name' => [
                Rule::requiredIf(fn (): bool => $this->input('key') === 'skills'),
                'string',
                'max:255',
            ],
            'value.items.*.category' => [
                Rule::requiredIf(fn (): bool => $this->input('key') === 'skills'),
                'string',
                'max:100',
            ],
            'value.items.*.logo_path' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }
}
