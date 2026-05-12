<?php

/**
 * Test Admin Settings API
 */

echo "=== Testing Admin Settings API ===\n\n";

$baseUrl = 'http://127.0.0.1:8000/api';
$email = 'jhaykhoma@gmail.com';
$password = '123456789';

// First, login to get a token (assuming this user is an admin)
echo "1. Logging in to get admin token:\n";
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

if ($httpCode !== 200) {
    echo "❌ Login failed: HTTP $httpCode\n";
    echo "Response: $response\n";
    exit(1);
}

$loginResult = json_decode($response, true);
if (!isset($loginResult['success']) || !$loginResult['success']) {
    echo "❌ Login unsuccessful\n";
    exit(1);
}

$token = $loginResult['data']['token'];
echo "✅ Login successful! Token: " . substr($token, 0, 20) . "...\n";

// Test 1: Get all settings (should initialize defaults)
echo "\n2. Getting all settings (should initialize defaults):\n";
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
    echo "Categories found: " . implode(', ', array_keys($result['data'])) . "\n";
    
    // Show a sample
    if (isset($result['data']['general'])) {
        echo "General settings sample:\n";
        foreach ($result['data']['general'] as $key => $value) {
            echo "  $key: " . (is_array($value) ? json_encode($value) : $value) . "\n";
        }
    }
} else {
    echo "❌ Failed to get settings: $response\n";
    exit(1);
}

// Test 2: Get specific category (general)
echo "\n3. Getting general settings:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/admin/settings/general');
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
    echo "✅ General settings retrieved!\n";
    echo "Platform name: " . $result['data']['general']['platform_name'] . "\n";
} else {
    echo "❌ Failed to get general settings: $response\n";
}

// Test 3: Update general settings
echo "\n4. Updating general settings:\n";
$updateData = [
    'platform_name' => 'Efiewura Property Management - Updated',
    'support_email' => 'newemail@efiewura.com'
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
    echo "✅ General settings updated!\n";
    echo "New platform name: " . $result['data']['general']['platform_name'] . "\n";
} else {
    echo "❌ Failed to update general settings: $response\n";
}

// Test 4: Update security settings (boolean and integer values)
echo "\n5. Updating security settings:\n";
$securityData = [
    'require_2fa' => true,
    'max_login_attempts' => 3,
    'lockout_duration' => 15
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/admin/settings/security');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($securityData));
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
    echo "✅ Security settings updated!\n";
    echo "2FA Required: " . ($result['data']['security']['require_2fa'] ? 'Yes' : 'No') . "\n";
    echo "Max login attempts: " . $result['data']['security']['max_login_attempts'] . "\n";
} else {
    echo "❌ Failed to update security settings: $response\n";
}

// Test 5: Reset settings to defaults
echo "\n6. Resetting settings to defaults:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/admin/settings/reset');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([]));
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
    echo "✅ Settings reset to defaults!\n";
    echo "Platform name restored to: " . $result['data']['general']['platform_name'] . "\n";
    echo "2FA Required reset to: " . ($result['data']['security']['require_2fa'] ? 'Yes' : 'No') . "\n";
} else {
    echo "❌ Failed to reset settings: $response\n";
}

echo "\n=== Admin Settings API Test Complete ===\n";
echo "🎉 Admin Settings system is ready for use!\n";
