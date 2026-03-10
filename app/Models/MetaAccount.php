<?php

namespace App\Models;

use App\Enums\Channel;
use App\Models\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetaAccount extends Model
{
    use HasFactory;
    use HasTenantScope;

    protected $fillable = [
        'tenant_id',
        'type',
        'page_id',
        'page_name',
        'instagram_user_id',
        'access_token',
        'status',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'type' => Channel::class,
            'access_token' => 'encrypted',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function socialMessages(): HasMany
    {
        return $this->hasMany(SocialMessage::class);
    }

    public function facebookLeadForms(): HasMany
    {
        return $this->hasMany(FacebookLeadForm::class);
    }

    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function isConnected(): bool
    {
        return $this->status === 'connected';
    }
}
