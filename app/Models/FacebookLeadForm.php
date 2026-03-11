<?php

namespace App\Models;

use App\Models\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacebookLeadForm extends Model
{
    use HasTenantScope;

    protected $fillable = [
        'tenant_id',
        'meta_account_id',
        'form_id',
        'form_name',
        'active',
        'field_mapping',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'field_mapping' => 'array',
            'last_synced_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function metaAccount(): BelongsTo
    {
        return $this->belongsTo(MetaAccount::class);
    }
}
