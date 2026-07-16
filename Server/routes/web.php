<?php
use App\Http\Controllers\MemberController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () { return view('home');});

Route::get('/members', [MemberController::class, 'index']);
