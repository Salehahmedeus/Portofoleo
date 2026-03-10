<?php

namespace App\Support;

use App\Models\Project;
use App\Models\ProjectDetail;
use App\Models\ProjectImage;
use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class PortfolioData
{
    /**
     * @return array<string, mixed>
     */
    public function settingsByGroup(string $group): array
    {
        return SiteSetting::query()
            ->where('group', $group)
            ->orderBy('key')
            ->get()
            ->mapWithKeys(fn (SiteSetting $setting): array => [$setting->key => $this->normalizeSettingValue($setting->value)])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function settingsByKey(): array
    {
        return SiteSetting::query()
            ->orderBy('group')
            ->orderBy('key')
            ->get()
            ->mapWithKeys(fn (SiteSetting $setting): array => [$setting->key => $this->normalizeSettingValue($setting->value)])
            ->all();
    }

    /**
     * @param  EloquentCollection<int, Project>  $projects
     * @return list<array<string, mixed>>
     */
    public function transformProjects(EloquentCollection $projects, bool $includeRelations = true): array
    {
        return $projects
            ->map(fn (Project $project): array => $this->transformProject($project, $includeRelations))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function transformProject(Project $project, bool $includeRelations = true): array
    {
        $payload = [
            'id' => $project->id,
            'title' => $project->title,
            'slug' => $project->slug,
            'type' => $project->type,
            'summary' => $project->summary,
            'featured' => $project->featured,
            'sort_order' => $project->sort_order,
            'thumbnail_path' => $project->thumbnail_path,
            'thumbnail_url' => $this->toPublicUrl($project->thumbnail_path),
            'meta_title' => $project->meta_title,
            'meta_description' => $project->meta_description,
            'created_at' => $project->created_at?->toISOString(),
            'updated_at' => $project->updated_at?->toISOString(),
        ];

        if ($includeRelations) {
            $payload['details'] = $this->transformDetails($project->details);
            $payload['images'] = $this->transformImages($project->images);
        }

        return $this->stripEmpty($payload);
    }

    /**
     * @param  EloquentCollection<int, ProjectDetail>  $details
     * @return array<string, array<string, mixed>>
     */
    public function transformDetails(EloquentCollection $details): array
    {
        return $details
            ->sortBy('field_name')
            ->mapWithKeys(function (ProjectDetail $detail): array {
                $value = $this->normalizeSettingValue($detail->field_value);

                if ($value === [] || $value === null || $value === '') {
                    return [];
                }

                return [
                    $detail->field_name => [
                        'field_name' => $detail->field_name,
                        'field_value' => $value,
                    ],
                ];
            })
            ->all();
    }

    /**
     * @param  EloquentCollection<int, ProjectImage>  $images
     * @return list<array<string, mixed>>
     */
    public function transformImages(EloquentCollection $images): array
    {
        return $images
            ->sortBy('sort_order')
            ->map(function (ProjectImage $image): array {
                return $this->stripEmpty([
                    'id' => $image->id,
                    'image_path' => $image->image_path,
                    'image_url' => $this->toPublicUrl($image->image_path),
                    'alt_text' => $image->alt_text,
                    'sort_order' => $image->sort_order,
                    'type' => $image->type,
                    'created_at' => $image->created_at?->toISOString(),
                    'updated_at' => $image->updated_at?->toISOString(),
                ]);
            })
            ->filter(fn (array $image): bool => isset($image['image_path']) && $image['image_path'] !== '')
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>|list<mixed>|string|int|float|bool|null
     */
    private function normalizeSettingValue(mixed $value): mixed
    {
        if ($value instanceof Collection) {
            $value = $value->all();
        }

        if (is_array($value)) {
            $normalized = [];

            foreach ($value as $key => $item) {
                $item = $this->normalizeSettingValue($item);

                if ($item === null || $item === '' || $item === []) {
                    continue;
                }

                $normalized[$key] = $item;
            }

            return $normalized;
        }

        return $value;
    }

    private function toPublicUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        return asset('storage/'.$path);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function stripEmpty(array $payload): array
    {
        return collect($payload)
            ->reject(function (mixed $value): bool {
                return $value === null || $value === '' || $value === [];
            })
            ->all();
    }
}
