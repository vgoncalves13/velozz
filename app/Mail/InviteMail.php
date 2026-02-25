<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InviteMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public \App\Models\User $user,
        public string $token
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $tenantName = $this->user->tenant?->name ?? 'VELOZZ.DIGITAL';

        return new Envelope(
            subject: "You've been invited to join {$tenantName}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Generate invite URL using tenant's domain
        $domain = $this->user->tenant?->domain ?? config('app.url');
        $protocol = config('app.env') === 'production' ? 'https' : 'http';
        $inviteUrl = "{$protocol}://{$domain}/accept-invite/{$this->token}";

        return new Content(
            markdown: 'emails.invite',
            with: [
                'user' => $this->user,
                'token' => $this->token,
                'inviteUrl' => $inviteUrl,
                'expiresAt' => $this->user->invite_expires_at,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
