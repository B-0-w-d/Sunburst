<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Khởi tạo đối tượng Application và xác định thư mục gốc của project
return Application::configure(basePath: dirname(__DIR__))

    // Cấu hình các file định tuyến (routing)
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php', // Định tuyến cho lệnh Artisan
        health: '/up',                            // Endpoint kiểm tra trạng thái ứng dụng
    )

    // Cấu hình Middleware (trung gian)
    ->withMiddleware(function (Middleware $middleware) {
        // Cho phép API sử dụng session (giúp xác thực qua Sanctum hoặc session-based auth)
        $middleware->statefulApi();

        // Đăng ký các biệt danh (alias) cho middleware để dễ gọi trong routes
        $middleware->alias([
            'dev.mode'   => \App\Http\Middleware\EnsureUserIsDeveloper::class, // Middleware kiểm tra quyền Developer
            'management' => \App\Http\Middleware\ManagementGuard::class,        // Middleware kiểm tra quyền Management
        ]);

        // Thêm các middleware vào nhóm API (thường dùng để hỗ trợ session/error messages cho API)
        $middleware->api(append: [
            \Illuminate\Session\Middleware\StartSession::class,        // Khởi động session cho API
            \Illuminate\View\Middleware\ShareErrorsFromSession::class, // Chia sẻ lỗi từ session sang view
        ]);
    })

    // Cấu hình cách xử lý các ngoại lệ (Exception Handling)
    ->withExceptions(function (Exceptions $exceptions) {})


    ->create();
