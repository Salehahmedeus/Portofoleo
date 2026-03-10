<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnalyticsEventStoreRequest;
use App\Models\AnalyticsEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class AnalyticsEventController extends Controller
{
    public function store(AnalyticsEventStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $event = AnalyticsEvent::query()->create([
            'event_type' => $validated['event_type'],
            'event_data' => $validated['event_data'] ?? null,
            'page_url' => $validated['page_url'],
            'referrer' => $validated['referrer'] ?? $request->headers->get('referer'),
            'device_type' => $validated['device_type'] ?? null,
            'country' => isset($validated['country']) ? Str::upper($validated['country']) : null,
            'ip_address' => $request->ip(),
            'session_id' => $validated['session_id'] ?? $request->session()->getId(),
        ]);

        return response()->json([
            'message' => 'Event stored successfully.',
            'id' => $event->id,
        ], 201);
    }
}
