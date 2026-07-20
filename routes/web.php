<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\MonitoringController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->middleware('auth')->name('home');

// Temporary route to setup admin (Render Free Tier workaround)
Route::get('/setup-admin', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $user = \App\Models\User::updateOrCreate(
            ['email' => 'admin@tpa.com'],
            [
                'name' => 'Admin TPA',
                'password' => bcrypt('admin123'),
                'role' => 'admin',
            ]
        );
        return 'SUKSES! Akun berhasil dibuat. Silakan kembali ke halaman login dan gunakan email: ' . $user->email . ' dan password: admin123';
    } catch (\Exception $e) {
        return 'ERROR: ' . $e->getMessage();
    }
});

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
    // Notifikasi
    Route::get('notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('notifications/read-all', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('profile', [ProfileController::class, 'update'])->name('profile.update');

    // Monitoring
    Route::get('monitoring/working-hour', [MonitoringController::class, 'workingHour'])->name('monitoring.working_hour');
    Route::get('monitoring/fuel', [MonitoringController::class, 'fuel'])->name('monitoring.fuel');
    Route::get('monitoring/efficiency', [MonitoringController::class, 'efficiency'])->name('monitoring.efficiency');
    Route::get('api/monitoring/filter-options', [MonitoringController::class, 'getFilterOptions'])->name('api.monitoring.filter_options');
    Route::get('monitoring/export', [MonitoringController::class, 'export'])->name('monitoring.export');
    Route::get('monitoring/export-pdf', [MonitoringController::class, 'exportPdf'])->name('monitoring.export_pdf');

    // System Flow
    Route::get('monitoring/flow', function () {
        return view('monitoring.flow');
    })->name('monitoring.flow');

    // Fallback for old routes
    Route::get('monitoring', function () {
        return redirect()->route('monitoring.working_hour');
    })->name('monitoring.index');
    Route::get('monitoring/laporan', function () {
        return redirect()->route('monitoring.working_hour');
    })->name('monitoring.laporan');

    // Admin Routes
    Route::middleware('role:admin')->group(function () {
        // User Management
        Route::get('pengguna', [UserController::class, 'index'])->name('users.index');
        Route::post('pengguna/create', [UserController::class, 'store'])->name('users.store');
        Route::post('pengguna/edit/{user}', [UserController::class, 'update'])->name('users.update');
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
