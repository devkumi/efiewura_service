<?php

namespace App\Models;

use App\Traits\SoftDeletable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory, SoftDeletable;

    protected $fillable = [
        'booking_id',
        'user_id',
        'landlord_id',
        'type',
        'amount',
        'currency',
        'status',
        'paystack_reference',
        'paystack_transaction_id',
        'paystack_authorization_code',
        'payment_channel',
        'paid_at',
        'failed_at',
        'failure_reason',
        'metadata',
        'refund_amount',
        'refund_reference',
        'refunded_at',
        'refund_reason',
        'deleted_by',
        'deleted_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'metadata' => 'array',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
        'refunded_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function landlord()
    {
        return $this->belongsTo(Landlord::class);
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', 'refunded');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    public static function generateReference(int $bookingId): string
    {
        return 'EFIEW-' . strtoupper(substr(md5($bookingId . microtime()), 0, 8)) . '-' . time();
    }

    public function restore(): static
    {
        $this->update(['deleted_by' => null, 'deleted_at' => null]);
        return $this;
    }
}
