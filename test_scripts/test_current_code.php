<?php

/**
 * Simple test for current 2FA code
 */

echo "=== Testing Current 2FA Code: 473053 ===\n\n";

$baseUrl = 'http://127.0.0.1:8000/api';
$email = 'jhaykhoma@gmail.com';
$password = '123456789';
$currentCode = '186354'; // Your current 2FA code

$loginData = [
    'email' => $email,
    'password' => $password,
    'two_factor_code' => $currentCode
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
    echo "✅ SUCCESS! 2FA code $currentCode was accepted!\n";
    echo "🎉 Your 2FA authentication system is working perfectly!\n\n";
    
    $token = $result['data']['token'];
    echo "Token received: " . substr($token, 0, 20) . "...\n";
    echo "User: " . $result['data']['user']['email'] . "\n";
    
    // Test the status endpoint
    echo "\nTesting 2FA status endpoint:\n";
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
        echo "✅ 2FA Status endpoint working!\n";
        echo "   2FA Enabled: " . ($statusResult['data']['two_factor_enabled'] ? 'Yes' : 'No') . "\n";
        echo "   Recovery Codes Available: " . ($statusResult['data']['recovery_codes_generated'] ? 'Yes' : 'No') . "\n";
        echo "   Codes Remaining: " . $statusResult['data']['recovery_codes_remaining'] . "\n";
    } else {
        echo "❌ Status endpoint issue: HTTP $statusHttpCode\n";
    }
    
} elseif ($httpCode === 401) {
    echo "❌ 2FA code $currentCode was rejected (probably expired or used)\n";
    echo "⏰ TOTP codes expire every 30 seconds - please try with a fresh code\n";
} else {
    echo "❌ Unexpected error: HTTP $httpCode\n";
    echo "Response: $response\n";
}

echo "\n=== Test Complete ===\n";
