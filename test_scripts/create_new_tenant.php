<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "👥 Creating New Tenant Test Account\n";
echo "=================================\n\n";

try {
    // Clean up existing test tenant
    $existingUser = User::where('email', 'premium.tenant@test.com')->first();
    if ($existingUser) {
        $existingUser->forceDelete();
        echo "🧹 Removed existing test tenant\n";
    }

    // Create new tenant user
    $user = User::create([
        'firstname' => 'Professional',
        'lastname' => 'Tenant',
        'email' => 'premium.tenant@test.com',
        'password' => Hash::make('password123'),
        'role' => 'tenant',
        'email_verified_at' => now(),
    ]);

    echo "✅ User created successfully\n";
    echo "   ID: {$user->id}\n";
    echo "   Firstname: {$user->firstname}\n";
    echo "   Lastname: {$user->lastname}\n";
    echo "   Full Name: {$user->full_name}\n";
    echo "   Email: {$user->email}\n";
    echo "   Role: {$user->role}\n\n";

    // Create tenant profile
    $tenant = Tenant::create([
        'user_id' => $user->id,
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

    echo "✅ Tenant profile created successfully\n";
    echo "   Phone: {$tenant->phone}\n";
    echo "   Occupation: {$tenant->occupation}\n";
    echo "   Employer: {$tenant->employer}\n";
    echo "   Monthly Income: GHS {$tenant->monthly_income}\n";
    echo "   Status: {$tenant->status}\n";
    echo "   Emergency Contact: {$tenant->emergency_contact_name} ({$tenant->emergency_contact_relationship})\n\n";

    // Create API token for testing
    $token = $user->createToken('test_token')->plainTextToken;
    echo "✅ API token generated\n";
    echo "   Token: " . substr($token, 0, 20) . "...\n\n";

    echo "===========================================\n";
    echo "🎉 NEW TENANT ACCOUNT CREATED SUCCESSFULLY!\n";
    echo "===========================================\n\n";

    echo "📋 TEST CREDENTIALS:\n";
    echo "   Firstname: {$user->firstname}\n";
    echo "   Lastname: {$user->lastname}\n";
    echo "   Full Name: {$user->full_name}\n";
    echo "   Email: premium.tenant@test.com\n";
    echo "   Password: password123\n";
    echo "   Role: tenant\n";
    echo "   Occupation: Software Engineer\n";
    echo "   Phone: +233244567890\n\n";

    echo "🧪 API TESTING:\n";
    echo "   1. Login: POST /api/login\n";
    echo "   2. Search Properties: GET /api/properties\n";
    echo "   3. Create Booking: POST /api/bookings\n";
    echo "   4. View My Bookings: GET /api/my-bookings\n\n";

    echo "🔗 cURL Login Example:\n";
    echo "curl -X POST http://localhost:8080/api/login \\\n";
    echo "  -H \"Content-Type: application/json\" \\\n";
    echo "  -d '{\"email\":\"premium.tenant@test.com\",\"password\":\"password123\"}'\n\n";

} catch (Exception $e) {
    echo "❌ Error creating tenant: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "Script completed.\n";
