<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes (Authentication)
Route::prefix('auth')->group(function () {
    Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login']);
    Route::post('/forgot-password', [App\Http\Controllers\Api\AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [App\Http\Controllers\Api\AuthController::class, 'resetPassword']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [App\Http\Controllers\Api\AuthController::class, 'logout']);
    });

    // Item Management - View: All authenticated, CRUD: Admin Gudang only
    Route::prefix('items')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\ItemController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\Api\ItemController::class, 'show']);
    });
    Route::prefix('items')->middleware('role:admin_gudang')->group(function () {
        Route::post('/', [App\Http\Controllers\Api\ItemController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\Api\ItemController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\ItemController::class, 'destroy']);
    });

    // Categories - View: All authenticated, CRUD: Admin Gudang only
    Route::prefix('categories')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\CategoryController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\Api\CategoryController::class, 'show']);
    });
    Route::prefix('categories')->middleware('role:admin_gudang')->group(function () {
        Route::post('/', [App\Http\Controllers\Api\CategoryController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\Api\CategoryController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\CategoryController::class, 'destroy']);
    });

    // Units - View: All authenticated, CRUD: Admin Gudang only
    Route::prefix('units')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\UnitController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\Api\UnitController::class, 'show']);
    });
    Route::prefix('units')->middleware('role:admin_gudang')->group(function () {
        Route::post('/', [App\Http\Controllers\Api\UnitController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\Api\UnitController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\UnitController::class, 'destroy']);
    });

    // Request Management - View: All authenticated, Create/Edit: Technician & Admin Gudang, Approve/Reject: SPV & Admin Gudang
    Route::prefix('requests')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\RequestController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\Api\RequestController::class, 'show']);
    });
    Route::prefix('requests')->middleware('role:technician,admin_gudang')->group(function () {
        Route::post('/', [App\Http\Controllers\Api\RequestController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\Api\RequestController::class, 'update']);
        Route::post('/{id}/submit', [App\Http\Controllers\Api\RequestController::class, 'submit']);
    });
    Route::prefix('requests')->middleware('role:spv,admin_gudang')->group(function () {
        Route::post('/{id}/approve', [App\Http\Controllers\Api\RequestController::class, 'approve']);
        Route::post('/{id}/reject', [App\Http\Controllers\Api\RequestController::class, 'reject']);
    });

    // Purchase Orders - View: Admin Gudang & SPV, CRUD: Admin Gudang only, Approve/Reject: Admin Gudang & SPV
    Route::prefix('purchase-orders')->middleware('role:admin_gudang,spv')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\PurchaseOrderController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\Api\PurchaseOrderController::class, 'show']);
        Route::post('/{id}/approve', [App\Http\Controllers\Api\PurchaseOrderController::class, 'approve']);
        Route::post('/{id}/cancel', [App\Http\Controllers\Api\PurchaseOrderController::class, 'cancel']);
    });
    Route::prefix('purchase-orders')->middleware('role:admin_gudang')->group(function () {
        Route::post('/', [App\Http\Controllers\Api\PurchaseOrderController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\Api\PurchaseOrderController::class, 'update']);
        Route::post('/{id}/submit', [App\Http\Controllers\Api\PurchaseOrderController::class, 'submit']);
        Route::post('/{id}/send-to-vendor', [App\Http\Controllers\Api\PurchaseOrderController::class, 'sendToVendor']);
        Route::post('/{id}/confirm', [App\Http\Controllers\Api\PurchaseOrderController::class, 'confirm']);
    });

    // Receivings - Admin Gudang only
    Route::prefix('receivings')->middleware('role:admin_gudang')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\ReceivingController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\ReceivingController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Api\ReceivingController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Api\ReceivingController::class, 'update']);
        Route::post('/{id}/return-item', [App\Http\Controllers\Api\ReceivingController::class, 'returnItem']);
    });

    // Inventory - Admin Gudang only
    Route::prefix('inventory')->middleware('role:admin_gudang')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\InventoryController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\Api\InventoryController::class, 'show']);
        Route::get('/{id}/movements', [App\Http\Controllers\Api\InventoryController::class, 'movements']);
    });

    // Stock Opnames - View: Admin Gudang & SPV, CRUD: Admin Gudang only, Approve/Adjustment: Admin Gudang & SPV
    Route::prefix('stock-opnames')->middleware('role:admin_gudang,spv')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\StockOpnameController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\Api\StockOpnameController::class, 'show']);
        Route::post('/{id}/approve', [App\Http\Controllers\Api\StockOpnameController::class, 'approve']);
        Route::post('/{id}/apply-adjustment', [App\Http\Controllers\Api\StockOpnameController::class, 'applyAdjustment']);
    });
    Route::prefix('stock-opnames')->middleware('role:admin_gudang')->group(function () {
        Route::post('/', [App\Http\Controllers\Api\StockOpnameController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\Api\StockOpnameController::class, 'update']);
        Route::post('/{id}/submit', [App\Http\Controllers\Api\StockOpnameController::class, 'submit']);
    });

    // Roles (for User Management) - Admin Gudang only
    Route::get('/roles', [App\Http\Controllers\Api\RoleController::class, 'index'])->middleware('role:admin_gudang');

    // User Management (Admin Gudang only)
    Route::prefix('users')->middleware('role:admin_gudang')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\UserController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\UserController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Api\UserController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Api\UserController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\UserController::class, 'destroy']);
        Route::post('/{id}/activate', [App\Http\Controllers\Api\UserController::class, 'activate']);
        Route::post('/{id}/deactivate', [App\Http\Controllers\Api\UserController::class, 'deactivate']);
    });
});
