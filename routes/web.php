<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArchiveFileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DisposisiController;
use App\Http\Controllers\ManajemenSuratController;
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
    Route::get('/surat-masuk/share/{token}', [SuratMasukController::class, 'showDepartmentPortal'])->name('surat-masuk.share');
    Route::post('/surat-masuk/share/{token}', [SuratMasukController::class, 'updateDepartmentReceipt'])->name('surat-masuk.share.update');
    Route::get('/surat-masuk/share/{token}/file', [ArchiveFileController::class, 'shareOpen'])->name('surat-masuk.share.file');
    Route::get('/surat-masuk/share/{token}/preview', [ArchiveFileController::class, 'sharePreview'])->name('surat-masuk.share.preview');
    Route::get('/surat-masuk/share/{token}/download', [ArchiveFileController::class, 'shareDownload'])->name('surat-masuk.share.download');
    Route::get('/surat-keluar/share/{token}', [SuratKeluarController::class, 'showSharePortal'])->name('surat-keluar.share');
    Route::get('/surat-keluar/share/{token}/file', [ArchiveFileController::class, 'shareOutgoingOpen'])->name('surat-keluar.share.file');
    Route::get('/surat-keluar/share/{token}/preview', [ArchiveFileController::class, 'shareOutgoingPreview'])->name('surat-keluar.share.preview');
    Route::get('/surat-keluar/share/{token}/download', [ArchiveFileController::class, 'shareOutgoingDownload'])->name('surat-keluar.share.download');

    // Protected Routes (require session)
    Route::middleware('check.session')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/manajemen-surat', [ManajemenSuratController::class, 'index'])->name('manajemen-surat');
        Route::get('/arsip/{type}/{id}/open', [ArchiveFileController::class, 'open'])->name('archive.open');
        Route::get('/arsip/{type}/{id}/preview', [ArchiveFileController::class, 'preview'])->name('archive.preview');
        Route::get('/arsip/{type}/{id}/download', [ArchiveFileController::class, 'download'])->name('archive.download');

        Route::middleware('role:admin')->group(function () {
            Route::get('/surat-masuk', [SuratMasukController::class, 'index'])->name('surat-masuk');
            Route::post('/surat-masuk/store', [SuratMasukController::class, 'store'])->name('surat-masuk.store');
            Route::post('/surat-masuk/{id}/status', [SuratMasukController::class, 'updateStatus'])->name('surat-masuk.updateStatus');
            Route::get('/surat-masuk/data', [SuratMasukController::class, 'getData'])->name('surat-masuk.getData');
            Route::delete('/surat-masuk/{id}', [SuratMasukController::class, 'destroy'])->name('surat-masuk.destroy');

            Route::get('/surat-keluar', [SuratKeluarController::class, 'index'])->name('surat-keluar');
            Route::post('/surat-keluar/store', [SuratKeluarController::class, 'store'])->name('surat-keluar.store');
            Route::post('/surat-keluar/store-generated', [SuratKeluarController::class, 'storeGenerated'])->name('surat-keluar.storeGenerated');
            Route::get('/surat-keluar/{id}/docx', [SuratKeluarController::class, 'downloadDocx'])->name('surat-keluar.downloadDocx');
            Route::delete('/surat-keluar/{id}', [SuratKeluarController::class, 'destroy'])->name('surat-keluar.destroy');

            Route::get('/sppd', [SppdController::class, 'index'])->name('sppd');
            Route::post('/sppd/store', [SppdController::class, 'store'])->name('sppd.store');
            Route::post('/sppd/store-generated', [SppdController::class, 'storeGenerated'])->name('sppd.storeGenerated');
            Route::post('/sppd/{id}/status', [SppdController::class, 'updateStatus'])->name('sppd.updateStatus');
            Route::get('/sppd/{id}/docx', [SppdController::class, 'downloadDocx'])->name('sppd.downloadDocx');
            Route::delete('/sppd/{id}', [SppdController::class, 'destroy'])->name('sppd.destroy');
        });

        Route::middleware('role:kepala_sekolah')->group(function () {
            Route::get('/disposisi', [DisposisiController::class, 'index'])->name('disposisi');
            Route::post('/disposisi/{type}/{id}/status', [DisposisiController::class, 'updateStatus'])->name('disposisi.updateStatus');
        });

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});
