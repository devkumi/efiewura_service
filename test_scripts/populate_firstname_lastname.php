<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔄 Populating firstname and lastname from existing name data\n";
echo "========================================================\n\n";

try {
    // Get all users where firstname or lastname might be empty
    $users = DB::table('users')->get();
    $updatedCount = 0;
    
    foreach ($users as $user) {
        if ($user->name && (empty($user->firstname) || empty($user->lastname))) {
            // Split the name into firstname and lastname
            $nameParts = explode(' ', trim($user->name), 2);
            $firstname = $nameParts[0] ?? '';
            $lastname = $nameParts[1] ?? '';
            
            // If there's no lastname, use the firstname as lastname to satisfy NOT NULL constraint
            if (empty($lastname)) {
                $lastname = $firstname;
            }
            
            // If firstname is still empty, use a default
            if (empty($firstname)) {
                $firstname = 'User';
                $lastname = 'User';
            }
            
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'updated_at' => now()
                ]);
            
            $updatedCount++;
            echo "Updated user ID {$user->id}: '{$user->name}' -> '{$firstname}' + '{$lastname}'\n";
        }
    }
    
    echo "\n✅ Successfully updated {$updatedCount} users\n";
    
    // Verify the update
    echo "\n📊 Verification:\n";
    $emptyFirstname = DB::table('users')->whereNull('firstname')->orWhere('firstname', '')->count();
    $emptyLastname = DB::table('users')->whereNull('lastname')->orWhere('lastname', '')->count();
    
    echo "Users with empty firstname: {$emptyFirstname}\n";
    echo "Users with empty lastname: {$emptyLastname}\n";
    
    if ($emptyFirstname == 0 && $emptyLastname == 0) {
        echo "✅ All users now have firstname and lastname values\n";
    } else {
        echo "⚠️ Some users still have empty firstname/lastname fields\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
