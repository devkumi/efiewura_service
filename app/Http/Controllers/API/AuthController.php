<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Models\Landlord;
use App\Models\Tenant;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\UserPreference;

class AuthController extends Controller
{
    /**
     * Register a new user with role-specific profile
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Create the user
            $userData = [
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ];

            $user = User::create($userData);

            // Create role-specific profile
            $profile = null;
            if ($request->role === 'landlord') {
                $profile = $this->createLandlordProfile($user, $request);
            } elseif ($request->role === 'tenant') {
                $profile = $this->createTenantProfile($user, $request);
            }

            // Create API token
            $token = $user->createToken('auth_token')->plainTextToken;
            
            // Create welcome notification
            Notification::createWelcomeMessage($user);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'user' => $user->load($request->role === 'landlord' ? 'landlord' : ($request->role === 'tenant' ? 'tenant' : [])),
                    'token' => $token,
                    'token_type' => 'Bearer',
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create landlord profile
     */
    private function createLandlordProfile(User $user, RegisterRequest $request): Landlord
    {
        return Landlord::create([
            'user_id' => $user->id,
            'business_name' => $request->business_name,
            'business_registration_number' => $request->business_registration_number,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'country' => $request->country ?? 'Ghana',
            'commission_rate' => 10.00, // Default commission rate
            'status' => 'active',
            'verified' => false,
        ]);
    }

    /**
     * Create tenant profile
     */
    private function createTenantProfile(User $user, RegisterRequest $request): Tenant
    {
        return Tenant::create([
            'user_id' => $user->id,
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'occupation' => $request->occupation,
            'employer' => $request->employer,
            'monthly_income' => $request->monthly_income,
            'current_address' => $request->current_address,
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_phone' => $request->emergency_contact_phone,
            'emergency_contact_relationship' => $request->emergency_contact_relationship,
            'status' => 'active',
            'verified' => false,
        ]);
    }

