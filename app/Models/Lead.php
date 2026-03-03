<?php

namespace App\Models;

use App\Enums\Channel;
use App\Enums\LeadSource;
use App\Models\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Lead extends Model
{
    use HasFactory;
    use HasTenantScope;
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
        'last_message_at',
        'last_message_channel',
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
            'last_message_at' => 'datetime',
            'last_message_channel' => Channel::class,
            'source' => LeadSource::class,
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

    public function whatsappMessages(): HasMany
    {
        return $this->hasMany(WhatsAppMessage::class);
    }

    public function socialMessages(): HasMany
    {
        return $this->hasMany(SocialMessage::class);
    }

    /**
     * Get all messages across all channels, merged and sorted by created_at.
     *
     * @return Collection<int, WhatsAppMessage|SocialMessage>
     */
    public function allMessages(): Collection
    {
        $whatsapp = $this->whatsappMessages()
            ->orderBy('created_at')
            ->get()
            ->map(function (WhatsAppMessage $msg) {
                $msg->channel = Channel::Whatsapp;

                return $msg;
            });

        $social = $this->socialMessages()
            ->orderBy('created_at')
            ->get();

        return $whatsapp->concat($social)->sortBy('created_at')->values();
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
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
