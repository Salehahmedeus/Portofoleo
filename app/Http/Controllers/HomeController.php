<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Service;
use App\Models\Skill;
use App\Support\PortfolioData;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class HomeController extends Controller
{
    public function __invoke(PortfolioData $portfolioData): Response
    {
        $featuredProjects = Project::query()
            ->with(['details', 'images'])
            ->where('featured', true)
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('welcome', [
            'canRegister' => Features::enabled(Features::registration()),
            'services' => Service::query()->orderBy('sort_order')->get(),
            'skills' => Skill::query()->orderBy('category')->orderBy('sort_order')->get(),
            'projects' => $portfolioData->transformProjects($featuredProjects),
            'settings' => $portfolioData->settingsByKey(),
        ]);
    }
}
