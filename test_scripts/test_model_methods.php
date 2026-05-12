<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing UserPreference model methods...\n";

try {
    // Test getDefaults method
    $defaults = App\Models\UserPreference::getDefaults();
    echo "✅ getDefaults() method works\n";
    echo "Default timezone: " . $defaults['timezone'] . "\n";
    echo "Default theme: " . $defaults['theme'] . "\n";
    echo "Default email notifications: " . ($defaults['email_notifications'] ? 'true' : 'false') . "\n";
    
    // Test model creation
    $user = App\Models\User::where('email', 'preferences.test@test.com')->first();
    
    if ($user) {
        echo "\nTesting preferences relationship...\n";
        
        // Delete existing preferences to test fresh creation
        if ($user->preferences) {
            $user->preferences->delete();
        }
        
        // Create new preferences
        $preferences = $user->preferences()->create($defaults);
        echo "✅ Preferences created successfully\n";
        echo "Created timezone: " . $preferences->timezone . "\n";
        echo "Created theme: " . $preferences->theme . "\n";
        
        // Test relationship loading
        $user->load('preferences');
        echo "✅ Preferences relationship loaded\n";
        echo "Loaded timezone: " . $user->preferences->timezone . "\n";
        
        // Test helper methods
        $notificationPrefs = $user->preferences->getNotificationPreferences();
        echo "✅ getNotificationPreferences() method works\n";
        echo "Email notifications: " . ($notificationPrefs['email'] ? 'true' : 'false') . "\n";
        
        $displayPrefs = $user->preferences->getDisplayPreferences();
        echo "✅ getDisplayPreferences() method works\n";
        echo "Display theme: " . $displayPrefs['theme'] . "\n";
        
    } else {
        echo "❌ Test user not found\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
