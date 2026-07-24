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
        // Lấy ID của thành viên hiện tại từ request
        $memberId = $request->user()->_id;

        // Truy vấn lấy danh sách thông báo (thuộc về user hoặc là thông báo hệ thống chung), sắp xếp mới nhất lên đầu
        $notifications = SystemNotification::where(function ($query) use ($memberId) {
            $query->where('recipient_id', $memberId)
                ->orWhere('type', 'system');
        })
            ->orderBy('created_at', 'desc')
            ->get();

        // Đếm số lượng thông báo chưa đọc (read_at là null) phục vụ hiển thị badge thông báo
        $unreadCount = SystemNotification::where(function ($query) use ($memberId) {
            $query->where('recipient_id', $memberId)
                ->orWhere('type', 'system');
        })
            ->whereNull('read_at')
            ->count();

        // Trả về dữ liệu JSON chứa danh sách và số lượng chưa đọc
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
        // Tìm thông báo theo ID
        $notification = SystemNotification::find($id);

        // Nếu tìm thấy, cập nhật thời điểm đọc (read_at) là thời gian hiện tại
        if ($notification) {
            $notification->update(['read_at' => now()]);
            return response()->json([
                'status' => 'success',
                'message' => 'Đã đánh dấu là đã đọc'
            ]);
        }

        // Trả về lỗi 404 nếu không tìm thấy thông báo
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
        // Lấy ID thành viên hiện tại
        $memberId = $request->user()->_id;

        // Cập nhật tất cả thông báo chưa đọc của user này thành đã đọc
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
        // Tìm thông báo theo ID và thực hiện xóa
        $notification = SystemNotification::find($id);

        if ($notification) {
            $notification->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Đã xóa thông báo'
            ]);
        }

        // Trả về lỗi 404 nếu không tìm thấy bản ghi thông báo
        return response()->json([
            'status' => 'error',
            'message' => 'Không tìm thấy thông báo'
        ], 404);
    }
}
