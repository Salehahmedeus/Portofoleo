<?php

namespace App\Services;

use App\Models\ProjectImage;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ImageUploadService
{
    private const int WEBP_QUALITY = 82;

    /**
     * @var array<string, int>
     */
    private const array SIZES = [
        'thumbnail' => 320,
        'medium' => 960,
        'large' => 1600,
    ];

    /**
     * @var list<string>
     */
    private const array ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    private const int MAX_FILE_SIZE_BYTES = 5 * 1024 * 1024;

    /**
     * @return array{
     *     disk: string,
     *     canonical_path: string,
     *     paths: array{thumbnail: string, medium: string, large: string},
     *     urls: array{thumbnail: string, medium: string, large: string},
     *     metadata: array{original_name: string, original_mime: string, original_size: int, width: int, height: int}
     * }
     */
    public function storeUploadedImage(UploadedFile $file, string $directory): array
    {
        $this->assertImageConstraints($file);

        $diskName = config('filesystems.default');

        if (! is_string($diskName) || $diskName === '') {
            $diskName = 'public';
        }

        $binary = $file->get();

        if (! is_string($binary) || $binary === '') {
            throw ValidationException::withMessages([
                'image' => 'The uploaded image could not be read.',
            ]);
        }

        $sourceImage = imagecreatefromstring($binary);

        if ($sourceImage === false) {
            throw ValidationException::withMessages([
                'image' => 'The uploaded image format is not supported.',
            ]);
        }

        $width = imagesx($sourceImage);
        $height = imagesy($sourceImage);
        $basename = (string) Str::uuid();
        $baseDirectory = trim($directory, '/');

        /** @var FilesystemAdapter $storageDisk */
        $storageDisk = Storage::disk($diskName);
        $paths = [];

        foreach (self::SIZES as $label => $maxDimension) {
            $resized = $this->resizeImage($sourceImage, $width, $height, $maxDimension);
            $path = sprintf('%s/%s_%s.webp', $baseDirectory, $basename, $label);

            $storageDisk->put($path, $this->encodeWebp($resized), [
                'visibility' => 'public',
                'ContentType' => 'image/webp',
            ]);

            if ($resized !== $sourceImage) {
                imagedestroy($resized);
            }

            $paths[$label] = $path;
        }

        imagedestroy($sourceImage);

        /** @var array{thumbnail: string, medium: string, large: string} $typedPaths */
        $typedPaths = $paths;

        return [
            'disk' => $diskName,
            'canonical_path' => $typedPaths['large'],
            'paths' => $typedPaths,
            'urls' => [
                'thumbnail' => $storageDisk->url($typedPaths['thumbnail']),
                'medium' => $storageDisk->url($typedPaths['medium']),
                'large' => $storageDisk->url($typedPaths['large']),
            ],
            'metadata' => [
                'original_name' => $file->getClientOriginalName(),
                'original_mime' => $file->getMimeType() ?: 'application/octet-stream',
                'original_size' => (int) $file->getSize(),
                'width' => $width,
                'height' => $height,
            ],
        ];
    }

    public function deleteProjectImage(ProjectImage $image): void
    {
        $this->deletePaths($image->image_path, $image->variants, $image->disk);
    }

    /**
     * @param  array{thumbnail?: string, medium?: string, large?: string}|null  $variants
     */
    public function deletePaths(?string $canonicalPath, ?array $variants = null, ?string $disk = null): void
    {
        if ($canonicalPath === null || $canonicalPath === '') {
            return;
        }

        $diskName = $disk ?: config('filesystems.default', 'public');
        $paths = collect([$canonicalPath]);

        if (is_array($variants) && $variants !== []) {
            $paths = $paths->merge($variants);
        } else {
            $paths = $paths->merge($this->variantPathsFromCanonical($canonicalPath));
        }

        Storage::disk($diskName)->delete(
            $paths
                ->filter(fn (mixed $path): bool => is_string($path) && $path !== '')
                ->unique()
                ->values()
                ->all(),
        );
    }

    /**
     * @return array{thumbnail?: string, medium?: string, large?: string}
     */
    public function variantPathsFromCanonical(string $canonicalPath): array
    {
        if (! str_ends_with($canonicalPath, '_large.webp')) {
            return [];
        }

        return [
            'thumbnail' => str_replace('_large.webp', '_thumbnail.webp', $canonicalPath),
            'medium' => str_replace('_large.webp', '_medium.webp', $canonicalPath),
            'large' => $canonicalPath,
        ];
    }

    private function assertImageConstraints(UploadedFile $file): void
    {
        $mime = (string) $file->getMimeType();

        if (! in_array($mime, self::ALLOWED_MIME_TYPES, true)) {
            throw ValidationException::withMessages([
                'image' => 'Unsupported image MIME type.',
            ]);
        }

        $size = (int) $file->getSize();

        if ($size > self::MAX_FILE_SIZE_BYTES) {
            throw ValidationException::withMessages([
                'image' => 'Image exceeds maximum allowed size of 5MB.',
            ]);
        }
    }

    private function resizeImage(\GdImage $source, int $sourceWidth, int $sourceHeight, int $maxDimension): \GdImage
    {
        $scale = min($maxDimension / $sourceWidth, $maxDimension / $sourceHeight, 1);
        $targetWidth = max(1, (int) floor($sourceWidth * $scale));
        $targetHeight = max(1, (int) floor($sourceHeight * $scale));

        if ($targetWidth === $sourceWidth && $targetHeight === $sourceHeight) {
            return $source;
        }

        $resized = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);

        imagecopyresampled(
            $resized,
            $source,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $sourceWidth,
            $sourceHeight,
        );

        return $resized;
    }

    private function encodeWebp(\GdImage $image): string
    {
        ob_start();
        imagewebp($image, null, self::WEBP_QUALITY);
        $encoded = ob_get_clean();

        if (! is_string($encoded) || $encoded === '') {
            throw ValidationException::withMessages([
                'image' => 'The uploaded image could not be converted to WebP.',
            ]);
        }

        return $encoded;
    }
}