    /**
     * Login user and create token
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'two_factor_code' => 'nullable|string|min:6|max:6',
            'recovery_code' => 'nullable|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Check if user requires 2FA
        if ($user->requiresTwoFactorAuthentication()) {
            // If 2FA is required but no code provided, request 2FA
            if (!$request->two_factor_code && !$request->recovery_code) {
                return response()->json([
                    'success' => false,
                    'message' => 'Two-factor authentication required',
                    'requires_2fa' => true,
                    'two_factor_method' => $user->two_factor_method ?: 'app'
                ], 422);
            }

            // Verify 2FA code or recovery code
            $isValidCode = false;
            
            if ($request->two_factor_code) {
                $google2fa = app('pragmarx.google2fa');
                $secret = decrypt($user->two_factor_secret);
                $isValidCode = $google2fa->verifyKey($secret, $request->two_factor_code);
            } elseif ($request->recovery_code) {
                $isValidCode = $user->useRecoveryCode($request->recovery_code);
            }

            if (!$isValidCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid two-factor authentication code'
                ], 401);
            }
        }

        // Load role-specific profile
        $relations = [];
        if ($user->role === 'landlord') {
            $relations = ['landlord'];
        } elseif ($user->role === 'tenant') {
            $relations = ['tenant'];
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user->load($relations),
                'token' => $token,
                'token_type' => 'Bearer',
            ]
        ]);
    }

    /**
     * Get authenticated user profile
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Load role-specific profile and preferences
        $relations = ['preferences'];
        if ($user->role === 'landlord') {
            $relations[] = 'landlord';
        } elseif ($user->role === 'tenant') {
            $relations[] = 'tenant';
        }

        $user->load($relations);

        // Ensure user has preferences, create with defaults if not
        if (!$user->preferences) {
            $defaults = [
                'timezone' => 'Africa/Lagos',
                'date_format' => 'DD/MM/YYYY',
                'theme' => 'light',
                'dashboard_refresh_interval' => 30,
                'email_notifications' => true,
                'sms_notifications' => false,
                'new_bookings' => true,
                'property_updates' => true,
                'user_registrations' => false,
                'system_alerts' => true,
                'weekly_reports' => false,
                'bio' => null,
            ];
            $user->preferences()->create($defaults);
            $user->load('preferences'); // Reload to get the created preferences
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatUserData($user),
        ]);
    }

    /**
     * Update user profile information
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Validate basic user fields
            $validator = validator($request->all(), [
                'firstname' => 'sometimes|string|max:255',
                'lastname' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $user->id,
                
                // Landlord-specific fields
                'business_name' => 'sometimes|string|max:255',
                'business_registration_number' => 'sometimes|string|max:50',
                'phone' => 'sometimes|string|max:20',
                'address' => 'sometimes|string|max:500',
                'city' => 'sometimes|string|max:100',
                'state' => 'sometimes|string|max:100',
                'country' => 'sometimes|string|max:100',
                'postal_code' => 'sometimes|string|max:20',
                
                // Tenant-specific fields
                'date_of_birth' => 'sometimes|date|before:today',
                'gender' => 'sometimes|in:male,female,other',
                'occupation' => 'sometimes|string|max:255',
                'employer' => 'sometimes|string|max:255',
                'monthly_income' => 'sometimes|numeric|min:0',
                'current_address' => 'sometimes|string|max:500',
                'emergency_contact_name' => 'sometimes|string|max:255',
                'emergency_contact_phone' => 'sometimes|string|max:20',
                'emergency_contact_relationship' => 'sometimes|string|max:100',
                
                // Preferences fields
                'bio' => 'sometimes|string|max:1000',
                'preferences' => 'sometimes|array',
                'preferences.timezone' => 'sometimes|string|max:50',
                'preferences.date_format' => 'sometimes|in:DD/MM/YYYY,MM/DD/YYYY,YYYY-MM-DD',
                'preferences.theme' => 'sometimes|in:light,dark',
                'preferences.dashboard_refresh_interval' => 'sometimes|integer|min:10|max:300',
                'preferences.notifications' => 'sometimes|array',
                'preferences.notifications.email' => 'sometimes|boolean',
                'preferences.notifications.sms' => 'sometimes|boolean',
                'preferences.notifications.new_bookings' => 'sometimes|boolean',
                'preferences.notifications.property_updates' => 'sometimes|boolean',
                'preferences.notifications.user_registrations' => 'sometimes|boolean',
                'preferences.notifications.system_alerts' => 'sometimes|boolean',
                'preferences.notifications.weekly_reports' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Update basic user information
            $userUpdated = false;
            if ($request->has('firstname')) {
                $user->firstname = $request->firstname;
                $userUpdated = true;
            }
            if ($request->has('lastname')) {
                $user->lastname = $request->lastname;
                $userUpdated = true;
            }
            if ($request->has('email')) {
                $user->email = $request->email;
                $userUpdated = true;
            }

            if ($userUpdated) {
                $user->save();
            }

            // Update role-specific profile
            if ($user->role === 'landlord' && $user->landlord) {
                $this->updateLandlordProfile($user->landlord, $request);
            } elseif ($user->role === 'tenant' && $user->tenant) {
                $this->updateTenantProfile($user->tenant, $request);
            }

            // Update or create user preferences
            $this->updateUserPreferences($user, $request);

            DB::commit();

            // Reload user with relationships
            $relations = ['preferences'];
            if ($user->role === 'landlord') {
                $relations[] = 'landlord';
            } elseif ($user->role === 'tenant') {
                $relations[] = 'tenant';
            }

            $user->load($relations);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $this->formatUserData($user),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user preferences
     */
    private function updateUserPreferences(User $user, Request $request): void
    {
        $defaults = [
            'timezone' => 'Africa/Lagos',
            'date_format' => 'DD/MM/YYYY',
            'theme' => 'light',
            'dashboard_refresh_interval' => 30,
            'email_notifications' => true,
            'sms_notifications' => false,
            'new_bookings' => true,
            'property_updates' => true,
            'user_registrations' => false,
            'system_alerts' => true,
            'weekly_reports' => false,
            'bio' => null,
        ];
        
        // Get or create user preferences
        $preferences = $user->preferences()->firstOrCreate(
            ['user_id' => $user->id],
            $defaults
        );

        $updated = false;

        // Update bio if provided
        if ($request->has('bio')) {
            $preferences->bio = $request->bio;
            $updated = true;
        }

        // Update preferences if provided
        if ($request->has('preferences')) {
            $preferencesData = $request->preferences;

            // Display preferences
            if (isset($preferencesData['timezone'])) {
                $preferences->timezone = $preferencesData['timezone'];
                $updated = true;
            }
            if (isset($preferencesData['date_format'])) {
                $preferences->date_format = $preferencesData['date_format'];
                $updated = true;
            }
            if (isset($preferencesData['theme'])) {
                $preferences->theme = $preferencesData['theme'];
                $updated = true;
            }
            if (isset($preferencesData['dashboard_refresh_interval'])) {
                $preferences->dashboard_refresh_interval = $preferencesData['dashboard_refresh_interval'];
                $updated = true;
            }

            // Notification preferences
            if (isset($preferencesData['notifications'])) {
                $notifications = $preferencesData['notifications'];

                if (isset($notifications['email'])) {
                    $preferences->email_notifications = $notifications['email'];
                    $updated = true;
                }
                if (isset($notifications['sms'])) {
                    $preferences->sms_notifications = $notifications['sms'];
                    $updated = true;
                }
                if (isset($notifications['new_bookings'])) {
                    $preferences->new_bookings = $notifications['new_bookings'];
                    $updated = true;
                }
                if (isset($notifications['property_updates'])) {
                    $preferences->property_updates = $notifications['property_updates'];
                    $updated = true;
                }
                if (isset($notifications['user_registrations'])) {
                    $preferences->user_registrations = $notifications['user_registrations'];
                    $updated = true;
                }
                if (isset($notifications['system_alerts'])) {
                    $preferences->system_alerts = $notifications['system_alerts'];
                    $updated = true;
                }
                if (isset($notifications['weekly_reports'])) {
                    $preferences->weekly_reports = $notifications['weekly_reports'];
                    $updated = true;
                }
            }
        }

        if ($updated) {
            $preferences->save();
        }
    }

