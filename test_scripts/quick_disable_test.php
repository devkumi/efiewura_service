<?php

// Quick test for 2FA disable - Just update the code below and run

$currentCode = "UPDATE_WITH_CURRENT_CODE"; // UPDATE THIS with your current 2FA code
$baseUrl = 'http://127.0.0.1:8000/api';

echo "Testing 2FA disable with code: $currentCode\n\n";

// Step 1: Login
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
$loginResponse = curl_exec($ch);
$loginCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Login: HTTP $loginCode\n";
if ($loginCode !== 200) {
    echo "Login failed: $loginResponse\n";
    exit;
}

$login = json_decode($loginResponse, true);
$token = $login['data']['token'];
echo "Token obtained: " . substr($token, 0, 20) . "...\n\n";

// Step 2: Disable 2FA
$disableData = json_encode(['password' => '123456789']);

$ch = curl_init($baseUrl . '/2fa/disable');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $disableData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$disableResponse = curl_exec($ch);
$disableCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Disable: HTTP $disableCode\n";
echo "Response: $disableResponse\n";

if ($disableCode === 200) {
    echo "\n✅ SUCCESS: 2FA has been disabled!\n";
} else {
    echo "\n❌ FAILED: Check the response above for error details\n";
    echo "💡 The debug info will be in storage/logs/laravel.log\n";
}
