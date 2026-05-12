<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Exception;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Browse available properties for booking
     */
    public function browseAvailable(Request $request): JsonResponse
    {
        $query = Property::query()
            ->with(['category', 'landlord.user'])
            ->where('availability_status', 'available')
            ->where('is_active', true);

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

        if ($request->filled('available_from')) {
            $query->where('available_from', '<=', $request->available_from);
        }

        // Exclude properties with active bookings
        $query->whereDoesntHave('bookings', function ($q) {
            $q->whereIn('status', ['confirmed', 'pending']);
        });

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if (in_array($sortBy, ['price', 'created_at', 'views_count', 'bedrooms'])) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        }

        $properties = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Available properties for booking',
            'data' => $properties->items(),
            'meta' => [
                'total' => $properties->total(),
                'per_page' => $properties->perPage(),
                'current_page' => $properties->currentPage(),
                'last_page' => $properties->lastPage(),
            ]
        ]);
    }

    /**
     * Create a booking request
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
            'move_in_date' => 'required|date|after:today',
            'lease_duration_months' => 'required|integer|min:1|max:60',
            'tenant_name' => 'required|string|max:255',
            'tenant_phone' => 'required|string|max:20',
            'tenant_email' => 'required|email|max:255',
            'tenant_message' => 'nullable|string|max:1000',
            'occupation' => 'nullable|string|max:255',
            'employer' => 'nullable|string|max:255',
            'monthly_income' => 'nullable|numeric|min:0',
            'emergency_contact' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $property = Property::with('landlord')->findOrFail($request->property_id);

            // Check if property is available
            if ($property->availability_status !== 'available') {
                return response()->json([
                    'success' => false,
                    'message' => 'Property is not available for booking'
                ], 400);
            }

            // Check for existing active bookings
            $existingBooking = Booking::where('property_id', $property->id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->exists();

            if ($existingBooking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property already has an active booking'
                ], 400);
            }

            // Calculate move-out date
            $moveInDate = Carbon::parse($request->move_in_date);
            $moveOutDate = $moveInDate->copy()->addMonths($request->lease_duration_months);

            // Calculate total amount (first month + security deposit)
            $monthlyRent = $property->price;
            $securityDeposit = $property->security_deposit ?? $monthlyRent;
            $totalAmount = $monthlyRent + $securityDeposit;

            $booking = Booking::create([
                'property_id' => $property->id,
                'user_id' => Auth::id(),
                'landlord_id' => $property->landlord_id,
                'status' => 'pending',
                'move_in_date' => $moveInDate,
                'move_out_date' => $moveOutDate,
                'lease_duration_months' => $request->lease_duration_months,
                'monthly_rent' => $monthlyRent,
                'security_deposit' => $securityDeposit,
                'total_amount' => $totalAmount,
                'currency' => $property->currency,
                'tenant_name' => $request->tenant_name,
                'tenant_phone' => $request->tenant_phone,
                'tenant_email' => $request->tenant_email,
                'tenant_message' => $request->tenant_message,
                'occupation' => $request->occupation,
                'employer' => $request->employer,
                'monthly_income' => $request->monthly_income,
                'emergency_contact' => $request->emergency_contact,
            ]);
            
            // Create notification for landlord about new booking request
            Notification::createNewBookingRequest($booking);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Booking request submitted successfully',
                'data' => $booking->load(['property.category', 'landlord.user'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create booking request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a test booking with past dates (for time-based notification testing)
     * This endpoint bypasses the normal date validation
     */
    public function storeTestBooking(Request $request): JsonResponse
    {
        // Only allow in non-production environments
        if (app()->environment('production')) {
            return response()->json([
                'success' => false,
                'message' => 'Test endpoint not available in production'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
            'move_in_date' => 'required|date', // Remove 'after:today' constraint
            'lease_duration_months' => 'required|integer|min:1|max:60',
            'tenant_name' => 'required|string|max:255',
            'tenant_phone' => 'required|string|max:20',
            'tenant_email' => 'required|email|max:255',
            'tenant_message' => 'nullable|string|max:1000',
            'occupation' => 'nullable|string|max:255',
            'employer' => 'nullable|string|max:255',
            'monthly_income' => 'nullable|numeric|min:0',
            'emergency_contact' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $property = Property::with('landlord')->findOrFail($request->property_id);
            
            // Check if property is available (but don't restrict past dates)
            if ($property->availability_status !== 'available') {
                return response()->json([
                    'success' => false,
                    'message' => 'Property is not available for booking'
                ], 400);
            }

            // Calculate move-out date
            $moveInDate = Carbon::parse($request->move_in_date);
            $moveOutDate = $moveInDate->copy()->addMonths($request->lease_duration_months);

            // Calculate amounts
            $monthlyRent = $property->price;
            $securityDeposit = $monthlyRent; // Default: 1 month security deposit
            $totalAmount = $monthlyRent + $securityDeposit;

            // Create booking
            $booking = Booking::create([
                'property_id' => $property->id,
                'user_id' => Auth::id(),
                'landlord_id' => $property->landlord->id,
                'status' => 'pending',
                'move_in_date' => $moveInDate,
                'move_out_date' => $moveOutDate,
                'lease_duration_months' => $request->lease_duration_months,
                'monthly_rent' => $monthlyRent,
                'security_deposit' => $securityDeposit,
                'total_amount' => $totalAmount,
                'currency' => $property->currency,
                'tenant_name' => $request->tenant_name,
                'tenant_phone' => $request->tenant_phone,
                'tenant_email' => $request->tenant_email,
                'tenant_message' => $request->tenant_message,
                'occupation' => $request->occupation,
                'employer' => $request->employer,
                'monthly_income' => $request->monthly_income,
                'emergency_contact' => $request->emergency_contact,
            ]);

            Notification::createNewBookingRequest($booking);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Test booking created successfully',
                'data' => [
                    'id' => $booking->id,
                    'property_id' => $booking->property_id,
                    'status' => $booking->status,
                    'move_in_date' => $booking->move_in_date->format('Y-m-d'),
                    'move_out_date' => $booking->move_out_date->format('Y-m-d'),
                    'lease_duration_months' => $booking->lease_duration_months,
                    'monthly_rent' => $booking->monthly_rent,
                    'security_deposit' => $booking->security_deposit,
                    'total_amount' => $booking->total_amount,
                    'currency' => $booking->currency,
                    'tenant_name' => $booking->tenant_name,
                    'tenant_email' => $booking->tenant_email,
                    'created_at' => $booking->created_at->format('Y-m-d H:i:s'),
                    'note' => 'This is a test booking that allows past dates for notification testing'
                ]
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create test booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's bookings
     */
    public function myBookings(Request $request): JsonResponse
    {
        $query = Auth::user()->bookings()
            ->with(['property.category', 'landlord.user']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->orderBy('created_at', 'desc')
                         ->paginate($request->get('per_page', 15));

        $counts = Auth::user()->bookings()
            ->selectRaw('COUNT(*) as total, SUM(status = "pending") as pending, SUM(status = "confirmed") as confirmed')
            ->first();

        return response()->json([
            'success' => true,
            'data' => $bookings->items(),
            'meta' => [
                'total' => $bookings->total(),
                'per_page' => $bookings->perPage(),
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
            ],
            'summary' => [
                'total_bookings' => (int) ($counts->total ?? 0),
                'pending_bookings' => (int) ($counts->pending ?? 0),
                'confirmed_bookings' => (int) ($counts->confirmed ?? 0),
            ]
        ]);
    }

    /**
     * Get specific booking details
     */
    public function show(Booking $booking): JsonResponse
    {
        // Check if user owns this booking
        if ($booking->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view this booking'
            ], 403);
        }

        $booking->load(['property.category', 'landlord.user']);

        return response()->json([
            'success' => true,
            'data' => $booking
        ]);
    }

    /**
     * Cancel a booking
     */
    public function cancel(Booking $booking): JsonResponse
    {
        // Check if user owns this booking
        if ($booking->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to cancel this booking'
            ], 403);
        }

        // Check if booking can be cancelled
        if (!in_array($booking->status, ['pending'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending bookings can be cancelled'
            ], 400);
        }

        try {
            $booking->cancel();

            return response()->json([
                'success' => true,
                'message' => 'Booking cancelled successfully',
                'data' => [
                    'booking_id' => $booking->id,
                    'new_status' => $booking->status
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get landlord's booking requests
     */
    public function landlordBookings(Request $request): JsonResponse
    {
        $landlord = Auth::user()->landlord;
        
        if (!$landlord) {
            return response()->json([
                'success' => false,
                'message' => 'Landlord profile not found'
            ], 400);
        }

        $query = $landlord->bookings()
            ->with(['property.category', 'user']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by property
        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        $bookings = $query->orderBy('created_at', 'desc')
                         ->paginate($request->get('per_page', 15));

        $counts = $landlord->bookings()
            ->selectRaw('COUNT(*) as total, SUM(status = "pending") as pending, SUM(status = "confirmed") as confirmed')
            ->first();

        return response()->json([
            'success' => true,
            'data' => $bookings->items(),
            'meta' => [
                'total' => $bookings->total(),
                'per_page' => $bookings->perPage(),
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
            ],
            'summary' => [
                'total_bookings' => (int) ($counts->total ?? 0),
                'pending_bookings' => (int) ($counts->pending ?? 0),
                'confirmed_bookings' => (int) ($counts->confirmed ?? 0),
            ]
        ]);
    }

    /**
     * Landlord confirms a booking
     */
    public function confirm(Booking $booking): JsonResponse
    {
        $landlord = Auth::user()->landlord;
        
        if (!$landlord || $booking->landlord_id !== $landlord->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to confirm this booking'
            ], 403);
        }

        if ($booking->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending bookings can be confirmed'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $booking->confirm();
            
            // Update property status to occupied
            $booking->property->update(['availability_status' => 'occupied']);
            
            // Create notification for tenant about booking confirmation
            Notification::createBookingConfirmed($booking);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Booking confirmed successfully',
                'data' => [
                    'booking_id' => $booking->id,
                    'new_status' => $booking->status
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Landlord rejects a booking
     */
    public function reject(Booking $booking): JsonResponse
    {
        $landlord = Auth::user()->landlord;
        
        if (!$landlord || $booking->landlord_id !== $landlord->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to reject this booking'
            ], 403);
        }

        if ($booking->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending bookings can be rejected'
            ], 400);
        }

        try {
            $booking->reject();
            
            // Create notification for tenant about booking rejection
            Notification::createBookingRejected($booking);

            return response()->json([
                'success' => true,
                'message' => 'Booking rejected successfully',
                'data' => [
                    'booking_id' => $booking->id,
                    'new_status' => $booking->status
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
