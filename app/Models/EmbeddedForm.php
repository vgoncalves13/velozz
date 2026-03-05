<?php

namespace App\Models;

use App\Models\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmbeddedForm extends Model
{
    /** @use HasFactory<\Database\Factories\EmbeddedFormFactory> */
    use HasFactory, HasTenantScope, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'fields',
        'styles',
        'status',
        'redirect_url',
    ];

    protected function casts(): array
    {
        return [
            'fields' => 'array',
            'styles' => 'array',
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
