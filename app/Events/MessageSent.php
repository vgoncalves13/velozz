<?php

namespace App\Events;

use App\Models\WhatsAppMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public WhatsAppMessage $message)
    {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("tenant.{$this->message->tenant_id}.inbox"),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'lead_id' => $this->message->lead_id,
                'content' => $this->message->content,
                'type' => $this->message->type,
                'status' => $this->message->status,
                'sent_by_user_id' => $this->message->sent_by_user_id,
                'created_at' => $this->message->created_at->toIso8601String(),
            ],
        ];
    }
}
