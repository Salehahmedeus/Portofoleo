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

        return Inertia::render('projects/show', [
            'project' => $portfolioData->transformProject($project),
        ]);
    }
}
