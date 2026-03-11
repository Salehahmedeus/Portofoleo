<?php

namespace App\Http\Requests\Admin;

use App\Models\SiteSetting;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateSettingRequest extends FormRequest
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
        /** @var SiteSetting $setting */
        $setting = $this->route('setting');

        return [
            'key' => ['required', 'string', 'max:255', Rule::unique('site_settings', 'key')->ignore($setting->id)],
            'group' => ['nullable', 'string', 'max:100'],
            'value' => ['required', 'array'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $value = $this->input('value', []);

            $this->validateSocialContactUrls($validator, is_array($value) ? $value : []);
        });
    }

    /**
     * @param  array<string, mixed>  $value
     */
    private function validateSocialContactUrls(Validator $validator, array $value): void
    {
        foreach (Arr::dot($value) as $field => $fieldValue) {
            $segments = explode('.', $field);
            $lastSegment = strtolower((string) end($segments));

            if (! in_array($lastSegment, ['linkedin', 'github', 'whatsapp'], true)) {
                continue;
            }

            if ($fieldValue === null || $fieldValue === '') {
                continue;
            }

            if (! is_string($fieldValue)) {
                $validator->errors()->add("value.{$field}", 'The '.$lastSegment.' field must be a valid URL string.');

                continue;
            }

            $isValid = match ($lastSegment) {
                'linkedin' => $this->isLinkedInUrl($fieldValue),
                'github' => $this->isGithubUrl($fieldValue),
                'whatsapp' => $this->isWhatsappUrl($fieldValue),
                default => true,
            };

            if (! $isValid) {
                $validator->errors()->add("value.{$field}", 'The '.$lastSegment.' field format is invalid.');
            }
        }
    }

    private function isLinkedInUrl(string $url): bool
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $path = (string) parse_url($url, PHP_URL_PATH);

        return $this->matchesHost($host, 'linkedin.com') && $path !== '' && $path !== '/';
    }

    private function isGithubUrl(string $url): bool
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');

        return $this->matchesHost($host, 'github.com') && $path !== '';
    }

    private function isWhatsappUrl(string $url): bool
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
        $query = (string) parse_url($url, PHP_URL_QUERY);

        if ($host === 'wa.me') {
            return (bool) preg_match('/^\d{7,15}$/', $path);
        }

        if ($host === 'api.whatsapp.com') {
            parse_str($query, $queryParams);

            return $path === 'send' && preg_match('/^\d{7,15}$/', (string) ($queryParams['phone'] ?? '')) === 1;
        }

        return in_array($host, ['whatsapp.com', 'www.whatsapp.com'], true);
    }

    private function matchesHost(string $host, string $domain): bool
    {
        return $host === $domain || str_ends_with($host, '.'.$domain);
    }
}
