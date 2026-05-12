<?php

/**
 * Test 2FA authentication with specific TOTP code
 */

echo "=== Testing 2FA Authentication with TOTP Code ===\n\n";

$baseUrl = 'http://127.0.0.1:8000/api';
$email = 'jhaykhoma@gmail.com';
$password = '123456789';
$twoFactorCode = '097581'; // User provided TOTP code

// First, verify that login without 2FA code requires 2FA
echo "1. Testing login without 2FA code (should require 2FA):\n";
$loginData = [
    'email' => $email,
    'password' => $password
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

if ($httpCode === 422 && isset($result['requires_2fa']) && $result['requires_2fa']) {
    echo "   ✅ Login correctly requires 2FA\n";
    echo "   Method: " . ($result['two_factor_method'] ?? 'unknown') . "\n";
    echo "   Message: " . ($result['message'] ?? 'No message') . "\n";
} else {
    echo "   ❌ Unexpected login response: HTTP $httpCode\n";
    echo "   Response: " . $response . "\n";
}

// Now test with the 2FA code
echo "\n2. Testing login with 2FA code ($twoFactorCode):\n";
$loginWith2FAData = [
    'email' => $email,
    'password' => $password,
    'two_factor_code' => $twoFactorCode
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginWith2FAData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$twoFAResponse = curl_exec($ch);
$twoFAHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$twoFAResult = json_decode($twoFAResponse, true);

if ($twoFAHttpCode === 200 && isset($twoFAResult['success']) && $twoFAResult['success']) {
    echo "   ✅ Login with 2FA code successful!\n";
    $token = $twoFAResult['data']['token'];
    echo "   Token: " . substr($token, 0, 20) . "...\n";
    echo "   User: " . $twoFAResult['data']['user']['email'] . "\n";
    
    // Test authenticated endpoint to verify token works
    echo "\n3. Testing authenticated endpoint with token:\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/user');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $userResponse = curl_exec($ch);
    $userHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($userHttpCode === 200) {
        $userResult = json_decode($userResponse, true);
        echo "   ✅ Authenticated user endpoint working!\n";
        echo "   User ID: " . $userResult['id'] . "\n";
        echo "   2FA Enabled: " . ($userResult['two_factor_confirmed_at'] ? 'Yes' : 'No') . "\n";
    } else {
        echo "   ❌ Authenticated endpoint failed: HTTP $userHttpCode\n";
        echo "   Response: " . $userResponse . "\n";
    }
    
    // Test 2FA status endpoint
    echo "\n4. Testing 2FA status endpoint:\n";
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
        echo "   ✅ 2FA status endpoint working!\n";
        echo "   2FA Enabled: " . ($statusResult['data']['two_factor_enabled'] ? 'Yes' : 'No') . "\n";
        echo "   Recovery Codes Available: " . ($statusResult['data']['recovery_codes_generated'] ? 'Yes' : 'No') . "\n";
        echo "   Codes Remaining: " . ($statusResult['data']['recovery_codes_remaining'] ?? 'Unknown') . "\n";
    } else {
        echo "   ❌ 2FA status endpoint failed: HTTP $statusHttpCode\n";
        echo "   Response: " . $statusResponse . "\n";
    }
    
} elseif ($twoFAHttpCode === 422 || $twoFAHttpCode === 401) {
    echo "   ❌ Login with 2FA code failed: HTTP $twoFAHttpCode\n";
    echo "   Error: " . ($twoFAResult['message'] ?? 'Unknown error') . "\n";
    echo "   This could mean:\n";
    echo "     - The 2FA code has expired (codes are time-based, valid for ~30 seconds)\n";
    echo "     - The code was already used\n";
    echo "     - Clock synchronization issue\n";
    echo "     - Code was typed incorrectly\n";
    
    // Show full response for debugging
    echo "   Full response: " . $twoFAResponse . "\n";
    
    echo "\n   💡 Try generating a new code from your authenticator app and test again.\n";
    echo "   ⏰ TOTP codes change every 30 seconds, so timing is important.\n";
    
} else {
    echo "   ❌ Unexpected response: HTTP $twoFAHttpCode\n";
    echo "   Response: " . $twoFAResponse . "\n";
}

echo "\n=== 2FA Code Test Complete ===\n";

if ($twoFAHttpCode === 200) {
    echo "🎉 Your 2FA authentication is working perfectly!\n";
    echo "📱 The TOTP code $twoFactorCode was accepted successfully.\n";
} else {
    echo "⚠️  The 2FA code $twoFactorCode was not accepted.\n";
    echo "📱 Please try with a fresh code from your authenticator app.\n";
    echo "⏰ Remember: TOTP codes expire every 30 seconds.\n";
}
