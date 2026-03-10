<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactSubmissionStoreRequest;
use App\Models\ContactSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ContactSubmissionController extends Controller
{
    public function store(ContactSubmissionStoreRequest $request): RedirectResponse|JsonResponse
    {
        $submission = ContactSubmission::query()->create([
            ...$request->validated(),
            'read' => false,
            'ip_address' => $request->ip(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Contact submission stored successfully.',
                'id' => $submission->id,
            ], 201);
        }

        return back()->with('success', 'Thanks for your message. I will get back to you soon.');
    }
}
