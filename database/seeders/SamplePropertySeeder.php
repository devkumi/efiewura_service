<?php

namespace Database\Seeders;

use App\Models\Landlord;
use App\Models\Property;
use App\Models\PropertyCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SamplePropertySeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'landlord@efiewura.com'],
            [
                'firstname' => 'Kwame',
                'lastname'  => 'Asante',
                'email'     => 'landlord@efiewura.com',
                'password'  => Hash::make('password'),
                'role'      => 'landlord',
                'status'    => 'active',
            ]
        );

        $landlord = Landlord::firstOrCreate(
            ['user_id' => $user->id],
            [
                'business_name' => 'Asante Properties GH',
                'phone'         => '+233244123456',
                'city'          => 'Accra',
                'country'       => 'Ghana',
                'status'        => 'active',
                'verified'      => true,
                'verified_at'   => now(),
            ]
        );

        $cat = PropertyCategory::pluck('id', 'name');

        $properties = [
            [
                'title'       => 'Executive 3-Bedroom Apartment — East Legon',
                'description' => 'Fully furnished luxury apartment in the heart of East Legon. Modern kitchen, en-suite bedrooms, 24-hour security, and a stunning city view from the rooftop terrace.',
                'price' => 3500, 'currency' => 'GHS', 'bedrooms' => 3, 'bathrooms' => 2, 'size_sqm' => 145,
                'furnished' => true, 'city' => 'East Legon, Accra', 'address' => 'Ring Road East, East Legon',
                'country' => 'Ghana', 'property_category_id' => $cat['Apartment'] ?? 1,
                'amenities' => ['WiFi', 'Air Conditioning', 'Security', 'Parking', 'Gym', 'Swimming Pool'],
                'images' => ['https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?w=800&auto=format&fit=crop'],
                'pets_allowed' => false, 'security_deposit' => 7000, 'minimum_lease_months' => 6,
                'views_count' => 284, 'published_at' => now()->subDays(15),
            ],
            [
                'title'       => 'Modern 4-Bedroom Villa — Cantonments',
                'description' => 'Stunning contemporary villa in the prestigious Cantonments area. Large garden, private pool, and staff quarters. Perfect for executives and diplomats.',
                'price' => 6500, 'currency' => 'GHS', 'bedrooms' => 4, 'bathrooms' => 3, 'size_sqm' => 280,
                'furnished' => true, 'city' => 'Cantonments, Accra', 'address' => 'Cantonments Road, Accra',
                'country' => 'Ghana', 'property_category_id' => $cat['House'] ?? 2,
                'amenities' => ['Swimming Pool', 'Garden', 'Security', 'Generator', 'Parking', 'Staff Quarters'],
                'images' => ['https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800&auto=format&fit=crop'],
                'pets_allowed' => true, 'security_deposit' => 13000, 'minimum_lease_months' => 12,
                'views_count' => 412, 'published_at' => now()->subDays(8),
            ],
            [
                'title'       => 'Cosy Studio Apartment — Osu',
                'description' => 'Stylish studio in vibrant Osu. Steps from restaurants, nightlife, and the beach. Ideal for young professionals.',
                'price' => 2200, 'currency' => 'GHS', 'bedrooms' => 1, 'bathrooms' => 1, 'size_sqm' => 55,
                'furnished' => true, 'city' => 'Osu, Accra', 'address' => 'Oxford Street, Osu',
                'country' => 'Ghana', 'property_category_id' => $cat['Studio'] ?? 4,
                'amenities' => ['WiFi', 'Air Conditioning', 'Security', 'Water Heater'],
                'images' => ['https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800&auto=format&fit=crop'],
                'pets_allowed' => false, 'security_deposit' => 4400, 'minimum_lease_months' => 3,
                'views_count' => 198, 'published_at' => now()->subDays(20),
            ],
            [
                'title'       => '2-Bedroom Apartment — Airport Residential',
                'description' => 'Well-appointed apartment in the sought-after Airport Residential Area. Close to international schools, embassies, and the airport.',
                'price' => 2800, 'currency' => 'GHS', 'bedrooms' => 2, 'bathrooms' => 2, 'size_sqm' => 110,
                'furnished' => false, 'city' => 'Airport Residential, Accra', 'address' => 'Liberation Road, Airport Area',
                'country' => 'Ghana', 'property_category_id' => $cat['Apartment'] ?? 1,
                'amenities' => ['Air Conditioning', 'Security', 'Parking', 'Generator'],
                'images' => ['https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800&auto=format&fit=crop'],
                'pets_allowed' => false, 'security_deposit' => 5600, 'minimum_lease_months' => 6,
                'views_count' => 156, 'published_at' => now()->subDays(5),
            ],
            [
                'title'       => 'Spacious Family Home — Labone',
                'description' => 'Beautiful family house in quiet, tree-lined Labone. Three en-suite bedrooms, large veranda, and a landscaped garden.',
                'price' => 4800, 'currency' => 'GHS', 'bedrooms' => 3, 'bathrooms' => 3, 'size_sqm' => 220,
                'furnished' => false, 'city' => 'Labone, Accra', 'address' => 'Labone Street, Accra',
                'country' => 'Ghana', 'property_category_id' => $cat['House'] ?? 2,
                'amenities' => ['Garden', 'Security', 'Parking', 'Generator', 'Water Heater'],
                'images' => ['https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=800&auto=format&fit=crop'],
                'pets_allowed' => true, 'security_deposit' => 9600, 'minimum_lease_months' => 12,
                'views_count' => 231, 'published_at' => now()->subDays(12),
            ],
            [
                'title'       => 'Premium Office Space — Ridge',
                'description' => "Class-A office in Accra's diplomatic and business district. Conference rooms, reception, high-speed internet, and panoramic views.",
                'price' => 8500, 'currency' => 'GHS', 'bedrooms' => 0, 'bathrooms' => 2, 'size_sqm' => 180,
                'furnished' => true, 'city' => 'Ridge, Accra', 'address' => 'Ridge Road, Accra',
                'country' => 'Ghana', 'property_category_id' => $cat['Office Space'] ?? 6,
                'amenities' => ['WiFi', 'Air Conditioning', 'Security', 'Parking', 'Conference Room', 'Reception'],
                'images' => ['https://images.unsplash.com/photo-1497366216548-37526070297c?w=800&auto=format&fit=crop'],
                'pets_allowed' => false, 'security_deposit' => 17000, 'minimum_lease_months' => 12,
                'views_count' => 88, 'published_at' => now()->subDays(3),
            ],
            [
                'title'       => 'Affordable 1-Bedroom — Tema Community 6',
                'description' => 'Clean, well-maintained apartment near Tema Harbour and major supermarkets. Ideal for port workers and professionals.',
                'price' => 1400, 'currency' => 'GHS', 'bedrooms' => 1, 'bathrooms' => 1, 'size_sqm' => 60,
                'furnished' => false, 'city' => 'Tema', 'address' => 'Community 6, Tema',
                'country' => 'Ghana', 'property_category_id' => $cat['Apartment'] ?? 1,
                'amenities' => ['Security', 'Water Supply', 'Parking'],
                'images' => ['https://images.unsplash.com/photo-1574362848149-11496d93a7c7?w=800&auto=format&fit=crop'],
                'pets_allowed' => false, 'security_deposit' => 2800, 'minimum_lease_months' => 3,
                'views_count' => 102, 'published_at' => now()->subDays(18),
            ],
            [
                'title'       => 'Contemporary 3-Bedroom Townhouse — Dzorwulu',
                'description' => 'Elegant gated-community townhouse near Accra Mall. CCTV security, modern amenities, and close to major shopping centres.',
                'price' => 4200, 'currency' => 'GHS', 'bedrooms' => 3, 'bathrooms' => 2, 'size_sqm' => 190,
                'furnished' => false, 'city' => 'Dzorwulu, Accra', 'address' => 'Independence Avenue, Dzorwulu',
                'country' => 'Ghana', 'property_category_id' => $cat['House'] ?? 2,
                'amenities' => ['Security', 'CCTV', 'Generator', 'Parking', 'Air Conditioning'],
                'images' => ['https://images.unsplash.com/photo-1583608205776-bfd35f0d9f83?w=800&auto=format&fit=crop'],
                'pets_allowed' => true, 'security_deposit' => 8400, 'minimum_lease_months' => 6,
                'views_count' => 177, 'published_at' => now()->subDays(10),
            ],
        ];

        foreach ($properties as $data) {
            Property::firstOrCreate(
                ['title' => $data['title'], 'landlord_id' => $landlord->id],
                array_merge($data, [
                    'landlord_id'         => $landlord->id,
                    'availability_status' => 'available',
                    'is_active'           => true,
                    'status'              => 'active',
                ])
            );
        }
    }
}
