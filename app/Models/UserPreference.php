<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserPreference extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'timezone',
        'date_format',
        'theme',
        'dashboard_refresh_interval',
        'email_notifications',
        'sms_notifications',
        'new_bookings',
        'property_updates',
        'user_registrations',
        'system_alerts',
        'weekly_reports',
        'bio',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_notifications' => 'boolean',
        'sms_notifications' => 'boolean',
        'new_bookings' => 'boolean',
        'property_updates' => 'boolean',
        'user_registrations' => 'boolean',
        'system_alerts' => 'boolean',
        'weekly_reports' => 'boolean',
        'dashboard_refresh_interval' => 'integer',
    ];

    /**
     * Get the user that owns the preferences.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get default preferences for a new user
     */
    public static function getDefaults()
    {
        return [
            'timezone' => 'Africa/Lagos',
            'date_format' => 'DD/MM/YYYY',
            'theme' => 'light',
            'dashboard_refresh_interval' => 30,
            'email_notifications' => true,
            'sms_notifications' => false,
            'new_bookings' => true,
            'property_updates' => true,
            'user_registrations' => false,
            'system_alerts' => true,
            'weekly_reports' => false,
            'bio' => null,
        ];
    }

    /**
     * Get notification preferences as structured array
     */
    public function getNotificationPreferences()
    {
        return [
            'email' => $this->email_notifications,
            'sms' => $this->sms_notifications,
            'new_bookings' => $this->new_bookings,
            'property_updates' => $this->property_updates,
            'user_registrations' => $this->user_registrations,
            'system_alerts' => $this->system_alerts,
            'weekly_reports' => $this->weekly_reports,
        ];
    }

    /**
     * Get display preferences as structured array
     */
    public function getDisplayPreferences()
    {
        return [
            'timezone' => $this->timezone,
            'date_format' => $this->date_format,
            'theme' => $this->theme,
            'dashboard_refresh_interval' => $this->dashboard_refresh_interval,
        ];
    }
}
