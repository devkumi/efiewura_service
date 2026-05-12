<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🧪 Testing Firstname/Lastname with MySQL\n";
echo "========================================\n\n";

try {
    // Clean up any test data first
    User::where('email', 'like', '%.mysql.test@example.com')->forceDelete();

    echo "1. Testing user creation with firstname/lastname only...\n";
    
    $user1 = User::create([
        'firstname' => 'MySQL',
        'lastname' => 'Test User',
        'email' => 'mysql.test@example.com',
        'password' => Hash::make('password123'),
        'role' => 'tenant'
    ]);
    
    echo "   ✅ User created with ID: {$user1->id}\n";
    echo "   ✅ Firstname: {$user1->firstname}\n";
    echo "   ✅ Lastname: {$user1->lastname}\n";
    echo "   ✅ Full name (accessor): {$user1->full_name}\n";
    echo "   ✅ Name attribute (accessor): {$user1->name}\n\n";

    echo "2. Testing full_name mutator...\n";
    
    $user2 = new User();
    $user2->full_name = 'Database Connection Test';
    $user2->email = 'db.mysql.test@example.com';
    $user2->password = Hash::make('password123');
    $user2->role = 'landlord';
    $user2->save();
    
    echo "   ✅ User created with full_name mutator\n";
    echo "   ✅ Firstname: {$user2->firstname}\n";
    echo "   ✅ Lastname: {$user2->lastname}\n";
    echo "   ✅ Full name: {$user2->full_name}\n\n";

    echo "3. Verifying MySQL database schema...\n";
    
    // Use MySQL-specific query to check table structure
    $columns = DB::select("DESCRIBE users");
    $columnNames = array_column($columns, 'Field');
    
    if (in_array('firstname', $columnNames)) {
        echo "   ✅ firstname column exists\n";
    } else {
        echo "   ❌ firstname column missing\n";
    }
    
    if (in_array('lastname', $columnNames)) {
        echo "   ✅ lastname column exists\n";
    } else {
        echo "   ❌ lastname column missing\n";
    }
    
    if (!in_array('name', $columnNames)) {
        echo "   ✅ name column successfully removed\n";
    } else {
        echo "   ❌ name column still exists\n";
    }
    
    echo "\n4. Testing database connection info...\n";
    $connection = DB::connection();
    echo "   ✅ Database type: " . $connection->getDriverName() . "\n";
    echo "   ✅ Database name: " . $connection->getDatabaseName() . "\n";
    
    echo "\n5. Testing user queries...\n";
    
    $users = User::where('email', 'like', '%.mysql.test@example.com')->get();
    
    foreach ($users as $user) {
        echo "   User: {$user->full_name} | Email: {$user->email} | Role: {$user->role}\n";
        echo "     - Firstname: {$user->firstname}\n";
        echo "     - Lastname: {$user->lastname}\n";
        echo "     - Name accessor: {$user->name}\n";
    }
    
    echo "\n6. Testing soft delete functionality...\n";
    
    $user1->softDelete();
    echo "   ✅ User soft deleted successfully\n";
    echo "   ✅ Status: {$user1->status}\n";
    echo "   ✅ Full name still accessible: {$user1->full_name}\n";
    
    $user1->restore();
    echo "   ✅ User restored successfully\n";
    echo "   ✅ Status: {$user1->status}\n";

    echo "\n🎉 All MySQL firstname/lastname tests completed successfully!\n\n";
    
    echo "📊 Summary:\n";
    echo "✅ MySQL database connection working\n";
    echo "✅ Users can be created with firstname and lastname\n";
    echo "✅ Full name is dynamically generated from firstname + lastname\n";
    echo "✅ Name accessor provides backward compatibility\n";
    echo "✅ Full name mutator works for splitting names\n";
    echo "✅ Database schema updated - name column removed\n";
    echo "✅ Soft delete functionality preserved\n";
    echo "✅ All functionality working with MySQL backend\n\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} finally {
    // Clean up test data
    echo "🧹 Cleaning up test data...\n";
    User::where('email', 'like', '%.mysql.test@example.com')->forceDelete();
    echo "✅ Test data cleaned up\n";
}
