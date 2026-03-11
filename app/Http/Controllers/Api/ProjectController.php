<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectCardResource;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ProjectController extends Controller
{
    private const string INDEX_CACHE_KEY = 'public_api:projects:index';

    public function index(): JsonResponse
    {
        $payload = Cache::remember(self::INDEX_CACHE_KEY, now()->addMinutes(5), function (): array {
            $projects = Project::query()
                ->orderBy('sort_order')
                ->get();

            return [
                'data' => ProjectCardResource::collection($projects)->resolve(),
            ];
        });

        return response()->json($payload);
    }

    public function show(Project $project): JsonResponse
    {
        $cacheKey = sprintf('public_api:projects:show:%s', $project->slug);

        $payload = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($project): array {
            $project->load(['details', 'images']);

            return [
                'data' => ProjectResource::make($project)->resolve(),
            ];
        });

        return response()->json($payload);
    }
}
