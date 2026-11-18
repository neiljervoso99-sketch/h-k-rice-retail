<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'profile_picture',
        'staff_permissions',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'role' => 'string',
            'staff_permissions' => 'array',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->is_admin;
    }

    public function isRider(): bool
    {
        return $this->role === 'rider';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff' && ! $this->is_admin;
    }

    public function hasStaffPermission(string $permission): bool
    {
        if ($this->isAdmin() || in_array($permission, ['dashboard', 'profile'], true)) {
            return true;
        }

        $permissions = $this->staff_permissions ?? [
            'sales',
            'manual_transactions',
        ];

        return $this->isStaff()
            && in_array($permission, $permissions, true);
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer' && ! $this->is_admin;
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class, 'user_id');
    }

    public function assignedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'rider_id');
    }

    public function cashRegisterShifts(): HasMany
    {
        return $this->hasMany(CashRegisterShift::class, 'opened_by');
    }

    public function loginHistories(): HasMany
    {
        return $this->hasMany(LoginHistory::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function favoriteProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'favorites')->withTimestamps();
    }
}
