<?php

namespace App\Models;

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
        return $this->direction === 'incoming';
    }

    /**
     * Check if message is outgoing
     */
    public function isOutgoing(): bool
    {
        return $this->direction === 'outgoing';
    }

    /**
     * Check if message was delivered
     */
    public function isDelivered(): bool
    {
        return in_array($this->status, ['delivered', 'read']);
    }

    /**
     * Check if message failed
     */
    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }
}
