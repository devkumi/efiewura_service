<?php

// Test Property Listing API
$baseUrl = 'http://127.0.0.1:8000/api';

echo "=== Testing Property Listing API ===\n";

// First, login as the landlord we created earlier
echo "1. Logging in as landlord...\n";
$loginResponse = apiCall('POST', $baseUrl . '/login', [
    'email' => 'landlord@test.com',
    'password' => 'password123'
]);

$landlordToken = $loginResponse['data']['token'] ?? null;
echo "Landlord Token: $landlordToken\n\n";

if (!$landlordToken) {
    echo "Failed to get landlord token. Exiting.\n";
    exit;
}

// Get property categories
echo "2. Getting property categories...\n";
$categoriesResponse = apiCall('GET', $baseUrl . '/property-categories');
echo "Categories Response: " . json_encode($categoriesResponse, JSON_PRETTY_PRINT) . "\n\n";

$categories = $categoriesResponse['data'] ?? [];
$apartmentCategoryId = null;

foreach ($categories as $category) {
    if (strtolower($category['name']) === 'apartment') {
        $apartmentCategoryId = $category['id'];
        break;
    }
}

if (!$apartmentCategoryId && !empty($categories)) {
    $apartmentCategoryId = $categories[0]['id']; // Use first category if apartment not found
}

echo "Using category ID: $apartmentCategoryId\n\n";

// Create a property listing
echo "3. Creating property listing...\n";
$propertyData = [
    'property_category_id' => $apartmentCategoryId,
    'title' => '2 Bedroom Apartment in East Legon',
    'description' => 'Beautiful 2-bedroom apartment with modern amenities in the heart of East Legon. Close to shopping centers, restaurants, and public transport. Perfect for young professionals or small families.',
    'price' => 1500.00,
    'currency' => 'GHS',
    'bedrooms' => 2,
    'bathrooms' => 2,
    'size_sqm' => 85.5,
    'furnished' => true,
    'address' => '123 East Legon Boulevard',
    'city' => 'Accra',
    'state' => 'Greater Accra',
    'country' => 'Ghana',
    'amenities' => [
        'Air Conditioning',
        'WiFi',
        'Kitchen',
        'Parking',
        'Security',
        'Swimming Pool'
    ],
    'availability_status' => 'available',
    'available_from' => '2025-07-01',
    'pets_allowed' => false,
    'smoking_allowed' => false,
    'lease_terms' => 'Minimum 12 months lease. First month rent and security deposit required upfront.',
    'security_deposit' => 1500.00,
    'minimum_lease_months' => 12,
    'additional_fees' => 'Utilities (electricity, water) not included. Service charge: GHS 200/month'
];

$propertyResponse = apiCall('POST', $baseUrl . '/properties', $propertyData, $landlordToken);
echo "Property Creation Response: " . json_encode($propertyResponse, JSON_PRETTY_PRINT) . "\n\n";

$propertyId = $propertyResponse['data']['id'] ?? null;

// Create another property
echo "4. Creating second property listing...\n";
$propertyData2 = [
    'property_category_id' => $apartmentCategoryId,
    'title' => 'Single Room in Tema',
    'description' => 'Affordable single room in a quiet neighborhood in Tema. Shared bathroom and kitchen facilities. Great for students or single professionals.',
    'price' => 600.00,
    'currency' => 'GHS',
    'bedrooms' => 1,
    'bathrooms' => 1,
    'furnished' => false,
    'address' => '456 Tema New Town',
    'city' => 'Tema',
    'state' => 'Greater Accra',
    'country' => 'Ghana',
    'amenities' => [
        'Shared Kitchen',
        'Security',
        'Water'
    ],
    'availability_status' => 'available',
    'pets_allowed' => true,
    'smoking_allowed' => false,
    'security_deposit' => 600.00,
    'minimum_lease_months' => 6
];

$property2Response = apiCall('POST', $baseUrl . '/properties', $propertyData2, $landlordToken);
echo "Second Property Creation Response: " . json_encode($property2Response, JSON_PRETTY_PRINT) . "\n\n";

// Get landlord's properties
echo "5. Getting landlord's properties...\n";
$myPropertiesResponse = apiCall('GET', $baseUrl . '/my-properties', null, $landlordToken);
echo "My Properties Response: " . json_encode($myPropertiesResponse, JSON_PRETTY_PRINT) . "\n\n";

// Browse all properties (public endpoint)
echo "6. Browsing all available properties (public)...\n";
$allPropertiesResponse = apiCall('GET', $baseUrl . '/properties');
echo "All Properties Response: " . json_encode($allPropertiesResponse, JSON_PRETTY_PRINT) . "\n\n";

// View specific property
if ($propertyId) {
    echo "7. Viewing specific property details...\n";
    $propertyDetailsResponse = apiCall('GET', $baseUrl . '/properties/' . $propertyId);
    echo "Property Details Response: " . json_encode($propertyDetailsResponse, JSON_PRETTY_PRINT) . "\n\n";
    
    // Update property
    echo "8. Updating property...\n";
    $updateData = [
        'price' => 1600.00,
        'description' => 'Beautiful 2-bedroom apartment with modern amenities in the heart of East Legon. Recently renovated with new appliances. Close to shopping centers, restaurants, and public transport.'
    ];
    
    $updateResponse = apiCall('PUT', $baseUrl . '/properties/' . $propertyId, $updateData, $landlordToken);
    echo "Property Update Response: " . json_encode($updateResponse, JSON_PRETTY_PRINT) . "\n\n";
    
    // Update property status
    echo "9. Updating property status to occupied...\n";
    $statusUpdateResponse = apiCall('PATCH', $baseUrl . '/properties/' . $propertyId . '/status', 
        ['status' => 'occupied'], $landlordToken);
    echo "Status Update Response: " . json_encode($statusUpdateResponse, JSON_PRETTY_PRINT) . "\n\n";
}

// Test filtering
echo "10. Testing property filtering...\n";
$filterResponse = apiCall('GET', $baseUrl . '/properties?city=Accra&min_price=1000&max_price=2000&bedrooms=2');
echo "Filtered Properties Response: " . json_encode($filterResponse, JSON_PRETTY_PRINT) . "\n\n";

echo "=== Property Listing API Testing Complete ===\n";

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
