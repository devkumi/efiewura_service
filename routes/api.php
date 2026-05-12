<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PropertyController;
use App\Http\Controllers\API\BookingController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\AdminSettingsController;
use App\Http\Controllers\API\TwoFactorController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public property routes (browsing)
Route::get('/properties', [PropertyController::class, 'index']);
Route::get('/properties/{property}', [PropertyController::class, 'show']);
Route::get('/property-categories', [PropertyController::class, 'categories']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/change-password', [AuthController::class, 'changePassword']);
    
    // Booking routes (for all authenticated users)
    Route::get('/browse-available-properties', [BookingController::class, 'browseAvailable']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::post('/test-bookings', [BookingController::class, 'storeTestBooking']); // Test endpoint for past dates
    Route::get('/my-bookings', [BookingController::class, 'myBookings']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);
    
    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/counts', [NotificationController::class, 'counts']);
    Route::get('/notifications/types', [NotificationController::class, 'types']);
    Route::get('/notifications/email-status', [NotificationController::class, 'emailStatus']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::patch('/notifications/{notification}/unread', [NotificationController::class, 'markAsUnread']);
    Route::patch('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::post('/notifications/{notification}/resend-email', [NotificationController::class, 'resendEmail']);
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy']);
    Route::delete('/notifications/clear-read', [NotificationController::class, 'deleteAllRead']);
    
    // Two-Factor Authentication routes
    Route::prefix('2fa')->group(function () {
        Route::get('/status', [TwoFactorController::class, 'status']);
        Route::post('/generate-secret', [TwoFactorController::class, 'generateSecret']);
        Route::post('/confirm', [TwoFactorController::class, 'confirmSetup']);
        Route::post('/verify', [TwoFactorController::class, 'verify']);
        Route::post('/disable', [TwoFactorController::class, 'disable']);
        Route::get('/recovery-codes', [TwoFactorController::class, 'getRecoveryCodes']);
        Route::post('/recovery-codes/regenerate', [TwoFactorController::class, 'regenerateRecoveryCodes']);
    });
    
    // Landlord property management routes
    Route::middleware('role:landlord')->group(function () {
        Route::post('/properties', [PropertyController::class, 'store']);
        Route::put('/properties/{property}', [PropertyController::class, 'update']);
        Route::delete('/properties/{property}', [PropertyController::class, 'destroy']);
        Route::get('/my-properties', [PropertyController::class, 'myProperties']);
        Route::patch('/properties/{property}/status', [PropertyController::class, 'updateStatus']);
        
        // Landlord booking management
        Route::get('/bookings', [BookingController::class, 'landlordBookings']);
        Route::patch('/bookings/{booking}/confirm', [BookingController::class, 'confirm']);
        Route::patch('/bookings/{booking}/reject', [BookingController::class, 'reject']);
    });
});

// Role-specific protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Admin routes
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        // Dashboard and analytics
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/analytics', [AdminController::class, 'analytics']);
        Route::get('/analytics/user-growth', [AdminController::class, 'userGrowthAnalytics']);
        Route::get('/analytics/booking-trends', [AdminController::class, 'bookingTrendsAnalytics']);
        Route::get('/analytics/revenue', [AdminController::class, 'revenueAnalytics']);
        Route::get('/settings', [AdminController::class, 'settings']);
        Route::patch('/settings', [AdminController::class, 'updateSettings']);
        
        // System management
        Route::get('/logs', [AdminController::class, 'logs']);
        Route::post('/backup', [AdminController::class, 'backup']);
        
        // User management
        Route::get('/users', [AdminController::class, 'users']);
        Route::get('/users/{user}', [AdminController::class, 'showUser']);
        Route::get('/users/{user}/profile', [AdminController::class, 'getUserProfile']);
        Route::patch('/users/{user}', [AdminController::class, 'updateUser']);
        Route::post('/users/{user}/actions', [AdminController::class, 'userActions']);
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser']);
        
        // Property management
        Route::get('/properties', [AdminController::class, 'properties']);
        Route::patch('/properties/{property}', [AdminController::class, 'updateProperty']);
        Route::put('/properties/{property}/status', [AdminController::class, 'updateProperty']);
        Route::post('/properties/{property}/moderate', [AdminController::class, 'moderateProperty']);
        
        // Booking management
        Route::get('/bookings', [AdminController::class, 'bookings']);
        Route::patch('/bookings/{booking}', [AdminController::class, 'updateBooking']);
        
        // Notification management
        Route::get('/notifications', [AdminController::class, 'notifications']);
        Route::post('/notifications', [AdminController::class, 'createNotification']);
        Route::patch('/notifications/{id}/read', [AdminController::class, 'markNotificationAsRead']);
        Route::patch('/notifications/read-multiple', [AdminController::class, 'markMultipleNotificationsAsRead']);
        Route::delete('/notifications/{id}', [AdminController::class, 'deleteNotification']);
        Route::patch('/notifications/{id}/restore', [AdminController::class, 'restoreNotification']);
        
        // Admin Two-Factor Authentication management
        Route::prefix('2fa')->group(function () {
            Route::get('/users/{user}/status', [TwoFactorController::class, 'adminGetUserStatus']);
            Route::patch('/users/{user}/toggle-requirement', [TwoFactorController::class, 'adminToggleRequirement']);
            Route::delete('/users/{user}/disable', [TwoFactorController::class, 'adminDisable']);
            Route::get('/overview', [TwoFactorController::class, 'adminOverview']);
        });

        // Admin Settings Management
        Route::prefix('settings')->group(function () {
            // Main settings endpoints
            Route::get('/', [AdminSettingsController::class, 'index']);
            Route::put('/', [AdminSettingsController::class, 'update']);
            Route::post('/reset', [AdminSettingsController::class, 'reset']);
            
            // Section-specific endpoints
            Route::get('/general', function(Request $request) {
                return app(AdminSettingsController::class)->getCategory('general');
            });
            Route::put('/general', function(Request $request) {
                return app(AdminSettingsController::class)->updateCategory($request, 'general');
            });
            
            Route::get('/users', function(Request $request) {
                return app(AdminSettingsController::class)->getCategory('users');
            });
            Route::put('/users', function(Request $request) {
                return app(AdminSettingsController::class)->updateCategory($request, 'users');
            });
            
            Route::get('/properties', function(Request $request) {
                return app(AdminSettingsController::class)->getCategory('properties');
            });
            Route::put('/properties', function(Request $request) {
                return app(AdminSettingsController::class)->updateCategory($request, 'properties');
            });
            
            Route::get('/payments', function(Request $request) {
                return app(AdminSettingsController::class)->getCategory('payments');
            });
            Route::put('/payments', function(Request $request) {
                return app(AdminSettingsController::class)->updateCategory($request, 'payments');
            });
            
            Route::get('/notifications', function(Request $request) {
                return app(AdminSettingsController::class)->getCategory('notifications');
            });
            Route::put('/notifications', function(Request $request) {
                return app(AdminSettingsController::class)->updateCategory($request, 'notifications');
            });
            
            Route::get('/security', function(Request $request) {
                return app(AdminSettingsController::class)->getCategory('security');
            });
            Route::put('/security', function(Request $request) {
                return app(AdminSettingsController::class)->updateCategory($request, 'security');
            });
        });
    });
    
    // Landlord routes
    Route::middleware('role:landlord')->prefix('landlord')->group(function () {
        // Landlord settings for auto-release
        Route::get('/settings', [App\Http\Controllers\API\LandlordController::class, 'getSettings']);
        Route::patch('/settings', [App\Http\Controllers\API\LandlordController::class, 'updateSettings']);
    });
    
    // Tenant routes
    Route::middleware('role:tenant')->prefix('tenant')->group(function () {
        // Tenant-only endpoints will go here
    });
});
