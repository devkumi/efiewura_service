<?php

// Simple test for 2FA disable with current code
// Update the code below before running

echo "=== Simple 2FA Disable Test ===\n";
echo "UPDATE THE CODE BELOW WITH YOUR CURRENT 2FA CODE!\n\n";

$currentCode = "YOUR_CURRENT_CODE_HERE"; // UPDATE THIS

if ($currentCode === "YOUR_CURRENT_CODE_HERE") {
    echo "❌ Please update \$currentCode with your current 6-digit 2FA code\n";
    echo "Edit this file at line 7 and run again\n";
    exit(1);
}

$baseUrl = 'http://127.0.0.1:8000/api';

// Step 1: Login with 2FA
echo "Logging in with code: $currentCode\n";
$loginData = json_encode([
    'email' => 'jhaykhoma@gmail.com',
    'password' => '123456789',
    'two_factor_code' => $currentCode
]);

$ch = curl_init($baseUrl . '/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo "❌ Login failed: HTTP $httpCode\n";
    echo "Response: $response\n";
    echo "💡 Make sure the 2FA code is current (they expire every 30 seconds)\n";
    exit(1);
}

$login = json_decode($response, true);
$token = $login['data']['token'];
echo "✅ Login successful!\n";

// Step 2: Disable 2FA
echo "Disabling 2FA...\n";
$disableData = json_encode(['password' => '123456789']);

$ch = curl_init($baseUrl . '/2fa/disable');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $disableData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";

if ($httpCode === 200) {
    echo "\n🎉 SUCCESS! 2FA has been disabled!\n";
    echo "You can now test admin settings without 2FA\n";
} else {
    echo "\n❌ Failed to disable 2FA\n";
    echo "Check the logs: tail -f storage/logs/laravel.log\n";
}
