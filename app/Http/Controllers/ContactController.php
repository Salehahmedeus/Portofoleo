<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactFormRequest;
use App\Mail\NewContactSubmissionMail;
use App\Models\ContactSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(ContactFormRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $submission = ContactSubmission::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'] ?? null,
            'message' => $validated['message'],
            'read' => false,
            'ip_address' => $request->ip(),
        ]);

        Mail::to(config('contact.notification_email'))
            ->send(new NewContactSubmissionMail($submission));

        return response()->json([
            'message' => 'Contact submission stored successfully.',
            'id' => $submission->id,
        ], 201);
    }
}
