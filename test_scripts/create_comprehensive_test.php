<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\User;
use App\Models\Landlord;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🚀 EFIEWURA COMPREHENSIVE TEST SETUP\n";
echo "====================================\n\n";

echo "📊 Database: " . config('database.connections.mysql.database') . " (MySQL)\n";
echo "🔗 Connection: " . config('database.default') . "\n\n";

$results = [
    'landlord_created' => false,
    'tenant_created' => false,
    'tests_passed' => 0,
    'tests_failed' => 0
];

try {
    echo "🧹 CLEANUP: Removing existing test accounts...\n";
    
    // Clean up existing test accounts
    User::where('email', 'premium.landlord@test.com')->forceDelete();
    User::where('email', 'premium.tenant@test.com')->forceDelete();
    
    echo "   ✅ Cleanup completed\n\n";

    // ==========================================
    // CREATE LANDLORD
    // ==========================================
    echo "🏠 CREATING LANDLORD ACCOUNT\n";
    echo "============================\n";

    $landlordUser = User::create([
        'firstname' => 'Premium',
        'lastname' => 'Property Owner',
        'email' => 'premium.landlord@test.com',
        'password' => Hash::make('password123'),
        'role' => 'landlord',
        'email_verified_at' => now(),
    ]);

    echo "✅ Landlord user created\n";
    echo "   ID: {$landlordUser->id}\n";
    echo "   Name: {$landlordUser->full_name}\n";
    echo "   Email: {$landlordUser->email}\n";

    $landlord = Landlord::create([
        'user_id' => $landlordUser->id,
        'business_name' => 'Premium Properties Ghana Ltd',
        'business_registration_number' => 'REG-PREMIUM-001',
        'phone' => '+233201234567',
        'address' => '789 Executive Heights, Airport Residential',
        'city' => 'Accra',
        'state' => 'Greater Accra',
        'country' => 'Ghana',
        'postal_code' => 'GA789',
        'commission_rate' => 8.50,
        'overdue_release_days' => 21,
        'status' => 'active',
        'verified' => true,
    ]);

    echo "✅ Landlord profile created\n";
    echo "   Business: {$landlord->business_name}\n";
    echo "   Phone: {$landlord->phone}\n\n";
    
    $results['landlord_created'] = true;

    // ==========================================
    // CREATE TENANT
    // ==========================================
    echo "👥 CREATING TENANT ACCOUNT\n";
    echo "==========================\n";

    $tenantUser = User::create([
        'firstname' => 'Professional',
        'lastname' => 'Tenant',
        'email' => 'premium.tenant@test.com',
        'password' => Hash::make('password123'),
        'role' => 'tenant',
        'email_verified_at' => now(),
    ]);

    echo "✅ Tenant user created\n";
    echo "   ID: {$tenantUser->id}\n";
    echo "   Name: {$tenantUser->full_name}\n";
    echo "   Email: {$tenantUser->email}\n";

    $tenant = Tenant::create([
        'user_id' => $tenantUser->id,
        'phone' => '+233244567890',
        'date_of_birth' => '1990-05-15',
        'gender' => 'male',
        'occupation' => 'Software Engineer',
        'employer' => 'Tech Solutions Ghana Ltd',
        'monthly_income' => 8500.00,
        'current_address' => '456 Tech Valley, East Legon',
        'emergency_contact_name' => 'Mary Tenant',
        'emergency_contact_phone' => '+233244567891',
        'emergency_contact_relationship' => 'Sister',
        'status' => 'active',
        'verified' => true,
    ]);

    echo "✅ Tenant profile created\n";
    echo "   Occupation: {$tenant->occupation}\n";
    echo "   Income: GHS {$tenant->monthly_income}\n\n";
    
    $results['tenant_created'] = true;

    // ==========================================
    // RUN TESTS
    // ==========================================
    echo "🧪 RUNNING COMPREHENSIVE TESTS\n";
    echo "===============================\n\n";

    // Test 1: Firstname/Lastname functionality
    echo "Test 1: Firstname/Lastname System\n";
    echo "---------------------------------\n";
    
    if ($landlordUser->firstname === 'Premium' && $landlordUser->lastname === 'Property Owner') {
        echo "✅ Landlord firstname/lastname stored correctly\n";
        $results['tests_passed']++;
    } else {
        echo "❌ Landlord firstname/lastname failed\n";
        $results['tests_failed']++;
    }
    
    if ($landlordUser->full_name === 'Premium Property Owner') {
        echo "✅ Landlord full_name accessor working\n";
        $results['tests_passed']++;
    } else {
        echo "❌ Landlord full_name accessor failed\n";
        $results['tests_failed']++;
    }
    
    if ($tenantUser->name === 'Professional Tenant') {
        echo "✅ Tenant name accessor working\n";
        $results['tests_passed']++;
    } else {
        echo "❌ Tenant name accessor failed\n";
        $results['tests_failed']++;
    }
    
    // Test 2: Database relationships
    echo "\nTest 2: Database Relationships\n";
    echo "-------------------------------\n";
    
    $landlordWithProfile = User::with('landlord')->find($landlordUser->id);
    if ($landlordWithProfile->landlord && $landlordWithProfile->landlord->business_name) {
        echo "✅ Landlord relationship working\n";
        $results['tests_passed']++;
    } else {
        echo "❌ Landlord relationship failed\n";
        $results['tests_failed']++;
    }
    
    $tenantWithProfile = User::with('tenant')->find($tenantUser->id);
    if ($tenantWithProfile->tenant && $tenantWithProfile->tenant->occupation) {
        echo "✅ Tenant relationship working\n";
        $results['tests_passed']++;
    } else {
        echo "❌ Tenant relationship failed\n";
        $results['tests_failed']++;
    }
    
    // Test 3: Authentication tokens
    echo "\nTest 3: API Token Generation\n";
    echo "-----------------------------\n";
    
    $landlordToken = $landlordUser->createToken('test')->plainTextToken;
    if ($landlordToken) {
        echo "✅ Landlord token generated: " . substr($landlordToken, 0, 20) . "...\n";
        $results['tests_passed']++;
    } else {
        echo "❌ Landlord token generation failed\n";
        $results['tests_failed']++;
    }
    
    $tenantToken = $tenantUser->createToken('test')->plainTextToken;
    if ($tenantToken) {
        echo "✅ Tenant token generated: " . substr($tenantToken, 0, 20) . "...\n";
        $results['tests_passed']++;
    } else {
        echo "❌ Tenant token generation failed\n";
        $results['tests_failed']++;
    }
    
    // Test 4: Soft delete functionality
    echo "\nTest 4: Soft Delete System\n";
    echo "--------------------------\n";
    
    // Test soft delete on tenant
    $tenantUser->softDelete();
    if ($tenantUser->status === 'deleted' && $tenantUser->deleted_at) {
        echo "✅ Soft delete working\n";
        $results['tests_passed']++;
    } else {
        echo "❌ Soft delete failed\n";
        $results['tests_failed']++;
    }
    
    // Restore tenant
    $tenantUser->restore();
    if ($tenantUser->status === 'active' && !$tenantUser->deleted_at) {
        echo "✅ Restore function working\n";
        $results['tests_passed']++;
    } else {
        echo "❌ Restore function failed\n";
        $results['tests_failed']++;
    }

    // ==========================================
    // SUMMARY
    // ==========================================
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "📊 TEST RESULTS SUMMARY\n";
    echo str_repeat("=", 50) . "\n\n";
    
    echo "👥 ACCOUNTS CREATED:\n";
    echo "   🏠 Landlord: " . ($results['landlord_created'] ? '✅ Success' : '❌ Failed') . "\n";
    echo "   👤 Tenant: " . ($results['tenant_created'] ? '✅ Success' : '❌ Failed') . "\n\n";
    
    echo "🧪 TEST RESULTS:\n";
    echo "   ✅ Passed: {$results['tests_passed']}\n";
    echo "   ❌ Failed: {$results['tests_failed']}\n";
    echo "   📊 Success Rate: " . round(($results['tests_passed'] / ($results['tests_passed'] + $results['tests_failed'])) * 100, 1) . "%\n\n";
    
    if ($results['tests_failed'] === 0) {
        echo "🎉 ALL TESTS PASSED! System is working perfectly!\n\n";
    } else {
        echo "⚠️  Some tests failed. Please check the details above.\n\n";
    }
    
    echo "📋 LANDLORD CREDENTIALS:\n";
    echo "   Email: premium.landlord@test.com\n";
    echo "   Password: password123\n";
    echo "   Business: Premium Properties Ghana Ltd\n\n";
    
    echo "📋 TENANT CREDENTIALS:\n";
    echo "   Email: premium.tenant@test.com\n";
    echo "   Password: password123\n";
    echo "   Occupation: Software Engineer\n\n";
    
    echo "🔗 cURL Login Examples:\n";
    echo "Landlord:\n";
    echo "curl -X POST http://localhost:8080/api/login \\\n";
    echo "  -H \"Content-Type: application/json\" \\\n";
    echo "  -d '{\"email\":\"premium.landlord@test.com\",\"password\":\"password123\"}'\n\n";
    
    echo "Tenant:\n";
    echo "curl -X POST http://localhost:8080/api/login \\\n";
    echo "  -H \"Content-Type: application/json\" \\\n";
    echo "  -d '{\"email\":\"premium.tenant@test.com\",\"password\":\"password123\"}'\n\n";

} catch (Exception $e) {
    echo "❌ Critical Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    $results['tests_failed']++;
}

echo "🏁 Script completed.\n";
