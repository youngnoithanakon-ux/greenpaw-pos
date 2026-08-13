<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PlantBatchController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SystemConfigController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ---- Auth routes ----
require __DIR__.'/auth.php';

// ---- Authenticated routes (all logged-in users) ----
Route::middleware('auth')->group(function () {

    // Home / Menu หลัก
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // POS
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/add', [PosController::class, 'addToCart'])->name('pos.add');
    Route::get('/pos/remove/{id}', [PosController::class, 'removeFromCart'])->name('pos.remove');
    Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    Route::get('/pos/receipt/{sale}', [PosController::class, 'receipt'])->name('pos.receipt');

    // Dashboard (ภาพรวมยอดขาย)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ---- Admin-only routes ----
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Products
    Route::resource('products', ProductController::class);
	Route::patch('products/{product}/toggle', [ProductController::class, 'toggleStatus'])->name('products.toggle');

    // Categories
    Route::resource('categories', CategoryController::class)->only(['index', 'store', 'destroy']);

    // Users
    Route::resource('users', UserController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');

    // Sales
    Route::get('sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('sales/export-excel', [SaleController::class, 'exportExcel'])->name('sales.export-excel');
    Route::get('sales/{sale}/items', [SaleController::class, 'items'])->name('sales.items');
    Route::post('sales/{sale}/void', [SaleController::class, 'void'])->name('sales.void');

    // Stock
    Route::get('stock', [StockController::class, 'index'])->name('stock.index');
    Route::post('stock', [StockController::class, 'store'])->name('stock.store');
    Route::get('stock/log', [StockController::class, 'log'])->name('stock.log');

    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.export-excel');
    Route::get('reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export-pdf');

    // Plant Batches (ระบบจัดการการปลูกหญ้าแมว)
    Route::get('plant-batches',                           [PlantBatchController::class, 'index'])->name('plant-batches.index');
    Route::get('plant-batches/create',                    [PlantBatchController::class, 'create'])->name('plant-batches.create');
    Route::post('plant-batches',                          [PlantBatchController::class, 'store'])->name('plant-batches.store');
    Route::get('plant-batches/{plantBatch}',              [PlantBatchController::class, 'show'])->name('plant-batches.show');
    Route::post('plant-batches/{plantBatch}/harvest',     [PlantBatchController::class, 'harvest'])->name('plant-batches.harvest');
    Route::post('plant-batches/{plantBatch}/fail',        [PlantBatchController::class, 'fail'])->name('plant-batches.fail');

    // System Settings
    Route::get('settings',        [SystemConfigController::class, 'index'])->name('settings.index');
    Route::post('settings',       [SystemConfigController::class, 'update'])->name('settings.update');
    Route::post('settings/test-line', [SystemConfigController::class, 'testLine'])->name('settings.test-line');
});
