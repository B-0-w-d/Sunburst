<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use App\Models\PersonalAccessToken;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Đăng ký các dịch vụ của ứng dụng (Register any application services).
     */
    public function register(): void
    {
        //
    }

    /**
     * Khởi động các dịch vụ của ứng dụng (Bootstrap any application services).
     */
    public function boot(): void
    {
        // Cấu hình bắt buộc Laravel Sanctum sử dụng model PersonalAccessToken tương thích với MongoDB
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }
}
