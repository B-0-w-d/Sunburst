<?php

use App\Http\Controllers\MemberController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

// 1. The public login portal intercept (When NOT logged in, they hit this)
Route::get('/login', function () {
    return view('login_portal'); // We will make this clear step next!
})->name('login');

// 2. PROTECTED ENTERPRISE ZONE (Must be logged in to see these)
Route::middleware(['auth'])->group(function () {

    // When logged in, hitting the root URL now safely renders your home welcome page!
    Route::get('/', function () {
        return view('home');
    });

    // The roster dashboard remains fully operational
    Route::get('/members', [MemberController::class, 'index'])->name('members.index');
});

// 3. Dev Mode Bypass Space
Route::middleware(['dev.mode'])->group(function () {
    Route::get('/dev-panel', function () {
        return "Welcome to the Developer Console Workspace";
    });
});
