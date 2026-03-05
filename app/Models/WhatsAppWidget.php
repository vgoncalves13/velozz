<?php

namespace App\Models;

use App\Models\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhatsAppWidget extends Model
{
    /** @use HasFactory<\Database\Factories\WhatsAppWidgetFactory> */
    use HasFactory, HasTenantScope, SoftDeletes;

    protected $table = 'whatsapp_widgets';

    protected $fillable = [
        'tenant_id',
        'name',
        'whatsapp_number',
        'auto_message',
        'position',
        'appearance',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'appearance' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
