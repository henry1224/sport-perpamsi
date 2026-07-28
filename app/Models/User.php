<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'phone', 'position', 'password', 'role', 'account_status', 'regional_committee_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function committee(): BelongsTo
    {
        return $this->belongsTo(RegionalCommittee::class, 'regional_committee_id');
    }

    public function sportAssignments(): HasMany
    {
        return $this->hasMany(SportAssignment::class);
    }

    public function isPdAdmin(): bool
    {
        return $this->role === 'pd_admin';
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdminEvent(): bool
    {
        return $this->role === 'admin_event';
    }

    public function canAccessAdminPortal(): bool
    {
        return in_array($this->role, ['super_admin', 'admin_event'], true);
    }

    public function isVerified(): bool
    {
        return $this->account_status === 'verified';
    }
}
