<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

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
            'slug' => ['nullable', 'string', 'alpha_dash', 'max:255', Rule::unique('projects', 'slug')],
            'type' => ['required', Rule::in(['development', 'uiux'])],
            'summary' => ['required', 'string', 'max:5000'],
            'featured' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'thumbnail' => ['nullable', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(5 * 1024)],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'details' => ['nullable', 'array'],
            'details.*.field_name' => ['required', 'string', 'max:100'],
            'details.*.field_value' => ['nullable', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! is_array($value) && ! is_string($value) && ! is_int($value) && ! is_float($value) && ! is_bool($value) && $value !== null) {
                    $fail('The '.$attribute.' field must be an array, string, number, boolean, or null.');
                }
            }],
            'links' => ['nullable', 'array'],
            'links.live_demo' => ['nullable', 'url', 'max:2048'],
            'links.github' => ['nullable', 'url', 'max:2048'],
            'links.prototype' => ['nullable', 'url', 'max:2048'],
            'images' => ['nullable', 'array', 'max:20'],
            'images.*.file' => ['required', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(5 * 1024)],
            'images.*.alt_text' => ['nullable', 'string', 'max:255'],
            'images.*.type' => ['nullable', Rule::in(['gallery', 'wireframe', 'screenshot', 'thumbnail'])],
            'images.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
