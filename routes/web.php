<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Member\MemberController;

/*
|--------------------------------------------------------------------------
| 1. Guest Routes (Dành cho khách - chưa đăng nhập)
|--------------------------------------------------------------------------
*/

Route::middleware(['guest'])->group(function () {

    // Giao diện Đăng nhập
    Route::get('/login', function () {
        return view('login');
    })->name('login');

    // Xử lý Đăng nhập AJAX
    Route::post('/login', [AuthController::class, 'login']);

    // Giao diện Đăng ký (3 bước Slide)
    Route::get('/register', function () {
        return view('register');
    })->name('register');

    // Xử lý Đăng ký AJAX
    Route::post('/register', [RegisterController::class, 'register']);
});

/*
|--------------------------------------------------------------------------
| 2. Protected Routes (Dành cho thành viên đã xác thực)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Trang chủ Dashboard sau khi đăng nhập
    Route::get('/', function () {
        return view('home');
    })->name('home');

    // Danh sách thành viên
    Route::get('/members', [MemberController::class, 'index'])->name('members.index');

    // Đăng xuất
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Hồ sơ cá nhân
    Route::get('/profile', [MemberController::class, 'editProfile'])->name('profile.edit');
    Route::put('/profile/update', [MemberController::class, 'updateProfile'])->name('profile.update');

    // Dành riêng cho Ban Quản Lý (Management)
    Route::middleware(['management'])->group(function () {
        Route::post('/members/store', [MemberController::class, 'store'])->name('members.store');
        Route::delete('/members/{id}', [MemberController::class, 'destroy'])->name('members.destroy');
    });
});
