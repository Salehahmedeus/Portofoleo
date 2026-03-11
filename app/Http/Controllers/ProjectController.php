<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Support\PortfolioData;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(PortfolioData $portfolioData): Response
    {
        $projects = Project::query()
            ->with(['details', 'images'])
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('projects/index', [
            'projects' => $portfolioData->transformProjects($projects),
        ]);
    }

    public function show(Project $project, PortfolioData $portfolioData): Response
    {
        $project->load(['details', 'images']);

        $previousProject = Project::query()
            ->where(function ($query) use ($project) {
                $query
                    ->where('sort_order', '<', $project->sort_order)
                    ->orWhere(function ($nestedQuery) use ($project) {
                        $nestedQuery
                            ->where('sort_order', $project->sort_order)
                            ->where('id', '<', $project->id);
                    });
            })
            ->orderByDesc('sort_order')
            ->orderByDesc('id')
            ->first(['slug', 'title']);

        $nextProject = Project::query()
            ->where(function ($query) use ($project) {
                $query
                    ->where('sort_order', '>', $project->sort_order)
                    ->orWhere(function ($nestedQuery) use ($project) {
                        $nestedQuery
                            ->where('sort_order', $project->sort_order)
                            ->where('id', '>', $project->id);
                    });
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first(['slug', 'title']);

        return Inertia::render('projects/show', [
            'project' => $portfolioData->transformProject($project),
            'previous_project' => $previousProject,
            'next_project' => $nextProject,
        ]);
    }
}
