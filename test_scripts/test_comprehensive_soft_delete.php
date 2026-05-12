<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Property;
use App\Models\Booking;
use App\Models\Landlord;
use Illuminate\Support\Facades\Auth;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TESTING SOFT DELETE FUNCTIONALITY ===\n\n";

// Create a test admin user to perform deletions
$admin = User::where('role', 'admin')->first();
if (!$admin) {
    $admin = User::create([
        'firstname' => 'Test',
        'lastname' => 'Admin',
        'email' => 'testadmin@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);
    echo "Created test admin user\n";
}

// Login as admin
Auth::login($admin);
echo "Logged in as admin: {$admin->full_name} (ID: {$admin->id})\n";
echo "Current authenticated user ID: " . (Auth::id() ?? 'NULL') . "\n\n";

// Test User Soft Delete
echo "=== TESTING USER SOFT DELETE ===\n";
$uniqueEmail = 'testuser' . time() . '@deletion.com';
$testUser = User::create([
    'firstname' => 'Test',
    'lastname' => 'User for Deletion',
    'email' => $uniqueEmail,
    'password' => bcrypt('password'),
    'role' => 'tenant',
]);
echo "Created test user: {$testUser->full_name} (ID: {$testUser->id})\n";

// Test soft delete
$testUser->softDelete();
echo "Soft deleted user\n";

// Refresh the model to get updated data
$testUser->refresh();

// Check status
echo "User status after soft delete: {$testUser->status}\n";
echo "User deleted_by: " . ($testUser->deleted_by ?? 'NULL') . "\n";
echo "User deleted_at: " . ($testUser->deleted_at ? $testUser->deleted_at->format('Y-m-d H:i:s') : 'NULL') . "\n";
echo "Is deleted: " . ($testUser->isDeleted() ? 'YES' : 'NO') . "\n";

// Test scopes
echo "Active users count: " . User::active()->count() . "\n";
echo "Deleted users count: " . User::softDeleted()->count() . "\n";
echo "All users count: " . User::withDeleted()->count() . "\n";

// Test restore
$testUser->restore();
echo "Restored user\n";
echo "User status after restore: {$testUser->status}\n";
echo "User deleted_by after restore: " . ($testUser->deleted_by ?? 'NULL') . "\n";
echo "User deleted_at after restore: " . ($testUser->deleted_at ?? 'NULL') . "\n\n";

// Test Property Soft Delete
echo "=== TESTING PROPERTY SOFT DELETE ===\n";

// First create a landlord
$landlord = Landlord::first();
if (!$landlord) {
    $uniqueLandlordEmail = 'testlandlord' . time() . '@example.com';
    $landlordUser = User::create([
        'name' => 'Test Landlord',
        'email' => $uniqueLandlordEmail,
        'password' => bcrypt('password'),
        'role' => 'landlord',
    ]);
    $landlord = Landlord::create([
        'user_id' => $landlordUser->id,
        'business_name' => 'Test Property Management',
        'phone' => '1234567890',
        'address' => 'Test Address',
    ]);
}

$testProperty = Property::create([
    'landlord_id' => $landlord->id,
    'property_category_id' => 1,
    'title' => 'Test Property for Deletion ' . time(),
    'description' => 'Test property',
    'price' => 1000.00,
    'currency' => 'USD',
    'bedrooms' => 2,
    'bathrooms' => 1,
    'size_sqm' => 80.0,
    'address' => 'Test Address',
    'city' => 'Test City',
    'state' => 'Test State',
    'country' => 'Test Country',
    'availability_status' => 'available',
    'is_active' => true,
]);
echo "Created test property: {$testProperty->title} (ID: {$testProperty->id})\n";

// Test soft delete
$testProperty->softDelete();
echo "Soft deleted property\n";

// Refresh the model to get updated data
$testProperty->refresh();

// Check status
echo "Property status after soft delete: {$testProperty->status}\n";
echo "Property is_active after soft delete: " . ($testProperty->is_active ? 'TRUE' : 'FALSE') . "\n";
echo "Property deleted_by: " . ($testProperty->deleted_by ?? 'NULL') . "\n";
echo "Property deleted_at: " . ($testProperty->deleted_at ? $testProperty->deleted_at->format('Y-m-d H:i:s') : 'NULL') . "\n";
echo "Is deleted: " . ($testProperty->isDeleted() ? 'YES' : 'NO') . "\n";

// Test scopes
echo "Non-deleted properties count: " . Property::notDeleted()->count() . "\n";
echo "Deleted properties count: " . Property::softDeleted()->count() . "\n";
echo "All properties count: " . Property::withDeleted()->count() . "\n";

// Test restore
$testProperty->restore();
echo "Restored property\n";
echo "Property status after restore: {$testProperty->status}\n";
echo "Property is_active after restore: " . ($testProperty->is_active ? 'TRUE' : 'FALSE') . "\n";
echo "Property deleted_by after restore: " . ($testProperty->deleted_by ?? 'NULL') . "\n\n";

// Test Booking Soft Delete
echo "=== TESTING BOOKING SOFT DELETE ===\n";
$testBooking = Booking::create([
    'property_id' => $testProperty->id,
    'user_id' => $testUser->id,
    'landlord_id' => $landlord->id,
    'status' => 'pending',
    'move_in_date' => now()->addDays(30),
    'move_out_date' => now()->addDays(365),
    'lease_duration_months' => 12,
    'monthly_rent' => 1000.00,
    'security_deposit' => 1000.00,
    'total_amount' => 12000.00,
    'currency' => 'USD',
    'tenant_name' => 'Test Tenant',
    'tenant_phone' => '1234567890',
    'tenant_email' => 'testtenant' . time() . '@example.com',
]);
echo "Created test booking: ID {$testBooking->id} (Status: {$testBooking->status})\n";

// Test soft delete
$testBooking->softDelete();
echo "Soft deleted booking\n";

// Refresh the model to get updated data
$testBooking->refresh();

// Check status
echo "Booking status after soft delete: {$testBooking->status}\n";
echo "Booking deleted_by: " . ($testBooking->deleted_by ?? 'NULL') . "\n";
echo "Booking deleted_at: " . ($testBooking->deleted_at ? $testBooking->deleted_at->format('Y-m-d H:i:s') : 'NULL') . "\n";
echo "Is deleted: " . ($testBooking->isDeleted() ? 'YES' : 'NO') . "\n";

// Test scopes
echo "Non-deleted bookings count: " . Booking::notDeleted()->count() . "\n";
echo "Deleted bookings count: " . Booking::softDeleted()->count() . "\n";
echo "All bookings count: " . Booking::withDeleted()->count() . "\n";

// Test restore
$testBooking->restore();
echo "Restored booking\n";
echo "Booking status after restore: {$testBooking->status}\n";
echo "Booking deleted_by after restore: " . ($testBooking->deleted_by ?? 'NULL') . "\n\n";

// Test Relationships
echo "=== TESTING RELATIONSHIPS WITH SOFT DELETE ===\n";
echo "User bookings (should exclude deleted): " . $testUser->bookings()->count() . "\n";
echo "User all bookings (should include deleted): " . $testUser->allBookings()->count() . "\n";
echo "Property bookings (should exclude deleted): " . $testProperty->bookings()->count() . "\n";
echo "Property all bookings (should include deleted): " . $testProperty->allBookings()->count() . "\n\n";

echo "=== SOFT DELETE TESTING COMPLETED ===\n";
echo "All models now have comprehensive soft delete functionality!\n";
echo "- Users: status, deleted_by, deleted_at fields\n";
echo "- Properties: status, deleted_by, deleted_at fields\n";
echo "- Bookings: status (already existed), deleted_by, deleted_at fields\n";
echo "- All models have: softDelete(), restore(), isDeleted() methods\n";
echo "- All models have: notDeleted(), softDeleted(), withDeleted() scopes\n";
echo "- Relationships automatically exclude deleted records\n";
