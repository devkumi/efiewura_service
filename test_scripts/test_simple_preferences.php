<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Simple Preferences Test\n";
echo "======================\n\n";

try {
    // Test API directly 
    $apiUrl = 'http://127.0.0.1:8080/api';
    
    // Login as test user
    echo "1. Login...\n";
    $loginData = [
        'email' => 'preferences.test@test.com',
        'password' => 'password123'
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
    
    if ($httpCode === 200) {
        $result = json_decode($response, true);
        $token = $result['data']['token'];
        echo "✅ Login successful\n\n";
        
        // Test profile endpoint
        echo "2. Get profile...\n";
        
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
        
        echo "HTTP Code: $httpCode\n";
        $profileData = json_decode($response, true);
        
        if ($httpCode === 200 && $profileData['success']) {
            echo "✅ Profile retrieved successfully\n";
            
            if (isset($profileData['data']['preferences'])) {
                echo "✅ Preferences found in response\n";
                $prefs = $profileData['data']['preferences'];
                echo "   Timezone: " . ($prefs['timezone'] ?? 'not set') . "\n";
                echo "   Theme: " . ($prefs['theme'] ?? 'not set') . "\n";
            } else {
                echo "❌ No preferences in response\n";
                echo "Available keys: " . implode(', ', array_keys($profileData['data'])) . "\n";
            }
        } else {
            echo "❌ Profile request failed\n";
            echo "Response: " . $response . "\n";
        }
        
    } else {
        echo "❌ Login failed\n";
        echo "Response: " . $response . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
