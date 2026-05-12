<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing UserPreference class loading...\n";

try {
    // Test if class exists
    if (class_exists('App\Models\UserPreference')) {
        echo "✅ UserPreference class exists\n";
        
        // Test reflection to see available methods
        $reflection = new ReflectionClass('App\Models\UserPreference');
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        
        echo "Available public methods:\n";
        foreach ($methods as $method) {
            if ($method->class === 'App\Models\UserPreference') {
                echo "  - " . $method->name . "\n";
            }
        }
        
        // Test if getDefaults method exists
        if ($reflection->hasMethod('getDefaults')) {
            echo "✅ getDefaults method exists\n";
            
            // Check if it's static
            $method = $reflection->getMethod('getDefaults');
            if ($method->isStatic()) {
                echo "✅ getDefaults is static\n";
                
                // Try to call it
                $result = call_user_func(['App\Models\UserPreference', 'getDefaults']);
                echo "✅ getDefaults called successfully\n";
                echo "Returned timezone: " . $result['timezone'] . "\n";
            } else {
                echo "❌ getDefaults is not static\n";
            }
        } else {
            echo "❌ getDefaults method does not exist\n";
        }
        
    } else {
        echo "❌ UserPreference class does not exist\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
