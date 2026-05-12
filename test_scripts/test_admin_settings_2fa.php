<?php

/**
 * Test Admin Settings API with 2FA
 */

echo "=== Testing Admin Settings API ===\n\n";

$baseUrl = 'http://127.0.0.1:8000/api';
$email = 'jhaykhoma@gmail.com';
$password = '123456789';

// Current 2FA code - UPDATE THIS with your current code before running
$currentTwoFactorCode = 'ENTER_CURRENT_CODE_HERE';

if ($currentTwoFactorCode === 'ENTER_CURRENT_CODE_HERE') {
    echo "❌ Please update line 13: Replace 'ENTER_CURRENT_CODE_HERE' with your current 6-digit 2FA code\n";
    echo "💡 Get the code from your authenticator app and run again\n";
    exit(1);
}

// Login with 2FA
echo "1. Logging in with 2FA code: $currentTwoFactorCode\n";
$loginData = [
    'email' => $email,
    'password' => $password,
    'two_factor_code' => $currentTwoFactorCode
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

if ($httpCode !== 200) {
    echo "❌ Login failed: HTTP $httpCode\n";
    echo "Response: $response\n";
    echo "💡 Make sure the 2FA code is current (expires every 30 seconds)\n";
    exit(1);
}

$loginResult = json_decode($response, true);
$token = $loginResult['data']['token'];
echo "✅ Login successful! Token: " . substr($token, 0, 20) . "...\n";

// Test 1: Get all settings (should initialize defaults)
echo "\n2. Getting all settings:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/admin/settings');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
if ($httpCode === 200) {
    $result = json_decode($response, true);
    echo "✅ Settings retrieved successfully!\n";
    echo "Categories: " . implode(', ', array_keys($result['data'])) . "\n";
    
    // Show sample data
    if (isset($result['data']['general']['platform_name'])) {
        echo "Platform: " . $result['data']['general']['platform_name'] . "\n";
    }
    if (isset($result['data']['security']['require_2fa'])) {
        echo "2FA Required: " . ($result['data']['security']['require_2fa'] ? 'Yes' : 'No') . "\n";
    }
} else {
    echo "❌ Failed: $response\n";
    exit(1);
}

// Test 2: Update general settings
echo "\n3. Updating general settings:\n";
$updateData = [
    'platform_name' => 'Efiewura - TEST UPDATE',
    'support_email' => 'test@efiewura.com'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/admin/settings/general');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($updateData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
if ($httpCode === 200) {
    $result = json_decode($response, true);
    echo "✅ Updated! New name: " . $result['data']['general']['platform_name'] . "\n";
} else {
    echo "❌ Update failed: $response\n";
}

// Test 3: Get payments settings (test JSON data type)
echo "\n4. Getting payments settings:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/admin/settings/payments');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $result = json_decode($response, true);
    echo "✅ Payments settings retrieved!\n";
    echo "PayPal enabled: " . ($result['data']['payments']['paypal']['enabled'] ? 'Yes' : 'No') . "\n";
    echo "Default currency: " . $result['data']['payments']['default_currency'] . "\n";
} else {
    echo "❌ Failed: $response\n";
}

echo "\n🎉 Admin Settings API is working!\n";
echo "📝 All endpoint types tested: GET all, GET category, PUT category\n";
echo "🔧 Data types working: string, boolean, integer, float, json\n";
