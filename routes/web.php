<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetLogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MaintenanceScheduleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AssetController::class, 'dashboard'])->name('dashboard');

    Route::get('/assets', [AssetController::class, 'index'])->name('assets.index');
    Route::get('/assets/export-pdf', [AssetController::class, 'exportPdf'])->name('assets.export-pdf');
    Route::get('/assets/export-csv', [AssetController::class, 'exportCsv'])->name('assets.export-csv');
    Route::get('/assets/next-code/{prefix}', [AssetController::class, 'nextCode'])->name('assets.next-code');
    Route::get('/assets/{asset}/qr-code', [AssetController::class, 'qrCode'])->name('assets.qr-code');

    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('/transactions/export-pdf', [TransactionController::class, 'exportPdf'])->name('transactions.export-pdf');
    Route::get('/transactions/export-csv', [TransactionController::class, 'exportCsv'])->name('transactions.export-csv');

    Route::get('/mutasi', [AssetLogController::class, 'mutasi'])->name('mutasi.index');
    Route::get('/mutasi/export-pdf', [AssetLogController::class, 'mutasiExportPdf'])->name('mutasi.export-pdf');
    Route::get('/mutasi/export-csv', [AssetLogController::class, 'mutasiExportCsv'])->name('mutasi.export-csv');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('role:admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // Fitur operasional — semua role yang login (admin & staff) berhak akses,
    // bedanya cuma manajemen user (di atas) yang dikunci role admin saja.
    Route::get('/assets/create', [AssetController::class, 'create'])->name('assets.create');
    Route::post('/assets', [AssetController::class, 'store'])->name('assets.store');
    Route::get('/assets/{asset}/edit', [AssetController::class, 'edit'])->name('assets.edit');
    Route::put('/assets/{asset}', [AssetController::class, 'update'])->name('assets.update');
    Route::delete('/assets/bulk-destroy', [AssetController::class, 'bulkDestroy'])->name('assets.bulk-destroy');
    Route::delete('/assets/{asset}', [AssetController::class, 'destroy'])->name('assets.destroy');
    Route::delete('/assets/{asset}/foto', [AssetController::class, 'deleteFoto'])->name('assets.delete-foto');
    Route::post('/assets/{asset}/report-damage', [AssetController::class, 'reportDamage'])->name('assets.report-damage');
    Route::post('/assets/{asset}/report-lost', [AssetController::class, 'reportLost'])->name('assets.report-lost');
    Route::post('/assets/{asset}/mark-found', [AssetController::class, 'markFound'])->name('assets.mark-found');
    Route::post('/assets/{asset}/process-disposal', [AssetController::class, 'processDisposal'])->name('assets.process-disposal')->middleware('role:admin');
    Route::get('/assets/{asset}/label/pdf', [AssetController::class, 'labelPdf'])->name('assets.label-pdf');

    Route::post('/transactions/{transaction}/return', [TransactionController::class, 'returnItem'])->name('transactions.return');

    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/locations', [LocationController::class, 'index'])->name('locations.index');
    Route::get('/locations/create', [LocationController::class, 'create'])->name('locations.create');
    Route::post('/locations', [LocationController::class, 'store'])->name('locations.store');
    Route::get('/locations/{location}/edit', [LocationController::class, 'edit'])->name('locations.edit');
    Route::put('/locations/{location}', [LocationController::class, 'update'])->name('locations.update');
    Route::delete('/locations/{location}', [LocationController::class, 'destroy'])->name('locations.destroy');

    Route::get('/maintenance', [MaintenanceScheduleController::class, 'index'])->name('maintenance.index');
    Route::get('/maintenance/export-pdf', [MaintenanceScheduleController::class, 'exportPdf'])->name('maintenance.export-pdf');
    Route::get('/maintenance/export-csv', [MaintenanceScheduleController::class, 'exportCsv'])->name('maintenance.export-csv');
    Route::get('/maintenance/create', [MaintenanceScheduleController::class, 'create'])->name('maintenance.create');
    Route::post('/maintenance', [MaintenanceScheduleController::class, 'store'])->name('maintenance.store');
    Route::get('/maintenance/{maintenanceSchedule}/edit', [MaintenanceScheduleController::class, 'edit'])->name('maintenance.edit');
    Route::put('/maintenance/{maintenanceSchedule}', [MaintenanceScheduleController::class, 'update'])->name('maintenance.update');
    Route::patch('/maintenance/{maintenanceSchedule}/start', [MaintenanceScheduleController::class, 'start'])->name('maintenance.start');
    Route::get('/maintenance/{maintenanceSchedule}/complete', [MaintenanceScheduleController::class, 'completeForm'])->name('maintenance.complete-form');
    Route::put('/maintenance/{maintenanceSchedule}/complete', [MaintenanceScheduleController::class, 'complete'])->name('maintenance.complete');
    Route::patch('/maintenance/{maintenanceSchedule}/cancel', [MaintenanceScheduleController::class, 'cancel'])->name('maintenance.cancel');

    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

    Route::post('/assets/{asset}/logs', [AssetLogController::class, 'store'])->name('assets.logs.store');

    Route::get('/assets/{asset}', [AssetController::class, 'show'])->name('assets.show');
});

require __DIR__.'/auth.php';
