<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DisposisiController;
use App\Http\Controllers\SppdController;
use App\Http\Controllers\SuratKeluarController;
use App\Http\Controllers\SuratMasukController;

// Middleware untuk check session
Route::middleware('web')->group(function () {
    // Redirect root to login
    Route::get('/', function () {
        if (session()->has('user')) {
            return redirect('/dashboard');
        }
        return redirect('/login');
    });

    // Login Routes
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Protected Routes (require session)
    Route::middleware('check.session')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Surat Masuk Routes
        Route::get('/surat-masuk', [SuratMasukController::class, 'index'])->name('surat-masuk');
        Route::post('/surat-masuk/store', [SuratMasukController::class, 'store'])->name('surat-masuk.store');
        Route::post('/surat-masuk/{id}/status', [SuratMasukController::class, 'updateStatus'])->name('surat-masuk.updateStatus');
        Route::get('/surat-masuk/data', [SuratMasukController::class, 'getData'])->name('surat-masuk.getData');
        Route::delete('/surat-masuk/{id}', [SuratMasukController::class, 'destroy'])->name('surat-masuk.destroy');

        Route::get('/surat-keluar', [SuratKeluarController::class, 'index'])->name('surat-keluar');
        Route::post('/surat-keluar/store', [SuratKeluarController::class, 'store'])->name('surat-keluar.store');
        Route::post('/surat-keluar/{id}/status', [SuratKeluarController::class, 'updateStatus'])->name('surat-keluar.updateStatus');

        Route::get('/sppd', [SppdController::class, 'index'])->name('sppd');
        Route::post('/sppd/store', [SppdController::class, 'store'])->name('sppd.store');
        Route::post('/sppd/{id}/status', [SppdController::class, 'updateStatus'])->name('sppd.updateStatus');

        Route::get('/disposisi', [DisposisiController::class, 'index'])->name('disposisi');
        Route::post('/disposisi/{type}/{id}/status', [DisposisiController::class, 'updateStatus'])->name('disposisi.updateStatus');

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});
