<?php

/**
 * Quick test of admin settings endpoints without authentication
 * This will help us verify the endpoints are working
 */

echo "=== Quick Admin Settings Test (No Auth) ===\n\n";

$baseUrl = 'http://127.0.0.1:8000/api';

// Test if the endpoint exists and returns something
echo "Testing endpoint accessibility:\n";

$endpoints = [
    '/admin/settings' => 'Main settings endpoint',
    '/admin/settings/general' => 'General settings',
    '/admin/settings/security' => 'Security settings'
];

foreach ($endpoints as $endpoint => $description) {
    echo "\nTesting: $description ($endpoint)\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $endpoint);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "  HTTP Code: $httpCode\n";
    
    if ($httpCode === 401) {
        echo "  ✅ Endpoint exists but requires authentication (expected)\n";
    } elseif ($httpCode === 404) {
        echo "  ❌ Endpoint not found - check routes\n";
    } elseif ($httpCode === 200) {
        echo "  ⚠️  Endpoint accessible without auth (unexpected)\n";
    } else {
        echo "  ❓ Unexpected response: " . substr($response, 0, 100) . "...\n";
    }
}

echo "\n=== Endpoint Test Complete ===\n";
echo "💡 To fully test functionality, use the 2FA test script with a current code\n";
