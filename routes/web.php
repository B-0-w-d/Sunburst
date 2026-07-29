<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Member\MemberController;

/*
|--------------------------------------------------------------------------
| Guest Routes (Chưa đăng nhập)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    // Đăng nhập
    Route::get('/login', fn() => view('management-site/login'))->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Đăng ký (3 bước Slide)
    Route::get('/register', fn() => view('management-site/register'))->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Đã đăng nhập)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Dashboard & Auth
    Route::get('/', fn() => view('management-site/home'))->name('home');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Profile
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [MemberController::class, 'editProfile'])->name('edit');
        Route::put('/update', [MemberController::class, 'updateProfile'])->name('update');
    });

    // Calendar & When2meet View
    Route::get('/calendar', function () {
        return view('management-site.calendar');
    })->name('calendar');

    // Members (User thông thường có thể xem)
    Route::get('/members', [MemberController::class, 'index'])->name('members.index');

    // Management Routes (Dành riêng cho Ban Quản Lý)
    Route::middleware('management')->prefix('members')->name('members.')->group(function () {
        Route::post('/store', [MemberController::class, 'store'])->name('store');
        Route::delete('/{id}', [MemberController::class, 'destroy'])->name('destroy');
    });
});
