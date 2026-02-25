<x-mail::message>
# Welcome to {{ $user->tenant?->name ?? config('app.name') }}!

Hi {{ $user->name }},

You've been invited to join **{{ $user->tenant?->name ?? config('app.name') }}** as a **{{ ucfirst(str_replace('_', ' ', $user->role)) }}**.

Click the button below to accept your invitation and set your password:

<x-mail::button :url="$inviteUrl">
Accept Invitation
</x-mail::button>

This invitation will expire on {{ $expiresAt->format('F j, Y \a\t g:i A') }}.

If you didn't expect this invitation, you can safely ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
