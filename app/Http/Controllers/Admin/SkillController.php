<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderSkillRequest;
use App\Http\Requests\Admin\StoreSkillRequest;
use App\Http\Requests\Admin\UpdateSkillRequest;
use App\Models\Skill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SkillController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $category = $request->query('category');

        return response()->json([
            'data' => Skill::query()
                ->when(is_string($category) && $category !== '', fn ($query) => $query->where('category', $category))
                ->orderBy('category')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(StoreSkillRequest $request): JsonResponse
    {
        $skill = Skill::query()->create([
            'name' => $request->validated('name'),
            'category' => $request->validated('category'),
            'logo_path' => $request->validated('logo_path'),
            'sort_order' => (int) ($request->validated('sort_order') ?? 0),
        ]);

        return response()->json([
            'message' => 'Skill created successfully.',
            'data' => $skill,
        ], 201);
    }

    public function show(Skill $skill): JsonResponse
    {
        return response()->json([
            'data' => $skill,
        ]);
    }

    public function update(UpdateSkillRequest $request, Skill $skill): JsonResponse
    {
        $before = $skill->toArray();

        $skill->update([
            'name' => $request->validated('name'),
            'category' => $request->validated('category'),
            'logo_path' => $request->validated('logo_path'),
            'sort_order' => (int) ($request->validated('sort_order') ?? $skill->sort_order),
        ]);

        $skill->refresh();

        return response()->json([
            'message' => 'Skill updated successfully.',
            'before' => $before,
            'after' => $skill->toArray(),
        ]);
    }

    public function destroy(Skill $skill): JsonResponse
    {
        $skill->delete();

        return response()->json([
            'message' => 'Skill deleted successfully.',
        ]);
    }

    public function reorder(ReorderSkillRequest $request): JsonResponse
    {
        $skillIds = $request->validated('skills');

        DB::transaction(function () use ($skillIds): void {
            foreach ($skillIds as $index => $skillId) {
                Skill::query()->whereKey($skillId)->update([
                    'sort_order' => $index,
                ]);
            }
        });

        return response()->json([
            'message' => 'Skill order updated successfully.',
        ]);
    }
}
