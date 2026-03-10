<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'alpha_dash', 'max:255', Rule::unique('projects', 'slug')],
            'type' => ['required', Rule::in(['development', 'uiux'])],
            'summary' => ['required', 'string', 'max:5000'],
            'featured' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'details' => ['nullable', 'array'],
            'details.*.field_name' => ['required', 'string', 'max:100'],
            'details.*.field_value' => ['nullable', 'array'],
            'images' => ['nullable', 'array', 'max:20'],
            'images.*.file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'images.*.alt_text' => ['nullable', 'string', 'max:255'],
            'images.*.type' => ['nullable', Rule::in(['gallery', 'wireframe', 'screenshot', 'thumbnail'])],
            'images.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
