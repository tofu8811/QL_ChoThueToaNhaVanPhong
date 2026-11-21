<?php

use App\Http\Controllers\Web\AdminWebController;
use App\Http\Controllers\Web\PortalWebController;
use Illuminate\Support\Facades\Route;

// ==========================================================================
// 1. PUBLIC PORTAL (Cổng thông tin công khai - Dành cho khách tìm thuê)
// ==========================================================================
Route::get('/', [PortalWebController::class, 'index'])->name('home');
Route::get('buildings/{slug}', [PortalWebController::class, 'showBuilding'])->name('buildings.show');
Route::get('quotes', [PortalWebController::class, 'quotePage'])->name('quote.page');
Route::redirect('quote', '/quotes');

// Tuyến đường Đăng nhập (Chuyển về trang chủ có Auth modal)
Route::get('login', fn () => redirect('/'))->name('login');

// ==========================================================================
// 2. ADMIN BMS (Bảng điều khiển & Vận hành nội bộ - Dành cho Admin/BQL/Sales)
// ==========================================================================
Route::get('admin', [AdminWebController::class, 'index'])->name('admin.index');
Route::get('admin/dashboard', [AdminWebController::class, 'index'])->name('admin.dashboard');

Route::prefix('admin')->group(function () {
    Route::get('contracts', [PortalWebController::class, 'contractsPage'])->name('contracts.page');
    Route::get('contracts/{id}/print', [PortalWebController::class, 'printContract'])->name('contracts.print');
    Route::get('tenants', [PortalWebController::class, 'tenantsPage'])->name('tenants.page');
    Route::get('leads', [PortalWebController::class, 'leadsPage'])->name('leads.page');
    Route::get('reports/overview-csv', [PortalWebController::class, 'exportDashboardCsv'])->name('admin.dashboard.export-csv');
});

// ==========================================================================
// 3. TENANT PORTAL (Cổng thông tin khách thuê B2B)
// ==========================================================================
Route::get('tenant/portal', [PortalWebController::class, 'tenantPortalPage'])->name('tenant-portal.page');
Route::redirect('tenant-portal', '/tenant/portal');

// ==========================================================================
// 4. SERVER-SIDE CALCULATION & EXPORT
// ==========================================================================
Route::get('api/quote-estimate', [PortalWebController::class, 'calculateQuoteEstimate'])->name('admin.quote.estimate');
Route::get('reports/quote-csv', [PortalWebController::class, 'exportQuoteCsv'])->name('admin.quote.export-csv');

// ==========================================================================
// 5. BACKWARD-COMPATIBILITY REDIRECTS (Link cũ tiếng Việt -> Link tiếng Anh mới)
// ==========================================================================
Route::get('toa-nha/{slug}', fn (string $slug) => redirect("/buildings/{$slug}", 301));
Route::redirect('nhan-bao-gia', '/quotes', 301);
Route::redirect('quan-ly-hop-dong', '/admin/contracts', 301);
Route::get('quan-ly-hop-dong/{id}/in-hop-dong', fn (string $id) => redirect("/admin/contracts/{$id}/print", 301));
Route::redirect('khach-thue', '/admin/tenants', 301);
Route::redirect('quan-ly-leads', '/admin/leads', 301);
Route::redirect('cong-khach-thue', '/tenant/portal', 301);
Route::redirect('bao-cao/tong-quan-csv', '/admin/reports/overview-csv', 301);
Route::redirect('bao-cao/bao-gia-csv', '/reports/quote-csv', 301);
