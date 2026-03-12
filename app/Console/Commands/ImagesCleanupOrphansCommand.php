<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\ProjectImage;
use App\Services\ImageUploadService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ImagesCleanupOrphansCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:cleanup-orphans {--dry-run : Show orphan files without deleting them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove project image files not linked in the database';

    public function __construct(private ImageUploadService $imageUploadService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $diskName = config('filesystems.default', 'public');
        $dryRun = (bool) $this->option('dry-run');
        $files = collect(Storage::disk($diskName)->allFiles('projects'));

        $linkedPaths = Project::query()
            ->whereNotNull('thumbnail_path')
            ->pluck('thumbnail_path')
            ->filter(fn (mixed $path): bool => is_string($path) && $path !== '')
            ->flatMap(function (string $path): array {
                return array_values(array_filter([
                    $path,
                    ...array_values($this->imageUploadService->variantPathsFromCanonical($path)),
                ]));
            })
            ->merge(
                ProjectImage::query()
                    ->select(['image_path', 'variants'])
                    ->get()
                    ->flatMap(function (ProjectImage $image): array {
                        $variants = is_array($image->variants) ? $image->variants : [];

                        return array_values(array_filter([
                            $image->image_path,
                            ...array_values($variants),
                        ]));
                    }),
            )
            ->unique()
            ->values();

        $orphans = $files
            ->diff($linkedPaths)
            ->values();

        if ($dryRun) {
            $this->info(sprintf(
                'Dry run complete. %d orphan files found on disk [%s].',
                $orphans->count(),
                $diskName,
            ));

            return self::SUCCESS;
        }

        $deletedCount = 0;

        foreach ($orphans as $orphanPath) {
            if (Storage::disk($diskName)->delete($orphanPath)) {
                $deletedCount++;
            }
        }

        $this->info(sprintf(
            'Deleted %d orphan files from disk [%s].',
            $deletedCount,
            $diskName,
        ));

        return self::SUCCESS;
    }
}
