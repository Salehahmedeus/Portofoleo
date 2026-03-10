<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderProjectRequest;
use App\Http\Requests\Admin\StoreProjectRequest;
use App\Http\Requests\Admin\ToggleFeaturedProjectRequest;
use App\Http\Requests\Admin\UpdateProjectRequest;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Support\PortfolioData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(PortfolioData $portfolioData): Response
    {
        $projects = Project::query()
            ->with(['details', 'images'])
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/projects/index', [
            'projects' => $projects->through(fn (Project $project): array => $portfolioData->transformProject($project)),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/projects/create');
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $project = DB::transaction(function () use ($request, $validated): Project {
            $thumbnailPath = $request->file('thumbnail')?->store('projects/thumbnails', 'public');

            $project = Project::query()->create([
                'title' => $validated['title'],
                'slug' => $validated['slug'],
                'type' => $validated['type'],
                'summary' => $validated['summary'],
                'featured' => (bool) ($validated['featured'] ?? false),
                'sort_order' => (int) ($validated['sort_order'] ?? 0),
                'thumbnail_path' => $thumbnailPath,
                'meta_title' => $validated['meta_title'] ?? null,
                'meta_description' => $validated['meta_description'] ?? null,
            ]);

            $details = collect($validated['details'] ?? [])
                ->map(fn (array $detail): array => [
                    'field_name' => $detail['field_name'],
                    'field_value' => $detail['field_value'] ?? [],
                ])
                ->filter(fn (array $detail): bool => $detail['field_value'] !== [])
                ->values()
                ->all();

            if ($details !== []) {
                $project->details()->createMany($details);
            }

            foreach ($validated['images'] ?? [] as $index => $image) {
                $path = $request->file("images.{$index}.file")?->store('projects/gallery', 'public');

                if ($path === null) {
                    continue;
                }

                $project->images()->create([
                    'image_path' => $path,
                    'alt_text' => $image['alt_text'] ?? null,
                    'sort_order' => (int) ($image['sort_order'] ?? $index),
                    'type' => $image['type'] ?? 'gallery',
                ]);
            }

            return $project;
        });

        return to_route('admin.projects.edit', $project)->with('success', 'Project created successfully.');
    }

    public function edit(Project $project, PortfolioData $portfolioData): Response
    {
        $project->load(['details', 'images']);

        return Inertia::render('admin/projects/edit', [
            'project' => $portfolioData->transformProject($project),
        ]);
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($project, $request, $validated): void {
            $currentThumbnail = $project->thumbnail_path;
            $nextThumbnail = $currentThumbnail;

            if ((bool) ($validated['remove_thumbnail'] ?? false) && $currentThumbnail !== null) {
                Storage::disk('public')->delete($currentThumbnail);
                $nextThumbnail = null;
            }

            if ($request->file('thumbnail') !== null) {
                if ($currentThumbnail !== null && $currentThumbnail !== '') {
                    Storage::disk('public')->delete($currentThumbnail);
                }

                $nextThumbnail = $request->file('thumbnail')->store('projects/thumbnails', 'public');
            }

            $project->update([
                'title' => $validated['title'],
                'slug' => $validated['slug'],
                'type' => $validated['type'],
                'summary' => $validated['summary'],
                'featured' => (bool) ($validated['featured'] ?? false),
                'sort_order' => (int) ($validated['sort_order'] ?? 0),
                'thumbnail_path' => $nextThumbnail,
                'meta_title' => $validated['meta_title'] ?? null,
                'meta_description' => $validated['meta_description'] ?? null,
            ]);

            if (array_key_exists('details', $validated)) {
                $project->details()->delete();

                $details = collect($validated['details'] ?? [])
                    ->map(fn (array $detail): array => [
                        'field_name' => $detail['field_name'],
                        'field_value' => $detail['field_value'] ?? [],
                    ])
                    ->filter(fn (array $detail): bool => $detail['field_value'] !== [])
                    ->values()
                    ->all();

                if ($details !== []) {
                    $project->details()->createMany($details);
                }
            }

            $imageIdsToDelete = $validated['images_to_delete'] ?? [];

            if ($imageIdsToDelete !== []) {
                $imagesToDelete = $project->images()
                    ->whereIn('id', $imageIdsToDelete)
                    ->get();

                foreach ($imagesToDelete as $image) {
                    Storage::disk('public')->delete($image->image_path);
                    $image->delete();
                }
            }

            foreach ($validated['images'] ?? [] as $index => $image) {
                $path = $request->file("images.{$index}.file")?->store('projects/gallery', 'public');

                if ($path === null) {
                    continue;
                }

                $project->images()->create([
                    'image_path' => $path,
                    'alt_text' => $image['alt_text'] ?? null,
                    'sort_order' => (int) ($image['sort_order'] ?? $index),
                    'type' => $image['type'] ?? 'gallery',
                ]);
            }
        });

        return to_route('admin.projects.edit', $project)->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        DB::transaction(function () use ($project): void {
            if ($project->thumbnail_path !== null && $project->thumbnail_path !== '') {
                Storage::disk('public')->delete($project->thumbnail_path);
            }

            $images = $project->images()->get();

            foreach ($images as $image) {
                Storage::disk('public')->delete($image->image_path);
            }

            $project->delete();
        });

        return to_route('admin.projects.index')->with('success', 'Project deleted successfully.');
    }

    public function reorder(ReorderProjectRequest $request): JsonResponse
    {
        $projectIds = $request->validated('projects');

        DB::transaction(function () use ($projectIds): void {
            foreach ($projectIds as $index => $projectId) {
                Project::query()->whereKey($projectId)->update([
                    'sort_order' => $index,
                ]);
            }
        });

        return response()->json([
            'message' => 'Project order updated successfully.',
        ]);
    }

    public function toggleFeatured(ToggleFeaturedProjectRequest $request, Project $project): JsonResponse
    {
        $project->update([
            'featured' => (bool) $request->validated('featured'),
        ]);

        return response()->json([
            'message' => 'Project featured state updated successfully.',
            'featured' => $project->featured,
        ]);
    }

    public function destroyImage(Project $project, ProjectImage $image): JsonResponse
    {
        if ($image->project_id !== $project->id) {
            abort(404);
        }

        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        return response()->json([
            'message' => 'Project image deleted successfully.',
        ]);
    }
}
