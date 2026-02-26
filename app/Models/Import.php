<?php

namespace App\Models;

use App\Models\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Import extends Model
{
    use HasTenantScope;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'filename',
        'type',
        'status',
        'total_rows',
        'imported',
        'duplicated',
        'errors',
        'mapping',
        'deduplication_rules',
        'tags',
        'report',
        'assigned_operator_id',
    ];

    protected function casts(): array
    {
        return [
            'mapping' => 'array',
            'deduplication_rules' => 'array',
            'tags' => 'array',
            'report' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedOperator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_operator_id');
    }
}
