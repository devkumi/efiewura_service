<?php

namespace Database\Seeders;

use App\Models\PropertyCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PropertyCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Apartment',
                'description' => 'Multi-unit residential building with individual units',
                'is_active' => true,
            ],
            [
                'name' => 'House',
                'description' => 'Single-family residential property',
                'is_active' => true,
            ],
            [
                'name' => 'Room',
                'description' => 'Single room for rent in a shared space',
                'is_active' => true,
            ],
            [
                'name' => 'Studio',
                'description' => 'Small apartment with combined living and sleeping areas',
                'is_active' => true,
            ],
            [
                'name' => 'Commercial',
                'description' => 'Properties for business and commercial use',
                'is_active' => true,
            ],
            [
                'name' => 'Office Space',
                'description' => 'Professional workspace for businesses',
                'is_active' => true,
            ],
            [
                'name' => 'Shop',
                'description' => 'Retail space for businesses',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            PropertyCategory::create($category);
        }
    }
}
