<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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

        Route::get('/surat-masuk', function () {
            return view('surat-masuk');
        })->name('surat-masuk');

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});
