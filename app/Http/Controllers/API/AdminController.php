<?php

namespace App\Http\Controllers\API;

use Illuminate\Routing\Controller as BaseController;
use App\Models\User;
use App\Models\Landlord;
use App\Models\Tenant;
use App\Models\Property;
use App\Models\PropertyCategory;
use App\Models\Booking;
use App\Models\Notification;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AdminController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:admin');
    }

    /**
     * Get admin dashboard overview
     */
    public function dashboard(): JsonResponse
    {
        try {
            // Calculate this month's statistics
            $thisMonth = Carbon::now()->startOfMonth();
            $totalUsers = User::count();
            $activeUsers = User::where('email_verified_at', '!=', null)->count();
            $newThisMonth = User::where('created_at', '>=', $thisMonth)->count();
            
            $totalProperties = Property::count();
            $activeProperties = Property::where('is_active', true)->where('availability_status', 'available')->count();
            $pendingReview = Property::where('is_active', false)->count();
            $suspended = Property::where('availability_status', 'under_maintenance')->count();
            $newPropertiesThisMonth = Property::where('created_at', '>=', $thisMonth)->count();
            
            $totalBookings = Booking::count();
            $pendingBookings = Booking::where('status', 'pending')->count();
            $confirmedBookings = Booking::where('status', 'confirmed')->count();
            $completedBookings = Booking::where('status', 'completed')->count();
            $cancelledBookings = Booking::where('status', 'cancelled')->count();
            $revenueThisMonth = Booking::whereIn('status', ['confirmed', 'completed'])
                ->where('created_at', '>=', $thisMonth)
                ->sum('total_amount') ?? 0;
            
            $totalNotifications = Notification::count();
            $unreadNotifications = Notification::whereNull('read_at')->count();
            $criticalNotifications = Notification::where('type', 'system')->whereNull('read_at')->count();
            
            $data = [
                'users' => [
                    'total' => $totalUsers,
                    'active' => $activeUsers,
                    'new_this_month' => $newThisMonth,
                    'landlords' => User::where('role', 'landlord')->count(),
                    'tenants' => User::where('role', 'tenant')->count(),
                    'admins' => User::where('role', 'admin')->count()
                ],
                'properties' => [
                    'total' => $totalProperties,
                    'active' => $activeProperties,
                    'pending_review' => $pendingReview,
                    'suspended' => $suspended,
                    'new_this_month' => $newPropertiesThisMonth
                ],
                'bookings' => [
                    'total' => $totalBookings,
                    'pending' => $pendingBookings,
                    'confirmed' => $confirmedBookings,
                    'completed' => $completedBookings,
                    'cancelled' => $cancelledBookings,
                    'revenue_this_month' => round($revenueThisMonth, 2)
                ],
                'notifications' => [
                    'total' => $totalNotifications,
                    'unread' => $unreadNotifications,
                    'critical' => $criticalNotifications
                ],
                'system' => [
                    'uptime' => '99.8%',
                    'last_backup' => Carbon::now()->subHours(3)->toISOString(),
                    'storage_used' => '78%',
                    'active_sessions' => User::whereNotNull('email_verified_at')->count()
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all users with filtering and pagination
     */
    public function users(Request $request): JsonResponse
    {
        try {
            $query = User::query()->with(['landlord', 'tenant']);
            
            // Filtering
            if ($request->filled('role')) {
                $query->where('role', $request->role);
            }
            
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }
            
            if ($request->filled('verified')) {
                if ($request->boolean('verified')) {
                    $query->whereHas('landlord', function($q) {
                        $q->where('verified', true);
                    })->orWhereHas('tenant', function($q) {
                        $q->where('verified', true);
                    });
                } else {
                    $query->whereHas('landlord', function($q) {
                        $q->where('verified', false);
                    })->orWhereHas('tenant', function($q) {
                        $q->where('verified', false);
                    });
                }
            }
            
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            
            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);
            
            // Pagination
            $perPage = $request->get('per_page', 15);
            $users = $query->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'message' => 'Users retrieved successfully',
                'data' => $users
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific user details
     */
    public function showUser(User $user): JsonResponse
    {
        try {
            $user->load(['landlord', 'tenant', 'bookings.property', 'notifications']);
            
            return response()->json([
                'success' => true,
                'message' => 'User details retrieved successfully',
                'data' => $user
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving user details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user status and verification
     */
    public function updateUser(Request $request, User $user): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $user->id,
                'role' => 'sometimes|in:admin,landlord,tenant,user',
                'verified' => 'sometimes|boolean',
                'status' => 'sometimes|in:active,inactive,suspended',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            DB::beginTransaction();
            
            // Update user basic info
            if ($request->has('name')) {
                $user->name = $request->name;
            }
            if ($request->has('email')) {
                $user->email = $request->email;
            }
            if ($request->has('role')) {
                $user->role = $request->role;
            }
            
            $user->save();
            
            // Update profile verification status
            if ($request->has('verified')) {
                if ($user->landlord) {
                    $user->landlord->verified = $request->verified;
                    $user->landlord->verified_at = $request->verified ? now() : null;
                    $user->landlord->save();
                }
                if ($user->tenant) {
                    $user->tenant->verified = $request->verified;
                    $user->tenant->verified_at = $request->verified ? now() : null;
                    $user->tenant->save();
                }
            }
            
            // Update profile status
            if ($request->has('status')) {
                if ($user->landlord) {
                    $user->landlord->status = $request->status;
                    $user->landlord->save();
                }
                if ($user->tenant) {
                    $user->tenant->status = $request->status;
                    $user->tenant->save();
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user->load(['landlord', 'tenant'])
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete/deactivate user
     */
    public function deleteUser(User $user): JsonResponse
    {
        try {
            // Check if user has active bookings
            $activeBookings = $user->bookings()->whereIn('status', ['pending', 'confirmed'])->count();
            
            if ($activeBookings > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete user with active bookings. Please cancel or complete all bookings first.',
                    'active_bookings' => $activeBookings
                ], 400);
            }
            
            DB::beginTransaction();
            
            // Soft delete related records
            if ($user->landlord) {
                $user->landlord->status = 'inactive';
                $user->landlord->save();
                
                // Mark properties as inactive
                Property::where('landlord_id', $user->landlord->id)->update(['is_active' => false]);
            }
            
            if ($user->tenant) {
                $user->tenant->status = 'inactive';
                $user->tenant->save();
            }
            
            // Mark notifications as deleted
            $user->notifications()->delete();
            
            // Delete user
            $user->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Perform actions on user account
     */
    public function userActions(Request $request, User $user): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'action' => 'required|in:suspend,activate,ban,verify_email,reset_password',
                'reason' => 'nullable|string|max:500',
                'duration' => 'nullable|integer|min:1|max:365' // days
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $action = $request->action;
            $reason = $request->reason;
            $duration = $request->duration;

            DB::beginTransaction();

            switch ($action) {
                case 'suspend':
                    $user->status = 'suspended';
                    $user->suspended_at = now();
                    $user->suspension_reason = $reason;
                    if ($duration) {
                        $user->suspension_until = now()->addDays($duration);
                    }
                    $user->save();
                    
                    // Update related profiles
                    if ($user->landlord) {
                        $user->landlord->status = 'suspended';
                        $user->landlord->save();
                        // Deactivate properties
                        Property::where('landlord_id', $user->landlord->id)->update(['is_active' => false]);
                    }
                    if ($user->tenant) {
                        $user->tenant->status = 'suspended';
                        $user->tenant->save();
                    }
                    
                    $message = 'User suspended successfully';
                    break;

                case 'activate':
                    $user->status = 'active';
                    $user->suspended_at = null;
                    $user->suspension_reason = null;
                    $user->suspension_until = null;
                    $user->save();
                    
                    // Update related profiles
                    if ($user->landlord) {
                        $user->landlord->status = 'active';
                        $user->landlord->save();
                    }
                    if ($user->tenant) {
                        $user->tenant->status = 'active';
                        $user->tenant->save();
                    }
                    
                    $message = 'User activated successfully';
                    break;

                case 'ban':
                    $user->status = 'banned';
                    $user->banned_at = now();
                    $user->ban_reason = $reason;
                    $user->save();
                    
                    // Update related profiles
                    if ($user->landlord) {
                        $user->landlord->status = 'banned';
                        $user->landlord->save();
                        // Deactivate all properties permanently
                        Property::where('landlord_id', $user->landlord->id)->update([
                            'is_active' => false,
                            'availability_status' => 'under_maintenance'
                        ]);
                    }
                    if ($user->tenant) {
                        $user->tenant->status = 'banned';
                        $user->tenant->save();
                        // Cancel pending bookings
                        Booking::where('user_id', $user->id)
                            ->where('status', 'pending')
                            ->update(['status' => 'cancelled', 'cancellation_reason' => 'User banned by admin']);
                    }
                    
                    $message = 'User banned successfully';
                    break;

                case 'verify_email':
                    $user->email_verified_at = now();
                    $user->save();
                    
                    $message = 'User email verified successfully';
                    break;

                case 'reset_password':
                    // Generate a temporary password
                    $tempPassword = 'temp_' . Str::random(8);
                    $user->password = Hash::make($tempPassword);
                    $user->password_reset_required = true;
                    $user->save();
                    
                    // TODO: Send email with temporary password
                    
                    $message = 'Password reset successfully. Temporary password: ' . $tempPassword;
                    break;

                default:
                    throw new \InvalidArgumentException('Invalid action');
            }

            // Log admin action
            // TODO: Implement admin action logging

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'user_id' => $user->id,
                    'action' => $action,
                    'reason' => $reason,
                    'duration' => $duration,
                    'performed_at' => now(),
                    'performed_by' => Auth::id()
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error performing user action',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user profile data (landlord or tenant)
     */
    public function getUserProfile(Request $request, User $user): JsonResponse
    {
        try {
            $profileData = [];
            
            if ($user->role === 'landlord' && $user->landlord) {
                $profileData = [
                    'id' => $user->landlord->id,
                    'business_name' => $user->landlord->business_name,
                    'business_registration_number' => $user->landlord->business_registration_number,
                    'phone' => $user->landlord->phone,
                    'address' => $user->landlord->address,
                    'city' => $user->landlord->city,
                    'state' => $user->landlord->state,
                    'country' => $user->landlord->country,
                    'postal_code' => $user->landlord->postal_code,
                    'commission_rate' => $user->landlord->commission_rate,
                    'status' => $user->landlord->status,
                    'verified' => $user->landlord->verified,
                    'verified_at' => $user->landlord->verified_at,
                    'properties_count' => Property::where('landlord_id', $user->landlord->id)->count(),
                    'active_properties_count' => Property::where('landlord_id', $user->landlord->id)->where('is_active', true)->count(),
                    'bookings_count' => Booking::where('landlord_id', $user->landlord->id)->count(),
                    'total_revenue' => Booking::where('landlord_id', $user->landlord->id)
                        ->whereIn('status', ['confirmed', 'completed'])
                        ->sum('total_amount') ?? 0
                ];
            } elseif ($user->role === 'tenant' && $user->tenant) {
                $profileData = [
                    'id' => $user->tenant->id,
                    'phone_number' => $user->tenant->phone_number,
                    'date_of_birth' => $user->tenant->date_of_birth,
                    'employment_status' => $user->tenant->employment_status,
                    'employer_name' => $user->tenant->employer_name,
                    'monthly_income' => $user->tenant->monthly_income,
                    'emergency_contact_name' => $user->tenant->emergency_contact_name,
                    'emergency_contact_phone' => $user->tenant->emergency_contact_phone,
                    'preferred_move_in_date' => $user->tenant->preferred_move_in_date,
                    'status' => $user->tenant->status,
                    'verified' => $user->tenant->verified,
                    'verified_at' => $user->tenant->verified_at,
                    'bookings_count' => Booking::where('user_id', $user->id)->count(),
                    'active_bookings_count' => Booking::where('user_id', $user->id)
                        ->whereIn('status', ['pending', 'confirmed'])->count(),
                    'completed_bookings_count' => Booking::where('user_id', $user->id)
                        ->where('status', 'completed')->count()
                ];
            }
            
            return response()->json([
                'success' => true,
                'data' => $profileData
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving user profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all properties with filtering
     */
    public function properties(Request $request): JsonResponse
    {
        try {
            $query = Property::query()->with(['category', 'landlord.user', 'bookings']);
            
            // Filtering
            if ($request->filled('status')) {
                $query->where('availability_status', $request->status);
            }
            
            if ($request->filled('city')) {
                $query->where('city', 'like', "%{$request->city}%");
            }
            
            if ($request->filled('category_id')) {
                $query->where('property_category_id', $request->category_id);
            }
            
            if ($request->filled('landlord_id')) {
                $query->where('landlord_id', $request->landlord_id);
            }
            
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%");
                });
            }
            
            if ($request->filled('price_min')) {
                $query->where('monthly_rent', '>=', $request->price_min);
            }
            
            if ($request->filled('price_max')) {
                $query->where('monthly_rent', '<=', $request->price_max);
            }
            
            if ($request->filled('active')) {
                $query->where('is_active', $request->boolean('active'));
            }
            
            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);
            
            // Pagination
            $perPage = $request->get('per_page', 15);
            $properties = $query->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'message' => 'Properties retrieved successfully',
                'data' => $properties
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving properties',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update property status
     */
    public function updateProperty(Request $request, Property $property): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'availability_status' => 'sometimes|in:available,occupied,under_maintenance,unavailable',
                'is_active' => 'sometimes|boolean',
                'admin_notes' => 'sometimes|string|max:1000',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            if ($request->has('availability_status')) {
                $property->availability_status = $request->availability_status;
            }
            
            if ($request->has('is_active')) {
                $property->is_active = $request->is_active;
            }
            
            if ($request->has('admin_notes')) {
                $property->admin_notes = $request->admin_notes;
            }
            
            $property->save();
            
            // Create notification for landlord
            if ($request->has('availability_status') || $request->has('is_active')) {
                Notification::create([
                    'user_id' => $property->landlord->user_id,
                    'type' => 'property_status_updated',
                    'title' => 'Property Status Updated by Admin',
                    'message' => "Your property '{$property->title}' status has been updated by an administrator.",
                    'data' => json_encode([
                        'property_id' => $property->id,
                        'new_status' => $property->availability_status,
                        'is_active' => $property->is_active,
                        'admin_notes' => $property->admin_notes
                    ]),
                    'priority' => 'medium'
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Property updated successfully',
                'data' => $property->load(['category', 'landlord.user'])
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating property',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Moderate property (approve, reject, flag, etc.)
     */
    public function moderateProperty(Request $request, Property $property): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'action' => 'required|in:approve,reject,flag,unflag,feature',
                'reason' => 'nullable|string|max:500',
                'feedback' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $action = $request->action;
            $reason = $request->reason;
            $feedback = $request->feedback;

            DB::beginTransaction();

            switch ($action) {
                case 'approve':
                    $property->is_active = true;
                    $property->availability_status = 'available';
                    $property->admin_approved_at = now();
                    $property->admin_notes = $feedback;
                    $message = 'Property approved successfully';
                    break;

                case 'reject':
                    $property->is_active = false;
                    $property->availability_status = 'under_maintenance';
                    $property->admin_rejected_at = now();
                    $property->admin_notes = $feedback;
                    $property->rejection_reason = $reason;
                    $message = 'Property rejected successfully';
                    break;

                case 'flag':
                    $property->flagged_at = now();
                    $property->flagged_reason = $reason;
                    $property->admin_notes = $feedback;
                    $message = 'Property flagged successfully';
                    break;

                case 'unflag':
                    $property->flagged_at = null;
                    $property->flagged_reason = null;
                    $message = 'Property unflagged successfully';
                    break;

                case 'feature':
                    $property->featured = true;
                    $property->featured_at = now();
                    $message = 'Property featured successfully';
                    break;

                default:
                    throw new \InvalidArgumentException('Invalid moderation action');
            }

            $property->save();

            // Send notification to landlord
            if ($property->landlord && $property->landlord->user) {
                Notification::create([
                    'user_id' => $property->landlord->user->id,
                    'type' => 'property_moderation',
                    'title' => 'Property Moderation Update',
                    'message' => "Your property '{$property->title}' has been {$action}d by an administrator." . 
                               ($feedback ? "\n\nFeedback: " . $feedback : ''),
                    'data' => json_encode([
                        'property_id' => $property->id,
                        'action' => $action,
                        'reason' => $reason,
                        'feedback' => $feedback
                    ]),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'property_id' => $property->id,
                    'action' => $action,
                    'reason' => $reason,
                    'feedback' => $feedback,
                    'performed_at' => now(),
                    'performed_by' => Auth::id()
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error moderating property',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all bookings with filtering
     */
    public function bookings(Request $request): JsonResponse
    {
        try {
            $query = Booking::query()->with(['property', 'user', 'landlord.user']);
            
            // Filtering
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }
            
            if ($request->filled('landlord_id')) {
                $query->where('landlord_id', $request->landlord_id);
            }
            
            if ($request->filled('property_id')) {
                $query->where('property_id', $request->property_id);
            }
            
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            
            if ($request->filled('amount_min')) {
                $query->where('total_amount', '>=', $request->amount_min);
            }
            
            if ($request->filled('amount_max')) {
                $query->where('total_amount', '<=', $request->amount_max);
            }
            
            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);
            
            // Pagination
            $perPage = $request->get('per_page', 15);
            $bookings = $query->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'message' => 'Bookings retrieved successfully',
                'data' => $bookings
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving bookings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update booking status
     */
    public function updateBooking(Request $request, Booking $booking): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'sometimes|in:pending,confirmed,cancelled,rejected,completed',
                'admin_notes' => 'sometimes|string|max:1000',
                'cancellation_reason' => 'sometimes|string|max:500',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            DB::beginTransaction();
            
            $oldStatus = $booking->status;
            
            if ($request->has('status')) {
                $booking->status = $request->status;
                
                // Set appropriate timestamps
                switch ($request->status) {
                    case 'confirmed':
                        $booking->confirmed_at = now();
                        break;
                    case 'cancelled':
                        $booking->cancelled_at = now();
                        if ($request->has('cancellation_reason')) {
                            $booking->cancellation_reason = $request->cancellation_reason;
                        }
                        break;
                    case 'rejected':
                        $booking->rejected_at = now();
                        break;
                }
            }
            
            if ($request->has('admin_notes')) {
                $booking->admin_notes = $request->admin_notes;
            }
            
            $booking->save();
            
            // Update property status if needed
            if ($request->status === 'confirmed' && $oldStatus !== 'confirmed') {
                $booking->property->update(['availability_status' => 'occupied']);
            } elseif (in_array($request->status, ['cancelled', 'rejected']) && $oldStatus === 'confirmed') {
                $booking->property->update(['availability_status' => 'available']);
            }
            
            // Create notifications
            if ($request->has('status') && $oldStatus !== $request->status) {
                // Notify tenant
                Notification::create([
                    'user_id' => $booking->user_id,
                    'type' => 'booking_status_updated',
                    'title' => 'Booking Status Updated by Admin',
                    'message' => "Your booking for '{$booking->property->title}' has been {$request->status} by an administrator.",
                    'data' => json_encode([
                        'booking_id' => $booking->id,
                        'property_id' => $booking->property_id,
                        'old_status' => $oldStatus,
                        'new_status' => $request->status,
                        'admin_notes' => $booking->admin_notes
                    ]),
                    'priority' => 'high'
                ]);
                
                // Notify landlord
                Notification::create([
                    'user_id' => $booking->landlord->user_id,
                    'type' => 'booking_status_updated',
                    'title' => 'Booking Status Updated by Admin',
                    'message' => "A booking for your property '{$booking->property->title}' has been {$request->status} by an administrator.",
                    'data' => json_encode([
                        'booking_id' => $booking->id,
                        'property_id' => $booking->property_id,
                        'old_status' => $oldStatus,
                        'new_status' => $request->status,
                        'admin_notes' => $booking->admin_notes
                    ]),
                    'priority' => 'medium'
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Booking updated successfully',
                'data' => $booking->load(['property', 'user', 'landlord.user'])
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get system notifications
     */
    public function notifications(Request $request): JsonResponse
    {
        try {
            $query = Notification::query()->with(['user', 'property', 'booking']);
            
            // Status filtering - by default show only active notifications
            $status = $request->get('status', 'active');
            if ($status === 'active') {
                $query->active();
            } elseif ($status === 'deleted') {
                $query->deleted();
            } elseif ($status === 'all') {
                $query->withDeleted();
            }
            
            // Filtering
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }
            
            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }
            
            if ($request->filled('read_status')) {
                if ($request->read_status === 'read') {
                    $query->whereNotNull('read_at');
                } else {
                    $query->whereNull('read_at');
                }
            }
            
            if ($request->filled('email_status')) {
                $query->where('email_sent', $request->boolean('email_status'));
            }
            
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }
            
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            
            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);
            
            // Pagination
            $perPage = $request->get('per_page', 15);
            $notifications = $query->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'message' => 'Notifications retrieved successfully',
                'data' => $notifications
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create system-wide notification
     */
    public function createNotification(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|string|max:50',
                'title' => 'required|string|max:255',
                'message' => 'required|string|max:1000',
                'priority' => 'required|in:low,medium,high',
                'target_users' => 'required|array',
                'target_users.*' => 'integer|exists:users,id',
                'send_email' => 'boolean',
                'data' => 'sometimes|array',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            DB::beginTransaction();
            
            $notifications = [];
            foreach ($request->target_users as $userId) {
                $notification = Notification::create([
                    'user_id' => $userId,
                    'type' => $request->type,
                    'title' => $request->title,
                    'message' => $request->message,
                    'priority' => $request->priority,
                    'data' => $request->has('data') ? json_encode($request->data) : null,
                    'email_sent' => false,
                    'triggered_by' => Auth::id(),
                ]);
                
                $notifications[] = $notification;
                
                // Send email if requested
                if ($request->boolean('send_email', false)) {
                    // TODO: Implement email sending logic
                    $notification->update(['email_sent' => true, 'email_sent_at' => now()]);
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Notifications created successfully',
                'data' => [
                    'total_sent' => count($notifications),
                    'email_sent' => $request->boolean('send_email', false),
                    'notifications' => $notifications
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get system analytics
     */
    public function analytics(Request $request): JsonResponse
    {
        try {
            $period = $request->get('period', '30d');
            $startDate = $this->getPeriodStartDate($period);
            $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : Carbon::now();
            
            // Calculate retention and churn rates
            $totalUsers = User::count();
            $activeUsers = User::where('email_verified_at', '!=', null)->count();
            $retentionRate = $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100, 1) : 0;
            $churnRate = 100 - $retentionRate;
            
            // Calculate booking metrics
            $totalBookings = Booking::whereBetween('created_at', [$startDate, $endDate])->count();
            $totalRevenue = Booking::whereIn('status', ['confirmed', 'completed'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('total_amount') ?? 0;
            $avgBookingValue = $totalBookings > 0 ? round($totalRevenue / $totalBookings, 2) : 0;
            $cancellationRate = $this->getCancellationRate($startDate, $endDate);
            
            // Calculate property metrics
            $totalViews = Property::sum('views_count') ?? 0;
            $bookingConversion = $totalViews > 0 ? round(($totalBookings / $totalViews) * 100, 1) : 0;
            
            // Calculate growth rates
            $previousPeriodStart = $this->getPreviousPeriodStartDate($period, $startDate);
            $userGrowth = $this->calculateGrowthRate(
                User::whereBetween('created_at', [$startDate, $endDate])->count(),
                User::whereBetween('created_at', [$previousPeriodStart, $startDate])->count()
            );
            $propertyGrowth = $this->calculateGrowthRate(
                Property::whereBetween('created_at', [$startDate, $endDate])->count(),
                Property::whereBetween('created_at', [$previousPeriodStart, $startDate])->count()
            );
            $revenueGrowth = $this->calculateGrowthRate(
                $totalRevenue,
                Booking::whereIn('status', ['confirmed', 'completed'])
                    ->whereBetween('created_at', [$previousPeriodStart, $startDate])
                    ->sum('total_amount') ?? 0
            );
            
            $data = [
                'users' => [
                    'total_signups' => User::whereBetween('created_at', [$startDate, $endDate])->count(),
                    'active_users' => $activeUsers,
                    'retention_rate' => $retentionRate,
                    'churn_rate' => round($churnRate, 1)
                ],
                'properties' => [
                    'new_listings' => Property::whereBetween('created_at', [$startDate, $endDate])->count(),
                    'total_views' => $totalViews,
                    'booking_conversion' => $bookingConversion
                ],
                'bookings' => [
                    'total_bookings' => $totalBookings,
                    'total_revenue' => round($totalRevenue, 2),
                    'average_booking_value' => $avgBookingValue,
                    'cancellation_rate' => $cancellationRate
                ],
                'growth' => [
                    'user_growth' => $userGrowth,
                    'property_growth' => $propertyGrowth,
                    'revenue_growth' => $revenueGrowth
                ]
            ];
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user growth analytics
     */
    public function userGrowthAnalytics(Request $request): JsonResponse
    {
        try {
            $period = $request->get('period', '30d');
            $granularity = $request->get('granularity', 'daily');
            
            $startDate = $this->getPeriodStartDate($period);
            $endDate = Carbon::now();
            
            $labels = [];
            $totalUsers = [];
            $newSignups = [];
            $landlords = [];
            $tenants = [];
            
            $currentDate = $startDate->copy();
            $runningTotal = User::where('created_at', '<', $startDate)->count();
            
            while ($currentDate->lte($endDate)) {
                $nextDate = $this->getNextDate($currentDate, $granularity);
                
                $labels[] = $currentDate->format($granularity === 'daily' ? 'Y-m-d' : 'Y-m');
                
                $dailySignups = User::whereBetween('created_at', [$currentDate, $nextDate])->count();
                $runningTotal += $dailySignups;
                
                $totalUsers[] = $runningTotal;
                $newSignups[] = $dailySignups;
                $landlords[] = User::where('role', 'landlord')->where('created_at', '<=', $nextDate)->count();
                $tenants[] = User::where('role', 'tenant')->where('created_at', '<=', $nextDate)->count();
                
                $currentDate = $nextDate;
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'datasets' => [
                        'total_users' => $totalUsers,
                        'new_signups' => $newSignups,
                        'landlords' => $landlords,
                        'tenants' => $tenants
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving user growth analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get booking trends analytics
     */
    public function bookingTrendsAnalytics(Request $request): JsonResponse
    {
        try {
            $period = $request->get('period', '30d');
            $granularity = $request->get('granularity', 'daily');
            
            $startDate = $this->getPeriodStartDate($period);
            $endDate = Carbon::now();
            
            $labels = [];
            $totalBookings = [];
            $confirmedBookings = [];
            $revenue = [];
            
            $currentDate = $startDate->copy();
            
            while ($currentDate->lte($endDate)) {
                $nextDate = $this->getNextDate($currentDate, $granularity);
                
                $labels[] = $currentDate->format($granularity === 'daily' ? 'Y-m-d' : 'Y-m');
                
                $dayBookings = Booking::whereBetween('created_at', [$currentDate, $nextDate])->count();
                $dayConfirmed = Booking::where('status', 'confirmed')
                    ->whereBetween('created_at', [$currentDate, $nextDate])->count();
                $dayRevenue = Booking::whereIn('status', ['confirmed', 'completed'])
                    ->whereBetween('created_at', [$currentDate, $nextDate])
                    ->sum('total_amount') ?? 0;
                
                $totalBookings[] = $dayBookings;
                $confirmedBookings[] = $dayConfirmed;
                $revenue[] = round($dayRevenue, 2);
                
                $currentDate = $nextDate;
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'datasets' => [
                        'total_bookings' => $totalBookings,
                        'confirmed_bookings' => $confirmedBookings,
                        'revenue' => $revenue
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving booking trends analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get revenue analytics
     */
    public function revenueAnalytics(Request $request): JsonResponse
    {
        try {
            $totalRevenue = Booking::whereIn('status', ['confirmed', 'completed'])->sum('total_amount') ?? 0;
            $commissionRate = 10.0; // This should come from settings
            $commissionEarned = round($totalRevenue * ($commissionRate / 100), 2);
            
            $monthlyRecurring = Booking::whereIn('status', ['confirmed', 'completed'])
                ->where('lease_duration_months', '>=', 12)
                ->sum('monthly_rent') ?? 0;
            
            $oneTimePayments = $totalRevenue - $monthlyRecurring;
            
            // Get monthly trends for the last 12 months
            $labels = [];
            $revenueData = [];
            $commissionsData = [];
            
            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $labels[] = $date->format('M');
                
                $monthRevenue = Booking::whereIn('status', ['confirmed', 'completed'])
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('total_amount') ?? 0;
                    
                $revenueData[] = round($monthRevenue, 2);
                $commissionsData[] = round($monthRevenue * ($commissionRate / 100), 2);
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'total_revenue' => round($totalRevenue, 2),
                    'commission_earned' => $commissionEarned,
                    'monthly_recurring' => round($monthlyRecurring, 2),
                    'one_time_payments' => round($oneTimePayments, 2),
                    'trends' => [
                        'labels' => $labels,
                        'revenue' => $revenueData,
                        'commissions' => $commissionsData
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving revenue analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get system settings
     */
    public function settings(): JsonResponse
    {
        try {
            $settings = [
                'general' => [
                    'site_name' => config('app.name', 'Efiewura'),
                    'site_description' => 'Property rental platform for Ghana',
                    'maintenance_mode' => config('app.env') === 'maintenance',
                    'registration_enabled' => true,
                    'email_verification_required' => true
                ],
                'notifications' => [
                    'email_notifications' => true,
                    'sms_notifications' => false,
                    'push_notifications' => true,
                    'notification_frequency' => 'immediate'
                ],
                'payments' => [
                    'commission_rate' => 10.0,
                    'payment_methods' => ['mobile_money', 'bank_transfer'],
                    'auto_payout' => true,
                    'payout_schedule' => 'weekly'
                ],
                'security' => [
                    'session_timeout' => 120,
                    'password_min_length' => 8,
                    'two_factor_required' => false,
                    'login_attempts_limit' => 5
                ],
                'features' => [
                    'property_verification' => true,
                    'auto_booking_approval' => false,
                    'review_system' => true,
                    'messaging_system' => true
                ],
                'integrations' => [
                    'google_maps_api' => 'configured',
                    'email_service' => 'configured',
                    'sms_service' => 'not_configured',
                    'analytics' => 'configured'
                ]
            ];
            
            return response()->json([
                'success' => true,
                'data' => $settings
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update system settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        try {
            // TODO: Implement settings update logic
            // This would typically involve updating configuration files or database settings
            
            return response()->json([
                'success' => true,
                'message' => 'Settings update feature not implemented yet',
                'data' => $request->all()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get system logs
     */
    public function logs(Request $request): JsonResponse
    {
        try {
            $logType = $request->get('type', 'application');
            $lines = $request->get('lines', 100);
            
            $logPath = storage_path('logs/laravel.log');
            
            if (!file_exists($logPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Log file not found'
                ], 404);
            }
            
            $logs = array_slice(file($logPath), -$lines);
            
            return response()->json([
                'success' => true,
                'message' => 'System logs retrieved successfully',
                'data' => [
                    'log_type' => $logType,
                    'lines_requested' => $lines,
                    'lines_returned' => count($logs),
                    'logs' => $logs
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Backup system data
     */
    public function backup(Request $request): JsonResponse
    {
        try {
            // TODO: Implement backup functionality
            return response()->json([
                'success' => true,
                'message' => 'Backup feature not implemented yet',
                'data' => [
                    'backup_type' => $request->get('type', 'full'),
                    'timestamp' => now(),
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating backup',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Private helper methods
    private function getDatabaseSize(): string
    {
        try {
            $path = database_path('database.sqlite');
            return file_exists($path) ? number_format(filesize($path) / 1024 / 1024, 2) . ' MB' : 'N/A';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    private function getUserRegistrationStats($startDate): array
    {
        return [
            'total' => User::where('created_at', '>=', $startDate)->count(),
            'by_role' => User::where('created_at', '>=', $startDate)
                ->select('role', DB::raw('count(*) as count'))
                ->groupBy('role')
                ->pluck('count', 'role')
                ->toArray(),
            'daily' => User::where('created_at', '>=', $startDate)
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count', 'date')
                ->toArray(),
        ];
    }

    private function getPropertyListingStats($startDate): array
    {
        return [
            'total' => Property::where('created_at', '>=', $startDate)->count(),
            'by_status' => Property::where('created_at', '>=', $startDate)
                ->select('availability_status', DB::raw('count(*) as count'))
                ->groupBy('availability_status')
                ->pluck('count', 'availability_status')
                ->toArray(),
            'daily' => Property::where('created_at', '>=', $startDate)
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count', 'date')
                ->toArray(),
        ];
    }

    private function getBookingTrendStats($startDate): array
    {
        return [
            'total' => Booking::where('created_at', '>=', $startDate)->count(),
            'by_status' => Booking::where('created_at', '>=', $startDate)
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            'daily' => Booking::where('created_at', '>=', $startDate)
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count', 'date')
                ->toArray(),
        ];
    }

    // New helper methods for enhanced analytics
    private function getPeriodStartDate($period): Carbon
    {
        switch($period) {
            case '7d':
                return Carbon::now()->subDays(7);
            case '30d':
                return Carbon::now()->subDays(30);
            case '90d':
                return Carbon::now()->subDays(90);
            case '1y':
                return Carbon::now()->subYear();
            default:
                return Carbon::now()->subDays(30);
        }
    }

    private function getPreviousPeriodStartDate($period, $startDate): Carbon
    {
        $days = $startDate->diffInDays(Carbon::now());
        return $startDate->copy()->subDays($days);
    }

    private function calculateGrowthRate($current, $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100.0 : 0.0;
        }
        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function getCancellationRate($startDate, $endDate): float
    {
        $totalBookings = Booking::whereBetween('created_at', [$startDate, $endDate])->count();
        $cancelledBookings = Booking::where('status', 'cancelled')
            ->whereBetween('created_at', [$startDate, $endDate])->count();
        
        return $totalBookings > 0 ? round(($cancelledBookings / $totalBookings) * 100, 1) : 0;
    }

    private function getNextDate($currentDate, $granularity): Carbon
    {
        switch($granularity) {
            case 'daily':
                return $currentDate->copy()->addDay();
            case 'weekly':
                return $currentDate->copy()->addWeek();
            case 'monthly':
                return $currentDate->copy()->addMonth();
            default:
                return $currentDate->copy()->addDay();
        }
    }

    private function getRevenueAnalytics($startDate): array
    {
        return [
            'total_value' => Booking::where('created_at', '>=', $startDate)
                ->whereIn('status', ['confirmed', 'completed'])
                ->sum('total_amount'),
            'by_status' => Booking::where('created_at', '>=', $startDate)
                ->select('status', DB::raw('sum(total_amount) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray(),
            'daily' => Booking::where('created_at', '>=', $startDate)
                ->whereIn('status', ['confirmed', 'completed'])
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('sum(total_amount) as total'))
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('total', 'date')
                ->toArray(),
        ];
    }

    private function getNotificationStats($startDate): array
    {
        return [
            'total' => Notification::where('created_at', '>=', $startDate)->count(),
            'by_type' => Notification::where('created_at', '>=', $startDate)
                ->select('type', DB::raw('count(*) as count'))
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
            'email_sent' => Notification::where('created_at', '>=', $startDate)
                ->where('email_sent', true)
                ->count(),
        ];
    }

    private function getPopularLocations(): array
    {
        return Property::select('city', DB::raw('count(*) as count'))
            ->groupBy('city')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->pluck('count', 'city')
            ->toArray();
    }

    private function getPopularCategories(): array
    {
        return Property::with('category')
            ->select('property_category_id', DB::raw('count(*) as count'))
            ->groupBy('property_category_id')
            ->orderBy('count', 'desc')
            ->get()
            ->pluck('count', 'category.name')
            ->toArray();
    }

    private function getLandlordPerformance(): array
    {
        return Landlord::with('user')
            ->withCount(['properties', 'bookings'])
            ->orderBy('bookings_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($landlord) {
                return [
                    'landlord_id' => $landlord->id,
                    'name' => $landlord->user->name,
                    'business_name' => $landlord->business_name,
                    'properties_count' => $landlord->properties_count,
                    'bookings_count' => $landlord->bookings_count,
                    'verified' => $landlord->verified,
                ];
            })
            ->toArray();
    }

    /**
     * Mark admin notification as read
     */
    public function markNotificationAsRead(Request $request, $id): JsonResponse
    {
        try {
            $notification = Notification::findOrFail($id);
            
            // Update read status
            $notification->update([
                'read_at' => now(),
                'read_by' => Auth::id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read successfully',
                'data' => $notification->fresh()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error marking notification as read',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark multiple admin notifications as read
     */
    public function markMultipleNotificationsAsRead(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'notification_ids' => 'required|array|min:1',
                'notification_ids.*' => 'integer|exists:notifications,id'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $updated = Notification::whereIn('id', $request->notification_ids)
                ->whereNull('read_at')
                ->update([
                    'read_at' => now(),
                    'read_by' => Auth::id()
                ]);
            
            return response()->json([
                'success' => true,
                'message' => "Successfully marked {$updated} notifications as read",
                'data' => [
                    'updated_count' => $updated,
                    'notification_ids' => $request->notification_ids
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error marking notifications as read',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete admin notification
     */
    public function deleteNotification($id): JsonResponse
    {
        try {
            $notification = Notification::findOrFail($id);
            $notification->softDelete(Auth::id());
            
            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully',
                'data' => [
                    'id' => $notification->id,
                    'status' => 'deleted',
                    'deleted_by' => Auth::id(),
                    'deleted_at' => $notification->fresh()->deleted_at
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a soft deleted notification
     */
    public function restoreNotification($id): JsonResponse
    {
        try {
            $notification = Notification::where('id', $id)->where('status', 'deleted')->firstOrFail();
            $notification->restore();
            
            return response()->json([
                'success' => true,
                'message' => 'Notification restored successfully',
                'data' => [
                    'id' => $notification->id,
                    'status' => 'active',
                    'restored_at' => now()
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error restoring notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Paginated transaction list with filters.
     */
    public function transactionReport(Request $request): JsonResponse
    {
        try {
            $query = Payment::with(['booking.property', 'user', 'landlord.user'])
                ->orderBy('created_at', 'desc');

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('date_from')) {
                $query->where('created_at', '>=', Carbon::parse($request->date_from)->startOfDay());
            }

            if ($request->filled('date_to')) {
                $query->where('created_at', '<=', Carbon::parse($request->date_to)->endOfDay());
            }

            if ($request->filled('landlord_id')) {
                $query->where('landlord_id', $request->landlord_id);
            }

            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->filled('booking_id')) {
                $query->where('booking_id', $request->booking_id);
            }

            $payments = $query->paginate($request->get('per_page', 20));

            $totals = Payment::selectRaw('
                SUM(status = "paid") as paid_count,
                SUM(status = "pending") as pending_count,
                SUM(status = "failed") as failed_count,
                SUM(status = "refunded") as refunded_count,
                SUM(amount * (status = "paid")) as total_collected,
                SUM(refund_amount) as total_refunded
            ')->first();

            return response()->json([
                'success' => true,
                'data' => $payments->items(),
                'meta' => [
                    'total' => $payments->total(),
                    'per_page' => $payments->perPage(),
                    'current_page' => $payments->currentPage(),
                    'last_page' => $payments->lastPage(),
                ],
                'summary' => [
                    'paid' => (int) ($totals->paid_count ?? 0),
                    'pending' => (int) ($totals->pending_count ?? 0),
                    'failed' => (int) ($totals->failed_count ?? 0),
                    'refunded' => (int) ($totals->refunded_count ?? 0),
                    'total_collected' => (float) ($totals->total_collected ?? 0),
                    'total_refunded' => (float) ($totals->total_refunded ?? 0),
                    'net_revenue' => (float) (($totals->total_collected ?? 0) - ($totals->total_refunded ?? 0)),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transaction report',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Revenue summary with 12-month trend and type breakdown.
     */
    public function revenueReport(Request $request): JsonResponse
    {
        try {
            $totals = Payment::paid()
                ->selectRaw('
                    SUM(amount) as total_collected,
                    SUM(CASE WHEN type = "booking_payment" THEN amount ELSE 0 END) as booking_payments,
                    SUM(CASE WHEN type = "rent_renewal" THEN amount ELSE 0 END) as rent_renewals,
                    COUNT(*) as transaction_count
                ')
                ->first();

            $totalRefunded = Payment::refunded()->sum('refund_amount');

            // Monthly trend — last 12 months
            $monthly = Payment::paid()
                ->where('paid_at', '>=', now()->subMonths(12)->startOfMonth())
                ->selectRaw("DATE_FORMAT(paid_at, '%Y-%m') as month, SUM(amount) as revenue, COUNT(*) as count")
                ->groupBy('month')
                ->orderBy('month', 'asc')
                ->get();

            // Fill in months with no data
            $months = [];
            for ($i = 11; $i >= 0; $i--) {
                $months[] = now()->subMonths($i)->format('Y-m');
            }

            $monthlyMap = $monthly->keyBy('month');
            $trend = array_map(fn($m) => [
                'month' => $m,
                'revenue' => (float) ($monthlyMap[$m]->revenue ?? 0),
                'count' => (int) ($monthlyMap[$m]->count ?? 0),
            ], $months);

            return response()->json([
                'success' => true,
                'data' => [
                    'total_collected' => (float) ($totals->total_collected ?? 0),
                    'total_refunded' => (float) $totalRefunded,
                    'net_revenue' => (float) (($totals->total_collected ?? 0) - $totalRefunded),
                    'transaction_count' => (int) ($totals->transaction_count ?? 0),
                    'by_type' => [
                        'booking_payments' => (float) ($totals->booking_payments ?? 0),
                        'rent_renewals' => (float) ($totals->rent_renewals ?? 0),
                    ],
                    'monthly_trend' => $trend,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch revenue report',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Revenue breakdown for a specific landlord.
     */
    public function landlordRevenueReport(Request $request, Landlord $landlord): JsonResponse
    {
        try {
            $totals = Payment::where('landlord_id', $landlord->id)
                ->paid()
                ->selectRaw('SUM(amount) as total, COUNT(*) as count')
                ->first();

            $refunded = Payment::where('landlord_id', $landlord->id)
                ->refunded()
                ->sum('refund_amount');

            $monthly = Payment::where('landlord_id', $landlord->id)
                ->paid()
                ->where('paid_at', '>=', now()->subMonths(12)->startOfMonth())
                ->selectRaw("DATE_FORMAT(paid_at, '%Y-%m') as month, SUM(amount) as revenue, COUNT(*) as count")
                ->groupBy('month')
                ->orderBy('month', 'asc')
                ->get();

            $activeBookings = Booking::where('landlord_id', $landlord->id)
                ->confirmed()
                ->notDeleted()
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'landlord' => $landlord->load('user'),
                    'total_received' => (float) ($totals->total ?? 0),
                    'total_refunded' => (float) $refunded,
                    'net_revenue' => (float) (($totals->total ?? 0) - $refunded),
                    'transaction_count' => (int) ($totals->count ?? 0),
                    'active_bookings' => $activeBookings,
                    'monthly_trend' => $monthly,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch landlord revenue report',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Full payment history for a specific tenant (admin view).
     */
    public function tenantPaymentHistory(Request $request, User $user): JsonResponse
    {
        try {
            $payments = Payment::where('user_id', $user->id)
                ->with(['booking.property', 'landlord.user'])
                ->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 20));

            $totals = Payment::where('user_id', $user->id)
                ->selectRaw('
                    SUM(status = "paid") as paid_count,
                    SUM(amount * (status = "paid")) as total_paid,
                    SUM(status = "refunded") as refunded_count,
                    SUM(refund_amount) as total_refunded
                ')
                ->first();

            return response()->json([
                'success' => true,
                'data' => $payments->items(),
                'meta' => [
                    'total' => $payments->total(),
                    'per_page' => $payments->perPage(),
                    'current_page' => $payments->currentPage(),
                    'last_page' => $payments->lastPage(),
                ],
                'summary' => [
                    'user' => $user->only(['id', 'name', 'email']),
                    'paid_transactions' => (int) ($totals->paid_count ?? 0),
                    'total_paid' => (float) ($totals->total_paid ?? 0),
                    'refunded_transactions' => (int) ($totals->refunded_count ?? 0),
                    'total_refunded' => (float) ($totals->total_refunded ?? 0),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tenant payment history',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
