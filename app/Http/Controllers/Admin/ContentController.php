<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateContentRequest;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Skill;
use App\Support\PortfolioData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ContentController extends Controller
{
    public function edit(PortfolioData $portfolioData): Response
    {
        return Inertia::render('admin/content/edit', [
            'settings' => $portfolioData->settingsByKey(),
            'services' => Service::query()
                ->orderBy('sort_order')
                ->orderBy('title')
                ->get(),
            'skills' => Skill::query()
                ->orderBy('category')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(UpdateContentRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $key = $validated['key'];

        if ($key === 'services') {
            $items = collect($validated['value']['items'] ?? [])->values();

            DB::transaction(function () use ($items): void {
                Service::query()->delete();

                $items->each(function (array $item, int $index): void {
                    Service::query()->create([
                        'title' => $item['title'],
                        'description' => $item['description'],
                        'icon' => $item['icon'] ?? null,
                        'sort_order' => $item['sort_order'] ?? $index,
                    ]);
                });
            });

            return to_route('admin.content.edit')->with('success', 'Services updated successfully.');
        }

        if ($key === 'skills') {
            $items = collect($validated['value']['items'] ?? [])->values();

            DB::transaction(function () use ($items): void {
                Skill::query()->delete();

                $items->each(function (array $item, int $index): void {
                    Skill::query()->create([
                        'name' => $item['name'],
                        'category' => $item['category'],
                        'logo_path' => $item['logo_path'] ?? null,
                        'sort_order' => $item['sort_order'] ?? $index,
                    ]);
                });
            });

            return to_route('admin.content.edit')->with('success', 'Skills updated successfully.');
        }

        SiteSetting::query()->updateOrCreate(
            ['key' => $key],
            [
                'group' => $validated['group'] ?? 'general',
                'value' => $validated['value'],
            ],
        );

        $messages = [
            'hero_content' => 'Hero content updated successfully.',
            'contact_information' => 'Contact information updated successfully.',
            'homepage_seo' => 'Homepage SEO updated successfully.',
        ];

        return to_route('admin.content.edit')->with('success', $messages[$key] ?? 'Content updated successfully.');
    }
}
