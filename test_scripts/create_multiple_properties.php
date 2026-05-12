<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\User;
use App\Models\Landlord;
use App\Models\Property;
use App\Models\PropertyCategory;
use Illuminate\Support\Facades\Hash;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🏢 Creating Multiple Properties for Landlord\n";
echo "============================================\n\n";

try {
    // Find the premium landlord
    $landlordUser = User::where('email', 'premium.landlord@test.com')->first();
    
    if (!$landlordUser) {
        echo "❌ Premium landlord not found. Please run create_new_landlord.php first.\n";
        exit(1);
    }
    
    $landlord = $landlordUser->landlord;
    if (!$landlord) {
        echo "❌ Landlord profile not found.\n";
        exit(1);
    }
    
    echo "✅ Found landlord: {$landlordUser->full_name}\n";
    echo "   Business: {$landlord->business_name}\n\n";
    
    // Get property categories
    $categories = PropertyCategory::all();
    if ($categories->count() < 3) {
        echo "❌ Not enough property categories found.\n";
        exit(1);
    }
    
    echo "📋 Available categories: " . $categories->pluck('name')->implode(', ') . "\n\n";
    
    // Properties to create
    $propertiesToCreate = [
        [
            'category' => 'Apartment',
            'title' => 'Luxury 2-Bedroom Apartment',
            'description' => 'Modern 2-bedroom apartment with stunning city views. Located in the heart of Accra with easy access to business districts.',
            'price' => 1800.00,
            'bedrooms' => 2,
            'bathrooms' => 1,
            'size_sqm' => 85.0,
            'furnished' => true,
            'address' => '45 Airport City, Airport Residential Area',
            'city' => 'Accra',
            'state' => 'Greater Accra',
            'amenities' => ['Air Conditioning', 'WiFi', 'Parking', 'Security', 'Gym']
        ],
        [
            'category' => 'House',
            'title' => 'Spacious 4-Bedroom Family House',
            'description' => 'Beautiful family home with large garden and parking for 2 cars. Perfect for families with children.',
            'price' => 3500.00,
            'bedrooms' => 4,
            'bathrooms' => 3,
            'size_sqm' => 200.0,
            'furnished' => false,
            'address' => '12 Cantonments Road, Cantonments',
            'city' => 'Accra',
            'state' => 'Greater Accra',
            'amenities' => ['Garden', 'Parking', 'Security', 'Generator', 'Water Tank']
        ],
        [
            'category' => 'Studio',
            'title' => 'Modern Studio Apartment',
            'description' => 'Compact and efficient studio apartment perfect for young professionals. Fully furnished with modern appliances.',
            'price' => 900.00,
            'bedrooms' => 1,
            'bathrooms' => 1,
            'size_sqm' => 45.0,
            'furnished' => true,
            'address' => '78 Oxford Street, Osu',
            'city' => 'Accra',
            'state' => 'Greater Accra',
            'amenities' => ['Air Conditioning', 'WiFi', 'Kitchenette', 'Security']
        ],
        [
            'category' => 'Townhouse',
            'title' => 'Executive 3-Bedroom Townhouse',
            'description' => 'Premium townhouse in gated community with swimming pool and 24/7 security. Modern finishes throughout.',
            'price' => 4200.00,
            'bedrooms' => 3,
            'bathrooms' => 2,
            'size_sqm' => 150.0,
            'furnished' => true,
            'address' => '5 Trassaco Valley, East Legon',
            'city' => 'Accra',
            'state' => 'Greater Accra',
            'amenities' => ['Swimming Pool', 'Gym', 'Security', 'Parking', 'Generator', 'Clubhouse']
        ],
        [
            'category' => 'Condo',
            'title' => 'Beachfront 2-Bedroom Condo',
            'description' => 'Stunning beachfront condominium with ocean views. Perfect for vacation rentals or permanent residence.',
            'price' => 2800.00,
            'bedrooms' => 2,
            'bathrooms' => 2,
            'size_sqm' => 110.0,
            'furnished' => true,
            'address' => '15 Labadi Beach Road, La',
            'city' => 'Accra',
            'state' => 'Greater Accra',
            'amenities' => ['Beach Access', 'Swimming Pool', 'WiFi', 'Air Conditioning', 'Balcony', 'Security']
        ]
    ];
    
    $createdProperties = [];
    
    foreach ($propertiesToCreate as $index => $propertyData) {
        $category = $categories->where('name', $propertyData['category'])->first();
        
        if (!$category) {
            echo "⚠️ Category '{$propertyData['category']}' not found, skipping property: {$propertyData['title']}\n";
            continue;
        }
        
        echo "🏠 Creating property " . ($index + 1) . ": {$propertyData['title']}\n";
        
        $property = Property::create([
            'landlord_id' => $landlord->id,
            'property_category_id' => $category->id,
            'title' => $propertyData['title'],
            'description' => $propertyData['description'],
            'price' => $propertyData['price'],
            'currency' => 'GHS',
            'bedrooms' => $propertyData['bedrooms'],
            'bathrooms' => $propertyData['bathrooms'],
            'size_sqm' => $propertyData['size_sqm'],
            'furnished' => $propertyData['furnished'],
            'address' => $propertyData['address'],
            'city' => $propertyData['city'],
            'state' => $propertyData['state'],
            'postal_code' => 'GA' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
            'country' => 'Ghana',
            'latitude' => 5.6037168 + (rand(-1000, 1000) / 100000),
            'longitude' => -0.1869644 + (rand(-1000, 1000) / 100000),
            'amenities' => json_encode($propertyData['amenities']),
            'images' => json_encode([
                "property_" . (count($createdProperties) + 1) . "_main.jpg",
                "property_" . (count($createdProperties) + 1) . "_interior.jpg",
                "property_" . (count($createdProperties) + 1) . "_exterior.jpg"
            ]),
            'availability_status' => 'available',
            'available_from' => now()->addDays(rand(1, 30)),
            'pets_allowed' => rand(0, 1),
            'smoking_allowed' => false,
            'lease_terms' => 'Standard lease terms apply. Minimum lease period as specified.',
            'security_deposit' => $propertyData['price'] * 2,
            'minimum_lease_months' => 12,
            'additional_fees' => json_encode([
                'Service Charge' => $propertyData['price'] * 0.1,
                'Utility Deposit' => 500.00
            ]),
            'is_active' => true,
            'published_at' => now(),
        ]);
        
        $createdProperties[] = $property;
        
        echo "   ✅ Created Property ID: {$property->id}\n";
        echo "   📍 Location: {$property->address}\n";
        echo "   💰 Price: {$property->currency} {$property->price}/month\n";
        echo "   🏠 {$property->bedrooms} bed, {$property->bathrooms} bath, {$property->size_sqm} sqm\n\n";
    }
    
    echo "===============================================\n";
    echo "🎉 PROPERTIES CREATED SUCCESSFULLY!\n";
    echo "===============================================\n\n";
    
    echo "📊 SUMMARY:\n";
    echo "   Landlord: {$landlordUser->full_name}\n";
    echo "   Business: {$landlord->business_name}\n";
    echo "   Total Properties: " . count($createdProperties) . "\n";
    
    $totalValue = array_sum(array_column($propertiesToCreate, 'price'));
    echo "   Total Portfolio Value: GHS {$totalValue}/month\n\n";
    
    echo "🏢 CREATED PROPERTIES:\n";
    foreach ($createdProperties as $property) {
        echo "   • ID {$property->id}: {$property->title} - GHS {$property->price}/month\n";
    }
    
    echo "\n🧪 API TESTING:\n";
    echo "   View all properties: GET /api/properties\n";
    echo "   View landlord properties: GET /api/landlord/properties\n";
    echo "   View specific property: GET /api/properties/{property_id}\n\n";
    
    echo "🔗 cURL Examples:\n";
    echo "View all properties:\n";
    echo "curl -X GET http://localhost:8080/api/properties \\\n";
    echo "  -H \"Accept: application/json\"\n\n";
    
    if (count($createdProperties) > 0) {
        $firstProperty = $createdProperties[0];
        echo "View specific property:\n";
        echo "curl -X GET http://localhost:8080/api/properties/{$firstProperty->id} \\\n";
        echo "  -H \"Accept: application/json\"\n\n";
    }

} catch (Exception $e) {
    echo "❌ Error creating properties: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "Script completed.\n";
