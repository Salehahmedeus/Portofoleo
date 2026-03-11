<?php

use App\Mail\NewContactSubmissionMail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

it('stores a valid contact submission and sends a notification mail', function () {
    Mail::fake();
    Config::set('contact.notification_email', 'recipient@portofolio.test');

    $payload = [
        'name' => 'Ahmed Saleh',
        'email' => 'ahmed@example.com',
        'subject' => 'Project inquiry',
        'message' => 'I would like to discuss a portfolio project.',
    ];

    $response = $this->postJson(route('contact-submissions.store'), $payload);

    $response->assertCreated()
        ->assertJsonPath('message', 'Contact submission stored successfully.');

    $this->assertDatabaseHas('contact_submissions', [
        'name' => 'Ahmed Saleh',
        'email' => 'ahmed@example.com',
        'subject' => 'Project inquiry',
        'message' => 'I would like to discuss a portfolio project.',
        'read' => 0,
    ]);

    Mail::assertSent(NewContactSubmissionMail::class, function (NewContactSubmissionMail $mail): bool {
        return $mail->hasTo('recipient@portofolio.test')
            && $mail->submission->email === 'ahmed@example.com';
    });
});

it('returns validation errors when required fields are missing', function () {
    Mail::fake();

    $response = $this->postJson(route('contact-submissions.store'), []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email', 'message']);

    $this->assertDatabaseCount('contact_submissions', 0);
    Mail::assertNothingSent();
});

it('accepts submissions without a subject', function () {
    Mail::fake();
    Config::set('contact.notification_email', 'recipient@portofolio.test');

    $response = $this->postJson(route('contact-submissions.store'), [
        'name' => 'No Subject Sender',
        'email' => 'nosubject@example.com',
        'message' => 'This message intentionally has no subject.',
    ]);

    $response->assertCreated();

    $this->assertDatabaseHas('contact_submissions', [
        'name' => 'No Subject Sender',
        'email' => 'nosubject@example.com',
        'subject' => null,
    ]);

    Mail::assertSent(NewContactSubmissionMail::class, 1);
});

it('rejects spam submissions when honeypot website field is present', function () {
    Mail::fake();

    $response = $this->postJson(route('contact-submissions.store'), [
        'name' => 'Spam Bot',
        'email' => 'spam@example.com',
        'subject' => 'spam',
        'message' => 'Buy now',
        'website' => 'https://spam.example.com',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['website']);

    $this->assertDatabaseCount('contact_submissions', 0);
    Mail::assertNothingSent();
});

it('rate limits contact submissions after three requests per hour', function () {
    Mail::fake();
    Config::set('contact.notification_email', 'recipient@portofolio.test');

    $payload = [
        'name' => 'Rate Limit User',
        'email' => 'limit@example.com',
        'subject' => 'Rate limit test',
        'message' => 'Testing contact submission rate limiting.',
    ];

    $this->postJson(route('contact-submissions.store'), $payload)->assertCreated();
    $this->postJson(route('contact-submissions.store'), $payload)->assertCreated();
    $this->postJson(route('contact-submissions.store'), $payload)->assertCreated();

    $this->postJson(route('contact-submissions.store'), $payload)
        ->assertStatus(429);

    $this->assertDatabaseCount('contact_submissions', 3);
});
