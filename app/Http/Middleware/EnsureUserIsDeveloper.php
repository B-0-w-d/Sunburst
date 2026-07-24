<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsDeveloper
{
    // Kiểm tra quyền hạn truy cập hệ thống dành cho nhà phát triển/quản trị viên
    public function handle(Request $request, Closure $next): Response
    {
        // Cho phép bỏ qua kiểm tra quyền nếu đang ở môi trường local và cấu hình ALLOW_DEV_BYPASS bật true
        if (app()->environment('local') && env('ALLOW_DEV_BYPASS') === true) {
            return $next($request);
        }

        // Kiểm tra nếu chưa đăng nhập hoặc vai trò không phải là admin thì hủy yêu cầu và trả về lỗi 403
        if (!Auth::check() || strtolower(Auth::user()->role ?? '') !== 'admin') {
            abort(403, 'Unauthorized access to Dev System Controls.');
        }

        // Cho phép request tiếp tục đi tiếp nếu thỏa mãn điều kiện
        return $next($request);
    }
}
