<?php

namespace App\Models;

use App\Traits\SoftDeletable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Booking extends Model
{
    use HasFactory, SoftDeletable;

    protected $fillable = [
        'property_id',
        'user_id',
        'landlord_id',
        'status',
        'payment_status',
        'payment_initiated_at',
        'payment_completed_at',
        'move_in_date',
        'move_out_date',
        'lease_duration_months',
        'monthly_rent',
        'security_deposit',
        'total_amount',
        'currency',
        'tenant_name',
        'tenant_phone',
        'tenant_email',
        'tenant_message',
        'occupation',
        'employer',
        'monthly_income',
        'emergency_contact',
        'confirmed_at',
        'cancelled_at',
        'rejected_at',
        'cancellation_reason',
        'admin_notes',
        'deleted_by',
        'deleted_at',
    ];

    protected $casts = [
        'move_in_date' => 'date',
        'move_out_date' => 'date',
        'monthly_rent' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'monthly_income' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'rejected_at' => 'datetime',
        'payment_initiated_at' => 'datetime',
        'payment_completed_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function restore(): static
    {
        $this->update(['status' => 'pending', 'deleted_by' => null, 'deleted_at' => null]);
        return $this;
    }

    // Relationships
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function landlord()
    {
        return $this->belongsTo(Landlord::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment()
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'confirmed'])->notDeleted();
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'unpaid');
    }

    // Accessors
    public function getFormattedMonthlyRentAttribute()
    {
        return $this->currency . ' ' . number_format($this->monthly_rent, 2);
    }

    public function getFormattedTotalAmountAttribute()
    {
        return $this->currency . ' ' . number_format($this->total_amount, 2);
    }

    public function getIsActiveAttribute()
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    // Methods
    public function confirm()
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }

    public function reject()
    {
        $this->update([
            'status' => 'rejected',
            'rejected_at' => now(),
        ]);
    }

    public function cancel()
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }
}
