<?php

use App\Http\Controllers\BetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LotteryResultController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LotteryApiController;
use Illuminate\Support\Facades\Route;

// Trang chủ
Route::get('/', function () {
    return view('welcome');
});

// routes/web.php
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

     // Tổng kết ngày
    Route::get('/daily-summary', [DailySummaryController::class, 'index'])->name('daily-summary.index');
    Route::get('/daily-summary/customer/{customer}', [DailySummaryController::class, 'customer'])->name('daily-summary.customer');
    
    // Quản lý khách hàng (dành cho Agent)
    Route::middleware(['role:Agent'])->group(function () {
        Route::resource('customers', CustomerController::class);
        Route::post('/customers/{customer}/adjust-balance', [CustomerController::class, 'adjustBalance'])->name('customers.adjust-balance');
        Route::get('/customers/{customer}/bet', [CustomerController::class, 'betForCustomer'])->name('customers.bet');
        Route::post('/customers/{customer}/bet', [BetController::class, 'storeForCustomer'])->name('customers.bet.store');
    });
    
    // Quản lý cược (đối với Agent là xem các vé đã đặt)
    Route::middleware(['role:Agent'])->group(function () {
        Route::get('/bets', [BetController::class, 'index'])->name('bets.index');
        Route::get('/bets/create', [BetController::class, 'create'])->name('bets.create');
        Route::post('/bets', [BetController::class, 'store'])->name('bets.store');
        Route::post('/bets/parse', [BetController::class, 'parse'])->name('bets.parse');
    });
    
    // Kết quả xổ số
    Route::resource('lottery-results', LotteryResultController::class);
    Route::post('/lottery-results/{id}/process', [LotteryResultController::class, 'process'])->name('lottery-results.process');
    
    // Báo cáo
    Route::get('/reports/daily', [ReportController::class, 'daily'])->name('reports.daily');
    Route::get('/reports/weekly', [ReportController::class, 'weekly'])->name('reports.weekly');
    Route::get('/reports/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');
    Route::get('/reports/by-customer', [ReportController::class, 'byCustomer'])->name('reports.by-customer');
    
    // Quản lý người dùng (Admin)
    Route::middleware(['role:Admin'])->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('provinces', ProvinceController::class);
    });
    
    // Hồ sơ người dùng
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// require __DIR__.'/auth.php';