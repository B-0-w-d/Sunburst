<?php

use App\Http\Controllers\MemberController;
use Illuminate\Support\Facades\Route;

// Standard API routes for Member operations
Route::get('/members', [MemberController::class, 'index']);
Route::post('/members', [MemberController::class, 'store']);
Route::put('/members/{id}', [MemberController::class, 'update']);
Route::delete('/members/{id}', [MemberController::class, 'destroy']);
