<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\User;
use App\Models\Landlord;
use App\Models\Tenant;
use App\Models\Property;
use App\Models\PropertyCategory;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "📊 EFIEWURA TEST DATA OVERVIEW\n";
echo "==============================\n\n";

try {
    // Count totals
    $totalUsers = User::count();
    $totalLandlords = Landlord::count();
    $totalTenants = Tenant::count();
    $totalProperties = Property::count();
    $totalCategories = PropertyCategory::count();
    
    echo "📈 SYSTEM OVERVIEW:\n";
    echo "   Total Users: {$totalUsers}\n";
    echo "   Total Landlords: {$totalLandlords}\n";
    echo "   Total Tenants: {$totalTenants}\n";
    echo "   Total Properties: {$totalProperties}\n";
    echo "   Property Categories: {$totalCategories}\n\n";
    
    // Show property categories
    echo "🏷️ PROPERTY CATEGORIES:\n";
    $categories = PropertyCategory::all();
    foreach ($categories as $category) {
        $propertyCount = Property::where('property_category_id', $category->id)->count();
        echo "   • {$category->name} ({$propertyCount} properties)\n";
    }
    echo "\n";
    
    // Show landlords
    echo "🏢 LANDLORDS:\n";
    $landlords = User::whereHas('landlord')->with('landlord')->get();
    foreach ($landlords as $user) {
        $landlord = $user->landlord;
        $propertyCount = Property::where('landlord_id', $landlord->id)->count();
        $totalValue = Property::where('landlord_id', $landlord->id)->sum('price');
        
        echo "   • {$user->full_name} ({$user->email})\n";
        echo "     Business: {$landlord->business_name}\n";
        echo "     Properties: {$propertyCount} (GHS {$totalValue}/month)\n";
        echo "     Phone: {$landlord->phone_number}\n\n";
    }
    
    // Show tenants
    echo "👤 TENANTS:\n";
    $tenants = User::whereHas('tenant')->with('tenant')->get();
    foreach ($tenants as $user) {
        $tenant = $user->tenant;
        echo "   • {$user->full_name} ({$user->email})\n";
        echo "     Phone: {$tenant->phone_number}\n";
        echo "     Employment: {$tenant->employment_status}\n";
        if ($tenant->employer_name) {
            echo "     Employer: {$tenant->employer_name}\n";
        }
        echo "\n";
    }
    
    // Show properties
    echo "🏠 PROPERTIES:\n";
    $properties = Property::with(['landlord.user', 'category'])->get();
    foreach ($properties as $property) {
        echo "   • ID {$property->id}: {$property->title}\n";
        echo "     Category: {$property->category->name}\n";
        echo "     Price: {$property->currency} {$property->price}/month\n";
        echo "     Bedrooms: {$property->bedrooms} | Bathrooms: {$property->bathrooms} | Size: {$property->size_sqm} sqm\n";
        echo "     Location: {$property->address}, {$property->city}\n";
        echo "     Landlord: {$property->landlord->user->full_name}\n";
        echo "     Status: {$property->availability_status}\n";
        
        $amenities = json_decode($property->amenities, true);
        if ($amenities && count($amenities) > 0) {
            echo "     Amenities: " . implode(', ', $amenities) . "\n";
        }
        echo "\n";
    }
    
    // API endpoints summary
    echo "🔗 API ENDPOINTS FOR TESTING:\n";
    echo "   Authentication:\n";
    echo "     POST /api/register - Register new user\n";
    echo "     POST /api/login - Login user\n";
    echo "     POST /api/logout - Logout user\n\n";
    
    echo "   Properties:\n";
    echo "     GET /api/properties - List all properties\n";
    echo "     GET /api/properties/{id} - Get specific property\n";
    echo "     POST /api/properties - Create property (landlord only)\n";
    echo "     PUT /api/properties/{id} - Update property (landlord only)\n";
    echo "     DELETE /api/properties/{id} - Delete property (landlord only)\n\n";
    
    echo "   Landlord:\n";
    echo "     GET /api/landlord/properties - Get landlord's properties\n";
    echo "     GET /api/landlord/profile - Get landlord profile\n";
    echo "     PUT /api/landlord/profile - Update landlord profile\n\n";
    
    echo "   Tenant:\n";
    echo "     GET /api/tenant/profile - Get tenant profile\n";
    echo "     PUT /api/tenant/profile - Update tenant profile\n\n";
    
    // Test credentials
    echo "🔑 TEST CREDENTIALS:\n";
    echo "   Landlord:\n";
    echo "     Email: premium.landlord@test.com\n";
    echo "     Password: SecurePass123!\n\n";
    
    echo "   Tenant:\n";
    echo "     Email: john.tenant@test.com\n";
    echo "     Password: SecurePass123!\n\n";
    
    // Sample API calls
    echo "🧪 SAMPLE API CALLS:\n";
    echo "   Login as landlord:\n";
    echo "   curl -X POST http://localhost:8080/api/login \\\n";
    echo "     -H \"Content-Type: application/json\" \\\n";
    echo "     -H \"Accept: application/json\" \\\n";
    echo "     -d '{\"email\":\"premium.landlord@test.com\",\"password\":\"SecurePass123!\"}'\n\n";
    
    echo "   Get all properties:\n";
    echo "   curl -X GET http://localhost:8080/api/properties \\\n";
    echo "     -H \"Accept: application/json\"\n\n";
    
    if ($properties->count() > 0) {
        $firstProperty = $properties->first();
        echo "   Get specific property (ID {$firstProperty->id}):\n";
        echo "   curl -X GET http://localhost:8080/api/properties/{$firstProperty->id} \\\n";
        echo "     -H \"Accept: application/json\"\n\n";
    }
    
    echo "   Get landlord's properties (with token):\n";
    echo "   curl -X GET http://localhost:8080/api/landlord/properties \\\n";
    echo "     -H \"Accept: application/json\" \\\n";
    echo "     -H \"Authorization: Bearer YOUR_TOKEN_HERE\"\n\n";
    
    echo "💡 NEXT STEPS:\n";
    echo "   1. Start the Laravel development server: php artisan serve --port=8080\n";
    echo "   2. Test the API endpoints using the provided cURL commands\n";
    echo "   3. Use the test credentials to login and get API tokens\n";
    echo "   4. Create additional properties, bookings, and test scenarios\n\n";
    
    echo "✅ Test environment is ready for API development and testing!\n";

} catch (Exception $e) {
    echo "❌ Error generating overview: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nScript completed.\n";
