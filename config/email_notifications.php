<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Email Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for email notifications in the Efiewura platform
    |
    */

    // Enable/disable email notifications globally
    'enabled' => env('EMAIL_NOTIFICATIONS_ENABLED', true),

    // From email configuration
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'notifications@efiewura.com'),
        'name' => env('MAIL_FROM_NAME', 'Efiewura Notifications'),
    ],

    // Email templates configuration
    'templates' => [
        'welcome_message' => [
            'template' => 'emails.welcome',
            'subject' => 'Welcome to Efiewura - Your Property Journey Starts Here!',
            'enabled' => true,
        ],
        'new_booking_request' => [
            'template' => 'emails.new-booking-request',
            'subject' => 'New Booking Request for {property_title} from {tenant_name}',
            'enabled' => true,
        ],
        'booking_confirmed' => [
            'template' => 'emails.booking-confirmed',
            'subject' => '🎉 Booking Confirmed for {property_title}!',
            'enabled' => true,
        ],
        'booking_rejected' => [
            'template' => 'emails.booking-rejected',
            'subject' => 'Booking Update for {property_title}',
            'enabled' => true,
        ],
        'property_viewed_milestone' => [
            'template' => 'emails.property-milestone',
            'subject' => '🎯 {property_title} reached {views} views!',
            'enabled' => true,
        ],
        'lease_expiry_reminder' => [
            'template' => 'emails.lease-expiry-reminder',
            'subject' => 'Lease Expiry Reminder - {property_title}',
            'enabled' => true,
        ],
        'payment_reminder_5_days' => [
            'template' => 'emails.payment-reminder',
            'subject' => 'Rent Due in 5 Days - {property_title}',
            'enabled' => true,
        ],
        'payment_due_today' => [
            'template' => 'emails.payment-reminder',
            'subject' => 'Rent Payment Due Today - {property_title}',
            'enabled' => true,
        ],
        'payment_overdue_3_days' => [
            'template' => 'emails.payment-reminder',
            'subject' => 'URGENT: Rent Overdue - {property_title}',
            'enabled' => true,
        ],
        'move_in_reminder_7_days' => [
            'template' => 'emails.move-in-reminder',
            'subject' => 'Move-in Reminder - {property_title} (7 Days)',
            'enabled' => true,
        ],
        'move_in_today' => [
            'template' => 'emails.move-in-reminder',
            'subject' => '🏠 Welcome to Your New Home - {property_title}!',
            'enabled' => true,
        ],
        'move_out_reminder' => [
            'template' => 'emails.move-out-reminder',
            'subject' => '📦 Move-out Reminder - {property_title} (7 Days)',
            'enabled' => true,
        ],
        'property_auto_released' => [
            'template' => 'emails.property-auto-released',
            'subject' => 'Property Auto-Release Notice - {property_title}',
            'enabled' => true,
        ],
        'payment_received' => [
            'template' => 'emails.payment-received',
            'subject' => 'Payment Confirmed - {currency} {amount} for {property_title}',
            'enabled' => true,
        ],
        'payment_failed' => [
            'template' => 'emails.payment-failed',
            'subject' => 'Payment Failed - {property_title}',
            'enabled' => true,
        ],
        'refund_processed' => [
            'template' => 'emails.refund-processed',
            'subject' => 'Refund Processed - {currency} {refund_amount} for {property_title}',
            'enabled' => true,
        ],
    ],

    // Batch processing settings
    'batch_size' => env('EMAIL_BATCH_SIZE', 100),
    'max_retries' => env('EMAIL_MAX_RETRIES', 3),
    'retry_delay' => env('EMAIL_RETRY_DELAY', 300), // seconds

    // Rate limiting
    'rate_limit' => [
        'per_minute' => env('EMAIL_RATE_LIMIT_PER_MINUTE', 60),
        'per_hour' => env('EMAIL_RATE_LIMIT_PER_HOUR', 1000),
        'per_day' => env('EMAIL_RATE_LIMIT_PER_DAY', 10000),
    ],

    // Delivery preferences
    'delivery' => [
        'queue' => env('EMAIL_USE_QUEUE', false),
        'queue_name' => env('EMAIL_QUEUE_NAME', 'emails'),
        'delay' => env('EMAIL_DELAY_SECONDS', 0),
    ],

    // SMTP Configuration for different environments
    'smtp' => [
        'production' => [
            'driver' => 'smtp',
            'host' => env('SMTP_HOST'),
            'port' => env('SMTP_PORT', 587),
            'username' => env('SMTP_USERNAME'),
            'password' => env('SMTP_PASSWORD'),
            'encryption' => env('SMTP_ENCRYPTION', 'tls'),
        ],
        'development' => [
            'driver' => 'log', // Use log driver for development
        ],
        'testing' => [
            'driver' => 'array', // Use array driver for testing
        ],
    ],

    // Popular email service configurations
    'services' => [
        'mailgun' => [
            'domain' => env('MAILGUN_DOMAIN'),
            'secret' => env('MAILGUN_SECRET'),
            'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        ],
        'sendgrid' => [
            'api_key' => env('SENDGRID_API_KEY'),
        ],
        'ses' => [
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        ],
    ],

    // Email tracking
    'tracking' => [
        'open_tracking' => env('EMAIL_OPEN_TRACKING', false),
        'click_tracking' => env('EMAIL_CLICK_TRACKING', false),
        'bounce_tracking' => env('EMAIL_BOUNCE_TRACKING', false),
    ],

    // Unsubscribe settings
    'unsubscribe' => [
        'enabled' => env('EMAIL_UNSUBSCRIBE_ENABLED', true),
        'url' => env('APP_URL', 'http://localhost') . '/unsubscribe',
    ],

    // Email content settings
    'content' => [
        'company_name' => 'Efiewura',
        'company_tagline' => "Ghana's Premier Property Management Platform",
        'support_email' => env('SUPPORT_EMAIL', 'support@efiewura.com'),
        'website_url' => env('APP_URL', 'http://localhost'),
        'social_links' => [
            'facebook' => env('FACEBOOK_URL', '#'),
            'twitter' => env('TWITTER_URL', '#'),
            'linkedin' => env('LINKEDIN_URL', '#'),
            'instagram' => env('INSTAGRAM_URL', '#'),
        ],
    ],

    // Development and testing
    'test_recipients' => [
        env('TEST_EMAIL_1', 'test1@efiewura.com'),
        env('TEST_EMAIL_2', 'test2@efiewura.com'),
    ],

    // Logging
    'logging' => [
        'enabled' => env('EMAIL_LOGGING_ENABLED', true),
        'level' => env('EMAIL_LOG_LEVEL', 'info'), // debug, info, warning, error
        'channel' => env('EMAIL_LOG_CHANNEL', 'single'),
    ],

];
