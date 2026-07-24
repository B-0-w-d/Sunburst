<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SystemNotification;

class NotificationController extends Controller
{
    /**
     * 1. Lấy danh sách thông báo của user (cá nhân + hệ thống chung)
     */
    public function index(Request $request)
    {
        $memberId = $request->user()->_id;

        // Lấy thông báo cá nhân của user hoặc thông báo hệ thống chung
        $notifications = SystemNotification::where(function ($query) use ($memberId) {
            $query->where('recipient_id', $memberId)
                ->orWhere('type', 'system');
        })
            ->orderBy('created_at', 'desc')
            ->get();

        // Đếm số lượng chưa đọc (read_at là null) để hiển thị badge chuông
        $unreadCount = SystemNotification::where(function ($query) use ($memberId) {
            $query->where('recipient_id', $memberId)
                ->orWhere('type', 'system');
        })
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'status' => 'success',
            'unread_count' => $unreadCount,
            'notifications' => $notifications
        ]);
    }

    /**
     * 2. Đánh dấu 1 thông báo cụ thể là đã đọc
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = SystemNotification::find($id);

        if ($notification) {
            $notification->update(['read_at' => now()]);
            return response()->json([
                'status' => 'success',
                'message' => 'Đã đánh dấu là đã đọc'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Không tìm thấy thông báo'
        ], 404);
    }

    /**
     * 3. Đánh dấu TẤT CẢ thông báo là đã đọc
     */
    public function markAllAsRead(Request $request)
    {
        $memberId = $request->user()->_id;

        SystemNotification::where(function ($query) use ($memberId) {
            $query->where('recipient_id', $memberId)
                ->orWhere('type', 'system');
        })
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'status' => 'success',
            'message' => 'Đã đánh dấu tất cả là đã đọc'
        ]);
    }

    /**
     * 4. Xóa một thông báo
     */
    public function destroy(Request $request, $id)
    {
        $notification = SystemNotification::find($id);

        if ($notification) {
            $notification->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Đã xóa thông báo'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Không tìm thấy thông báo'
        ], 404);
    }
}
