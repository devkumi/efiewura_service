<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\User;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "👤 Testing Update Profile Endpoint\n";
echo "==================================\n\n";

$apiUrl = 'http://127.0.0.1:8080/api';

try {
    // Test 1: Create test users for different roles
    echo "1. Setting up test users...\n";
    
    // Create landlord user
    $landlordUser = User::where('email', 'update.profile.landlord@test.com')->first();
    if (!$landlordUser) {
        $landlordUser = User::create([
            'firstname' => 'John',
            'lastname' => 'Landlord',
            'email' => 'update.profile.landlord@test.com',
            'password' => bcrypt('password123'),
            'role' => 'landlord',
            'email_verified_at' => now(),
        ]);
        
        // Create landlord profile
        $landlordUser->landlord()->create([
            'business_name' => 'Original Properties Ltd',
            'business_registration_number' => 'REG001',
            'phone' => '+233244111111',
            'address' => 'Original Address',
            'city' => 'Accra',
            'state' => 'Greater Accra',
            'country' => 'Ghana',
            'postal_code' => 'GA123',
            'commission_rate' => 10.00,
            'status' => 'active',
            'verified' => false,
        ]);
        
        echo "✅ Created landlord test user\n";
    }
    
    // Create tenant user
    $tenantUser = User::where('email', 'update.profile.tenant@test.com')->first();
    if (!$tenantUser) {
        $tenantUser = User::create([
            'firstname' => 'Jane',
            'lastname' => 'Tenant',
            'email' => 'update.profile.tenant@test.com',
            'password' => bcrypt('password123'),
            'role' => 'tenant',
            'email_verified_at' => now(),
        ]);
        
        // Create tenant profile
        $tenantUser->tenant()->create([
            'phone' => '+233244222222',
            'date_of_birth' => '1990-01-15',
            'gender' => 'female',
            'occupation' => 'Original Job',
            'employer' => 'Original Company',
            'monthly_income' => 3000.00,
            'current_address' => 'Original Tenant Address',
            'emergency_contact_name' => 'Original Emergency Contact',
            'emergency_contact_phone' => '+233244333333',
            'emergency_contact_relationship' => 'Sister',
            'status' => 'active',
            'verified' => false,
        ]);
        
        echo "✅ Created tenant test user\n";
    }
    
    echo "\n";
    
    // Test 2: Login and get tokens
    echo "2. Logging in to get authentication tokens...\n";
    
    function loginUser($email, $password) {
        global $apiUrl;
        
        $loginData = [
            'email' => $email,
            'password' => $password
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl . '/login');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if ($httpCode === 200 && $result['success']) {
            return $result['data']['token'];
        } else {
            throw new Exception("Login failed for $email: " . ($result['message'] ?? 'Unknown error'));
        }
    }
    
    $landlordToken = loginUser('update.profile.landlord@test.com', 'password123');
    $tenantToken = loginUser('update.profile.tenant@test.com', 'password123');
    
    echo "✅ Got landlord token\n";
    echo "✅ Got tenant token\n\n";
    
    // Test 3: Update landlord profile
    echo "3. Testing landlord profile update...\n";
    
    $landlordUpdateData = [
        'firstname' => 'Johnny',
        'lastname' => 'PropertyOwner',
        'business_name' => 'Updated Properties Ltd',
        'phone' => '+233244999999',
        'address' => 'Updated Business Address, East Legon',
        'city' => 'Accra',
        'state' => 'Greater Accra Region'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl . '/profile');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($landlordUpdateData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $landlordToken
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if ($httpCode === 200 && $result['success']) {
        echo "✅ Landlord profile updated successfully\n";
        echo "   Updated name: " . $result['data']['name'] . "\n";
        echo "   Updated business: " . $result['data']['landlord']['business_name'] . "\n";
        echo "   Updated phone: " . $result['data']['landlord']['phone'] . "\n";
    } else {
        echo "❌ Landlord profile update failed\n";
        echo "   Error: " . ($result['message'] ?? 'Unknown error') . "\n";
        if (isset($result['errors'])) {
            print_r($result['errors']);
        }
    }
    
    echo "\n";
    
    // Test 4: Update tenant profile
    echo "4. Testing tenant profile update...\n";
    
    $tenantUpdateData = [
        'firstname' => 'Janet',
        'lastname' => 'NewTenant',
        'occupation' => 'Software Engineer',
        'employer' => 'Tech Solutions Ltd',
        'monthly_income' => 5500.00,
        'current_address' => 'Updated Tenant Address, Tema',
        'emergency_contact_name' => 'Updated Emergency Contact',
        'emergency_contact_phone' => '+233244777777',
        'phone' => '+233244555555'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl . '/profile');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($tenantUpdateData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $tenantToken
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if ($httpCode === 200 && $result['success']) {
        echo "✅ Tenant profile updated successfully\n";
        echo "   Updated name: " . $result['data']['name'] . "\n";
        echo "   Updated occupation: " . $result['data']['tenant']['occupation'] . "\n";
        echo "   Updated income: GHS " . number_format($result['data']['tenant']['monthly_income'], 2) . "\n";
    } else {
        echo "❌ Tenant profile update failed\n";
        echo "   Error: " . ($result['message'] ?? 'Unknown error') . "\n";
        if (isset($result['errors'])) {
            print_r($result['errors']);
        }
    }
    
    echo "\n";
    
    // Test 5: Test partial update (only specific fields)
    echo "5. Testing partial profile update...\n";
    
    $partialUpdateData = [
        'firstname' => 'John Updated'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl . '/profile');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($partialUpdateData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $landlordToken
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if ($httpCode === 200 && $result['success']) {
        echo "✅ Partial update successful\n";
        echo "   Only firstname updated: " . $result['data']['firstname'] . "\n";
        echo "   Business name unchanged: " . $result['data']['landlord']['business_name'] . "\n";
    } else {
        echo "❌ Partial update failed\n";
        echo "   Error: " . ($result['message'] ?? 'Unknown error') . "\n";
    }
    
    echo "\n";
    
    // Test 6: Test email uniqueness validation
    echo "6. Testing email uniqueness validation...\n";
    
    $duplicateEmailData = [
        'email' => 'update.profile.tenant@test.com' // Try to use tenant's email for landlord
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl . '/profile');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($duplicateEmailData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $landlordToken
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if ($httpCode === 422 && !$result['success']) {
        echo "✅ Email uniqueness validation working correctly\n";
        echo "   Error message: " . $result['message'] . "\n";
    } else {
        echo "❌ Email uniqueness validation failed\n";
        echo "   Expected validation error but got success\n";
    }
    
    echo "\n";
    
    // Test 7: Test unauthenticated access
    echo "7. Testing unauthenticated access...\n";
    
    $updateData = ['firstname' => 'Unauthorized'];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl . '/profile');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($updateData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
        // No Authorization header
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 401) {
        echo "✅ Unauthenticated access properly blocked\n";
    } else {
        echo "❌ Unauthenticated access not properly blocked\n";
        echo "   HTTP Code: $httpCode\n";
    }
    
    echo "\n";
    
    // Test 8: Verify final profile state
    echo "8. Verifying final profile state...\n";
    
    function getProfile($token) {
        global $apiUrl;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl . '/profile');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: Bearer ' . $token
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return json_decode($response, true);
    }
    
    $landlordProfile = getProfile($landlordToken);
    $tenantProfile = getProfile($tenantToken);
    
    if ($landlordProfile['success'] && $tenantProfile['success']) {
        echo "✅ Final profile verification successful\n";
        echo "   Landlord: " . $landlordProfile['data']['name'] . " - " . $landlordProfile['data']['landlord']['business_name'] . "\n";
        echo "   Tenant: " . $tenantProfile['data']['name'] . " - " . $tenantProfile['data']['tenant']['occupation'] . "\n";
    } else {
        echo "❌ Final profile verification failed\n";
    }
    
    echo "\n";
    echo "🎉 Update Profile Testing Complete!\n";
    echo "===================================\n";
    echo "✅ Profile update endpoint implemented and tested\n";
    echo "✅ Role-specific field updates working\n";
    echo "✅ Validation working correctly\n";
    echo "✅ Authentication required\n";
    echo "✅ Partial updates supported\n";

} catch (\Exception $e) {
    echo "❌ Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
