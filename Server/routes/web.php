<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\MemberController;

// 1. Public route for the login page
Route::get('/login', function () {
    return view('components.login');
})->name('login');

// 2. POST route for login processing (stays public)
Route::post('/login', [MemberController::class, 'login']);

// 3. Protected routes: Only accessible if authenticated
Route::middleware(['auth'])->group(function () {

    Route::get('/', function () {
        return view('home');
    });

    Route::get('/members', [MemberController::class, 'index'])->name('members.index');

    Route::post('/logout', [MemberController::class, 'logout']);

    // Debug routes (Keep protected for security)
    Route::get('/debug-auth', function () {
        return response()->json([
            'check' => Auth::check(),
            'user' => Auth::user(),
        ]);
    });
});
