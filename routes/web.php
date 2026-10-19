<?php

use App\Http\Controllers\AssetActivityIntervalController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Telematics IoT Fleet Management
|--------------------------------------------------------------------------
*/

// 1. Primary Fleet Telematics Dashboard (Images 1 & 2)
Route::get('/', [DashboardController::class, 'showFleetOverview'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'showFleetOverview']);

// Dedicated partial endpoints for filters & refresh
Route::get('/dashboard/day-overview', [DashboardController::class, 'dayOverview'])->name('dashboard.day-overview');
Route::get('/dashboard/metrics', [DashboardController::class, 'metrics'])->name('dashboard.metrics');

// 2. Heavy Asset Management Resource Routes
Route::resource('assets', AssetController::class);

// 3. Asset Activity Timeline Intervals & IoT Ping Route
Route::post('/telematics/ping', [AssetActivityIntervalController::class, 'ping'])->name('telematics.ping');
Route::resource('activity-intervals', AssetActivityIntervalController::class);

// 4. User Profile Routes (Breeze defaults)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
