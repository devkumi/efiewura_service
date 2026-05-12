<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use App\Models\PropertyCategory;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PropertyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show', 'categories']);
        $this->middleware('role:landlord')->except(['index', 'show', 'categories']);
    }

    /**
     * Display a listing of properties (public endpoint for browsing)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Property::query()->with(['category', 'landlord.user']);

        // Public listings show only available and active properties
        if (!$request->user() || $request->user()->role !== 'landlord') {
            $query->available();
        }

        // Filter by landlord (if user is a landlord viewing their own properties)
        if ($request->user() && $request->user()->role === 'landlord') {
            $landlordId = $request->get('my_properties') ? $request->user()->landlord->id : null;
            if ($landlordId) {
                $query->where('landlord_id', $landlordId);
            }
        }

        // Apply filters
        if ($request->filled('city')) {
            $query->inCity($request->city);
        }

        if ($request->filled('category_id')) {
            $query->where('property_category_id', $request->category_id);
        }

        if ($request->filled('min_price') && $request->filled('max_price')) {
            $query->priceBetween($request->min_price, $request->max_price);
        }

        if ($request->filled('bedrooms')) {
            $query->withBedrooms($request->bedrooms);
        }

        if ($request->filled('furnished')) {
            $query->where('furnished', $request->boolean('furnished'));
        }

        if ($request->filled('pets_allowed')) {
            $query->where('pets_allowed', $request->boolean('pets_allowed'));
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if (in_array($sortBy, ['price', 'created_at', 'views_count', 'bedrooms'])) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        }

        $properties = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => PropertyResource::collection($properties),
            'meta' => [
                'total' => $properties->total(),
                'per_page' => $properties->perPage(),
                'current_page' => $properties->currentPage(),
                'last_page' => $properties->lastPage(),
            ]
        ]);
    }

    /**
     * Store a newly created property
     */
    public function store(StorePropertyRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $landlord = $request->user()->landlord;
            
            if (!$landlord) {
                return response()->json([
                    'success' => false,
                    'message' => 'Landlord profile not found. Please complete your profile first.'
                ], 400);
            }

            $propertyData = $request->validated();
            $propertyData['landlord_id'] = $landlord->id;
            $propertyData['published_at'] = Carbon::now();

            $property = Property::create($propertyData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Property listed successfully',
                'data' => new PropertyResource($property->load(['category', 'landlord.user']))
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create property listing',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified property
     */
    public function show(Property $property): JsonResponse
    {
        $viewerUserId = Auth::id();
        
        // Increment view count (but not for property owner)
        if (!Auth::check() || Auth::user()->landlord?->id !== $property->landlord_id) {
            $property->incrementViews();
            
            // Refresh the model to get updated views count
            $property->refresh();
            
            // Create milestone notification if applicable
            Notification::createPropertyViewed($property, $viewerUserId);
        }

        $property->load(['category', 'landlord.user']);

        return response()->json([
            'success' => true,
            'data' => new PropertyResource($property)
        ]);
    }

    /**
     * Update the specified property
     */
    public function update(UpdatePropertyRequest $request, Property $property): JsonResponse
    {
        try {
            $property->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Property updated successfully',
                'data' => new PropertyResource($property->load(['category', 'landlord.user']))
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update property',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified property
     */
    public function destroy(Property $property): JsonResponse
    {
        // Check if the landlord owns this property
        if (Auth::user()->landlord->id !== $property->landlord_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this property'
            ], 403);
        }

        try {
            $property->delete();

            return response()->json([
                'success' => true,
                'message' => 'Property deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete property',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get landlord's own properties
     */
    public function myProperties(Request $request): JsonResponse
    {
        $landlord = $request->user()->landlord;
        
        if (!$landlord) {
            return response()->json([
                'success' => false,
                'message' => 'Landlord profile not found'
            ], 400);
        }

        $query = $landlord->properties()->with(['category']);

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('availability_status', $request->status);
        }

        $properties = $query->orderBy('created_at', 'desc')
                           ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => PropertyResource::collection($properties),
            'meta' => [
                'total' => $properties->total(),
                'per_page' => $properties->perPage(),
                'current_page' => $properties->currentPage(),
                'last_page' => $properties->lastPage(),
            ],
            'summary' => [
                'total_properties' => $landlord->total_properties,
                'available_properties' => $landlord->available_properties_count,
            ]
        ]);
    }

    /**
     * Get property categories
     */
    public function categories(): JsonResponse
    {
        $categories = PropertyCategory::active()
                                     ->orderBy('name')
                                     ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Update property status
     */
    public function updateStatus(Request $request, Property $property): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:available,occupied,maintenance,reserved'
        ]);

        // Check if the landlord owns this property
        if (Auth::user()->landlord->id !== $property->landlord_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this property'
            ], 403);
        }

        try {
            $property->update(['availability_status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Property status updated successfully',
                'data' => [
                    'property_id' => $property->id,
                    'new_status' => $property->availability_status
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update property status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
