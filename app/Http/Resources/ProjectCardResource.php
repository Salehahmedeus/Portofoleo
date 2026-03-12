<?php

namespace App\Http\Resources;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin \App\Models\Project */
class ProjectCardResource extends JsonResource
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
            'thumbnail_path' => $this->whenNotNull($this->thumbnail_path),
            'thumbnail_url' => $this->whenNotNull($this->publicUrl($this->thumbnail_path)),
        ];
    }

    private function publicUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        /** @var FilesystemAdapter $storageDisk */
        $storageDisk = Storage::disk(config('filesystems.default', 'public'));

        return $storageDisk->url($path);
    }
}
