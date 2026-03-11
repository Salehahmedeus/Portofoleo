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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(Request $request, PortfolioData $portfolioData): Response|JsonResponse
    {
        $search = trim((string) $request->query('search', ''));
        $type = $request->query('type');
        $sort = (string) $request->query('sort', 'sort_order');

        $allowedTypes = ['development', 'uiux'];
        $sortColumns = [
            'sort_order' => 'sort_order',
            'created_at' => 'created_at',
            'title' => 'title',
        ];

        $sortDirection = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $sortKey = ltrim($sort, '-');

        if (! array_key_exists($sortKey, $sortColumns)) {
            $sortKey = 'sort_order';
            $sortDirection = 'asc';
            $sort = 'sort_order';
        }

        $filters = [
            'search' => $search,
            'type' => in_array($type, $allowedTypes, true) ? $type : null,
            'sort' => $sort,
        ];

        $projects = Project::query()
            ->with(['details', 'images'])
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $query->where(function ($nestedQuery) use ($filters): void {
                    $nestedQuery->where('title', 'like', '%'.$filters['search'].'%')
                        ->orWhere('slug', 'like', '%'.$filters['search'].'%')
                        ->orWhere('summary', 'like', '%'.$filters['search'].'%');
                });
            })
            ->when($filters['type'] !== null, fn ($query) => $query->where('type', $filters['type']))
            ->orderBy($sortColumns[$sortKey], $sortDirection)
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        if ($request->expectsJson()) {
            $payload = $projects->toArray();
            $payload['data'] = $projects->getCollection()
                ->map(fn (Project $project): array => $portfolioData->transformProject($project))
                ->values()
                ->all();
            $payload['filters'] = $filters;

            return response()->json($payload);
        }

        return Inertia::render('admin/projects/index', [
            'projects' => $projects->through(fn (Project $project): array => $portfolioData->transformProject($project)),
            'filters' => $filters,
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
            $slug = $this->generateUniqueSlug($validated['slug'] ?? null, $validated['title']);

            $project = Project::query()->create([
                'title' => $validated['title'],
                'slug' => $slug,
                'type' => $validated['type'],
                'summary' => $validated['summary'],
                'featured' => (bool) ($validated['featured'] ?? false),
                'sort_order' => (int) ($validated['sort_order'] ?? 0),
                'thumbnail_path' => $thumbnailPath,
                'meta_title' => $validated['meta_title'] ?? null,
                'meta_description' => $validated['meta_description'] ?? null,
            ]);

            $details = $this->buildDetailsPayload($validated);

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

    public function show(Project $project, PortfolioData $portfolioData): JsonResponse
    {
        $project->load(['details', 'images']);

        return response()->json([
            'data' => $portfolioData->transformProject($project),
        ]);
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($project, $request, $validated): void {
            $currentThumbnail = $project->thumbnail_path;
            $nextThumbnail = $currentThumbnail;
            $slug = $this->generateUniqueSlug($validated['slug'] ?? null, $validated['title'], $project);

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
                'slug' => $slug,
                'type' => $validated['type'],
                'summary' => $validated['summary'],
                'featured' => (bool) ($validated['featured'] ?? false),
                'sort_order' => (int) ($validated['sort_order'] ?? 0),
                'thumbnail_path' => $nextThumbnail,
                'meta_title' => $validated['meta_title'] ?? null,
                'meta_description' => $validated['meta_description'] ?? null,
            ]);

            if (array_key_exists('details', $validated) || array_key_exists('links', $validated)) {
                $project->details()->delete();

                $details = $this->buildDetailsPayload($validated);

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

    /**
     * @param  array<string, mixed>  $validated
     * @return list<array{field_name: string, field_value: mixed}>
     */
    private function buildDetailsPayload(array $validated): array
    {
        $details = collect($validated['details'] ?? [])
            ->map(fn (array $detail): array => [
                'field_name' => $detail['field_name'],
                'field_value' => $detail['field_value'] ?? null,
            ])
            ->reject(fn (array $detail): bool => $detail['field_name'] === 'links')
            ->filter(function (array $detail): bool {
                return $detail['field_value'] !== null
                    && $detail['field_value'] !== ''
                    && $detail['field_value'] !== [];
            })
            ->values();

        $links = collect($validated['links'] ?? [])
            ->only(['live_demo', 'github', 'prototype'])
            ->filter(fn (mixed $value): bool => $value !== null && $value !== '')
            ->all();

        if ($links !== []) {
            $details->push([
                'field_name' => 'links',
                'field_value' => $links,
            ]);
        }

        return $details->all();
    }

    private function generateUniqueSlug(?string $slugInput, string $title, ?Project $ignoreProject = null): string
    {
        $baseSlug = Str::slug($slugInput ?: $title);

        if ($baseSlug === '') {
            $baseSlug = 'project';
        }

        $slug = $baseSlug;
        $suffix = 2;

        while (Project::query()
            ->where('slug', $slug)
            ->when($ignoreProject !== null, fn ($query) => $query->where('id', '!=', $ignoreProject->id))
            ->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
