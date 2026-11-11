<?php

use App\Http\Controllers\Api\V1\BuildingApiController;
use App\Http\Controllers\Api\V1\ProvinceApiController;
use App\Http\Controllers\Api\V1\UnitApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Hệ Thống Quản Lý Và Cho Thuê Tòa Nhà Văn Phòng
|--------------------------------------------------------------------------
|
| Định tuyến RESTful API chuẩn hóa theo phiên bản (v1).
| Tự động áp dụng GlobalExceptionMiddleware và ForceJsonResponseMiddleware.
|
*/

Route::prefix('v1')->group(function () {

    // =========================================================================
    // MODULE 1: DANH MỤC BẤT ĐỘNG SẢN & PUBLIC PORTAL (CÔNG KHAI)
    // =========================================================================
    // 1. Địa giới hành chính
    Route::get('provinces', [ProvinceApiController::class, 'index'])->name('v1.provinces.index');
    Route::get('provinces/{code}', [ProvinceApiController::class, 'show'])->name('v1.provinces.show');

    // 2. Tra cứu Tòa nhà công khai (Public Portal)
    Route::get('portal/buildings', [BuildingApiController::class, 'portalIndex'])->name('v1.portal.buildings.index');
    Route::get('portal/buildings/{slug}', [BuildingApiController::class, 'portalShow'])->name('v1.portal.buildings.show');

    // 3. Tra cứu Mặt bằng công khai (Public Portal)
    Route::get('portal/units', [UnitApiController::class, 'portalIndex'])->name('v1.portal.units.index');
    Route::get('portal/units/{id}', [UnitApiController::class, 'portalShow'])->name('v1.portal.units.show');

    // =========================================================================
    // MODULE 1: QUẢN TRỊ TÒA NHÀ & MẶT BẰNG (ADMIN & BQL)
    // =========================================================================
    Route::prefix('admin')->group(function () {
        // Quản lý Tòa nhà & Thư viện hình ảnh
        Route::get('buildings', [BuildingApiController::class, 'adminIndex'])->name('v1.admin.buildings.index');
        Route::post('buildings', [BuildingApiController::class, 'store'])->name('v1.admin.buildings.store');
        Route::get('buildings/{id}', [BuildingApiController::class, 'show'])->name('v1.admin.buildings.show');
        Route::put('buildings/{id}', [BuildingApiController::class, 'update'])->name('v1.admin.buildings.update');
        Route::delete('buildings/{id}', [BuildingApiController::class, 'destroy'])->name('v1.admin.buildings.destroy');

        Route::post('buildings/{id}/images', [BuildingApiController::class, 'uploadImages'])->name('v1.admin.buildings.images.store');
        Route::delete('buildings/{id}/images/{imageId}', [BuildingApiController::class, 'deleteImage'])->name('v1.admin.buildings.images.destroy');
        Route::patch('buildings/{id}/images/{imageId}/set-primary', [BuildingApiController::class, 'setPrimaryImage'])->name('v1.admin.buildings.images.primary');

        // Quản lý Mặt bằng sàn & Layout
        Route::get('units', [UnitApiController::class, 'adminIndex'])->name('v1.admin.units.index');
        Route::post('units', [UnitApiController::class, 'store'])->name('v1.admin.units.store');
        Route::get('units/{id}', [UnitApiController::class, 'show'])->name('v1.admin.units.show');
        Route::put('units/{id}', [UnitApiController::class, 'update'])->name('v1.admin.units.update');
        Route::delete('units/{id}', [UnitApiController::class, 'destroy'])->name('v1.admin.units.destroy');

        Route::patch('units/{id}/status', [UnitApiController::class, 'updateStatus'])->name('v1.admin.units.status');
        Route::post('units/{id}/images', [UnitApiController::class, 'uploadImages'])->name('v1.admin.units.images.store');
        Route::delete('units/{id}/images/{imageId}', [UnitApiController::class, 'deleteImage'])->name('v1.admin.units.images.destroy');
    });

});
