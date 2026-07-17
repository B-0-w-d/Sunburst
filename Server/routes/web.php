<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MemberController;

// 1. Guest Routes: Accessible only when NOT logged in
Route::middleware(['guest'])->group(function () {
    Route::get('/login', function () {
        return view('components.login');
    })->name('login');

    // Only define this once!
    Route::post('/login', [MemberController::class, 'login']);
});

// 2. Protected Routes: Accessible only after authentication
Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return view('home');
    });

    Route::get('/members', [MemberController::class, 'index'])->name('members.index');
    Route::post('/logout', [MemberController::class, 'logout']);
});
