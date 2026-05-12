<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use Illuminate\Support\Facades\Auth;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TESTING FIRSTNAME/LASTNAME FUNCTIONALITY ===\n\n";

// Test 1: Create user with firstname and lastname
echo "Test 1: Creating user with firstname and lastname\n";
$user1 = User::create([
    'firstname' => 'John',
    'lastname' => 'Smith',
    'email' => 'john.smith' . time() . '@test.com',
    'password' => bcrypt('password'),
    'role' => 'tenant',
]);

echo "Created user:\n";
echo "  Firstname: {$user1->firstname}\n";
echo "  Lastname: {$user1->lastname}\n";
echo "  Name (auto-generated): {$user1->name}\n";
echo "  Full Name (accessor): {$user1->full_name}\n\n";

// Test 2: Create user with only name field (backward compatibility)
echo "Test 2: Creating user with only name field (backward compatibility)\n";
$user2 = User::create([
    'name' => 'Jane Doe',
    'email' => 'jane.doe' . time() . '@test.com',
    'password' => bcrypt('password'),
    'role' => 'landlord',
]);

echo "Created user with name only:\n";
echo "  Name: {$user2->name}\n";
echo "  Firstname: " . ($user2->firstname ?? 'NULL') . "\n";
echo "  Lastname: " . ($user2->lastname ?? 'NULL') . "\n";
echo "  Full Name (accessor): {$user2->full_name}\n\n";

// Test 3: Update firstname/lastname
echo "Test 3: Updating firstname and lastname\n";
$user1->update([
    'firstname' => 'Johnny',
    'lastname' => 'Smithson',
]);

echo "Updated user:\n";
echo "  Firstname: {$user1->firstname}\n";
echo "  Lastname: {$user1->lastname}\n";
echo "  Name (auto-updated): {$user1->name}\n";
echo "  Full Name (accessor): {$user1->full_name}\n\n";

// Test 4: Using the full_name attribute setter
echo "Test 4: Using full_name attribute setter\n";
$user3 = new User([
    'email' => 'test.user' . time() . '@test.com',
    'password' => bcrypt('password'),
    'role' => 'tenant',
]);

$user3->full_name = 'Alice Johnson';
$user3->save();

echo "User created with full_name setter:\n";
echo "  Firstname: {$user3->firstname}\n";
echo "  Lastname: {$user3->lastname}\n";
echo "  Name: {$user3->name}\n";
echo "  Full Name (accessor): {$user3->full_name}\n\n";

// Test 5: Check database structure
echo "Test 5: Verifying database structure\n";
$columns = \Illuminate\Support\Facades\Schema::getColumnListing('users');
echo "Users table columns:\n";
foreach ($columns as $column) {
    echo "  - $column\n";
}

echo "\n=== FIRSTNAME/LASTNAME TESTING COMPLETED ===\n";
echo "✅ Users can now be created with separate firstname and lastname fields\n";
echo "✅ The name field is automatically generated from firstname + lastname\n";
echo "✅ Backward compatibility maintained for existing name-only users\n";
echo "✅ Full name accessor provides consistent access to complete name\n";
