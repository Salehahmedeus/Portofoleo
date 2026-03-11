<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectCardResource;
use App\Http\Resources\ServiceResource;
use App\Http\Resources\SkillResource;
use App\Models\Project;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Skill;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    private const string CACHE_KEY = 'public_api:home';

    public function __invoke(): JsonResponse
    {
        $payload = Cache::remember(self::CACHE_KEY, now()->addMinutes(5), function (): array {
            $heroContent = $this->settingValueByKey('hero_content');
            $contactInfo = $this->settingValueByKey('contact_information');
            $socialLinks = $this->settingValueByKey('social_links');

            if ($socialLinks === []) {
                $socialKeys = ['linkedin', 'github', 'x', 'twitter', 'instagram', 'facebook', 'youtube', 'dribbble', 'behance'];

                foreach ($socialKeys as $socialKey) {
                    if (array_key_exists($socialKey, $contactInfo)) {
                        $socialLinks[$socialKey] = $contactInfo[$socialKey];
                        unset($contactInfo[$socialKey]);
                    }
                }
            }

            return [
                'hero_content' => $heroContent,
                'featured_projects' => ProjectCardResource::collection(
                    Project::query()
                        ->where('featured', true)
                        ->orderBy('sort_order')
                        ->get()
                )->resolve(),
                'services' => ServiceResource::collection(
                    Service::query()->orderBy('sort_order')->get()
                )->resolve(),
                'skills' => SkillResource::collection(
                    Skill::query()->orderBy('category')->orderBy('sort_order')->get()
                )->resolve(),
                'contact_info' => $contactInfo,
                'social_links' => $socialLinks,
            ];
        });

        return response()->json($payload);
    }

    /**
     * @return array<string, mixed>
     */
    private function settingValueByKey(string $key): array
    {
        $value = SiteSetting::query()->where('key', $key)->value('value');

        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->reject(fn (mixed $item): bool => $item === null || $item === '' || $item === [])
            ->all();
    }
}
