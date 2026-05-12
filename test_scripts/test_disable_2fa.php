<?php

/**
 * Test 2FA disable functionality
 */

echo "=== Testing 2FA Disable ===\n\n";

$baseUrl = 'http://127.0.0.1:8000/api';
$email = 'jhaykhoma@gmail.com';
$password = '123456789';

// First, login to get a token
echo "1. Logging in to get authentication token:\n";
$loginData = [
    'email' => $email,
    'password' => $password,
    'two_factor_code' => '473053' // Use your current 2FA code
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);

if ($httpCode === 200 && isset($result['success']) && $result['success']) {
    echo "   ✅ Login successful\n";
    $token = $result['data']['token'];
    echo "   Token: " . substr($token, 0, 20) . "...\n";
    
    // Now try to disable 2FA
    echo "\n2. Attempting to disable 2FA:\n";
    
    $disableData = [
        'password' => $password
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/2fa/disable');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($disableData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $disableResponse = curl_exec($ch);
    $disableHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "   HTTP Code: $disableHttpCode\n";
    echo "   Response: $disableResponse\n";
    
    $disableResult = json_decode($disableResponse, true);
    
    if ($disableHttpCode === 200 && isset($disableResult['success']) && $disableResult['success']) {
        echo "   ✅ 2FA disabled successfully!\n";
        echo "   Message: " . $disableResult['message'] . "\n";
        
        // Verify 2FA is now disabled by checking status
        echo "\n3. Verifying 2FA is disabled:\n";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $baseUrl . '/2fa/status');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $statusResponse = curl_exec($ch);
        $statusHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($statusHttpCode === 200) {
            $statusResult = json_decode($statusResponse, true);
            echo "   2FA Status: " . ($statusResult['data']['two_factor_enabled'] ? 'Enabled' : 'Disabled') . "\n";
        }
        
    } else {
        echo "   ❌ Failed to disable 2FA\n";
        if (isset($disableResult['message'])) {
            echo "   Error: " . $disableResult['message'] . "\n";
        }
        if (isset($disableResult['errors'])) {
            echo "   Validation errors: " . json_encode($disableResult['errors']) . "\n";
        }
    }
    
} else {
    echo "   ❌ Login failed: HTTP $httpCode\n";
    echo "   Response: $response\n";
    echo "   Please update the 2FA code in this script with a current one\n";
}

echo "\n=== Test Complete ===\n";
