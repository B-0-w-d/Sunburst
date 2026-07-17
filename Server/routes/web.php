<?php

use App\Http\Controllers\MemberController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/members', [MemberController::class, 'index']);

// Login check rule
Route::middleware(['auth'])->group(function () {
    Route::get('/members', [MemberController::class, 'index'])->name('members.index');
});

// Dev Mode bypass route group
Route::middleware(['dev.mode'])->group(function () {
    Route::get('/dev-panel', function () {
        return "Welcome to the Developer Console Workspace";
    });
});
