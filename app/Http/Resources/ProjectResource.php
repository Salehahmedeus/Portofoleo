<?php

namespace App\Http\Resources;

use App\Models\ProjectDetail;
use App\Models\ProjectImage;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin \App\Models\Project */
class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'type' => $this->type,
            'summary' => $this->summary,
            'featured' => (bool) $this->featured,
            'sort_order' => (int) $this->sort_order,
            'thumbnail_path' => $this->whenNotNull($this->thumbnail_path),
            'thumbnail_url' => $this->whenNotNull($this->publicUrl($this->thumbnail_path)),
            'thumbnail_urls' => $this->whenNotNull($this->variantUrls($this->variantPaths($this->thumbnail_path))),
            'meta_title' => $this->whenNotNull($this->meta_title),
            'meta_description' => $this->whenNotNull($this->meta_description),
            $this->mergeWhen($this->relationLoaded('details'), [
                'details' => $this->details(),
                'links' => $this->whenNotNull($this->detailValue('links')),
            ]),
            $this->mergeWhen($this->relationLoaded('images'), [
                'images' => $this->images(),
            ]),
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function details(): array
    {
        return $this->details
            ->sortBy('field_name')
            ->mapWithKeys(function (ProjectDetail $detail): array {
                if ($detail->field_value === null || $detail->field_value === []) {
                    return [];
                }

                return [
                    $detail->field_name => [
                        'field_name' => $detail->field_name,
                        'field_value' => $detail->field_value,
                    ],
                ];
            })
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function images(): array
    {
        return $this->images
            ->sortBy('sort_order')
            ->map(function (ProjectImage $image): array {
                $payload = [
                    'id' => $image->id,
                    'disk' => $image->disk,
                    'image_path' => $image->image_path,
                    'image_url' => $this->publicUrl($image->image_path, $image->disk),
                    'image_urls' => $this->variantUrls($image->variants, $image->disk),
                    'sort_order' => (int) $image->sort_order,
                ];

                if ($image->alt_text !== null) {
                    $payload['alt_text'] = $image->alt_text;
                }

                if ($image->type !== null) {
                    $payload['type'] = $image->type;
                }

                return $payload;
            })
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>|list<mixed>|string|int|float|bool|null
     */
    private function detailValue(string $fieldName): mixed
    {
        $detail = $this->details
            ->firstWhere('field_name', $fieldName);

        if (! $detail instanceof ProjectDetail) {
            return null;
        }

        return $detail->field_value;
    }

    private function publicUrl(?string $path, ?string $disk = null): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        /** @var FilesystemAdapter $storageDisk */
        $storageDisk = Storage::disk($disk ?: config('filesystems.default', 'public'));

        return $storageDisk->url($path);
    }

    /**
     * @param  array{thumbnail?: string, medium?: string, large?: string}|null  $paths
     * @return array{thumbnail?: string, medium?: string, large?: string}|null
     */
    private function variantUrls(?array $paths, ?string $disk = null): ?array
    {
        if (! is_array($paths) || $paths === []) {
            return null;
        }

        /** @var FilesystemAdapter $storageDisk */
        $storageDisk = Storage::disk($disk ?: config('filesystems.default', 'public'));

        $mapped = collect($paths)
            ->filter(fn (mixed $path): bool => is_string($path) && $path !== '')
            ->map(fn (string $path): string => $storageDisk->url($path))
            ->all();

        return $mapped === [] ? null : $mapped;
    }

    /**
     * @return array{thumbnail?: string, medium?: string, large?: string}|null
     */
    private function variantPaths(?string $canonicalPath): ?array
    {
        if ($canonicalPath === null || $canonicalPath === '') {
            return null;
        }

        if (! str_ends_with($canonicalPath, '_large.webp')) {
            return null;
        }

        return [
            'thumbnail' => str_replace('_large.webp', '_thumbnail.webp', $canonicalPath),
            'medium' => str_replace('_large.webp', '_medium.webp', $canonicalPath),
            'large' => $canonicalPath,
        ];
    }
}
