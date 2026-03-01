<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ItemController;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\UnitController;
use App\Http\Controllers\Web\RequestController;
use App\Http\Controllers\Web\PurchaseOrderController;
use App\Http\Controllers\Web\ReceivingController;
use App\Http\Controllers\Web\InventoryController;
use App\Http\Controllers\Web\StockOpnameController;
use App\Http\Controllers\Web\UserController;
use Illuminate\Support\Facades\Route;

// Public routes (Authentication)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('forgot-password');
Route::get('/reset-password/{token?}', [AuthController::class, 'showResetPasswordForm'])->name('reset-password');
Route::post('/auth/set-session', [AuthController::class, 'setSession'])->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)->name('auth.set-session');
Route::post('/auth/logout', [AuthController::class, 'logout'])->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)->name('auth.logout');

// Protected routes - require authentication via Sanctum
Route::middleware('auth:sanctum')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // Items - View: All authenticated, CRUD: Admin Gudang only
    Route::get('/items', [ItemController::class, 'index'])->name('items.index');
    Route::get('/items/{id}', [ItemController::class, 'show'])->name('items.show');
    Route::middleware('role:admin_gudang')->group(function () {
        Route::get('/items/create', [ItemController::class, 'create'])->name('items.create');
        Route::get('/items/{id}/edit', [ItemController::class, 'edit'])->name('items.edit');
    });

    // Categories - View: All authenticated, CRUD: Admin Gudang only
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::middleware('role:admin_gudang')->group(function () {
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::get('/categories/{id}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    });

    // Units - View: All authenticated, CRUD: Admin Gudang only
    Route::get('/units', [UnitController::class, 'index'])->name('units.index');
    Route::middleware('role:admin_gudang')->group(function () {
        Route::get('/units/create', [UnitController::class, 'create'])->name('units.create');
        Route::get('/units/{id}/edit', [UnitController::class, 'edit'])->name('units.edit');
    });

    // Requests - View: All authenticated, Create/Edit: Technician & Admin Gudang
    Route::get('/requests', [RequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/{id}', [RequestController::class, 'show'])->name('requests.show');
    Route::middleware('role:technician,admin_gudang')->group(function () {
        Route::get('/requests/create', [RequestController::class, 'create'])->name('requests.create');
        Route::get('/requests/{id}/edit', [RequestController::class, 'edit'])->name('requests.edit');
    });

    // Purchase Orders - View: Admin Gudang & SPV, CRUD: Admin Gudang only
    Route::middleware('role:admin_gudang,spv')->group(function () {
        Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
        Route::get('/purchase-orders/{id}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
    });
    Route::middleware('role:admin_gudang')->group(function () {
        Route::get('/purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
        Route::get('/purchase-orders/{id}/edit', [PurchaseOrderController::class, 'edit'])->name('purchase-orders.edit');
    });

    // Receivings - Admin Gudang only
    Route::middleware('role:admin_gudang')->group(function () {
        Route::get('/receivings', [ReceivingController::class, 'index'])->name('receivings.index');
        Route::get('/receivings/create', [ReceivingController::class, 'create'])->name('receivings.create');
        Route::get('/receivings/{id}', [ReceivingController::class, 'show'])->name('receivings.show');
        Route::get('/receivings/{id}/edit', [ReceivingController::class, 'edit'])->name('receivings.edit');
    });

    // Inventory - Admin Gudang only
    Route::middleware('role:admin_gudang')->group(function () {
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/inventory/{id}', [InventoryController::class, 'show'])->name('inventory.show');
    });

    // Stock Opnames - View: Admin Gudang & SPV, CRUD: Admin Gudang only
    Route::middleware('role:admin_gudang,spv')->group(function () {
        Route::get('/stock-opnames', [StockOpnameController::class, 'index'])->name('stock-opnames.index');
        Route::get('/stock-opnames/{id}', [StockOpnameController::class, 'show'])->name('stock-opnames.show');
    });
    Route::middleware('role:admin_gudang')->group(function () {
        Route::get('/stock-opnames/create', [StockOpnameController::class, 'create'])->name('stock-opnames.create');
        Route::get('/stock-opnames/{id}/edit', [StockOpnameController::class, 'edit'])->name('stock-opnames.edit');
    });

    // Users (Admin Gudang only)
    Route::middleware('role:admin_gudang')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
        Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    });
});
