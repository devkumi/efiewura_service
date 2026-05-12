<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\SoftDeletable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, SoftDeletable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'password',
        'role',
        'status',
        'deleted_by',
        'deleted_at',
        'two_factor_enabled',
        'two_factor_required',
        'two_factor_method',
        'two_factor_phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'recovery_codes',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<string>
     */
    protected $appends = [
        'name',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'deleted_at' => 'datetime',
            'two_factor_enabled' => 'boolean',
            'two_factor_required' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_recovery_codes' => 'encrypted:array',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'deleted');
    }

    /**
     * Get the user's full name by combining firstname and lastname
     */
    public function getFullNameAttribute(): string
    {
        return trim(($this->firstname ?? '') . ' ' . ($this->lastname ?? ''));
    }

    /**
     * Set the full name by splitting into firstname and lastname
     */
    public function setFullNameAttribute($value): void
    {
        $names = explode(' ', trim($value), 2);
        $this->firstname = $names[0] ?? '';
        $this->lastname = $names[1] ?? '';
    }

    /**
     * Get the user's display name (alias for full_name)
     */
    public function getNameAttribute(): string
    {
        return $this->getFullNameAttribute();
    }

    /**
     * Get the user preferences associated with the user.
     */
    public function preferences()
    {
        return $this->hasOne(UserPreference::class);
    }

    /**
     * Get the landlord profile associated with the user.
     */
    public function landlord()
    {
        return $this->hasOne(Landlord::class);
    }

    /**
     * Get the tenant profile associated with the user.
     */
    public function tenant()
    {
        return $this->hasOne(Tenant::class);
    }

    /**
     * Get the bookings associated with the user.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class)->notDeleted();
    }

    /**
     * Get all bookings including deleted ones (admin use)
     */
    public function allBookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the notifications associated with the user.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class)->active();
    }

    /**
     * Get all notifications including deleted ones (admin use)
     */
    public function allNotifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Check if user is a landlord
     */
    public function isLandlord(): bool
    {
        return $this->role === 'landlord';
    }

    /**
     * Check if user is a tenant
     */
    public function isTenant(): bool
    {
        return $this->role === 'tenant';
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Get role-specific profile
     */
    public function getProfileAttribute()
    {
        if ($this->isLandlord()) {
            return $this->landlord;
        }
        
        if ($this->isTenant()) {
            return $this->tenant;
        }
        
        return null;
    }

    /**
     * Check if 2FA is set up and confirmed
     */
    public function hasTwoFactorAuthentication(): bool
    {
        return !is_null($this->two_factor_secret) && !is_null($this->two_factor_confirmed_at);
    }

    /**
     * Check if 2FA is required for this user
     */
    public function requiresTwoFactorAuthentication(): bool
    {
        return $this->two_factor_required || $this->two_factor_enabled;
    }

    /**
     * Generate 2FA recovery codes
     */
    public function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(\Illuminate\Support\Str::random(8));
        }

        $this->two_factor_recovery_codes = $codes;
        $this->save();

        return $codes;
    }

    /**
     * Use a recovery code
     */
    public function useRecoveryCode(string $code): bool
    {
        $codes = $this->two_factor_recovery_codes ?? [];
        $code = strtoupper($code);
        
        if (($key = array_search($code, $codes)) !== false) {
            unset($codes[$key]);
            $this->two_factor_recovery_codes = array_values($codes);
            $this->save();
            return true;
        }
        
        return false;
    }

    /**
     * Disable 2FA
     */
    public function disableTwoFactorAuthentication(): void
    {
        $this->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_enabled' => false,
        ]);
    }
}
