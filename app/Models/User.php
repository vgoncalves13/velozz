<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'phone',
        'role',
        'status',
        'locale',
        'photo',
        'invite_token',
        'invite_expires_at',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'invite_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'invite_expires_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assignedLeads(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_user_id');
    }

    public function sentMessages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WhatsAppMessage::class, 'sent_by_user_id');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            // Only admin_master (no tenant_id) can access admin panel
            return $this->role === 'admin_master' && $this->tenant_id === null;
        }

        if ($panel->getId() === 'client') {
            // Must have appropriate role
            if (! in_array($this->role, ['admin_client', 'supervisor', 'operator', 'financial'])) {
                return false;
            }

            // CRITICAL: Must belong to the current tenant
            // Get tenant from container or directly from database
            $currentTenant = app()->bound('tenant') ? app('tenant') : null;

            if (! $currentTenant) {
                // Fallback: get tenant directly from request host
                $host = request()->getHost();
                $currentTenant = Tenant::where('domain', $host)->first();
            }

            if (! $currentTenant) {
                return false;
            }

            return $this->tenant_id === $currentTenant->id;
        }

        return false;
    }

    public function isAdminMaster(): bool
    {
        return $this->role === 'admin_master';
    }

    public function isAdminClient(): bool
    {
        return $this->role === 'admin_client';
    }

    public function isSupervisor(): bool
    {
        return $this->role === 'supervisor';
    }

    public function isOperator(): bool
    {
        return $this->role === 'operator';
    }

    public function isFinancial(): bool
    {
        return $this->role === 'financial';
    }
}
