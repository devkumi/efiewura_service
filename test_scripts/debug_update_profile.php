<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\User;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🐛 Debug Update Profile Response\n";
echo "===============================\n\n";

$apiUrl = 'http://127.0.0.1:8080/api';

try {
    // Find existing test user
    $testUser = User::where('email', 'update.profile.landlord@test.com')->first();
    
    if (!$testUser) {
        echo "❌ Test user not found. Run the main test first.\n";
        exit(1);
    }
    
    // Login
    $loginData = [
        'email' => 'update.profile.landlord@test.com',
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
    
    $loginResult = json_decode($response, true);
    
    if (!$loginResult['success']) {
        echo "❌ Login failed\n";
        exit(1);
    }
    
    $token = $loginResult['data']['token'];
    echo "✅ Login successful\n\n";
    
    // Test update profile with small change
    echo "Testing update profile...\n";
    
    $updateData = [
        'firstname' => 'Debug Test'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl . '/profile');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($updateData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $token
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    echo "HTTP Code: $httpCode\n";
    echo "Curl Error: " . ($curlError ?: 'None') . "\n";
    echo "Raw Response: " . $response . "\n\n";
    
    $result = json_decode($response, true);
    
    if ($result === null) {
        echo "❌ Failed to decode JSON response\n";
        echo "JSON Error: " . json_last_error_msg() . "\n";
    } else {
        echo "Decoded Response:\n";
        print_r($result);
    }

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
