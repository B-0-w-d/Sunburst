<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\KeyController;
use App\Http\Controllers\System\CalendarController;
use App\Http\Controllers\Member\MemberController;
use App\Http\Controllers\Member\NotificationController;

/*
|--------------------------------------------------------------------------
| Public API Routes
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);

/*
|--------------------------------------------------------------------------
| Protected API Routes (Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);

    // Profile Management
    Route::prefix('profile')->group(function () {
        Route::get('/', [MemberController::class, 'editProfile']);
        Route::put('/', [MemberController::class, 'updateProfile']);
    });

    // Key Management (Đặt trước các route dynamic của member)
    Route::post('/members/generate-key', [KeyController::class, 'generateKey']);

    // Member Management
    Route::prefix('members')->group(function () {
        Route::get('/', [MemberController::class, 'index']);
        Route::post('/', [MemberController::class, 'store']);
        Route::put('/{id}', [MemberController::class, 'update']);
        Route::delete('/{id}', [MemberController::class, 'destroy']);
    });

    // Calendar & Arrange Management
    Route::prefix('calendar')->group(function () {
        Route::get('/', [CalendarController::class, 'index']);                             // Lấy danh sách lịch / khảo sát
        Route::post('/', [CalendarController::class, 'store']);                             // Admin tạo lịch hoặc tạo khảo sát POLL
        Route::post('/{eventId}/availability', [CalendarController::class, 'submitAvailability']); // Thành viên điền lịch rảnh When2meet
        Route::get('/{eventId}/poll-report', [CalendarController::class, 'getPollReport']); // Admin xem báo cáo tổng hợp rảnh/bận
        Route::post('/{eventId}/confirm-poll', [CalendarController::class, 'confirmPoll']); // Admin chốt lịch từ khảo sát
        Route::delete('/{id}', [CalendarController::class, 'destroy']);                     // <--- THÊM DÒNG NÀY VÀO ĐÂY
    });

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::patch('/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::patch('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
    });
});
