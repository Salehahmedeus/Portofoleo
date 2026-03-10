<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateContentRequest;
use App\Models\SiteSetting;
use App\Support\PortfolioData;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ContentController extends Controller
{
    public function edit(PortfolioData $portfolioData): Response
    {
        return Inertia::render('admin/content/edit', [
            'settings' => $portfolioData->settingsByKey(),
        ]);
    }

    public function update(UpdateContentRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        SiteSetting::query()->updateOrCreate(
            ['key' => $validated['key']],
            [
                'group' => $validated['group'] ?? 'general',
                'value' => $validated['value'],
            ],
        );

        return to_route('admin.content.edit')->with('success', 'Content updated successfully.');
    }
}
