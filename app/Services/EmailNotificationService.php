<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Booking;
use App\Models\Property;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class EmailNotificationService
{
    /**
     * Send email notification based on notification type
     */
    public function sendNotificationEmail(Notification $notification): bool
    {
        try {
            $user = $notification->user;
            
            if (!$user || !$user->email) {
                Log::warning("Cannot send email: User or email not found for notification {$notification->id}");
                return false;
            }

            $emailData = $this->prepareEmailData($notification);
            
            if (!$emailData) {
                Log::warning("Email data preparation failed for notification {$notification->id}");
                return false;
            }

            // Send email based on notification type
            switch ($notification->type) {
                case 'welcome_message':
                    return $this->sendWelcomeEmail($user, $notification, $emailData);
                    
                case 'new_booking_request':
                    return $this->sendNewBookingRequestEmail($user, $notification, $emailData);
                    
                case 'booking_confirmed':
                    return $this->sendBookingConfirmedEmail($user, $notification, $emailData);
                    
                case 'booking_rejected':
                    return $this->sendBookingRejectedEmail($user, $notification, $emailData);
                    
                case 'property_viewed_milestone':
                    return $this->sendPropertyMilestoneEmail($user, $notification, $emailData);
                    
                case 'lease_expiry_reminder':
                    return $this->sendLeaseExpiryReminderEmail($user, $notification, $emailData);
                    
                case 'payment_reminder_5_days':
                case 'payment_due_today':
                case 'payment_overdue_3_days':
                    return $this->sendPaymentReminderEmail($user, $notification, $emailData);
                    
                case 'move_in_reminder_7_days':
                case 'move_in_today':
                    return $this->sendMoveInReminderEmail($user, $notification, $emailData);
                    
                case 'move_out_reminder':
                    return $this->sendMoveOutReminderEmail($user, $notification, $emailData);
                    
                case 'property_auto_released':
                    return $this->sendPropertyAutoReleasedEmail($user, $notification, $emailData);
                    
                default:
                    Log::warning("Unknown notification type: {$notification->type}");
                    return false;
            }
            
        } catch (Exception $e) {
            Log::error("Failed to send email for notification {$notification->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Prepare email data based on notification type
     */
    private function prepareEmailData(Notification $notification): ?array
    {
        try {
            $data = [
                'notification' => $notification,
                'user' => $notification->user,
            ];

            // Add related models based on notification type
            if ($notification->booking_id) {
                $data['booking'] = Booking::with(['property', 'landlord.user', 'user'])->find($notification->booking_id);
                if (!$data['booking']) {
                    Log::warning("Booking not found for notification {$notification->id}");
                    return null;
                }
            }

            if ($notification->property_id) {
                $data['property'] = Property::with(['landlord.user', 'category'])->find($notification->property_id);
                if (!$data['property']) {
                    Log::warning("Property not found for notification {$notification->id}");
                    return null;
                }
            }

            // Add specific data based on notification type
            switch ($notification->type) {
                case 'new_booking_request':
                    $data['landlord'] = $data['booking']->landlord ?? null;
                    break;
                    
                case 'property_viewed_milestone':
                    $data['landlord'] = $data['property']->landlord ?? null;
                    break;
            }

            return $data;
            
        } catch (Exception $e) {
            Log::error("Failed to prepare email data for notification {$notification->id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Send welcome email
     */
    private function sendWelcomeEmail(User $user, Notification $notification, array $data): bool
    {
        try {
            Mail::send('emails.welcome', $data, function ($message) use ($user, $notification) {
                $message->to($user->email, $user->name)
                       ->subject('Welcome to Efiewura - Your Property Journey Starts Here!')
                       ->from(config('mail.from.address'), config('mail.from.name'));
            });

            $this->markEmailSent($notification);
            return true;
            
        } catch (Exception $e) {
            Log::error("Failed to send welcome email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send new booking request email
     */
    private function sendNewBookingRequestEmail(User $user, Notification $notification, array $data): bool
    {
        try {
            $propertyTitle = $data['property']->title ?? 'Property';
            $tenantName = $data['booking']->tenant_name ?? 'Tenant';
            
            Mail::send('emails.new-booking-request', $data, function ($message) use ($user, $notification, $propertyTitle, $tenantName) {
                $message->to($user->email, $user->name)
                       ->subject("New Booking Request for {$propertyTitle} from {$tenantName}")
                       ->from(config('mail.from.address'), config('mail.from.name'));
            });

            $this->markEmailSent($notification);
            return true;
            
        } catch (Exception $e) {
            Log::error("Failed to send new booking request email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send booking confirmed email
     */
    private function sendBookingConfirmedEmail(User $user, Notification $notification, array $data): bool
    {
        try {
            $propertyTitle = $data['property']->title ?? 'Property';
            
            Mail::send('emails.booking-confirmed', $data, function ($message) use ($user, $notification, $propertyTitle) {
                $message->to($user->email, $user->name)
                       ->subject("🎉 Booking Confirmed for {$propertyTitle}!")
                       ->from(config('mail.from.address'), config('mail.from.name'));
            });

            $this->markEmailSent($notification);
            return true;
            
        } catch (Exception $e) {
            Log::error("Failed to send booking confirmed email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send booking rejected email
     */
    private function sendBookingRejectedEmail(User $user, Notification $notification, array $data): bool
    {
        try {
            $propertyTitle = $data['property']->title ?? 'Property';
            
            Mail::send('emails.booking-rejected', $data, function ($message) use ($user, $notification, $propertyTitle) {
                $message->to($user->email, $user->name)
                       ->subject("Booking Update for {$propertyTitle}")
                       ->from(config('mail.from.address'), config('mail.from.name'));
            });

            $this->markEmailSent($notification);
            return true;
            
        } catch (Exception $e) {
            Log::error("Failed to send booking rejected email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send property milestone email
     */
    private function sendPropertyMilestoneEmail(User $user, Notification $notification, array $data): bool
    {
        try {
            $propertyTitle = $data['property']->title ?? 'Property';
            $views = $data['property']->views_count ?? 0;
            
            Mail::send('emails.property-milestone', $data, function ($message) use ($user, $notification, $propertyTitle, $views) {
                $message->to($user->email, $user->name)
                       ->subject("🎯 {$propertyTitle} reached {$views} views!")
                       ->from(config('mail.from.address'), config('mail.from.name'));
            });

            $this->markEmailSent($notification);
            return true;
            
        } catch (Exception $e) {
            Log::error("Failed to send property milestone email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send lease expiry reminder email
     */
    private function sendLeaseExpiryReminderEmail(User $user, Notification $notification, array $data): bool
    {
        try {
            $propertyTitle = $data['property']->title ?? 'Property';
            $days = $notification->data['reminder_days'] ?? 7;
            
            $subjectMap = [
                60 => "Lease Renewal Notice - {$propertyTitle} (2 Months)",
                30 => "Urgent: Lease Expires Soon - {$propertyTitle}",
                7 => "Final Notice: Lease Expires in 1 Week - {$propertyTitle}"
            ];
            
            $subject = $subjectMap[$days] ?? "Lease Expiry Reminder - {$propertyTitle}";
            
            Mail::send('emails.lease-expiry-reminder', $data, function ($message) use ($user, $notification, $subject) {
                $message->to($user->email, $user->name)
                       ->subject($subject)
                       ->from(config('mail.from.address'), config('mail.from.name'));
            });

            $this->markEmailSent($notification);
            return true;
            
        } catch (Exception $e) {
            Log::error("Failed to send lease expiry reminder email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send payment reminder email
     */
    private function sendPaymentReminderEmail(User $user, Notification $notification, array $data): bool
    {
        try {
            $propertyTitle = $data['property']->title ?? 'Property';
            $amount = $notification->data['monthly_rent'] ?? 0;
            $currency = $notification->data['currency'] ?? 'GHS';
            
            $subjectMap = [
                'payment_reminder_5_days' => "Rent Due in 5 Days - {$propertyTitle}",
                'payment_due_today' => "Rent Payment Due Today - {$propertyTitle}",
                'payment_overdue_3_days' => "URGENT: Rent Overdue - {$propertyTitle}"
            ];
            
            $subject = $subjectMap[$notification->type] ?? "Payment Reminder - {$propertyTitle}";
            
            Mail::send('emails.payment-reminder', $data, function ($message) use ($user, $notification, $subject) {
                $message->to($user->email, $user->name)
                       ->subject($subject)
                       ->from(config('mail.from.address'), config('mail.from.name'));
            });

            $this->markEmailSent($notification);
            return true;
            
        } catch (Exception $e) {
            Log::error("Failed to send payment reminder email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send move-in reminder email
     */
    private function sendMoveInReminderEmail(User $user, Notification $notification, array $data): bool
    {
        try {
            $propertyTitle = $data['property']->title ?? 'Property';
            $isToday = $notification->type === 'move_in_today';
            
            $subject = $isToday 
                ? "🏠 Welcome to Your New Home - {$propertyTitle}!"
                : "Move-in Reminder - {$propertyTitle} (7 Days)";
            
            Mail::send('emails.move-in-reminder', $data, function ($message) use ($user, $notification, $subject) {
                $message->to($user->email, $user->name)
                       ->subject($subject)
                       ->from(config('mail.from.address'), config('mail.from.name'));
            });

            $this->markEmailSent($notification);
            return true;
            
        } catch (Exception $e) {
            Log::error("Failed to send move-in reminder email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send move-out reminder email
     */
    private function sendMoveOutReminderEmail(User $user, Notification $notification, array $data): bool
    {
        try {
            $propertyTitle = $data['property']->title ?? 'Property';
            
            Mail::send('emails.move-out-reminder', $data, function ($message) use ($user, $notification, $propertyTitle) {
                $message->to($user->email, $user->name)
                       ->subject("📦 Move-out Reminder - {$propertyTitle} (7 Days)")
                       ->from(config('mail.from.address'), config('mail.from.name'));
            });

            $this->markEmailSent($notification);
            return true;
            
        } catch (Exception $e) {
            Log::error("Failed to send move-out reminder email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send property auto-released email
     */
    private function sendPropertyAutoReleasedEmail(User $user, Notification $notification, array $data): bool
    {
        try {
            $propertyTitle = $data['property']->title ?? 'Property';
            $recipientType = $notification->data['recipient_type'] ?? 'tenant';
            $daysPastDue = $notification->data['days_past_due'] ?? 0;
            
            $subjectMap = [
                'tenant' => "⚠️ Lease Cancelled - {$propertyTitle}",
                'landlord' => "🏠 Property Auto-Released - {$propertyTitle}"
            ];
            
            $subject = $subjectMap[$recipientType] ?? "Property Auto-Release Notice - {$propertyTitle}";
            
            Mail::send('emails.property-auto-released', $data, function ($message) use ($user, $notification, $subject) {
                $message->to($user->email, $user->name)
                       ->subject($subject)
                       ->from(config('mail.from.address'), config('mail.from.name'));
            });

            $this->markEmailSent($notification);
            return true;
            
        } catch (Exception $e) {
            Log::error("Failed to send property auto-released email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark notification as email sent
     */
    private function markEmailSent(Notification $notification): void
    {
        try {
            $notification->update([
                'email_sent' => true,
            ]);
        } catch (Exception $e) {
            Log::error("Failed to mark email as sent for notification {$notification->id}: " . $e->getMessage());
        }
    }

    /**
     * Send bulk emails for multiple notifications
     */
    public function sendBulkEmails(array $notificationIds): array
    {
        $results = [
            'sent' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($notificationIds as $notificationId) {
            try {
                $notification = Notification::find($notificationId);
                
                if (!$notification) {
                    $results['failed']++;
                    $results['errors'][] = "Notification {$notificationId} not found";
                    continue;
                }

                if ($notification->email_sent) {
                    continue; // Skip already sent emails
                }

                if ($this->sendNotificationEmail($notification)) {
                    $results['sent']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = "Failed to send email for notification {$notificationId}";
                }
                
            } catch (Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Error processing notification {$notificationId}: " . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Send pending email notifications
     */
    public function sendPendingEmails(): array
    {
        try {
            // Get notifications that haven't been emailed yet
            $pendingNotifications = Notification::where('email_sent', false)
                                                ->where('is_sent', true) // Only sent notifications
                                                ->whereNull('scheduled_for') // Only immediate notifications
                                                ->with(['user'])
                                                ->limit(100) // Process in batches
                                                ->get();

            $results = [
                'processed' => 0,
                'sent' => 0,
                'failed' => 0,
                'errors' => []
            ];

            foreach ($pendingNotifications as $notification) {
                $results['processed']++;
                
                if ($this->sendNotificationEmail($notification)) {
                    $results['sent']++;
                } else {
                    $results['failed']++;
                }
            }

            return $results;
            
        } catch (Exception $e) {
            Log::error("Failed to send pending emails: " . $e->getMessage());
            return [
                'processed' => 0,
                'sent' => 0,
                'failed' => 0,
                'errors' => [$e->getMessage()]
            ];
        }
    }

    /**
     * Test email configuration
     */
    public function testEmailConfiguration(): bool
    {
        try {
            Mail::raw('This is a test email from Efiewura notification system.', function ($message) {
                $message->to('test@efiewura.com')
                       ->subject('Test Email - Efiewura Notification System')
                       ->from(config('mail.from.address'), config('mail.from.name'));
            });
            
            return true;
            
        } catch (Exception $e) {
            Log::error("Email configuration test failed: " . $e->getMessage());
            return false;
        }
    }
}
