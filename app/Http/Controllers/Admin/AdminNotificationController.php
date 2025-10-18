<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminNotificationController extends Controller
{
    /**
     * Get recent admin notifications (for dashboard)
     */
    public function recent(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 10);
            
            $notifications = AdminNotification::recent($limit)->get();
            $unreadCount = AdminNotification::unreadCount();
            
            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => $unreadCount
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching recent admin notifications: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch notifications'
            ], 500);
        }
    }
    
    /**
     * Get unread admin notifications
     */
    public function unread(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 20);
            
            $notifications = AdminNotification::unread()->limit($limit)->get();
            $unreadCount = AdminNotification::unreadCount();
            
            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => $unreadCount
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching unread admin notifications: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch unread notifications'
            ], 500);
        }
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, $id): JsonResponse
    {
        try {
            $notification = AdminNotification::findOrFail($id);
            $notification->markAsRead();
            
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error marking notification as read: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read'
            ], 500);
        }
    }
    
    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        try {
            AdminNotification::where('is_read', false)
                            ->update([
                                'is_read' => true,
                                'read_at' => now()
                            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error marking all notifications as read: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all notifications as read'
            ], 500);
        }
    }
    
    /**
     * Get notification count (unread)
     */
    public function count(): JsonResponse
    {
        try {
            $unreadCount = AdminNotification::unreadCount();
            
            return response()->json([
                'success' => true,
                'unread_count' => $unreadCount
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching notification count: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'unread_count' => 0
            ]);
        }
    }
}
