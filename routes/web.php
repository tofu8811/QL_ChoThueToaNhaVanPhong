<?php

use App\Http\Controllers\Web\PortalWebController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PortalWebController::class, 'index'])->name('home');
Route::get('toa-nha/{slug}', [PortalWebController::class, 'showBuilding'])->name('buildings.show');
Route::get('nhan-bao-gia', [PortalWebController::class, 'quotePage'])->name('quote.page');
Route::get('quan-ly-hop-dong', [PortalWebController::class, 'contractsPage'])->name('contracts.page');
Route::get('quan-ly-hop-dong/{id}/in-hop-dong', [PortalWebController::class, 'printContract'])->name('contracts.print');
Route::get('khach-thue', [PortalWebController::class, 'tenantsPage'])->name('tenants.page');
Route::get('quan-ly-leads', [PortalWebController::class, 'leadsPage'])->name('leads.page');
Route::get('cong-khach-thue', [PortalWebController::class, 'tenantPortalPage'])->name('tenant-portal.page');

// Các endpoint tính toán & kết xuất báo cáo từ Backend (Server-side Calculation & Export)
Route::get('bao-cao/tong-quan-csv', [PortalWebController::class, 'exportDashboardCsv'])->name('admin.dashboard.export-csv');
Route::get('api/quote-estimate', [PortalWebController::class, 'calculateQuoteEstimate'])->name('admin.quote.estimate');
Route::get('bao-cao/bao-gia-csv', [PortalWebController::class, 'exportQuoteCsv'])->name('admin.quote.export-csv');
