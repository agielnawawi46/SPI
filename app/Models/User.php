<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'department_id',
        'position',
        'nip',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // ============================================
    // Relationships
    // ============================================

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'created_by');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(Approval::class, 'approver_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(SpiNotification::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    // ============================================
    // Helper Methods
    // ============================================

    /**
     * Get the primary role name for display.
     */
    public function getRoleNameAttribute(): string
    {
        return $this->getRoleNames()->first() ?? '-';
    }

    /**
     * Get unread notification count.
     */
    public function unreadNotificationsCount(): int
    {
        return $this->notifications()->where('is_read', false)->count();
    }

    /**
     * Check if user is in the SPI internal team (Auditor, KSPI).
     */
    public function isSpiTeam(): bool
    {
        return $this->hasAnyRole(['kspi', 'auditor']);
    }

    /**
     * Check if user is an Auditee (either manager or staff level).
     */
    public function isAuditee(): bool
    {
        return $this->hasAnyRole(['manager', 'asman', 'staff']);
    }
}
