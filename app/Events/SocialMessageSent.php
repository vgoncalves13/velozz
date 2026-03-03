<?php

namespace App\Events;

use App\Models\SocialMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SocialMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public SocialMessage $message) {}

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("tenant.{$this->message->tenant_id}.inbox"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'lead_id' => $this->message->lead_id,
                'channel' => $this->message->channel->value,
                'content' => $this->message->content,
                'type' => $this->message->type->value,
                'created_at' => $this->message->created_at->toIso8601String(),
            ],
        ];
    }
}
