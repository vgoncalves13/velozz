<?php

namespace App\Models;

use App\Models\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PipelineStage extends Model
{
    use HasFactory;
    use HasTenantScope;

    protected $fillable = [
        'tenant_id',
        'name',
        'color',
        'order',
        'icon',
        'sla_hours',
        'entry_automation',
        'exit_automation',
    ];

    protected function casts(): array
    {
        return [
            'entry_automation' => 'array',
            'exit_automation' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }
}
