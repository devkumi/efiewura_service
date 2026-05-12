<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone',
        'date_of_birth',
        'gender',
        'occupation',
        'employer',
        'monthly_income',
        'current_address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'status',
        'verified',
        'verified_at',
    ];

    protected $casts = [
        'verified' => 'boolean',
        'verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'monthly_income' => 'decimal:2',
    ];

    /**
     * Get the user that owns the tenant profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if tenant is verified
     */
    public function isVerified(): bool
    {
        return $this->verified;
    }

    /**
     * Check if tenant is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if tenant is blacklisted
     */
    public function isBlacklisted(): bool
    {
        return $this->status === 'blacklisted';
    }
}
