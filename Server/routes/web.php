<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// 1. The Root Page
Route::get('/', function () {
    return view('home');
});

// 2. GET Login Route
Route::get('/login', function () {
    // If already signed in, bypass the login
    if (Auth::check()) {
        return redirect('/');
    }
    // or return the home view
    return view('home');
})->name('login');

// 3. The POST Login Route
Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();

        return response()->json([
            'status' => 'success',
            'message' => 'Authenticated successfully.',
            'redirect' => '/'
        ]);
    }

    return response()->json([
        'status' => 'error',
        'message' => 'The provided credentials do not match our records.'
    ], 401);
});
