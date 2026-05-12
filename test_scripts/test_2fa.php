<?php

/**
 * Test script for Two-Factor Authentication (2FA) functionality
 * 
 * This script tests the complete 2FA workflow including:
 * 1. Setting up 2FA with QR code generation
 * 2. Confirming 2FA setup with a test code
 * 3. Testing login with 2FA
 * 4. Testing recovery codes
 * 5. Admin management of 2FA
 */

require_once __DIR__ . '/../vendor/autoload.php';

class TwoFactorAuthTest
{
    private $baseUrl;
    private $token;
    private $adminToken;
    private $testUser;
    private $twoFactorSecret;
    private $recoveryCodes;

    public function __construct()
    {
        $this->baseUrl = 'http://localhost:8000/efiewura/public/api';
    }

    public function runTests()
    {
        echo "=== TWO-FACTOR AUTHENTICATION TEST SUITE ===\n\n";

        try {
            // Step 1: Create a test user
            $this->createTestUser();
            
            // Step 2: Test 2FA setup
            $this->test2FASetup();
            
            // Step 3: Test 2FA confirmation
            $this->test2FAConfirmation();
            
            // Step 4: Test login with 2FA
            $this->testLoginWith2FA();
            
            // Step 5: Test recovery codes
            $this->testRecoveryCodes();
            
            // Step 6: Test admin 2FA management
            $this->testAdmin2FAManagement();
            
            // Step 7: Test 2FA disable
            $this->test2FADisable();

            echo "\n✅ ALL 2FA TESTS COMPLETED SUCCESSFULLY!\n";

        } catch (Exception $e) {
            echo "\n❌ TEST FAILED: " . $e->getMessage() . "\n";
            echo "Stack trace: " . $e->getTraceAsString() . "\n";
        }
    }

