<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;

Route::middleware('guest')->group(function () {
    // Trang đăng nhập
    Route::get('login', [LoginController::class, 'showLoginForm'])
        ->name('login');
    
    // Xử lý đăng nhập
    Route::post('login', [LoginController::class, 'login']);
    
    // Trang đăng ký
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])
        ->name('register');
    
    // Xử lý đăng ký
    Route::post('register', [RegisterController::class, 'register']);
});

// Đăng xuất
Route::post('logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');