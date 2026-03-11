<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSettingRequest;
use App\Http\Requests\Admin\UpdateSettingRequest;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => SiteSetting::query()
                ->orderBy('group')
                ->orderBy('key')
                ->get(),
        ]);
    }

    public function store(StoreSettingRequest $request): JsonResponse
    {
        $setting = SiteSetting::query()->create([
            'key' => $request->validated('key'),
            'group' => $request->validated('group') ?? 'general',
            'value' => $request->validated('value'),
        ]);

        return response()->json([
            'message' => 'Setting created successfully.',
            'data' => $setting,
        ], 201);
    }

    public function show(SiteSetting $setting): JsonResponse
    {
        return response()->json([
            'data' => $setting,
        ]);
    }

    public function update(UpdateSettingRequest $request, SiteSetting $setting): JsonResponse
    {
        $before = $setting->toArray();

        $setting->update([
            'key' => $request->validated('key'),
            'group' => $request->validated('group') ?? 'general',
            'value' => $request->validated('value'),
        ]);

        $setting->refresh();

        return response()->json([
            'message' => 'Setting updated successfully.',
            'before' => $before,
            'after' => $setting->toArray(),
        ]);
    }

    public function destroy(SiteSetting $setting): JsonResponse
    {
        $setting->delete();

        return response()->json([
            'message' => 'Setting deleted successfully.',
        ]);
    }
}
