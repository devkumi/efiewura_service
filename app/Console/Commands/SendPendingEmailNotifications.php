<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailNotificationService;

class SendPendingEmailNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-emails {--test : Test email configuration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send pending email notifications to users';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $emailService = new EmailNotificationService();

        // Test email configuration if requested
        if ($this->option('test')) {
            $this->info('Testing email configuration...');
            
            if ($emailService->testEmailConfiguration()) {
                $this->info('✅ Email configuration test passed!');
                return 0;
            } else {
                $this->error('❌ Email configuration test failed!');
                return 1;
            }
        }

        // Send pending emails
        $this->info('Sending pending email notifications...');
        
        $results = $emailService->sendPendingEmails();
        
        $this->info("📊 Email Processing Results:");
        $this->info("   • Processed: {$results['processed']} notifications");
        $this->info("   • Sent: {$results['sent']} emails");
        $this->info("   • Failed: {$results['failed']} emails");
        
        if (!empty($results['errors'])) {
            $this->warn("❌ Errors encountered:");
            foreach ($results['errors'] as $error) {
                $this->warn("   • {$error}");
            }
        }
        
        if ($results['sent'] > 0) {
            $this->info("✅ Successfully sent {$results['sent']} email notifications!");
        }
        
        return $results['failed'] > 0 ? 1 : 0;
    }
}
