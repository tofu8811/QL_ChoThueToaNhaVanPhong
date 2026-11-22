<?php

use App\Http\Controllers\Web\AccountantWebController;
use App\Http\Controllers\Web\AdminWebController;
use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\PmWebController;
use App\Http\Controllers\Web\PortalWebController;
use App\Http\Controllers\Web\SaleWebController;
use Illuminate\Support\Facades\Route;

// ==========================================================================
// 1. PUBLIC PORTAL (Cổng thông tin công khai - Dành cho khách tìm thuê)
// ==========================================================================
Route::get('/', [PortalWebController::class, 'index'])->name('home');
Route::get('buildings/{slug}', [PortalWebController::class, 'showBuilding'])->name('buildings.show');
Route::get('quotes', [PortalWebController::class, 'quotePage'])->name('quote.page');
Route::redirect('quote', '/quotes');

// Tuyến đường Đăng nhập / Đăng xuất tập trung
Route::get('login', fn () => redirect('/'))->name('login');
Route::get('workspace', [AuthWebController::class, 'redirectToWorkspace'])->name('workspace.redirect');
Route::post('logout', [AuthWebController::class, 'logout'])->name('web.logout');

// ==========================================================================
// 2. PHÂN HỆ QUẢN TRỊ TOÀN HỆ THỐNG (/admin - Super Admin & Admin)
// ==========================================================================
Route::prefix('admin')->middleware(['role.web:admin,super_admin'])->group(function () {
    Route::get('/', [AdminWebController::class, 'index'])->name('admin.index');
    Route::get('dashboard', [AdminWebController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('buildings', [AdminWebController::class, 'buildings'])->name('admin.buildings');
    Route::get('contracts', [AdminWebController::class, 'contracts'])->name('admin.contracts');
    Route::get('contracts/{id}/print', [PortalWebController::class, 'printContract'])->name('contracts.print');
    Route::get('tenants', [AdminWebController::class, 'tenants'])->name('admin.tenants');
    Route::get('tickets', [AdminWebController::class, 'tickets'])->name('admin.tickets');
    Route::get('billing', [AdminWebController::class, 'billing'])->name('admin.billing');
    Route::get('staff', [AdminWebController::class, 'staff'])->name('admin.staff');
    Route::get('mail-settings', [AdminWebController::class, 'mailSettings'])->name('admin.mail-settings');
    Route::get('leads', [AdminWebController::class, 'leads'])->name('admin.leads');
    Route::get('crm-leads', fn () => redirect()->route('admin.leads'))->name('leads.page');
    Route::get('reports/overview-csv', [PortalWebController::class, 'exportDashboardCsv'])->name('admin.dashboard.export-csv');
});

// ==========================================================================
// 3. PHÂN HỆ BAN QUẢN LÝ TÒA NHÀ (/pm - Property Manager)
// ==========================================================================
Route::prefix('pm')->middleware(['role.web:pm,property_manager,admin,super_admin'])->group(function () {
    Route::get('/', fn () => redirect()->route('pm.dashboard'));
    Route::get('dashboard', [PmWebController::class, 'dashboard'])->name('pm.dashboard');
    Route::get('buildings', [PmWebController::class, 'buildings'])->name('pm.buildings');
    Route::get('contracts', [PmWebController::class, 'contracts'])->name('pm.contracts');
    Route::get('tickets', [PmWebController::class, 'tickets'])->name('pm.tickets');
    Route::get('utilities', [PmWebController::class, 'utilities'])->name('pm.utilities');
});

// ==========================================================================
// 4. PHÂN HỆ KINH DOANH & CRM (/sales - Chuyên viên Sales)
// ==========================================================================
Route::prefix('sales')->middleware(['role.web:sales,sale,admin,super_admin'])->group(function () {
    Route::get('/', fn () => redirect()->route('sales.dashboard'));
    Route::get('dashboard', [SaleWebController::class, 'dashboard'])->name('sales.dashboard');
    Route::get('leads', [SaleWebController::class, 'leads'])->name('sales.leads');
    Route::get('quotes', [SaleWebController::class, 'quotes'])->name('sales.quotes');
    Route::get('available-units', [SaleWebController::class, 'availableUnits'])->name('sales.available-units');
});

// ==========================================================================
// 5. PHÂN HỆ KẾ TOÁN & CÔNG NỢ (/accountant - Kế toán tòa nhà)
// ==========================================================================
Route::prefix('accountant')->middleware(['role.web:accountant,admin,super_admin'])->group(function () {
    Route::get('/', fn () => redirect()->route('accountant.dashboard'));
    Route::get('dashboard', [AccountantWebController::class, 'dashboard'])->name('accountant.dashboard');
    Route::get('billing', [AccountantWebController::class, 'billing'])->name('accountant.billing');
    Route::get('contracts', [AccountantWebController::class, 'contracts'])->name('accountant.contracts');
    Route::get('tenants', [AccountantWebController::class, 'tenants'])->name('accountant.tenants');
});

// ==========================================================================
// 6. CỔNG KHÁCH THUÊ DOANH NGHIỆP (/tenant - B2B Tenant Portal)
// ==========================================================================
Route::prefix('tenant')->group(function () {
    Route::get('portal', [PortalWebController::class, 'tenantPortalPage'])->name('tenant-portal.page');
});
Route::redirect('tenant-portal', '/tenant/portal');

// ==========================================================================
// 7. SERVER-SIDE CALCULATION & EXPORT
// ==========================================================================
Route::get('api/quote-estimate', [PortalWebController::class, 'calculateQuoteEstimate'])->name('admin.quote.estimate');
Route::get('reports/quote-csv', [PortalWebController::class, 'exportQuoteCsv'])->name('admin.quote.export-csv');

// ==========================================================================
// 8. BACKWARD-COMPATIBILITY REDIRECTS (Link cũ -> Link phân hệ mới)
// ==========================================================================
Route::get('toa-nha/{slug}', fn (string $slug) => redirect("/buildings/{$slug}", 301));
Route::redirect('nhan-bao-gia', '/quotes', 301);
Route::get('quan-ly-hop-dong', [PortalWebController::class, 'contractsPage'])->name('contracts.page');
Route::get('quan-ly-hop-dong/{id}/in-hop-dong', fn (string $id) => redirect("/admin/contracts/{$id}/print", 301));
Route::get('khach-thue', [PortalWebController::class, 'tenantsPage'])->name('tenants.page');
Route::redirect('quan-ly-leads', '/sales/leads', 301);
Route::redirect('cong-khach-thue', '/tenant/portal', 301);
Route::redirect('bao-cao/tong-quan-csv', '/admin/reports/overview-csv', 301);
Route::redirect('bao-cao/bao-gia-csv', '/reports/quote-csv', 301);
