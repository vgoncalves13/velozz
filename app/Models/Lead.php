<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'full_name',
        'email',
        'phones',
        'whatsapps',
        'primary_whatsapp_index',
        'street_type',
        'street_name',
        'number',
        'complement',
        'district',
        'neighborhood',
        'region',
        'city',
        'postal_code',
        'country',
        'source',
        'assigned_user_id',
        'pipeline_stage_id',
        'tags',
        'priority',
        'consent_status',
        'consent_date',
        'opt_out',
        'opt_out_reason',
        'opt_out_date',
        'do_not_contact',
        'notes',
        'custom_fields',
    ];

    protected function casts(): array
    {
        return [
            'phones' => 'array',
            'whatsapps' => 'array',
            'tags' => 'array',
            'custom_fields' => 'array',
            'consent_date' => 'date',
            'opt_out' => 'boolean',
            'opt_out_date' => 'date',
            'do_not_contact' => 'boolean',
        ];
    }

    /**
     * Set phones attribute - reindex array to remove UUID keys from Filament Repeater
     */
    public function setPhonesAttribute($value): void
    {
        $this->attributes['phones'] = ! empty($value) && is_array($value)
            ? json_encode(array_values($value))
            : null;
    }

    /**
     * Set whatsapps attribute - reindex array to remove UUID keys from Filament Repeater
     */
    public function setWhatsappsAttribute($value): void
    {
        $this->attributes['whatsapps'] = ! empty($value) && is_array($value)
            ? json_encode(array_values($value))
            : null;
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function pipelineStage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class);
    }

    /**
     * Get the primary WhatsApp number
     */
    public function getPrimaryWhatsAppAttribute(): ?string
    {
        if (empty($this->whatsapps) || ! is_array($this->whatsapps)) {
            return null;
        }

        $index = $this->primary_whatsapp_index ?? 0;

        return $this->whatsapps[$index] ?? $this->whatsapps[0] ?? null;
    }

    /**
     * Get the first phone number
     */
    public function getFirstPhoneAttribute(): ?string
    {
        return ! empty($this->phones) && is_array($this->phones) ? $this->phones[0] : null;
    }

    /**
     * Get the first WhatsApp number
     */
    public function getFirstWhatsAppAttribute(): ?string
    {
        return ! empty($this->whatsapps) && is_array($this->whatsapps) ? $this->whatsapps[0] : null;
    }
}
