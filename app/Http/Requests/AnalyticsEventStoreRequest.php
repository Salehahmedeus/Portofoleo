<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnalyticsEventStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'event_type' => ['required', 'string', 'max:100'],
            'event_data' => ['nullable', 'array'],
            'page_url' => ['required', 'string', 'max:2048'],
            'referrer' => ['nullable', 'string', 'max:2048'],
            'device_type' => ['nullable', Rule::in(['desktop', 'mobile', 'tablet'])],
            'country' => ['nullable', 'string', 'size:2', 'alpha'],
            'session_id' => ['nullable', 'string', 'max:255'],
        ];
    }
}
