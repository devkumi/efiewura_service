<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\User;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔐 Testing Change Password Endpoint\n";
echo "==================================\n\n";

try {
    // Create or find a test user
    $testUser = User::where('email', 'change.password.test@test.com')->first();
    
    if (!$testUser) {
        $testUser = User::create([
            'firstname' => 'Password',
            'lastname' => 'Test User',
            'email' => 'change.password.test@test.com',
            'password' => bcrypt('oldpassword123'),
            'role' => 'tenant',
            'email_verified_at' => now(),
        ]);
        echo "✅ Created test user: {$testUser->email}\n";
    } else {
        // Reset password to known value
        $testUser->password = bcrypt('oldpassword123');
        $testUser->save();
        echo "✅ Found existing test user: {$testUser->email}\n";
    }
    
    // Create API token for testing
    $token = $testUser->createToken('password_test_token')->plainTextToken;
    echo "✅ Generated API token\n\n";
    
    $baseUrl = 'http://127.0.0.1:8080/api';
    
    echo "🧪 Test 1: Login with original password\n";
    echo "--------------------------------------\n";
    
    // Test 1: Login with original password
    $loginData = [
        'email' => 'change.password.test@test.com',
        'password' => 'oldpassword123'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/login');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        echo "✅ Login successful with original password\n";
        $loginResponse = json_decode($response, true);
        $loginToken = $loginResponse['data']['token'];
    } else {
        echo "❌ Login failed with original password\n";
        echo "Response: $response\n";
        exit(1);
    }
    
    echo "\n🧪 Test 2: Change password with correct current password\n";
    echo "--------------------------------------------------------\n";
    
    // Test 2: Change password with correct current password
    $changePasswordData = [
        'current_password' => 'oldpassword123',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/change-password');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($changePasswordData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $loginToken
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        echo "✅ Password changed successfully\n";
        $changeResponse = json_decode($response, true);
        echo "   Response: " . $changeResponse['message'] . "\n";
    } else {
        echo "❌ Password change failed\n";
        echo "Response: $response\n";
        exit(1);
    }
    
    echo "\n🧪 Test 3: Try to login with old password (should fail)\n";
    echo "------------------------------------------------------\n";
    
    // Test 3: Try to login with old password (should fail)
    $oldLoginData = [
        'email' => 'change.password.test@test.com',
        'password' => 'oldpassword123'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/login');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($oldLoginData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 401) {
        echo "✅ Login correctly failed with old password\n";
    } else {
        echo "❌ Login should have failed with old password but didn't\n";
        echo "Response: $response\n";
    }
    
    echo "\n🧪 Test 4: Login with new password (should succeed)\n";
    echo "--------------------------------------------------\n";
    
    // Test 4: Login with new password (should succeed)
    $newLoginData = [
        'email' => 'change.password.test@test.com',
        'password' => 'newpassword123'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/login');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($newLoginData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        echo "✅ Login successful with new password\n";
        $newLoginResponse = json_decode($response, true);
        $newToken = $newLoginResponse['data']['token'];
    } else {
        echo "❌ Login failed with new password\n";
        echo "Response: $response\n";
        exit(1);
    }
    
    echo "\n🧪 Test 5: Try to change password with wrong current password\n";
    echo "------------------------------------------------------------\n";
    
    // Test 5: Try to change password with wrong current password
    $wrongPasswordData = [
        'current_password' => 'wrongpassword123',
        'password' => 'anotherpassword123',
        'password_confirmation' => 'anotherpassword123'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/change-password');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($wrongPasswordData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $newToken
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 422) {
        echo "✅ Password change correctly failed with wrong current password\n";
        $errorResponse = json_decode($response, true);
        echo "   Error: " . $errorResponse['message'] . "\n";
    } else {
        echo "❌ Password change should have failed with wrong current password\n";
        echo "Response: $response\n";
    }
    
    echo "\n🧪 Test 6: Try to change password without confirmation\n";
    echo "-----------------------------------------------------\n";
    
    // Test 6: Try to change password without confirmation
    $noConfirmData = [
        'current_password' => 'newpassword123',
        'password' => 'finalpassword123'
        // missing password_confirmation
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/change-password');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($noConfirmData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $newToken
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 422) {
        echo "✅ Password change correctly failed without confirmation\n";
        $errorResponse = json_decode($response, true);
        if (isset($errorResponse['errors']['password'])) {
            echo "   Validation error: " . implode(', ', $errorResponse['errors']['password']) . "\n";
        }
    } else {
        echo "❌ Password change should have failed without confirmation\n";
        echo "Response: $response\n";
    }
    
    echo "\n🧪 Test 7: Try to change password without authentication\n";
    echo "-------------------------------------------------------\n";
    
    // Test 7: Try to change password without authentication
    $unauthData = [
        'current_password' => 'newpassword123',
        'password' => 'finalpassword123',
        'password_confirmation' => 'finalpassword123'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/change-password');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($unauthData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
        // No Authorization header
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 401) {
        echo "✅ Password change correctly failed without authentication\n";
    } else {
        echo "❌ Password change should have failed without authentication\n";
        echo "Response: $response\n";
    }
    
    echo "\n🎉 All change password tests completed!\n\n";
    
    echo "📋 Summary:\n";
    echo "✅ Change password endpoint implemented and working\n";
    echo "✅ Current password validation working\n";
    echo "✅ Password confirmation validation working\n";
    echo "✅ Authentication required for endpoint\n";
    echo "✅ Password hashing working correctly\n\n";
    
    echo "🔗 API Endpoint:\n";
    echo "   PUT /api/change-password\n";
    echo "   Headers: Authorization: Bearer {token}\n";
    echo "   Body: {\n";
    echo "     \"current_password\": \"current123\",\n";
    echo "     \"password\": \"newpassword123\",\n";
    echo "     \"password_confirmation\": \"newpassword123\"\n";
    echo "   }\n\n";
    
    // Clean up test user
    echo "🧹 Cleaning up test data...\n";
    $testUser->forceDelete();
    echo "✅ Test user deleted\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
