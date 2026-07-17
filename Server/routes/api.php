<?php

use App\Models\Member;
use App\Http\Controllers\MemberController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;


Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    // 1. Find the member in MongoDB
    $member = Member::where('email', $request->email)->first();

    // 2. Explicitly verify the password hash manually
    if (!$member || !Hash::check($request->password, $member->password)) {
        return response()->json([
            'status' => 'error',
            'message' => 'The provided credentials do not match our records.'
        ], 401);
    }

    // 3. Generate a stateless token using Sanctum
    $token = $member->createToken('auth_token')->plainTextToken;

    return response()->json([
        'status' => 'success',
        'message' => 'Authenticated successfully.',
        'access_token' => $token,
        'token_type' => 'Bearer',
    ]);
});

Route::middleware(['auth:web'])->group(function () {
    Route::get('/members', [MemberController::class, 'index']);
    Route::post('/members', [MemberController::class, 'store']);
    Route::put('/members/{id}', [MemberController::class, 'update']);
    Route::delete('/members/{id}', [MemberController::class, 'destroy']);
});
