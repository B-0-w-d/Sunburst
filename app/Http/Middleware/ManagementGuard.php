<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class ManagementGuard
{
    // Kiểm tra quyền hạn truy cập của người dùng thuộc cấp quản lý (management tier)
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\Member|null $user */
        $user = Auth::user();

        // Cho phép request tiếp tục nếu người dùng đã đăng nhập và đạt cấp quản lý
        if ($user && $user->isManagementTier()) {
            return $next($request);
        }

        // Trả về lỗi 403 dưới dạng JSON nếu request yêu cầu JSON, hoặc hủy yêu cầu với thông báo lỗi dành cho web
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Insufficient role clearance.'], 403);
        }

        abort(403, 'Only managers, presidents, or administrators can modify this space.');
    }
}
