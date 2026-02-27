<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'currency',
        'leads_limit_per_month',
        'messages_limit_per_day',
        'operators_limit',
        'whatsapp_instances_limit',
        'trial_days',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'leads_limit_per_month' => 'integer',
            'messages_limit_per_day' => 'integer',
            'operators_limit' => 'integer',
            'whatsapp_instances_limit' => 'integer',
            'trial_days' => 'integer',
        ];
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }
}
