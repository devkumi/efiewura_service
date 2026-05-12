<?php

namespace App\Http\Controllers\API;

use Illuminate\Routing\Controller as BaseController;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Get user's notifications
     */
    public function index(Request $request): JsonResponse
    {
        $query = Auth::user()->notifications()
                    ->with(['property:id,title', 'booking:id,property_id', 'triggeredBy:id,name'])
                    ->orderBy('created_at', 'desc');

        // Filter by read status
        if ($request->filled('unread_only')) {
            if ($request->boolean('unread_only')) {
                $query->unread();
            }
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->byPriority($request->priority);
        }

        $notifications = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $notifications->items(),
            'meta' => [
                'total' => $notifications->total(),
                'per_page' => $notifications->perPage(),
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
            ],
            'summary' => [
                'total_notifications' => Auth::user()->notifications()->count(),
                'unread_count' => Auth::user()->notifications()->unread()->count(),
                'high_priority_unread' => Auth::user()->notifications()->unread()->byPriority('high')->count(),
            ]
        ]);
    }

    /**
     * Get notification counts
     */
    public function counts(): JsonResponse
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $user->notifications()->count(),
                'unread' => $user->notifications()->unread()->count(),
                'read' => $user->notifications()->read()->count(),
                'by_priority' => [
                    'urgent' => $user->notifications()->unread()->byPriority('urgent')->count(),
                    'high' => $user->notifications()->unread()->byPriority('high')->count(),
                    'medium' => $user->notifications()->unread()->byPriority('medium')->count(),
                    'low' => $user->notifications()->unread()->byPriority('low')->count(),
                ],
                'by_type' => [
                    'booking_confirmed' => $user->notifications()->unread()->byType('booking_confirmed')->count(),
                    'booking_rejected' => $user->notifications()->unread()->byType('booking_rejected')->count(),
                    'new_booking_request' => $user->notifications()->unread()->byType('new_booking_request')->count(),
                    'property_viewed_milestone' => $user->notifications()->unread()->byType('property_viewed_milestone')->count(),
                ],
            ]
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Notification $notification): JsonResponse
    {
        // Check if user owns this notification
        if ($notification->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this notification'
            ], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'data' => [
                'notification_id' => $notification->id,
                'is_read' => $notification->is_read,
                'read_at' => $notification->read_at,
            ]
        ]);
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread(Notification $notification): JsonResponse
    {
        // Check if user owns this notification
        if ($notification->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this notification'
            ], 403);
        }

        $notification->markAsUnread();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as unread',
            'data' => [
                'notification_id' => $notification->id,
                'is_read' => $notification->is_read,
            ]
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        $updated = Auth::user()->notifications()
                      ->unread()
                      ->update([
                          'is_read' => true,
                          'read_at' => now(),
                      ]);

        return response()->json([
            'success' => true,
            'message' => "Marked {$updated} notifications as read",
            'data' => [
                'updated_count' => $updated,
            ]
        ]);
    }

    /**
     * Delete notification
     */
    public function destroy(Notification $notification): JsonResponse
    {
        // Check if user owns this notification
        if ($notification->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this notification'
            ], 403);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully'
        ]);
    }

    /**
     * Delete all read notifications
     */
    public function deleteAllRead(): JsonResponse
    {
        $deleted = Auth::user()->notifications()
                      ->read()
                      ->delete();

        return response()->json([
            'success' => true,
            'message' => "Deleted {$deleted} read notifications",
            'data' => [
                'deleted_count' => $deleted,
            ]
        ]);
    }

    /**
     * Get notification types and their descriptions
     */
    public function types(): JsonResponse
    {
        $types = [
            'booking_confirmed' => [
                'name' => 'Booking Confirmed',
                'description' => 'Your booking request has been approved by the landlord',
                'category' => 'booking',
                'typical_priority' => 'high',
            ],
            'booking_rejected' => [
                'name' => 'Booking Rejected',
                'description' => 'Your booking request has been declined',
                'category' => 'booking',
                'typical_priority' => 'medium',
            ],
            'new_booking_request' => [
                'name' => 'New Booking Request',
                'description' => 'A new booking request has been received for your property',
                'category' => 'booking',
                'typical_priority' => 'high',
            ],
            'property_viewed_milestone' => [
                'name' => 'Property Views Milestone',
                'description' => 'Your property has reached a view milestone',
                'category' => 'property',
                'typical_priority' => 'low',
            ],
            'welcome_message' => [
                'name' => 'Welcome Message',
                'description' => 'Welcome to the platform',
                'category' => 'system',
                'typical_priority' => 'medium',
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $types
        ]);
    }

    /**
     * Resend notification email
     */
    public function resendEmail(Notification $notification): JsonResponse
    {
        // Check if user owns this notification
        if ($notification->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to resend this notification'
            ], 403);
        }

        try {
            $emailService = new \App\Services\EmailNotificationService();
            
            if ($emailService->sendNotificationEmail($notification)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Email notification resent successfully',
                    'data' => [
                        'notification_id' => $notification->id,
                        'email_sent' => true,
                        'sent_at' => now()->toISOString(),
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to resend email notification'
                ], 500);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error resending email notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get email delivery status for notifications
     */
    public function emailStatus(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $notifications = $user->notifications()
                             ->select(['id', 'type', 'title', 'email_sent', 'created_at'])
                             ->orderBy('created_at', 'desc')
                             ->paginate($request->get('per_page', 20));

        $stats = [
            'total_notifications' => $user->notifications()->count(),
            'emails_sent' => $user->notifications()->where('email_sent', true)->count(),
            'emails_pending' => $user->notifications()->where('email_sent', false)->count(),
            'delivery_rate' => 0,
        ];
        
        if ($stats['total_notifications'] > 0) {
            $stats['delivery_rate'] = round(($stats['emails_sent'] / $stats['total_notifications']) * 100, 2);
        }

        return response()->json([
            'success' => true,
            'data' => $notifications->items(),
            'meta' => [
                'total' => $notifications->total(),
                'per_page' => $notifications->perPage(),
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
            ],
            'stats' => $stats
        ]);
    }
}
