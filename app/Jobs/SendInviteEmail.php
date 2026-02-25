<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendInviteEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public \App\Models\User $user
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Generate secure token
        $token = \Illuminate\Support\Str::random(64);
        $expiresAt = now()->addHours(48);

        // Update user with invite token
        $this->user->update([
            'invite_token' => $token,
            'invite_expires_at' => $expiresAt,
            'status' => 'invited',
        ]);

        // Send invitation email
        \Illuminate\Support\Facades\Mail::to($this->user->email)
            ->send(new \App\Mail\InviteMail($this->user, $token));
    }
}
