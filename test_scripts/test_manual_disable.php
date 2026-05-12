<?php

/**
 * Manual test script - Update the 2FA code before running
 */

echo "=== Manual 2FA Disable Test ===\n\n";

$baseUrl = 'http://127.0.0.1:8000/api';
$email = 'jhaykhoma@gmail.com';
$password = '123456789';

// IMPORTANT: Update this with your current 2FA code before running!
$currentTwoFactorCode = 'UPDATE_THIS_WITH_CURRENT_CODE';

if ($currentTwoFactorCode === 'UPDATE_THIS_WITH_CURRENT_CODE') {
    echo "❌ Please update the \$currentTwoFactorCode variable with your current 2FA code from your authenticator app!\n";
    echo "Edit this file and replace 'UPDATE_THIS_WITH_CURRENT_CODE' with the current 6-digit code.\n";
    echo "Then run: php test_scripts/test_manual_disable.php\n";
    exit(1);
}

// Step 1: Login with 2FA
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
    echo "Please check if the 2FA code is current (codes change every 30 seconds)\n";
    exit(1);
}

$loginResult = json_decode($response, true);
if (!isset($loginResult['success']) || !$loginResult['success']) {
    echo "❌ Login unsuccessful\n";
    echo "Response: $response\n";
    exit(1);
}

$token = $loginResult['data']['token'];
echo "✅ Login successful! Token: " . substr($token, 0, 20) . "...\n";

// Step 2: Disable 2FA
echo "\n2. Disabling 2FA with password verification:\n";
$disableData = [
    'password' => $password
];

echo "Sending disable request with password: $password\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/2fa/disable');
curl_setopt($ch, CURLOPT_POST, true); // Now using POST instead of DELETE
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

echo "HTTP Response Code: $disableHttpCode\n";
echo "Raw Response: $disableResponse\n";

$disableResult = json_decode($disableResponse, true);

if ($disableHttpCode === 200 && isset($disableResult['success']) && $disableResult['success']) {
    echo "✅ 2FA disabled successfully!\n";
    echo "Message: " . $disableResult['message'] . "\n";
} else {
    echo "❌ Failed to disable 2FA\n";
    if (isset($disableResult['errors'])) {
        echo "Validation errors:\n";
        foreach ($disableResult['errors'] as $field => $messages) {
            echo "  $field: " . implode(', ', $messages) . "\n";
        }
    }
    if (isset($disableResult['message'])) {
        echo "Error message: " . $disableResult['message'] . "\n";
    }
}

echo "\n=== Manual Test Complete ===\n";
echo "💡 If you see 'The password field is required', there might be an issue with how the password data is being sent.\n";
