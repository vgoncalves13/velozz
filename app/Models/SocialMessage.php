<?php

namespace App\Models;

use App\Enums\Channel;
use App\Enums\MessageDirection;
use App\Enums\MessageStatus;
use App\Enums\MessageType;
use App\Models\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialMessage extends Model
{
    use HasFactory;
    use HasTenantScope;

    protected $fillable = [
        'tenant_id',
        'lead_id',
        'meta_account_id',
        'channel',
        'direction',
        'type',
        'content',
        'media_url',
        'status',
        'external_message_id',
        'external_thread_id',
        'sender_id',
        'sent_by_user_id',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'channel' => Channel::class,
            'direction' => MessageDirection::class,
            'type' => MessageType::class,
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

    public function metaAccount(): BelongsTo
    {
        return $this->belongsTo(MetaAccount::class);
    }

    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by_user_id');
    }

    public function isIncoming(): bool
    {
        return $this->direction === MessageDirection::Incoming;
    }

    public function isOutgoing(): bool
    {
        return $this->direction === MessageDirection::Outgoing;
    }
}
