<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyCategory;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $query = Property::with(['category', 'landlord'])
            ->available()
            ->orderByDesc('views_count');

        if ($city = $request->input('city')) {
            $query->where('city', 'LIKE', "%{$city}%");
        }
        if ($categoryId = $request->input('category')) {
            $query->where('property_category_id', $categoryId);
        }
        if ($max = $request->input('max_price')) {
            $query->where('price', '<=', $max);
        }

        $properties         = $query->paginate(9)->withQueryString();
        $featuredProperties = Property::with(['category'])->available()->orderByDesc('views_count')->take(3)->get();
        $categories         = PropertyCategory::active()->get();

        $stats = [
            'properties' => Property::active()->count(),
            'cities'     => Property::active()->distinct('city')->count('city'),
            'available'  => Property::available()->count(),
        ];

        return view('home', compact('properties', 'featuredProperties', 'categories', 'stats'));
    }
}
