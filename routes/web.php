<?php

use App\Http\Controllers\Web\PortalWebController;
use Illuminate\Support\Facades\Route;

// Tuyến đường Công khai (Public Portal)
Route::get('/', [PortalWebController::class, 'index'])->name('home');
Route::get('toa-nha/{slug}', [PortalWebController::class, 'showBuilding'])->name('buildings.show');
Route::get('nhan-bao-gia', [PortalWebController::class, 'quotePage'])->name('quote.page');

// Tuyến đường Đăng nhập
Route::get('login', fn () => redirect('/admin/login'))->name('login');

// Tuyến đường Quản trị Nội bộ (Yêu cầu nhân viên đăng nhập)
Route::middleware(['web', 'auth:admin,web'])->group(function () {
    Route::get('quan-ly-hop-dong', [PortalWebController::class, 'contractsPage'])->name('contracts.page');
    Route::get('quan-ly-hop-dong/{id}/in-hop-dong', [PortalWebController::class, 'printContract'])->name('contracts.print');
    Route::get('khach-thue', [PortalWebController::class, 'tenantsPage'])->name('tenants.page');
    Route::get('quan-ly-leads', [PortalWebController::class, 'leadsPage'])->name('leads.page');
});

// Cổng thông tin Khách thuê
Route::get('cong-khach-thue', [PortalWebController::class, 'tenantPortalPage'])->name('tenant-portal.page');
