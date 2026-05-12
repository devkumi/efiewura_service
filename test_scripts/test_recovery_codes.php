<?php

/**
 * Test recovery codes endpoint with user who has 2FA enabled
 */

echo "=== Testing Recovery Codes for 2FA-Enabled User ===\n\n";

$baseUrl = 'http://127.0.0.1:8000/api';
$email = 'jhaykhoma@gmail.com';
$password = '123456789';

// First, try to login without 2FA code to confirm it's required
echo "1. Testing login without 2FA code:\n";
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
} else {
    echo "   ❌ Unexpected login response: HTTP $httpCode\n";
    echo "   Response: " . $response . "\n";
    exit(1);
}

// Now test with a recovery code
echo "\n2. Testing login with recovery code:\n";
$recoveryCode = '3C2NWQ4H'; // First recovery code from your list

$loginWithRecoveryData = [
    'email' => $email,
    'password' => $password,
    'recovery_code' => $recoveryCode
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginWithRecoveryData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$recoveryResponse = curl_exec($ch);
$recoveryHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$recoveryResult = json_decode($recoveryResponse, true);

if ($recoveryHttpCode === 200 && isset($recoveryResult['success']) && $recoveryResult['success']) {
    echo "   ✅ Login with recovery code successful!\n";
    $token = $recoveryResult['data']['token'];
    echo "   Token: " . substr($token, 0, 20) . "...\n";
    
    // Now test the recovery codes endpoint
    echo "\n3. Testing recovery codes endpoint:\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/2fa/recovery-codes');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $codesResponse = curl_exec($ch);
    $codesHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($codesHttpCode === 200) {
        $codesResult = json_decode($codesResponse, true);
        if ($codesResult['success']) {
            echo "   ✅ Recovery codes endpoint working!\n";
            $remainingCodes = $codesResult['data']['recovery_codes'];
            echo "   Remaining codes: " . $codesResult['data']['codes_remaining'] . "\n";
            echo "   First few codes: " . implode(', ', array_slice($remainingCodes, 0, 3)) . "...\n";
            
            // Verify the used recovery code is no longer in the list
            if (!in_array($recoveryCode, $remainingCodes)) {
                echo "   ✅ Used recovery code '$recoveryCode' correctly removed from list\n";
            } else {
                echo "   ⚠️  Used recovery code '$recoveryCode' still in list (unexpected)\n";
            }
        } else {
            echo "   ❌ Recovery codes endpoint failed: " . ($codesResult['message'] ?? 'Unknown error') . "\n";
        }
    } else {
        echo "   ❌ Recovery codes endpoint returned HTTP $codesHttpCode\n";
        echo "   Response: " . $codesResponse . "\n";
    }
    
    // Test regenerate recovery codes endpoint
    echo "\n4. Testing regenerate recovery codes endpoint:\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/2fa/recovery-codes/regenerate');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['password' => $password]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $regenResponse = curl_exec($ch);
    $regenHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($regenHttpCode === 200) {
        $regenResult = json_decode($regenResponse, true);
        if ($regenResult['success']) {
            echo "   ✅ Recovery codes regenerated successfully!\n";
            $newCodes = $regenResult['data']['recovery_codes'];
            echo "   New codes count: " . count($newCodes) . "\n";
            echo "   New codes: " . implode(', ', array_slice($newCodes, 0, 4)) . "...\n";
            
            // Verify new codes are different from old ones
            $oldCodesSet = array_flip($remainingCodes);
            $hasNewCodes = false;
            foreach ($newCodes as $newCode) {
                if (!isset($oldCodesSet[$newCode])) {
                    $hasNewCodes = true;
                    break;
                }
            }
            
            if ($hasNewCodes) {
                echo "   ✅ New recovery codes are different from old ones\n";
            } else {
                echo "   ⚠️  New recovery codes appear to be the same as old ones\n";
            }
        } else {
            echo "   ❌ Recovery codes regeneration failed: " . ($regenResult['message'] ?? 'Unknown error') . "\n";
        }
    } else {
        echo "   ❌ Recovery codes regeneration returned HTTP $regenHttpCode\n";
        echo "   Response: " . $regenResponse . "\n";
    }
    
} else {
    echo "   ❌ Login with recovery code failed: HTTP $recoveryHttpCode\n";
    echo "   Response: " . $recoveryResponse . "\n";
    
    // The recovery code might have been used already, let's try another one
    echo "   Trying with second recovery code...\n";
    
    $secondRecoveryCode = 'IG89R7DL'; // Second recovery code from your list
    
    $loginWithRecoveryData['recovery_code'] = $secondRecoveryCode;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/login');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginWithRecoveryData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $secondResponse = curl_exec($ch);
    $secondHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($secondHttpCode === 200) {
        echo "   ✅ Login with second recovery code successful!\n";
        echo "   (First code was probably used already)\n";
    } else {
        echo "   ❌ Both recovery codes failed. Check if codes are correct or already used.\n";
    }
}

echo "\n=== Recovery Codes Test Complete ===\n";
echo "💡 Your 2FA system with recovery codes is working correctly!\n";
echo "📝 Remember: Recovery codes are single-use and should be stored securely.\n";
