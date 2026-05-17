<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Services\EmailNotificationService;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'property_id',
        'booking_id',
        'triggered_by_user_id',
        'priority',
        'status',
        'is_read',
        'read_at',
        'read_by',
        'deleted_by',
        'deleted_at',
        'email_sent',
        'sms_sent',
        'push_sent',
        'scheduled_for',
        'is_sent',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'email_sent' => 'boolean',
        'sms_sent' => 'boolean',
        'push_sent' => 'boolean',
        'is_sent' => 'boolean',
        'read_at' => 'datetime',
        'deleted_at' => 'datetime',
        'scheduled_for' => 'datetime',
    ];

    /**
     * Scope query to only include active notifications (not soft deleted)
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope query to only include deleted notifications (admin view)
     */
    public function scopeDeleted($query)
    {
        return $query->where('status', 'deleted');
    }

    /**
     * Scope query to include all notifications (active and deleted)
     */
    public function scopeWithDeleted($query)
    {
        return $query; // No filter, shows all
    }

    /**
     * Check if notification is soft deleted
     */
    public function isDeleted()
    {
        return $this->status === 'deleted';
    }

    /**
     * Soft delete the notification
     */
    public function softDelete($deletedBy = null)
    {
        return $this->update([
            'status' => 'deleted',
            'deleted_by' => $deletedBy,
            'deleted_at' => now(),
        ]);
    }

    /**
     * Restore a soft deleted notification
     */
    public function restore()
    {
        return $this->update([
            'status' => 'active',
            'deleted_by' => null,
            'deleted_at' => null,
        ]);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically send email when notification is created
        static::created(function ($notification) {
            try {
                $emailService = new EmailNotificationService();
                $emailService->sendNotificationEmail($notification);
            } catch (\Exception $e) {
                \Log::error("Failed to send email for notification {$notification->id}: " . $e->getMessage());
            }
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function triggeredBy()
    {
        return $this->belongsTo(User::class, 'triggered_by_user_id');
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopePending($query)
    {
        return $query->where('is_sent', false)
                    ->where(function ($q) {
                        $q->whereNull('scheduled_for')
                          ->orWhere('scheduled_for', '<=', now());
                    });
    }

    // Methods
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function markAsUnread()
    {
        $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    // Static methods for creating different notification types
    public static function createBookingConfirmed($booking)
    {
        return self::create([
            'user_id' => $booking->user_id,
            'type' => 'booking_confirmed',
            'title' => 'Booking Confirmed!',
            'message' => "Your booking request for {$booking->property->title} has been confirmed by the landlord. Move-in date: " . $booking->move_in_date->format('M d, Y'),
            'data' => [
                'booking_id' => $booking->id,
                'property_title' => $booking->property->title,
                'move_in_date' => $booking->move_in_date->toDateString(),
                'landlord_name' => $booking->landlord->user->name,
            ],
            'property_id' => $booking->property_id,
            'booking_id' => $booking->id,
            'triggered_by_user_id' => $booking->landlord->user_id,
            'priority' => 'high',
        ]);
    }

    public static function createBookingRejected($booking, $reason = null)
    {
        $message = "Unfortunately, your booking request for {$booking->property->title} has been declined.";
        if ($reason) {
            $message .= " Reason: {$reason}";
        }

        return self::create([
            'user_id' => $booking->user_id,
            'type' => 'booking_rejected',
            'title' => 'Booking Request Declined',
            'message' => $message,
            'data' => [
                'booking_id' => $booking->id,
                'property_title' => $booking->property->title,
                'reason' => $reason,
                'landlord_name' => $booking->landlord->user->name,
            ],
            'property_id' => $booking->property_id,
            'booking_id' => $booking->id,
            'triggered_by_user_id' => $booking->landlord->user_id,
            'priority' => 'medium',
        ]);
    }

    public static function createNewBookingRequest($booking)
    {
        return self::create([
            'user_id' => $booking->landlord->user_id,
            'type' => 'new_booking_request',
            'title' => 'New Booking Request',
            'message' => "New booking request received for {$booking->property->title} from {$booking->tenant_name}. Monthly income: GHS " . number_format($booking->monthly_income, 2),
            'data' => [
                'booking_id' => $booking->id,
                'property_title' => $booking->property->title,
                'tenant_name' => $booking->tenant_name,
                'tenant_email' => $booking->tenant_email,
                'tenant_phone' => $booking->tenant_phone,
                'monthly_income' => $booking->monthly_income,
                'move_in_date' => $booking->move_in_date->toDateString(),
            ],
            'property_id' => $booking->property_id,
            'booking_id' => $booking->id,
            'triggered_by_user_id' => $booking->user_id,
            'priority' => 'high',
        ]);
    }

    public static function createPropertyViewed($property, $viewerUserId = null)
    {
        if (!$viewerUserId || $property->landlord->user_id === $viewerUserId) {
            return null; // Don't notify if landlord views their own property
        }

        // Only notify on milestone views (every 10 views)
        if ($property->views_count % 10 !== 0) {
            return null;
        }

        return self::create([
            'user_id' => $property->landlord->user_id,
            'type' => 'property_viewed_milestone',
            'title' => 'Property Views Milestone!',
            'message' => "Your property '{$property->title}' has reached {$property->views_count} views!",
            'data' => [
                'property_title' => $property->title,
                'total_views' => $property->views_count,
            ],
            'property_id' => $property->id,
            'triggered_by_user_id' => $viewerUserId,
            'priority' => 'low',
        ]);
    }

    public static function createWelcomeMessage($user)
    {
        $roleMessages = [
            'landlord' => "Welcome to Efiewura! Start listing your properties and connect with potential tenants.",
            'tenant' => "Welcome to Efiewura! Browse available properties and find your perfect home.",
            'admin' => "Welcome to Efiewura Admin! You have full access to manage the platform.",
        ];

        return self::create([
            'user_id' => $user->id,
            'type' => 'welcome_message',
            'title' => 'Welcome to Efiewura!',
            'message' => $roleMessages[$user->role] ?? "Welcome to Efiewura! Complete your profile to get started.",
            'data' => [
                'user_role' => $user->role,
                'registration_date' => $user->created_at->toDateString(),
            ],
            'priority' => 'medium',
        ]);
    }

    public static function createLeaseExpiryReminder($booking, $days, $recipientType)
    {
        $isUrgent = $days <= 7;
        $priority = $isUrgent ? 'urgent' : ($days <= 30 ? 'high' : 'medium');
        
        $userId = $recipientType === 'tenant' ? $booking->user_id : $booking->landlord->user_id;
        $recipientName = $recipientType === 'tenant' ? $booking->tenant_name : $booking->landlord->user->name;
        
        $titles = [
            60 => 'Lease Renewal Notice - 2 Months Remaining',
            30 => 'Urgent: Lease Expires in 30 Days',
            7 => 'Final Notice: Lease Expires in 1 Week'
        ];
        
        $messages = [
            'tenant' => [
                60 => "Your lease for {$booking->property->title} expires in 2 months. Please contact your landlord to discuss renewal options.",
                30 => "URGENT: Your lease expires in 30 days! Please arrange renewal or prepare for move-out.",
                7 => "FINAL NOTICE: Your lease expires in 1 week. Immediate action required!"
            ],
            'landlord' => [
                60 => "Tenant lease for {$booking->property->title} expires in 2 months. Consider reaching out about renewal.",
                30 => "URGENT: Tenant lease expires in 30 days. Follow up on renewal status.",
                7 => "FINAL NOTICE: Tenant lease expires in 1 week. Prepare for potential vacancy."
            ]
        ];

        return self::create([
            'user_id' => $userId,
            'type' => 'lease_expiry_reminder',
            'title' => $titles[$days],
            'message' => $messages[$recipientType][$days],
            'data' => [
                'booking_id' => $booking->id,
                'property_title' => $booking->property->title,
                'tenant_name' => $booking->tenant_name,
                'move_out_date' => $booking->move_out_date->toDateString(),
                'reminder_days' => $days,
                'recipient_type' => $recipientType,
                'lease_duration_months' => $booking->lease_duration_months,
            ],
            'property_id' => $booking->property_id,
            'booking_id' => $booking->id,
            'triggered_by_user_id' => null,
            'priority' => $priority,
        ]);
    }

    public static function createPaymentReminder($booking, $reminderType, $dueDate)
    {
        $priorityMap = [
            'payment_reminder_5_days' => 'medium',
            'payment_due_today' => 'high',
            'payment_overdue_3_days' => 'urgent'
        ];
        
        $titleMap = [
            'payment_reminder_5_days' => 'Rent Payment Reminder - Due in 5 Days',
            'payment_due_today' => 'Rent Payment Due Today',
            'payment_overdue_3_days' => 'URGENT: Rent Payment 3 Days Overdue'
        ];
        
        $messageMap = [
            'payment_reminder_5_days' => "Your rent payment of {$booking->currency} " . number_format($booking->monthly_rent, 2) . " for {$booking->property->title} is due in 5 days.",
            'payment_due_today' => "Your rent payment is due TODAY! Please pay {$booking->currency} " . number_format($booking->monthly_rent, 2) . " to avoid late fees.",
            'payment_overdue_3_days' => "URGENT: Your rent payment is 3 days overdue! Please pay immediately to avoid lease termination."
        ];

        return self::create([
            'user_id' => $booking->user_id,
            'type' => $reminderType,
            'title' => $titleMap[$reminderType],
            'message' => $messageMap[$reminderType],
            'data' => [
                'booking_id' => $booking->id,
                'property_title' => $booking->property->title,
                'monthly_rent' => $booking->monthly_rent,
                'currency' => $booking->currency,
                'due_date' => $dueDate->toDateString(),
                'landlord_name' => $booking->landlord->user->name,
                'landlord_phone' => $booking->landlord->phone,
            ],
            'property_id' => $booking->property_id,
            'booking_id' => $booking->id,
            'triggered_by_user_id' => null,
            'priority' => $priorityMap[$reminderType],
        ]);
    }

    public static function createMoveInReminder($booking, $days)
    {
        $isToday = $days === 0;
        $type = $isToday ? 'move_in_today' : 'move_in_reminder_7_days';
        
        $title = $isToday ? 'Welcome to Your New Home!' : 'Move-in Reminder - 7 Days';
        $message = $isToday 
            ? "Welcome to your new home at {$booking->property->title}! We hope you enjoy your stay."
            : "Your move-in date for {$booking->property->title} is in 7 days. Here's your preparation checklist.";

        return self::create([
            'user_id' => $booking->user_id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => [
                'booking_id' => $booking->id,
                'property_title' => $booking->property->title,
                'move_in_date' => $booking->move_in_date->toDateString(),
                'landlord_name' => $booking->landlord->user->name,
                'landlord_phone' => $booking->landlord->phone,
                'total_amount_due' => $booking->total_amount,
                'currency' => $booking->currency,
            ],
            'property_id' => $booking->property_id,
            'booking_id' => $booking->id,
            'triggered_by_user_id' => null,
            'priority' => $isToday ? 'high' : 'medium',
        ]);
    }

    public static function createMoveOutReminder($booking)
    {
        return self::create([
            'user_id' => $booking->user_id,
            'type' => 'move_out_reminder',
            'title' => 'Move-out Reminder - 7 Days',
            'message' => "Your lease for {$booking->property->title} expires in 7 days. Please prepare for move-out and schedule a final inspection.",
            'data' => [
                'booking_id' => $booking->id,
                'property_title' => $booking->property->title,
                'move_out_date' => $booking->move_out_date->toDateString(),
                'landlord_name' => $booking->landlord->user->name,
                'landlord_phone' => $booking->landlord->phone,
                'security_deposit' => $booking->security_deposit,
                'currency' => $booking->currency,
            ],
            'property_id' => $booking->property_id,
            'booking_id' => $booking->id,
            'triggered_by_user_id' => null,
            'priority' => 'high',
        ]);
    }

    public static function createPaymentReceived($booking, $payment)
    {
        return self::create([
            'user_id' => $booking->user_id,
            'type' => 'payment_received',
            'title' => 'Payment Received',
            'message' => "Your payment of {$booking->currency} " . number_format($payment->amount, 2) . " for {$booking->property->title} has been received successfully.",
            'data' => [
                'booking_id' => $booking->id,
                'payment_id' => $payment->id,
                'property_title' => $booking->property->title,
                'amount' => $payment->amount,
                'currency' => $booking->currency,
                'payment_type' => $payment->type,
                'reference' => $payment->paystack_reference,
                'channel' => $payment->payment_channel,
            ],
            'property_id' => $booking->property_id,
            'booking_id' => $booking->id,
            'priority' => 'high',
        ]);
    }

    public static function createPaymentFailed($booking, $payment)
    {
        return self::create([
            'user_id' => $booking->user_id,
            'type' => 'payment_failed',
            'title' => 'Payment Failed',
            'message' => "Your payment of {$booking->currency} " . number_format($payment->amount, 2) . " for {$booking->property->title} could not be processed. " . ($payment->failure_reason ? "Reason: {$payment->failure_reason}" : 'Please try again.'),
            'data' => [
                'booking_id' => $booking->id,
                'payment_id' => $payment->id,
                'property_title' => $booking->property->title,
                'amount' => $payment->amount,
                'currency' => $booking->currency,
                'failure_reason' => $payment->failure_reason,
                'reference' => $payment->paystack_reference,
            ],
            'property_id' => $booking->property_id,
            'booking_id' => $booking->id,
            'priority' => 'urgent',
        ]);
    }

    public static function createRefundProcessed($booking, $payment)
    {
        return self::create([
            'user_id' => $booking->user_id,
            'type' => 'refund_processed',
            'title' => 'Refund Processed',
            'message' => "A refund of {$booking->currency} " . number_format($payment->refund_amount, 2) . " for {$booking->property->title} has been processed and will reflect in your account within 3-5 business days.",
            'data' => [
                'booking_id' => $booking->id,
                'payment_id' => $payment->id,
                'property_title' => $booking->property->title,
                'original_amount' => $payment->amount,
                'refund_amount' => $payment->refund_amount,
                'currency' => $booking->currency,
                'refund_reason' => $payment->refund_reason,
                'refund_reference' => $payment->refund_reference,
            ],
            'property_id' => $booking->property_id,
            'booking_id' => $booking->id,
            'priority' => 'high',
        ]);
    }

    public static function createPropertyAutoReleased($booking, $daysPastDue, $recipientType)
    {
        $userId = $recipientType === 'tenant' ? $booking->user_id : $booking->landlord->user_id;
        
        $titles = [
            'tenant' => 'Lease Cancelled - Payment Overdue',
            'landlord' => 'Property Auto-Released - Tenant Overdue'
        ];
        
        $messages = [
            'tenant' => "Your lease for {$booking->property->title} has been automatically cancelled due to {$daysPastDue} days overdue payment. Please contact support for assistance.",
            'landlord' => "Property {$booking->property->title} has been automatically released back to the market due to tenant payment being {$daysPastDue} days overdue."
        ];

        return self::create([
            'user_id' => $userId,
            'type' => 'property_auto_released',
            'title' => $titles[$recipientType],
            'message' => $messages[$recipientType],
            'data' => [
                'booking_id' => $booking->id,
                'property_title' => $booking->property->title,
                'days_past_due' => $daysPastDue,
                'recipient_type' => $recipientType,
                'cancellation_reason' => "Auto-cancelled due to {$daysPastDue} days overdue payment",
                'monthly_rent' => $booking->monthly_rent,
                'currency' => $booking->currency,
            ],
            'property_id' => $booking->property_id,
            'booking_id' => $booking->id,
            'triggered_by_user_id' => null,
            'priority' => 'urgent',
        ]);
    }
}
