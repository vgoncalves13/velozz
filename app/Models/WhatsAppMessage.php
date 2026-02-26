<?php

namespace App\Models;

use App\Enums\MessageDirection;
use App\Enums\MessageStatus;
use App\Enums\MessageType;
use App\Models\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppMessage extends Model
{
    use HasTenantScope;

    protected $table = 'whatsapp_messages';

    protected $fillable = [
        'tenant_id',
        'lead_id',
        'whatsapp_instance_id',
        'type',
        'direction',
        'content',
        'media_url',
        'status',
        'error_message',
        'remote_message_id',
        'sent_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => MessageType::class,
            'direction' => MessageDirection::class,
            'status' => MessageStatus::class,
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function whatsappInstance(): BelongsTo
    {
        return $this->belongsTo(WhatsAppInstance::class);
    }

    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by_user_id');
    }

    /**
     * Check if message is incoming
     */
    public function isIncoming(): bool
    {
        return $this->direction === MessageDirection::Incoming;
    }

    /**
     * Check if message is outgoing
     */
    public function isOutgoing(): bool
    {
        return $this->direction === MessageDirection::Outgoing;
    }

    /**
     * Check if message was delivered
     */
    public function isDelivered(): bool
    {
        return in_array($this->status, [MessageStatus::Delivered, MessageStatus::Read]);
    }

    /**
     * Check if message failed
     */
    public function hasFailed(): bool
    {
        return $this->status === MessageStatus::Failed;
    }
}