    /**
     * Update landlord profile information
     */
    private function updateLandlordProfile(Landlord $landlord, Request $request): void
    {
        $landlordFields = [
            'business_name', 'business_registration_number', 'phone', 
            'address', 'city', 'state', 'country', 'postal_code'
        ];

        $updated = false;
        foreach ($landlordFields as $field) {
            if ($request->has($field)) {
                $landlord->$field = $request->$field;
                $updated = true;
            }
        }

        if ($updated) {
            $landlord->save();
        }
    }

    /**
     * Update tenant profile information
     */
    private function updateTenantProfile(Tenant $tenant, Request $request): void
    {
        $tenantFields = [
            'phone', 'date_of_birth', 'gender', 'occupation', 'employer',
            'monthly_income', 'current_address', 'emergency_contact_name',
            'emergency_contact_phone', 'emergency_contact_relationship'
        ];

        $updated = false;
        foreach ($tenantFields as $field) {
            if ($request->has($field)) {
                $tenant->$field = $request->$field;
                $updated = true;
            }
        }

        if ($updated) {
            $tenant->save();
        }
    }

    /**
     * Change user password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        // Check if current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
                'errors' => [
                    'current_password' => ['The current password is incorrect.']
                ]
            ], 422);
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    private function formatUserData(User $user): array
    {
        $data = $user->toArray();

        if ($user->preferences) {
            $p = $user->preferences;
            $data['preferences'] = [
                'timezone' => $p->timezone,
                'date_format' => $p->date_format,
                'theme' => $p->theme,
                'dashboard_refresh_interval' => $p->dashboard_refresh_interval,
                'email_notifications' => $p->email_notifications,
                'sms_notifications' => $p->sms_notifications,
                'new_bookings' => $p->new_bookings,
                'property_updates' => $p->property_updates,
                'user_registrations' => $p->user_registrations,
                'system_alerts' => $p->system_alerts,
                'weekly_reports' => $p->weekly_reports,
                'bio' => $p->bio,
            ];
        }

        return $data;
    }
}
