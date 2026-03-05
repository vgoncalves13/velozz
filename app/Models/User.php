<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasDefaultTenant;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasDefaultTenant, HasTenants
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
            return $this->role === 'admin_master' && $this->tenant_id === null;
        }

        if ($panel->getId() === 'client') {
            if ($this->isAdminMaster()) {
                return true;
            }

            return in_array($this->role, ['admin_client', 'supervisor', 'operator', 'financial']);
        }

        return false;
    }

    public function getTenants(Panel $panel): Collection
    {
        if ($this->isAdminMaster()) {
            return Tenant::all();
        }

        return $this->tenant ? collect([$this->tenant]) : collect();
    }

    public function canAccessTenant(EloquentModel $tenant): bool
    {
        if ($this->isAdminMaster()) {
            return true;
        }

        return $this->tenant_id === $tenant->id;
    }

    public function getDefaultTenant(Panel $panel): ?EloquentModel
    {
        return $this->tenant;
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
