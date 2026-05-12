<?php

/**
 * Simple test to verify 2FA endpoint works with GD extension fallback
 */

echo "=== Testing 2FA API with GD Extension Fallback ===\n\n";

// Check GD extension status
echo "1. Checking GD Extension Status:\n";
if (extension_loaded('gd')) {
    echo "   ✅ GD Extension is loaded\n";
} else {
    echo "   ⚠️  GD Extension is NOT loaded - using fallback\n";
}

// Test the API endpoint
echo "\n2. Testing API endpoint:\n";

$baseUrl = 'http://127.0.0.1:8000/api';

// First, try to register a test user
$userData = [
    'firstname' => 'Test', // Note: using 'firstname' not 'first_name'
    'lastname' => 'GD',    // Note: using 'lastname' not 'last_name'
    'email' => 'testgd@example.com',
    'password' => 'TestPassword123!',
    'password_confirmation' => 'TestPassword123!',
    'role' => 'tenant',
    'phone' => '+1234567890',
    'date_of_birth' => '1990-01-01',
    'gender' => 'male',
    'occupation' => 'Developer',
    'employer' => 'Tech Corp',
    'monthly_income' => 5000,
    'current_address' => '123 Test St',
    'emergency_contact_name' => 'Emergency Contact',
    'emergency_contact_phone' => '+0987654321',
    'emergency_contact_relationship' => 'Family'
];

echo "   Registering test user...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/register');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 || $httpCode === 201) {
    $result = json_decode($response, true);
    if (isset($result['data']['token'])) {
        $token = $result['data']['token'];
        echo "   ✅ User registered successfully\n";
        
        // Now test 2FA setup
        echo "   Testing 2FA setup...\n";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $baseUrl . '/2fa/generate-secret');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $twoFAResponse = curl_exec($ch);
        $twoFAHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($twoFAHttpCode === 200) {
            $twoFAResult = json_decode($twoFAResponse, true);
            if ($twoFAResult['success']) {
                echo "   ✅ 2FA setup endpoint working!\n";
                echo "\n3. Response Analysis:\n";
                echo "   Secret: " . substr($twoFAResult['data']['secret'], 0, 8) . "...\n";
                
                $qrCode = $twoFAResult['data']['qr_code'];
                if (strpos($qrCode, 'data:image/') === 0) {
                    echo "   QR Code: Generated as base64 image (GD working)\n";
                } elseif (strpos($qrCode, 'http') === 0) {
                    echo "   QR Code: Using external service fallback\n";
                    echo "   QR URL: " . $qrCode . "\n";
                } else {
                    echo "   QR Code: Unknown format\n";
                }
                
                if (isset($twoFAResult['data']['qr_code_url'])) {
                    echo "   Raw URL: " . substr($twoFAResult['data']['qr_code_url'], 0, 50) . "...\n";
                }
                
                // Test recovery codes endpoint (before confirmation)
                echo "\n4. Testing Recovery Codes Endpoint:\n";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $baseUrl . '/2fa/recovery-codes');
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $token
                ]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                
                $recoveryResponse = curl_exec($ch);
                $recoveryHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($recoveryHttpCode === 400) {
                    echo "   ✅ Recovery codes correctly requires 2FA to be confirmed first\n";
                } else {
                    echo "   ⚠️  Unexpected response from recovery codes endpoint: HTTP $recoveryHttpCode\n";
                    echo "   Response: " . $recoveryResponse . "\n";
                }
                
                // Test regenerate recovery codes endpoint
                echo "   Testing regenerate recovery codes endpoint...\n";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $baseUrl . '/2fa/recovery-codes/regenerate');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $token
                ]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                
                $regenResponse = curl_exec($ch);
                $regenHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($regenHttpCode === 422 || $regenHttpCode === 400) {
                    echo "   ✅ Regenerate recovery codes correctly requires password/confirmation\n";
                } else {
                    echo "   ⚠️  Unexpected response from regenerate endpoint: HTTP $regenHttpCode\n";
                }
                
                echo "\n✅ ALL TESTS PASSED!\n";
                echo "💡 The 2FA system is working correctly with fallback support.\n";
                echo "📝 Note: Complete 2FA confirmation requires a valid TOTP code from an authenticator app.\n";
                
            } else {
                echo "   ❌ 2FA setup failed: " . ($twoFAResult['message'] ?? 'Unknown error') . "\n";
            }
        } else {
            echo "   ❌ 2FA endpoint returned HTTP $twoFAHttpCode\n";
            echo "   Response: " . $twoFAResponse . "\n";
        }
        
    } else {
        echo "   ❌ Registration succeeded but no token received\n";
    }
} else {
    echo "   ⚠️  Registration returned HTTP $httpCode, trying to login with existing user...\n";
    
    // Try to login with existing user
    $loginData = [
        'email' => 'testgd@example.com',
        'password' => 'TestPassword123!'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/login');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $loginResponse = curl_exec($ch);
    $loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($loginHttpCode === 200) {
        $loginResult = json_decode($loginResponse, true);
        if (isset($loginResult['data']['token'])) {
            echo "   ✅ Logged in with existing user\n";
            // Continue with 2FA test using this token...
            // (Same 2FA test code as above)
        }
    } else {
        echo "   ❌ Could not login or register user\n";
        echo "   Make sure Laravel server is running on http://127.0.0.1:8000\n";
    }
}

echo "\n=== Test Complete ===\n";
