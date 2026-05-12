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

echo "📋 Creating Additional Test Bookings\n";
echo "===================================\n\n";

try {
    // Get available properties that don't have bookings yet
    $bookedPropertyIds = Booking::pluck('property_id')->toArray();
    $availableProperties = Property::whereNotIn('id', $bookedPropertyIds)
                                 ->where('availability_status', 'available')
                                 ->get();
    
    if ($availableProperties->count() == 0) {
        echo "⚠️ No available properties without bookings found.\n";
        echo "Creating bookings for properties that already have bookings...\n\n";
        $availableProperties = Property::where('availability_status', 'available')->take(2)->get();
    }
    
    echo "📋 Properties available for booking: {$availableProperties->count()}\n";
    
    // Create a couple more test tenants
    $newTenantData = [
        [
            'firstname' => 'David',
            'lastname' => 'Mensah',
            'email' => 'david.mensah@test.com',
            'phone' => '+233271234567',
            'employment' => 'Doctor',
            'employer' => 'Korle Bu Teaching Hospital',
            'income' => 8500
        ],
        [
            'firstname' => 'Rebecca',
            'lastname' => 'Asante',
            'email' => 'rebecca.asante@test.com',
            'phone' => '+233281234567',
            'employment' => 'Lawyer',
            'employer' => 'Asante & Associates',
            'income' => 7200
        ]
    ];
    
    $newTenants = [];
    foreach ($newTenantData as $data) {
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
            'date_of_birth' => Carbon::now()->subYears(rand(28, 45)),
            'employment_status' => $data['employment'],
            'employer_name' => $data['employer'],
            'monthly_income' => $data['income'],
            'emergency_contact_name' => 'Emergency Contact',
            'emergency_contact_phone' => '+233200000000',
            'preferred_move_in_date' => Carbon::now()->addDays(rand(10, 60)),
            'status' => 'active',
            'verified' => true,
        ]);

        $newTenants[] = $user;
        echo "✅ Created tenant: {$user->full_name} ({$data['employment']})\n";
    }
    
    // Additional booking scenarios
    $additionalBookings = [
        [
            'status' => 'completed',
            'description' => 'Medical professional long-term lease',
            'move_in_offset' => -30, // Already moved in (past date)
            'lease_months' => 18,
            'notes' => 'Doctor seeking stable accommodation near hospital'
        ],
        [
            'status' => 'cancelled',
            'description' => 'Legal professional booking',
            'move_in_offset' => 60,
            'lease_months' => 12,
            'notes' => 'Lawyer requiring executive accommodation'
        ]
    ];
    
    $createdBookings = [];
    
    foreach ($additionalBookings as $index => $scenario) {
        if ($index >= $availableProperties->count() || $index >= count($newTenants)) break;
        
        $property = $availableProperties[$index];
        $tenant = $newTenants[$index];
        $tenantProfile = $tenant->tenant;

        echo "\n📋 Creating additional booking " . ($index + 1) . ": {$scenario['description']}\n";

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
            'confirmed_at' => in_array($scenario['status'], ['confirmed', 'completed']) ? Carbon::now()->subDays(rand(5, 15)) : null,
            'cancelled_at' => $scenario['status'] === 'cancelled' ? Carbon::now()->subDays(rand(1, 5)) : null,
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
        echo "   📝 Duration: {$scenario['lease_months']} months\n";
    }
    
    echo "\n===============================================\n";
    echo "🎉 ADDITIONAL BOOKINGS CREATED SUCCESSFULLY!\n";
    echo "===============================================\n\n";
    
    // Get all bookings for summary
    $allBookings = Booking::with(['property', 'user'])->get();
    
    echo "📊 COMPLETE BOOKING SUMMARY:\n";
    echo "   Total Bookings in System: {$allBookings->count()}\n";
    echo "   Total Properties: " . Property::count() . "\n";
    echo "   Total Tenants: " . User::where('role', 'tenant')->count() . "\n\n";
    
    // Status breakdown
    $statusCounts = $allBookings->countBy('status');
    echo "📋 ALL BOOKING STATUS BREAKDOWN:\n";
    foreach ($statusCounts as $status => $count) {
        echo "   • " . ucfirst(str_replace('_', ' ', $status)) . ": {$count}\n";
    }
    echo "\n";
    
    echo "📋 ALL BOOKINGS:\n";
    foreach ($allBookings as $booking) {
        $statusEmoji = [
            'pending' => '⏳',
            'confirmed' => '✅', 
            'cancelled' => '❌',
            'rejected' => '🚫',
            'completed' => '🏁'
        ];
        
        $emoji = $statusEmoji[$booking->status] ?? '📋';
        
        echo "   {$emoji} Booking ID {$booking->id}: {$booking->user->full_name} → {$booking->property->title}\n";
        echo "      Status: {$booking->status} | Rent: GHS {$booking->monthly_rent}/month\n";
        echo "      Move-in: {$booking->move_in_date->format('Y-m-d')} | Duration: {$booking->lease_duration_months} months\n\n";
    }
    
    echo "🧪 COMPREHENSIVE API TESTING:\n";
    echo "   Filter by status: GET /api/bookings?status=confirmed\n";
    echo "   Filter by property: GET /api/bookings?property_id=2\n";
    echo "   Landlord bookings: GET /api/landlord/bookings\n";
    echo "   Tenant bookings: GET /api/tenant/bookings\n\n";
    
    echo "🔗 Advanced cURL Examples:\n";
    echo "Get confirmed bookings:\n";
    echo "curl -X GET \"http://localhost:8080/api/bookings?status=confirmed\" \\\n";
    echo "  -H \"Accept: application/json\"\n\n";
    
    if ($allBookings->count() > 0) {
        $randomBooking = $allBookings->random();
        echo "Get bookings for specific property:\n";
        echo "curl -X GET \"http://localhost:8080/api/bookings?property_id={$randomBooking->property_id}\" \\\n";
        echo "  -H \"Accept: application/json\"\n\n";
    }
    
    echo "✅ Complete booking test environment ready!\n";

} catch (Exception $e) {
    echo "❌ Error creating additional bookings: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nScript completed.\n";