    private function createTestUser()
    {
        echo "📝 Creating test user...\n";

        $userData = [
            'first_name' => 'Test',
            'last_name' => '2FA',
            'email' => 'test2fa@example.com',
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

        $response = $this->makeRequest('POST', '/register', $userData);
        
        if (!$response['success']) {
            // User might already exist, try to login
            $loginResponse = $this->makeRequest('POST', '/login', [
                'email' => 'test2fa@example.com',
                'password' => 'TestPassword123!'
            ]);
            
            if ($loginResponse['success']) {
                $this->token = $loginResponse['data']['token'];
                $this->testUser = $loginResponse['data']['user'];
                echo "✅ Logged in with existing test user\n";
                return;
            }
            
            throw new Exception("Failed to create or login test user: " . json_encode($response));
        }

        $this->token = $response['data']['token'];
        $this->testUser = $response['data']['user'];
        echo "✅ Test user created successfully\n";
    }

    private function test2FASetup()
    {
        echo "\n🔐 Testing 2FA setup...\n";

        $response = $this->makeRequest('POST', '/2fa/generate-secret', [], $this->token);
        
        if (!$response['success']) {
            throw new Exception("Failed to generate 2FA secret: " . json_encode($response));
        }

        $this->twoFactorSecret = $response['data']['secret'];
        
        echo "✅ 2FA secret generated successfully\n";
        echo "📱 QR Code URL: " . $response['data']['qr_code_url'] . "\n";
        echo "🔑 Manual entry key: " . $response['data']['manual_entry_key'] . "\n";
        
        // Verify QR code URL format
        if (!filter_var($response['data']['qr_code_url'], FILTER_VALIDATE_URL)) {
            throw new Exception("Invalid QR code URL format");
        }
        
        echo "✅ QR code URL format is valid\n";
    }

    private function test2FAConfirmation()
    {
        echo "\n✅ Testing 2FA confirmation...\n";
        
        // Generate a test code using Google2FA
        $google2fa = new \PragmaRX\Google2FA\Google2FA();
        $testCode = $google2fa->getCurrentOtp($this->twoFactorSecret);
        
        echo "🔢 Generated test code: " . $testCode . "\n";

        $response = $this->makeRequest('POST', '/2fa/confirm', [
            'code' => $testCode
        ], $this->token);
        
        if (!$response['success']) {
            throw new Exception("Failed to confirm 2FA setup: " . json_encode($response));
        }

        echo "✅ 2FA confirmed successfully\n";
        echo "🔒 Recovery codes generated: " . count($response['data']['recovery_codes']) . " codes\n";
        
        // Store recovery codes for testing
        $this->recoveryCodes = $response['data']['recovery_codes'];
    }

    private function testLoginWith2FA()
    {
        echo "\n🔓 Testing login with 2FA...\n";
        
        // First try login without 2FA code (should fail)
        $response = $this->makeRequest('POST', '/login', [
            'email' => 'test2fa@example.com',
            'password' => 'TestPassword123!'
        ]);
        
        if ($response['success'] || !isset($response['requires_2fa'])) {
            throw new Exception("Login should require 2FA but didn't");
        }
        
        echo "✅ Login correctly requires 2FA\n";
        
        // Now try with correct 2FA code
        $google2fa = new \PragmaRX\Google2FA\Google2FA();
        $testCode = $google2fa->getCurrentOtp($this->twoFactorSecret);
        
        $response = $this->makeRequest('POST', '/login', [
            'email' => 'test2fa@example.com',
            'password' => 'TestPassword123!',
            'two_factor_code' => $testCode
        ]);
        
        if (!$response['success']) {
            throw new Exception("Failed to login with 2FA: " . json_encode($response));
        }
        
        echo "✅ Login with 2FA successful\n";
        
        // Update token
        $this->token = $response['data']['token'];
    }

    private function testRecoveryCodes()
    {
        echo "\n🔑 Testing recovery codes...\n";
        
        // Test login with recovery code
        $recoveryCode = $this->recoveryCodes[0];
        
        $response = $this->makeRequest('POST', '/login', [
            'email' => 'test2fa@example.com',
            'password' => 'TestPassword123!',
            'recovery_code' => $recoveryCode
        ]);
        
        if (!$response['success']) {
            throw new Exception("Failed to login with recovery code: " . json_encode($response));
        }
        
        echo "✅ Login with recovery code successful\n";
        
        // Update token
        $this->token = $response['data']['token'];
        
        // Test getting remaining recovery codes
        $response = $this->makeRequest('GET', '/2fa/recovery-codes', [], $this->token);
        
        if (!$response['success']) {
            throw new Exception("Failed to get recovery codes: " . json_encode($response));
        }
        
        echo "✅ Retrieved recovery codes (remaining: " . count($response['data']['recovery_codes']) . ")\n";
        
        // Test regenerating recovery codes
        $response = $this->makeRequest('POST', '/2fa/recovery-codes/regenerate', [], $this->token);
        
        if (!$response['success']) {
            throw new Exception("Failed to regenerate recovery codes: " . json_encode($response));
        }
        
        echo "✅ Recovery codes regenerated successfully\n";
    }

    private function testAdmin2FAManagement()
    {
        echo "\n👨‍💼 Testing admin 2FA management...\n";
        
        // Create admin user and get admin token
        $this->createAdminUser();
        
        // Test getting user 2FA status
        $response = $this->makeRequest('GET', "/admin/2fa/users/{$this->testUser['id']}/status", [], $this->adminToken);
        
        if (!$response['success']) {
            throw new Exception("Failed to get user 2FA status: " . json_encode($response));
        }
        
        echo "✅ Admin retrieved user 2FA status\n";
        
        // Test toggling 2FA requirement
        $response = $this->makeRequest('PATCH', "/admin/2fa/users/{$this->testUser['id']}/toggle-requirement", [], $this->adminToken);
        
        if (!$response['success']) {
            throw new Exception("Failed to toggle 2FA requirement: " . json_encode($response));
        }
        
        echo "✅ Admin toggled 2FA requirement\n";
        
        // Test admin overview
        $response = $this->makeRequest('GET', '/admin/2fa/overview', [], $this->adminToken);
        
        if (!$response['success']) {
            throw new Exception("Failed to get 2FA overview: " . json_encode($response));
        }
        
        echo "✅ Admin retrieved 2FA overview\n";
        echo "📊 Users with 2FA enabled: " . $response['data']['users_with_2fa_enabled'] . "\n";
        echo "📊 Users with 2FA required: " . $response['data']['users_with_2fa_required'] . "\n";
    }

    private function test2FADisable()
    {
        echo "\n🔓 Testing 2FA disable...\n";
        
        $response = $this->makeRequest('DELETE', '/2fa/disable', [], $this->token);
        
        if (!$response['success']) {
            throw new Exception("Failed to disable 2FA: " . json_encode($response));
        }
        
        echo "✅ 2FA disabled successfully\n";
        
        // Verify 2FA is disabled by trying to login without code
        $response = $this->makeRequest('POST', '/login', [
            'email' => 'test2fa@example.com',
            'password' => 'TestPassword123!'
        ]);
        
        if (!$response['success']) {
            throw new Exception("Login should work without 2FA after disabling: " . json_encode($response));
        }
        
        echo "✅ Login works without 2FA after disabling\n";
    }

    private function createAdminUser()
    {
        echo "👨‍💼 Creating admin user...\n";

        $adminData = [
            'first_name' => 'Admin',
            'last_name' => 'Test',
            'email' => 'admin2fa@example.com',
            'password' => 'AdminPassword123!',
            'password_confirmation' => 'AdminPassword123!',
            'role' => 'admin'
        ];

        $response = $this->makeRequest('POST', '/register', $adminData);
        
        if (!$response['success']) {
            // Admin might already exist, try to login
            $loginResponse = $this->makeRequest('POST', '/login', [
                'email' => 'admin2fa@example.com',
                'password' => 'AdminPassword123!'
            ]);
            
            if ($loginResponse['success']) {
                $this->adminToken = $loginResponse['data']['token'];
                echo "✅ Logged in with existing admin user\n";
                return;
            }
            
            throw new Exception("Failed to create or login admin user: " . json_encode($response));
        }

        $this->adminToken = $response['data']['token'];
        echo "✅ Admin user created successfully\n";
    }

    private function makeRequest($method, $endpoint, $data = [], $token = null)
    {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $headers = ['Content-Type: application/json'];
        
        if ($token) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case 'PATCH':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($data)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch)) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        $decodedResponse = json_decode($response, true);
        
        if ($decodedResponse === null) {
            throw new Exception("Invalid JSON response: " . $response);
        }
        
        echo "📡 {$method} {$endpoint} -> HTTP {$httpCode}\n";
        
        return $decodedResponse;
    }
}

// Run the tests
$test = new TwoFactorAuthTest();
$test->runTests();
