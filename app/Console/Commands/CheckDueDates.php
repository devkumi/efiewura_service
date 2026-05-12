<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Notification;
use App\Models\Property;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckDueDates extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:check-due-dates {--test : Run in test mode without making changes}';

    /**
     * The console command description.
     */
    protected $description = 'Check for due dates and send appropriate notifications (lease expiry, payment reminders, overdue actions)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isTestMode = $this->option('test');
        
        if ($isTestMode) {
            $this->info('Running in TEST MODE - no changes will be made');
        }

        $this->info('Starting due date checks...');
        
        // Check lease expiry reminders
        $this->checkLeaseExpiryReminders($isTestMode);
        
        // Check payment reminders
        $this->checkPaymentReminders($isTestMode);
        
        // Check move-in reminders
        $this->checkMoveInReminders($isTestMode);
        
        // Check move-out reminders
        $this->checkMoveOutReminders($isTestMode);
        
        // Check overdue payments and auto-release properties
        $this->checkOverduePayments($isTestMode);
        
        $this->info('Due date checks completed successfully!');
        
        return 0;
    }

    /**
     * Check for lease expiry reminders (60, 30, 7 days before)
     */
    private function checkLeaseExpiryReminders($isTestMode = false)
    {
        $this->info('Checking lease expiry reminders...');
        
        $reminderDays = [60, 30, 7];
        $notificationsCreated = 0;
        
        foreach ($reminderDays as $days) {
            $targetDate = Carbon::now()->addDays($days)->toDateString();
            
            $bookings = Booking::where('status', 'confirmed')
                ->where('move_out_date', $targetDate)
                ->with(['user', 'landlord.user', 'property'])
                ->get();
                
            foreach ($bookings as $booking) {
                // Check if reminder already sent for this booking and timeframe
                $existingNotification = Notification::where('booking_id', $booking->id)
                    ->where('type', 'lease_expiry_reminder')
                    ->where('data->reminder_days', $days)
                    ->exists();
                    
                if (!$existingNotification) {
                    if (!$isTestMode) {
                        // Send to tenant
                        Notification::createLeaseExpiryReminder($booking, $days, 'tenant');
                        
                        // Send to landlord
                        Notification::createLeaseExpiryReminder($booking, $days, 'landlord');
                        
                        $notificationsCreated += 2;
                    }
                    
                    $this->line("  → {$days}-day reminder for booking #{$booking->id} (Property: {$booking->property->title})");
                }
            }
        }
        
        $this->info("Lease expiry reminders: {$notificationsCreated} notifications created");
    }

    /**
     * Check for payment reminders (5 days before, due date, 3 days overdue)
     */
    private function checkPaymentReminders($isTestMode = false)
    {
        $this->info('Checking payment reminders...');
        
        $notificationsCreated = 0;
        $today = Carbon::now();
        
        $confirmedBookings = Booking::where('status', 'confirmed')
            ->where('move_in_date', '<=', $today)
            ->where('move_out_date', '>', $today)
            ->with(['user', 'landlord.user', 'property'])
            ->get();
            
        foreach ($confirmedBookings as $booking) {
            // Calculate next payment due date (monthly from move-in date)
            $moveInDate = Carbon::parse($booking->move_in_date);
            
            // Find the next payment due date by iterating monthly from move-in
            $currentPaymentDate = $moveInDate->copy()->addMonth();
            while ($currentPaymentDate->lte($today)) {
                $currentPaymentDate->addMonth();
            }
            $nextPaymentDue = $currentPaymentDate;
            
            $daysUntilPayment = (int) round($today->startOfDay()->diffInDays($nextPaymentDue->startOfDay(), false));
            
            // 5 days before due date
            if ($daysUntilPayment === 5 && $today->lt($nextPaymentDue)) {
                if (!$this->paymentReminderExists($booking, 'payment_reminder_5_days')) {
                    if (!$isTestMode) {
                        Notification::createPaymentReminder($booking, 'payment_reminder_5_days', $nextPaymentDue);
                        $notificationsCreated++;
                    }
                    $this->line("  → 5-day payment reminder for booking #{$booking->id}");
                }
            }
            
            // Due date (today)
            if ($today->isSameDay($nextPaymentDue)) {
                if (!$this->paymentReminderExists($booking, 'payment_due_today')) {
                    if (!$isTestMode) {
                        Notification::createPaymentReminder($booking, 'payment_due_today', $nextPaymentDue);
                        $notificationsCreated++;
                    }
                    $this->line("  → Payment due today reminder for booking #{$booking->id}");
                }
            }
            
            // 3 days overdue
            if ($daysUntilPayment === -3 && $today->gt($nextPaymentDue)) {
                if (!$this->paymentReminderExists($booking, 'payment_overdue_3_days')) {
                    if (!$isTestMode) {
                        Notification::createPaymentReminder($booking, 'payment_overdue_3_days', $nextPaymentDue);
                        $notificationsCreated++;
                    }
                    $this->line("  → 3-day overdue payment reminder for booking #{$booking->id}");
                }
            }
        }
        
        $this->info("Payment reminders: {$notificationsCreated} notifications created");
    }

    /**
     * Check for move-in reminders (7 days before, day of)
     */
    private function checkMoveInReminders($isTestMode = false)
    {
        $this->info('Checking move-in reminders...');
        
        $notificationsCreated = 0;
        $today = Carbon::now();
        
        // 7 days before move-in
        $sevenDaysFromNow = $today->copy()->addDays(7)->toDateString();
        $bookings = Booking::where('status', 'confirmed')
            ->where('move_in_date', $sevenDaysFromNow)
            ->with(['user', 'landlord.user', 'property'])
            ->get();
            
        foreach ($bookings as $booking) {
            $existingNotification = Notification::where('booking_id', $booking->id)
                ->where('type', 'move_in_reminder_7_days')
                ->exists();
                
            if (!$existingNotification && !$isTestMode) {
                Notification::createMoveInReminder($booking, 7);
                $notificationsCreated++;
            }
            
            if (!$existingNotification) {
                $this->line("  → 7-day move-in reminder for booking #{$booking->id}");
            }
        }
        
        // Move-in day
        $todayString = $today->toDateString();
        $bookings = Booking::where('status', 'confirmed')
            ->where('move_in_date', $todayString)
            ->with(['user', 'landlord.user', 'property'])
            ->get();
            
        foreach ($bookings as $booking) {
            $existingNotification = Notification::where('booking_id', $booking->id)
                ->where('type', 'move_in_today')
                ->exists();
                
            if (!$existingNotification && !$isTestMode) {
                Notification::createMoveInReminder($booking, 0);
                $notificationsCreated++;
            }
            
            if (!$existingNotification) {
                $this->line("  → Move-in day welcome for booking #{$booking->id}");
            }
        }
        
        $this->info("Move-in reminders: {$notificationsCreated} notifications created");
    }

    /**
     * Check for move-out reminders (7 days before)
     */
    private function checkMoveOutReminders($isTestMode = false)
    {
        $this->info('Checking move-out reminders...');
        
        $notificationsCreated = 0;
        $sevenDaysFromNow = Carbon::now()->addDays(7)->toDateString();
        
        $bookings = Booking::where('status', 'confirmed')
            ->where('move_out_date', $sevenDaysFromNow)
            ->with(['user', 'landlord.user', 'property'])
            ->get();
            
        foreach ($bookings as $booking) {
            $existingNotification = Notification::where('booking_id', $booking->id)
                ->where('type', 'move_out_reminder')
                ->exists();
                
            if (!$existingNotification && !$isTestMode) {
                Notification::createMoveOutReminder($booking);
                $notificationsCreated++;
            }
            
            if (!$existingNotification) {
                $this->line("  → 7-day move-out reminder for booking #{$booking->id}");
            }
        }
        
        $this->info("Move-out reminders: {$notificationsCreated} notifications created");
    }

    /**
     * Check for overdue payments and auto-release properties
     */
    private function checkOverduePayments($isTestMode = false)
    {
        $this->info('Checking overdue payments for auto-release...');
        
        $propertiesReleased = 0;
        $notificationsCreated = 0;
        $today = Carbon::now();
        
        $confirmedBookings = Booking::where('status', 'confirmed')
            ->where('move_in_date', '<=', $today)
            ->where('move_out_date', '>', $today)
            ->with(['user', 'landlord.user', 'property'])
            ->get();
            
        foreach ($confirmedBookings as $booking) {
            $property = $booking->property;
            
            // Get landlord's overdue release setting (default 30 days if not set)
            $overdueReleaseDays = $property->landlord->overdue_release_days ?? 30;
            
            // Calculate next payment due date
            $moveInDate = Carbon::parse($booking->move_in_date);
            $monthsSinceMovein = $today->diffInMonths($moveInDate);
            $nextPaymentDue = $moveInDate->copy()->addMonths($monthsSinceMovein + 1);
            
            // Check if payment is overdue by the specified number of days
            $daysPastDue = $today->diffInDays($nextPaymentDue);
            
            if ($today->gt($nextPaymentDue) && $daysPastDue >= $overdueReleaseDays) {
                // Check if auto-release notification already sent
                $existingNotification = Notification::where('booking_id', $booking->id)
                    ->where('type', 'property_auto_released')
                    ->exists();
                    
                if (!$existingNotification) {
                    if (!$isTestMode) {
                        // Cancel the booking
                        $booking->update([
                            'status' => 'cancelled',
                            'cancelled_at' => now(),
                            'cancellation_reason' => "Auto-cancelled due to {$daysPastDue} days overdue payment"
                        ]);
                        
                        // Make property available
                        $property->update([
                            'availability_status' => 'available'
                        ]);
                        
                        // Notify tenant about cancellation
                        Notification::createPropertyAutoReleased($booking, $daysPastDue, 'tenant');
                        
                        // Notify landlord about auto-release
                        Notification::createPropertyAutoReleased($booking, $daysPastDue, 'landlord');
                        
                        $propertiesReleased++;
                        $notificationsCreated += 2;
                        
                        Log::info("Auto-released property #{$property->id} due to {$daysPastDue} days overdue payment for booking #{$booking->id}");
                    }
                    
                    $this->line("  → Auto-releasing property #{$property->id} - {$daysPastDue} days overdue (limit: {$overdueReleaseDays} days)");
                }
            }
        }
        
        $this->info("Auto-release: {$propertiesReleased} properties released, {$notificationsCreated} notifications created");
    }

    /**
     * Check if payment reminder already exists for this type
     */
    private function paymentReminderExists($booking, $reminderType)
    {
        return Notification::where('booking_id', $booking->id)
            ->where('type', $reminderType)
            ->whereDate('created_at', Carbon::today())
            ->exists();
    }
}
