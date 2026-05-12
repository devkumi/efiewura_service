<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DEBUGGING SOFT DELETE ===\n\n";

// Login as admin
$admin = User::where('role', 'admin')->first();
Auth::login($admin);
echo "Logged in as admin: {$admin->name} (ID: {$admin->id})\n";
echo "Current Auth::id(): " . Auth::id() . "\n\n";

// Create test user
$testUser = User::create([
    'name' => 'Debug User',
    'email' => 'debug' . time() . '@test.com',
    'password' => bcrypt('password'),
    'role' => 'tenant',
]);

echo "Created user: {$testUser->name} (ID: {$testUser->id})\n";
echo "Before soft delete:\n";
echo "  Status: {$testUser->status}\n";
echo "  deleted_by: " . ($testUser->deleted_by ?? 'NULL') . "\n";
echo "  deleted_at: " . ($testUser->deleted_at ?? 'NULL') . "\n\n";

// Perform soft delete with debugging
echo "Performing soft delete...\n";
echo "Auth::id() in context: " . Auth::id() . "\n";
echo "now() in context: " . now() . "\n";

$result = $testUser->update([
    'status' => 'deleted',
    'deleted_by' => Auth::id(),
    'deleted_at' => now(),
]);

echo "Update result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";

// Refresh and check
$testUser->refresh();
echo "\nAfter soft delete (refreshed):\n";
echo "  Status: {$testUser->status}\n";
echo "  deleted_by: " . ($testUser->deleted_by ?? 'NULL') . "\n";
echo "  deleted_at: " . ($testUser->deleted_at ?? 'NULL') . "\n";

// Check database directly
echo "\nDirect database query:\n";
$dbUser = DB::table('users')->where('id', $testUser->id)->first();
echo "  Status: {$dbUser->status}\n";
echo "  deleted_by: " . ($dbUser->deleted_by ?? 'NULL') . "\n";
echo "  deleted_at: " . ($dbUser->deleted_at ?? 'NULL') . "\n";
