<p>A new contact submission was received.</p>

<p><strong>Name:</strong> {{ $submission->name }}</p>
<p><strong>Email:</strong> {{ $submission->email }}</p>
<p><strong>Subject:</strong> {{ $submission->subject ?: 'N/A' }}</p>
<p><strong>Message:</strong></p>
<p>{{ $submission->message }}</p>

<p><strong>IP Address:</strong> {{ $submission->ip_address ?: 'Unknown' }}</p>
