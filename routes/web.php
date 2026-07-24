<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Member\MemberController;

Route::get('/', function () {
    return view('home');
});
// 1. Guest Routes: Dành cho khách (chưa đăng nhập)
Route::middleware(['guest'])->group(function () {

    // Route hiển thị giao diện đăng nhập
    Route::get('/login', function () {
        return view('login');
    })->name('login');

    // Xử lý logic đăng nhập qua POST
    Route::post('/login', [AuthController::class, 'login']);

    // Route hiển thị giao diện đăng ký
    Route::get('/register', function () {
        return view('register');
    })->name('register');

    // Xử lý logic đăng ký qua POST
    Route::post('/register', [RegisterController::class, 'register']);
});

// 2. Protected Routes: Dành cho thành viên đã xác thực (đăng nhập)
Route::middleware(['auth'])->group(function () {

    // Trang chủ sau khi đăng nhập
    Route::get('/', function () {
        return view('home');
    });

    // Danh sách thành viên
    Route::get('/members', [MemberController::class, 'index'])->name('members.index');

    // Đăng xuất (Dùng AuthController thay vì MemberController)
    Route::post('/logout', [AuthController::class, 'logout']);

    // Quản lý hồ sơ cá nhân
    Route::get('/profile', [MemberController::class, 'editProfile'])->name('profile.edit');
    Route::put('/profile/update', [MemberController::class, 'updateProfile'])->name('profile.update');

    // Thêm nhóm route chỉ dành cho cấp cao
    Route::middleware(['management'])->group(function () {
        // Chức năng thêm mới thành viên
        Route::post('/members/store', [MemberController::class, 'store']);
        // Chức năng xóa thành viên
        Route::delete('/members/{id}', [MemberController::class, 'destroy']);
    });
});
