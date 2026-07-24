<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Khởi tạo đối tượng Application và xác định thư mục gốc của project Laravel
return Application::configure(basePath: dirname(__DIR__))

    // Cấu hình các tệp định tuyến (routing) cho ứng dụng
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php', // Định tuyến cho các lệnh Artisan
        health: '/up',                                // Endpoint kiểm tra trạng thái sức khỏe ứng dụng (health check)
    )

    // Cấu hình các Middleware (tầng trung gian xử lý request)
    ->withMiddleware(function (Middleware $middleware) {
        // Cho phép API sử dụng session (hỗ trợ xác thực SPA / Sanctum stateful authentication)
        $middleware->statefulApi();

        // Đăng ký các biệt danh (alias) cho middleware để dễ dàng gọi tên trong các file định tuyến
        $middleware->alias([
            'dev.mode'   => \App\Http\Middleware\EnsureUserIsDeveloper::class, // Middleware kiểm tra quyền Developer/Admin
            'management' => \App\Http\Middleware\ManagementGuard::class,        // Middleware kiểm tra quyền Management tier
        ]);

        // Bổ sung các middleware vào nhóm API (hỗ trợ quản lý session và chia sẻ thông tin lỗi)
        $middleware->api(append: [
            \Illuminate\Session\Middleware\StartSession::class,        // Khởi động session cho các request API
            \Illuminate\View\Middleware\ShareErrorsFromSession::class, // Chia sẻ lỗi từ session sang các response/view nếu cần
        ]);
    })

    // Cấu hình cách xử lý các ngoại lệ và lỗi hệ thống (Exception Handling)
    ->withExceptions(function (Exceptions $exceptions) {})

    // Trả về instance hoàn chỉnh của ứng dụng Laravel
    ->create();
