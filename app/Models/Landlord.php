<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Landlord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_name',
        'business_registration_number',
        'phone',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'commission_rate',
        'status',
        'verified',
        'verified_at',
        'overdue_release_days',
    ];

    protected $casts = [
        'verified' => 'boolean',
        'verified_at' => 'datetime',
        'commission_rate' => 'decimal:2',
    ];

    /**
     * Get the user that owns the landlord profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if landlord is verified
     */
    public function isVerified(): bool
    {
        return $this->verified;
    }

    /**
     * Check if landlord is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get the properties owned by this landlord.
     */
    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    /**
     * Get the bookings for this landlord's properties.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the available properties owned by this landlord.
     */
    public function availableProperties()
    {
        return $this->hasMany(Property::class)->available();
    }

    /**
     * Get the total number of properties owned by this landlord.
     */
    public function getTotalPropertiesAttribute(): int
    {
        return $this->properties()->count();
    }

    /**
     * Get the total number of available properties owned by this landlord.
     */
    public function getAvailablePropertiesCountAttribute(): int
    {
        return $this->availableProperties()->count();
    }
}
