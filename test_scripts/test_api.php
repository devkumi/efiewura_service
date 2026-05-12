<?php

// Simple API test for Efiewura
$baseUrl = 'http://127.0.0.1:8000/api';

echo "=== Testing Efiewura API with PHP ===\n";

// Test 1: Register a Landlord
echo "1. Testing Landlord Registration...\n";
$landlordData = [
    'name' => 'John Property Owner',
    'email' => 'landlord@test.com',
    'password' => 'password123',
    'password_confirmation' => 'password123',
    'role' => 'landlord',
    'business_name' => 'Johns Properties Ltd',
    'phone' => '+233244123456',
    'city' => 'Accra',
    'country' => 'Ghana'
];

$response = apiCall('POST', $baseUrl . '/register', $landlordData);
echo "Response: " . json_encode($response, JSON_PRETTY_PRINT) . "\n\n";

$landlordToken = $response['data']['token'] ?? null;

// Test 2: Register a Tenant
echo "2. Testing Tenant Registration...\n";
$tenantData = [
    'name' => 'Jane Tenant',
    'email' => 'tenant@test.com',
    'password' => 'password123',
    'password_confirmation' => 'password123',
    'role' => 'tenant',
    'phone' => '+233244654321',
    'date_of_birth' => '1990-01-15',
    'gender' => 'female',
    'occupation' => 'Software Engineer',
    'monthly_income' => 5000.00
];

$response = apiCall('POST', $baseUrl . '/register', $tenantData);
echo "Response: " . json_encode($response, JSON_PRETTY_PRINT) . "\n\n";

$tenantToken = $response['data']['token'] ?? null;

// Test 3: Login as Landlord
echo "3. Testing Landlord Login...\n";
$loginData = [
    'email' => 'landlord@test.com',
    'password' => 'password123'
];

$response = apiCall('POST', $baseUrl . '/login', $loginData);
echo "Response: " . json_encode($response, JSON_PRETTY_PRINT) . "\n\n";

// Test 4: Get Landlord Profile
if ($landlordToken) {
    echo "4. Testing Get Landlord Profile...\n";
    $response = apiCall('GET', $baseUrl . '/profile', null, $landlordToken);
    echo "Response: " . json_encode($response, JSON_PRETTY_PRINT) . "\n\n";
}

// Test 5: Get Tenant Profile
if ($tenantToken) {
    echo "5. Testing Get Tenant Profile...\n";
    $response = apiCall('GET', $baseUrl . '/profile', null, $tenantToken);
    echo "Response: " . json_encode($response, JSON_PRETTY_PRINT) . "\n\n";
}

echo "=== API Testing Complete ===\n";

function apiCall($method, $url, $data = null, $token = null) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    $headers = ['Content-Type: application/json'];
    
    if ($token) {
        $headers[] = "Authorization: Bearer $token";
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    curl_close($ch);
    
    $decodedResponse = json_decode($response, true);
    
    if ($httpCode >= 400) {
        echo "HTTP Error $httpCode: $response\n";
    }
    
    return $decodedResponse;
}
