<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderServiceRequest;
use App\Http\Requests\Admin\StoreServiceRequest;
use App\Http\Requests\Admin\UpdateServiceRequest;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Service::query()
                ->orderBy('sort_order')
                ->orderBy('title')
                ->get(),
        ]);
    }

    public function store(StoreServiceRequest $request): JsonResponse
    {
        $service = Service::query()->create([
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'icon' => $request->validated('icon'),
            'sort_order' => (int) ($request->validated('sort_order') ?? 0),
        ]);

        return response()->json([
            'message' => 'Service created successfully.',
            'data' => $service,
        ], 201);
    }

    public function show(Service $service): JsonResponse
    {
        return response()->json([
            'data' => $service,
        ]);
    }

    public function update(UpdateServiceRequest $request, Service $service): JsonResponse
    {
        $before = $service->toArray();

        $service->update([
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'icon' => $request->validated('icon'),
            'sort_order' => (int) ($request->validated('sort_order') ?? $service->sort_order),
        ]);

        $service->refresh();

        return response()->json([
            'message' => 'Service updated successfully.',
            'before' => $before,
            'after' => $service->toArray(),
        ]);
    }

    public function destroy(Service $service): JsonResponse
    {
        $service->delete();

        return response()->json([
            'message' => 'Service deleted successfully.',
        ]);
    }

    public function reorder(ReorderServiceRequest $request): JsonResponse
    {
        $serviceIds = $request->validated('services');

        DB::transaction(function () use ($serviceIds): void {
            foreach ($serviceIds as $index => $serviceId) {
                Service::query()->whereKey($serviceId)->update([
                    'sort_order' => $index,
                ]);
            }
        });

        return response()->json([
            'message' => 'Service order updated successfully.',
        ]);
    }
}
