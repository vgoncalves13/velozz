<?php

namespace App\Models;

use App\Models\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsAppInstance extends Model
{
    use HasTenantScope;

    protected $table = 'whatsapp_instances';

    protected $fillable = [
        'tenant_id',
        'instance_id',
        'token',
        'status',
        'qr_code',
        'phone_number',
        'webhook_url',
        'last_connected_at',
    ];

    protected function casts(): array
    {
        return [
            'last_connected_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(WhatsAppMessage::class);
    }

    /**
     * Check if instance is connected
     */
    public function isConnected(): bool
    {
        return $this->status === 'connected';
    }

    /**
     * Check if instance needs QR code
     */
    public function needsQrCode(): bool
    {
        return in_array($this->status, ['disconnected', 'connecting']);
    }
}
