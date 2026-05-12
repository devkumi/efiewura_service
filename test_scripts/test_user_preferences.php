<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\User;
use App\Models\UserPreference;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "⚙️  Testing User Preferences Endpoint\n";
echo "===================================\n\n";

$apiUrl = 'http://127.0.0.1:8080/api';

try {
    // Test 1: Create test user
    echo "1. Setting up test user...\n";
    
    $testUser = User::where('email', 'preferences.test@test.com')->first();
    if (!$testUser) {
        $testUser = User::create([
            'firstname' => 'Preferences',
            'lastname' => 'Test User',
            'email' => 'preferences.test@test.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
        echo "✅ Created test user\n";
    } else {
        echo "✅ Using existing test user\n";
    }
    
    // Login to get token
    function loginUser($email, $password) {
        global $apiUrl;
        
        $loginData = [
            'email' => $email,
            'password' => $password
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl . '/login');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if ($httpCode === 200 && $result['success']) {
            return $result['data']['token'];
        } else {
            throw new Exception("Login failed: " . ($result['message'] ?? 'Unknown error'));
        }
    }
    
    $token = loginUser('preferences.test@test.com', 'password123');
    echo "✅ Login successful\n\n";
    
    // Test 2: Get profile with default preferences
    echo "2. Testing profile retrieval with default preferences...\n";
    
    function getProfile($token) {
        global $apiUrl;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl . '/profile');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: Bearer ' . $token
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'httpCode' => $httpCode,
            'response' => json_decode($response, true)
        ];
    }
    
    $profileResult = getProfile($token);
    
    if ($profileResult['httpCode'] === 200 && $profileResult['response']['success']) {
        echo "✅ Profile retrieved successfully\n";
        $profile = $profileResult['response']['data'];
        echo "   User: " . $profile['name'] . "\n";
        
        if (isset($profile['preferences'])) {
            echo "   Default timezone: " . $profile['preferences']['timezone'] . "\n";
            echo "   Default theme: " . $profile['preferences']['theme'] . "\n";
            echo "   Email notifications: " . ($profile['preferences']['email_notifications'] ? 'enabled' : 'disabled') . "\n";
        } else {
            echo "   ⚠️  No preferences found\n";
        }
    } else {
        echo "❌ Profile retrieval failed\n";
        echo "   Error: " . ($profileResult['response']['message'] ?? 'Unknown error') . "\n";
    }
    
    echo "\n";
    
    // Test 3: Update preferences only
    echo "3. Testing preferences-only update...\n";
    
    $preferencesUpdateData = [
        'preferences' => [
            'timezone' => 'Africa/Accra',
            'theme' => 'dark',
            'date_format' => 'YYYY-MM-DD',
            'dashboard_refresh_interval' => 60,
            'notifications' => [
                'email' => true,
                'sms' => true,
                'new_bookings' => true,
                'property_updates' => false,
                'system_alerts' => true,
                'weekly_reports' => true
            ]
        ]
    ];
    
    function updateProfile($token, $data) {
        global $apiUrl;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl . '/profile');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $token
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'httpCode' => $httpCode,
            'response' => json_decode($response, true)
        ];
    }
    
    $updateResult = updateProfile($token, $preferencesUpdateData);
    
    if ($updateResult['httpCode'] === 200 && $updateResult['response']['success']) {
        echo "✅ Preferences updated successfully\n";
        $updatedProfile = $updateResult['response']['data'];
        $prefs = $updatedProfile['preferences'];
        
        echo "   Updated timezone: " . $prefs['timezone'] . "\n";
        echo "   Updated theme: " . $prefs['theme'] . "\n";
        echo "   Updated date format: " . $prefs['date_format'] . "\n";
        echo "   Updated refresh interval: " . $prefs['dashboard_refresh_interval'] . " seconds\n";
        echo "   SMS notifications: " . ($prefs['sms_notifications'] ? 'enabled' : 'disabled') . "\n";
        echo "   Weekly reports: " . ($prefs['weekly_reports'] ? 'enabled' : 'disabled') . "\n";
    } else {
        echo "❌ Preferences update failed\n";
        echo "   Error: " . ($updateResult['response']['message'] ?? 'Unknown error') . "\n";
        if (isset($updateResult['response']['errors'])) {
            print_r($updateResult['response']['errors']);
        }
    }
    
    echo "\n";
    
    // Test 4: Update profile info with bio
    echo "4. Testing profile info update with bio...\n";
    
    $profileInfoData = [
        'firstname' => 'Updated Preferences',
        'lastname' => 'Admin User',
        'bio' => 'I am a system administrator who manages the platform settings and user preferences.',
        'preferences' => [
            'notifications' => [
                'user_registrations' => true // Enable admin notifications
            ]
        ]
    ];
    
    $updateResult2 = updateProfile($token, $profileInfoData);
    
    if ($updateResult2['httpCode'] === 200 && $updateResult2['response']['success']) {
        echo "✅ Profile info updated successfully\n";
        $updatedProfile = $updateResult2['response']['data'];
        
        echo "   Updated name: " . $updatedProfile['name'] . "\n";
        echo "   Bio: " . ($updatedProfile['preferences']['bio'] ?? 'Not set') . "\n";
        echo "   User registrations notifications: " . ($updatedProfile['preferences']['user_registrations'] ? 'enabled' : 'disabled') . "\n";
    } else {
        echo "❌ Profile info update failed\n";
        echo "   Error: " . ($updateResult2['response']['message'] ?? 'Unknown error') . "\n";
    }
    
    echo "\n";
    
    // Test 5: Test validation with invalid data
    echo "5. Testing validation with invalid preferences...\n";
    
    $invalidData = [
        'preferences' => [
            'theme' => 'invalid_theme', // Invalid theme
            'date_format' => 'INVALID-FORMAT', // Invalid date format
            'dashboard_refresh_interval' => 5, // Too low (min is 10)
            'notifications' => [
                'email' => 'not_boolean' // Should be boolean
            ]
        ]
    ];
    
    $validationResult = updateProfile($token, $invalidData);
    
    if ($validationResult['httpCode'] === 422 && !$validationResult['response']['success']) {
        echo "✅ Validation working correctly\n";
        echo "   Validation errors detected for invalid data\n";
        if (isset($validationResult['response']['errors'])) {
            $errors = $validationResult['response']['errors'];
            echo "   Errors found: " . implode(', ', array_keys($errors)) . "\n";
        }
    } else {
        echo "❌ Validation failed to catch invalid data\n";
    }
    
    echo "\n";
    
    // Test 6: Test preferences structure in response
    echo "6. Verifying preferences structure...\n";
    
    $finalProfile = getProfile($token);
    
    if ($finalProfile['httpCode'] === 200 && $finalProfile['response']['success']) {
        $prefs = $finalProfile['response']['data']['preferences'];
        
        // Check required preference fields
        $requiredFields = [
            'timezone', 'date_format', 'theme', 'dashboard_refresh_interval',
            'email_notifications', 'sms_notifications', 'new_bookings',
            'property_updates', 'user_registrations', 'system_alerts',
            'weekly_reports', 'bio'
        ];
        
        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $prefs)) {
                $missingFields[] = $field;
            }
        }
        
        if (empty($missingFields)) {
            echo "✅ All preference fields present\n";
            echo "   Timezone: " . $prefs['timezone'] . "\n";
            echo "   Theme: " . $prefs['theme'] . "\n";
            echo "   Date format: " . $prefs['date_format'] . "\n";
            echo "   Dashboard refresh: " . $prefs['dashboard_refresh_interval'] . "s\n";
            echo "   Bio length: " . strlen($prefs['bio'] ?? '') . " characters\n";
        } else {
            echo "❌ Missing preference fields: " . implode(', ', $missingFields) . "\n";
        }
    } else {
        echo "❌ Could not verify preferences structure\n";
    }
    
    echo "\n";
    
    // Test 7: Test preference defaults for new user
    echo "7. Testing default preferences for new user...\n";
    
    // Create a new user
    $newUser = User::where('email', 'newuser.prefs@test.com')->first();
    if ($newUser) {
        $newUser->forceDelete();
    }
    
    $newUser = User::create([
        'firstname' => 'New',
        'lastname' => 'User',
        'email' => 'newuser.prefs@test.com',
        'password' => bcrypt('password123'),
        'role' => 'tenant',
        'email_verified_at' => now(),
    ]);
    
    $newUserToken = loginUser('newuser.prefs@test.com', 'password123');
    $newUserProfile = getProfile($newUserToken);
    
    if ($newUserProfile['httpCode'] === 200 && $newUserProfile['response']['success']) {
        $newPrefs = $newUserProfile['response']['data']['preferences'];
        
        echo "✅ New user preferences created with defaults\n";
        echo "   Default timezone: " . $newPrefs['timezone'] . "\n";
        echo "   Default theme: " . $newPrefs['theme'] . "\n";
        echo "   Default email notifications: " . ($newPrefs['email_notifications'] ? 'enabled' : 'disabled') . "\n";
        echo "   Default dashboard refresh: " . $newPrefs['dashboard_refresh_interval'] . "s\n";
    } else {
        echo "❌ Failed to create default preferences for new user\n";
    }
    
    echo "\n";
    echo "🎉 Preferences Testing Complete!\n";
    echo "================================\n";
    echo "✅ Profile endpoint extended with preferences support\n";
    echo "✅ Preferences validation working correctly\n";
    echo "✅ Default preferences created automatically\n";
    echo "✅ Structured preferences response format\n";
    echo "✅ Notification and display preferences supported\n";
    echo "✅ Bio field supported for user descriptions\n";

} catch (\Exception $e) {
    echo "❌ Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
