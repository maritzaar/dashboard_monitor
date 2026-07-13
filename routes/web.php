<?php

use App\Http\Controllers\ImportController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->middleware('auth')->name('home');

// Language Switcher
Route::get('lang/{locale}', function ($locale) {
    if (in_array($locale, ['id', 'en'])) {
        session()->put('locale', $locale);
    }
    return redirect()->back();
})->name('lang.switch');

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    // Profile
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('profile', [ProfileController::class, 'update'])->name('profile.update');

    // Monitoring
    Route::get('monitoring/working-hour', [MonitoringController::class, 'workingHour'])->name('monitoring.working_hour');
    Route::get('monitoring/working-hour/detail/{idAset}', [MonitoringController::class, 'workingHourDetail'])->name('monitoring.working_hour_detail');
    Route::get('monitoring/fuel', [MonitoringController::class, 'fuel'])->name('monitoring.fuel');
    Route::get('monitoring/fuel/detail/{idAset}', [MonitoringController::class, 'fuelDetail'])->name('monitoring.fuel_detail');
    Route::get('monitoring/export', [MonitoringController::class, 'export'])->name('monitoring.export');
    
    // System Flow
    Route::get('monitoring/flow', function () {
        return view('monitoring.flow');
    })->name('monitoring.flow');

    // Fallback for old routes
    Route::get('monitoring', function() { return redirect()->route('monitoring.working_hour'); })->name('monitoring.index');
    Route::get('monitoring/laporan', function() { return redirect()->route('monitoring.working_hour'); })->name('monitoring.laporan');

    // Admin Routes
    Route::middleware('role:admin')->group(function () {
        // User Management
        Route::get('pengguna', [UserController::class, 'index'])->name('users.index');
        Route::post('pengguna/create', [UserController::class, 'store'])->name('users.store');
        Route::post('pengguna/toggle/{user}', [UserController::class, 'toggleRole'])->name('users.toggle');
        Route::post('pengguna/reset-password/{user}', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::delete('pengguna/delete/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // Import
        Route::get('import', [ImportController::class, 'index'])->name('import.index');
        Route::post('import', [ImportController::class, 'import'])->name('import.upload');
        Route::get('import/clear', [ImportController::class, 'clearData'])->name('import.clear');
        Route::delete('import/delete/{id}', [ImportController::class, 'deleteLog'])->name('import.delete-log');
    });
});