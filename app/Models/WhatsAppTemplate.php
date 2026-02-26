<?php

namespace App\Models;

use App\Models\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppTemplate extends Model
{
    use HasTenantScope;

    protected $table = 'whatsapp_templates';

    protected $fillable = [
        'tenant_id',
        'name',
        'content',
        'active',
        'trigger_on',
        'pipeline_stage_id',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function pipelineStage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class);
    }

    /**
     * Render template with variables
     */
    public function render(Lead $lead, ?User $operator = null): string
    {
        $variables = [
            '{name}' => $lead->full_name ?? 'there',
            '{company}' => $lead->tenant->name ?? '',
            '{operator}' => $operator?->name ?? auth()->user()?->name ?? 'Team',
            '{date}' => now()->format('d/m/Y'),
            '{product}' => '',
            '{link}' => '',
        ];

        return str_replace(array_keys($variables), array_values($variables), $this->content);
    }
}
