<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Property;
use App\Models\Booking;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "📋 Creating Test Bookings\n";
echo "========================\n\n";

try {
    // Get existing tenant
    $tenantUser = User::where('email', 'premium.tenant@test.com')->first();
    if (!$tenantUser) {
        echo "❌ Tenant not found. Please run create_new_tenant.php first.\n";
        exit(1);
    }

    $tenant = $tenantUser->tenant;
    if (!$tenant) {
        echo "❌ Tenant profile not found.\n";
        exit(1);
    }

    echo "✅ Found tenant: {$tenantUser->full_name}\n";
    echo "   Email: {$tenantUser->email}\n\n";

    // Get available properties
    $properties = Property::where('availability_status', 'available')->get();
    if ($properties->count() < 2) {
        echo "❌ Not enough available properties found. Need at least 2 properties.\n";
        exit(1);
    }

    echo "📋 Available properties: {$properties->count()}\n\n";

    // Create additional tenant users for variety
    $additionalTenants = [];
    $tenantData = [
        [
            'firstname' => 'Sarah',
            'lastname' => 'Johnson',
            'email' => 'sarah.johnson@test.com',
            'phone' => '+233241234567',
            'employment' => 'Software Engineer',
            'employer' => 'Tech Solutions Ltd'
        ],
        [
            'firstname' => 'Michael',
            'lastname' => 'Osei',
            'email' => 'michael.osei@test.com',
            'phone' => '+233551234567',
            'employment' => 'Marketing Manager',
            'employer' => 'Brand Excellence Ltd'
        ],
        [
            'firstname' => 'Grace',
            'lastname' => 'Addo',
            'email' => 'grace.addo@test.com',
            'phone' => '+233261234567',
            'employment' => 'Financial Analyst',
            'employer' => 'Global Finance Corp'
        ]
    ];

    foreach ($tenantData as $data) {
        $existingUser = User::where('email', $data['email'])->first();
        if ($existingUser) {
            $existingUser->forceDelete();
        }

        $user = User::create([
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'email' => $data['email'],
            'password' => Hash::make('password123'),
            'role' => 'tenant',
            'email_verified_at' => now(),
        ]);

        $tenant = Tenant::create([
            'user_id' => $user->id,
            'phone_number' => $data['phone'],
            'date_of_birth' => Carbon::now()->subYears(rand(25, 40)),
            'employment_status' => $data['employment'],
            'employer_name' => $data['employer'],
            'monthly_income' => rand(3000, 8000),
            'emergency_contact_name' => 'Emergency Contact',
            'emergency_contact_phone' => '+233200000000',
            'preferred_move_in_date' => Carbon::now()->addDays(rand(10, 60)),
            'status' => 'active',
            'verified' => true,
        ]);

        $additionalTenants[] = $user;
        echo "✅ Created additional tenant: {$user->full_name}\n";
    }

    // All tenants (original + additional)
    $allTenants = collect([$tenantUser])->merge($additionalTenants);
    echo "\n📊 Total tenants available: {$allTenants->count()}\n\n";

    // Booking scenarios to create
    $bookingScenarios = [
        [
            'status' => 'confirmed',
            'description' => 'Long-term family rental',
            'move_in_offset' => 15,
            'lease_months' => 12,
            'notes' => 'Family with children, excellent references'
        ],
        [
            'status' => 'pending',
            'description' => 'Professional looking for modern apartment',
            'move_in_offset' => 30,
            'lease_months' => 6,
            'notes' => 'Young professional, stable income'
        ],
        [
            'status' => 'confirmed',
            'description' => 'Executive short-term rental',
            'move_in_offset' => 7,
            'lease_months' => 3,
            'notes' => 'Corporate executive, immediate occupancy needed'
        ],
        [
            'status' => 'rejected',
            'description' => 'Couple seeking furnished accommodation',
            'move_in_offset' => 45,
            'lease_months' => 24,
            'notes' => 'Young couple, both employed, looking for long-term'
        ],
        [
            'status' => 'confirmed',
            'description' => 'Student accommodation request',
            'move_in_offset' => 20,
            'lease_months' => 9,
            'notes' => 'Graduate student, university guaranteed income'
        ]
    ];

    $createdBookings = [];
    
    foreach ($bookingScenarios as $index => $scenario) {
        if ($index >= $properties->count() || $index >= $allTenants->count()) break;
        
        $property = $properties[$index];
        $tenant = $allTenants[$index];
        $tenantProfile = $tenant->tenant;

        echo "📋 Creating booking " . ($index + 1) . ": {$scenario['description']}\n";

        $moveInDate = Carbon::now()->addDays($scenario['move_in_offset']);
        $moveOutDate = $moveInDate->copy()->addMonths($scenario['lease_months']);
        $totalAmount = $property->price * $scenario['lease_months'];

        $booking = Booking::create([
            'user_id' => $tenant->id,
            'property_id' => $property->id,
            'landlord_id' => $property->landlord_id,
            'move_in_date' => $moveInDate,
            'move_out_date' => $moveOutDate,
            'monthly_rent' => $property->price,
            'security_deposit' => $property->security_deposit ?? ($property->price * 2),
            'total_amount' => $totalAmount,
            'currency' => 'GHS',
            'status' => $scenario['status'],
            'lease_duration_months' => $scenario['lease_months'],
            'tenant_name' => $tenant->full_name,
            'tenant_phone' => $tenantProfile->phone_number ?? '+233200000000',
            'tenant_email' => $tenant->email,
            'tenant_message' => $scenario['notes'],
            'occupation' => $tenantProfile->employment_status ?? 'Professional',
            'employer' => $tenantProfile->employer_name ?? 'Self Employed',
            'monthly_income' => $tenantProfile->monthly_income ?? 5000,
            'emergency_contact' => $tenantProfile->emergency_contact_name ?? 'Emergency Contact',
            'confirmed_at' => in_array($scenario['status'], ['confirmed']) ? Carbon::now()->subDays(rand(1, 5)) : null,
        ]);

        $createdBookings[] = $booking;

        echo "   ✅ Created Booking ID: {$booking->id}\n";
        echo "   👤 Tenant: {$tenant->full_name}\n";
        echo "   🏠 Property: {$property->title}\n";
        echo "   📅 Move-in: {$moveInDate->format('Y-m-d')}\n";
        echo "   📅 Move-out: {$moveOutDate->format('Y-m-d')}\n";
        echo "   💰 Monthly Rent: GHS {$property->price}\n";
        echo "   💰 Total Amount: GHS {$totalAmount}\n";
        echo "   📊 Status: {$scenario['status']}\n";
        echo "   📝 Duration: {$scenario['lease_months']} months\n\n";

        echo "\n";
    }

    echo "===============================================\n";
    echo "🎉 BOOKINGS CREATED SUCCESSFULLY!\n";
    echo "===============================================\n\n";

    echo "📊 SUMMARY:\n";
    echo "   Total Bookings Created: " . count($createdBookings) . "\n";
    echo "   Total Tenants: {$allTenants->count()}\n";
    echo "   Properties with Bookings: " . count($createdBookings) . "\n\n";

    // Status breakdown
    $statusCounts = collect($createdBookings)->countBy('status');
    echo "📋 BOOKING STATUS BREAKDOWN:\n";
    foreach ($statusCounts as $status => $count) {
        echo "   • " . ucfirst(str_replace('_', ' ', $status)) . ": {$count}\n";
    }
    echo "\n";

    echo "📋 BOOKING DETAILS:\n";
    foreach ($createdBookings as $booking) {
        $property = Property::find($booking->property_id);
        $tenant = User::find($booking->user_id);
        
        echo "   • Booking ID {$booking->id}: {$tenant->full_name} → {$property->title}\n";
        echo "     Status: {$booking->status} | Duration: {$booking->lease_duration_months} months\n";
        echo "     Move-in: {$booking->move_in_date->format('Y-m-d')} | Rent: GHS {$booking->monthly_rent}/month\n\n";
    }

    echo "🧪 API TESTING ENDPOINTS:\n";
    echo "   GET /api/bookings - List all bookings\n";
    echo "   GET /api/bookings/{id} - Get specific booking\n";
    echo "   GET /api/landlord/bookings - Get landlord's bookings\n";
    echo "   GET /api/tenant/bookings - Get tenant's bookings\n";
    echo "   POST /api/bookings - Create new booking\n";
    echo "   PUT /api/bookings/{id} - Update booking\n\n";

    echo "🔗 cURL Examples:\n";
    if (count($createdBookings) > 0) {
        $firstBooking = $createdBookings[0];
        echo "Get specific booking:\n";
        echo "curl -X GET http://localhost:8080/api/bookings/{$firstBooking->id} \\\n";
        echo "  -H \"Accept: application/json\"\n\n";
    }

    echo "Get all bookings:\n";
    echo "curl -X GET http://localhost:8080/api/bookings \\\n";
    echo "  -H \"Accept: application/json\"\n\n";

    echo "💡 NEXT STEPS:\n";
    echo "   1. Test booking API endpoints\n";
    echo "   2. Create payment processing workflows\n";
    echo "   3. Test booking status transitions\n";
    echo "   4. Implement booking notifications\n\n";

    echo "✅ Booking test environment is ready!\n";

} catch (Exception $e) {
    echo "❌ Error creating bookings: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nScript completed.\n";
