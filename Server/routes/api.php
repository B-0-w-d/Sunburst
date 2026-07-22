<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\KeyController;
use App\Http\Controllers\Member\MemberController;
use App\Http\Controllers\Member\NotificationController;

// 1. Routes công khai (Không cần đăng nhập)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);

// 2. Routes cần đăng nhập (Sử dụng Sanctum cho API)
Route::middleware(['auth:sanctum'])->group(function () {

    // Auth - Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Member Management
    Route::get('/members', [MemberController::class, 'index']);
    Route::post('/members', [MemberController::class, 'store']);
    Route::put('/members/{id}', [MemberController::class, 'update']);
    Route::delete('/members/{id}', [MemberController::class, 'destroy']);

    // Key Management
    Route::post('/members/generate-key', [KeyController::class, 'generateKey']);

    // Profile Management
    Route::get('/profile', [MemberController::class, 'editProfile']);
    Route::put('/profile', [MemberController::class, 'updateProfile']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
});
