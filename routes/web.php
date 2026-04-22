<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
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
        Route::get('/dashboard', function () {
            return view('dashboard');
        })->name('dashboard');

        // Surat Masuk Routes
        Route::get('/surat-masuk', [SuratMasukController::class, 'index'])->name('surat-masuk');
        Route::post('/surat-masuk/store', [SuratMasukController::class, 'store'])->name('surat-masuk.store');
        Route::post('/surat-masuk/{id}/status', [SuratMasukController::class, 'updateStatus'])->name('surat-masuk.updateStatus');
        Route::get('/surat-masuk/data', [SuratMasukController::class, 'getData'])->name('surat-masuk.getData');
        Route::delete('/surat-masuk/{id}', [SuratMasukController::class, 'destroy'])->name('surat-masuk.destroy');

        Route::get('/surat-keluar', function () {
            return view('surat-keluar');
        })->name('surat-keluar');

        Route::get('/sppd', function () {
            return view('sppd');
        })->name('sppd');

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});
